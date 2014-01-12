<?php

App::import('Sanitize');
class PlayersController extends AppController {
    var $name = 'Players';
	var $helpers = array('Html', 'Form', 'Session', 'Javascript', 'TabDisplay');
	var $components = array('Recaptcha','Email'); 
	var $belongsTo = array('Game');
	var $hasMany = array('Character');
	var $player = array( );
			
    function beforeFilter() {
	$this->Auth->allow( 'signup', 'forgot', 'logout', 'view', 'confirm', 'send' );
	parent::beforeFilter();   
        $this->Recaptcha->publickey = "6Ldb3wkAAAAAAFfHSbY2EvzTagFSbb0hhKGCuM0h";
        $this->Recaptcha->privatekey = "6Ldb3wkAAAAAALnxifQJnCND9NRJU4cz54zLAKcx";
    }
    

    function login() { 
        // handled by Auth
        if( $this->player ) {
            $this->redirect( 'view' );
        }
    }
   
    function logout() {
        $this->Session->setFlash( 'You have been logged out' );
	    $this->redirect($this->Auth->logout());
    }
   
    private function generate_hash() {
        return md5(uniqid(mt_rand(), true));
    }
    

    function signup() {
        if( $this->player ) {
            $this->redirect( 'view' );
        }
        
        if(! empty( $this->data ) ) {
            if(! $this->Recaptcha->Valid( $this->params['form'] ) ) {
                $this->Session->setFlash( 'Sorry!  You\'ve entered an invalid captcha.', 'default', array( 'class'=>'error' ) );
            } elseif(! $this->Player->save( $this->data ) ) {
               $this->set( 'errors', $this->Player->invalidFields() );
            } else {
                $data = Set::extract( 'Player', $this->data );
                $data['id'] = $this->Player->id;
                $this->generateAndSendConfirm( $data );
                
                // log in and redirect
                $this->data['Player']['password'] = md5( $this->data['Player']['password'] );
                $login['Players'] = $this->data['Player'];
                $this->Auth->login($login);                
                $this->Session->setFlash( 'Welcome!  Please check your email to verify your account', 'default', array( 'class'=>'message' ) );
                $this->redirect( 'view' );
            }
        }

    }

    private function generateAndSendConfirm( $player ) {
        $data['verify_hash'] = $this->generate_hash();
        
        $this->Player->set( 'id', $player['id'] );
        $this->Player->set( 'verify_hash', md5( $data['verify_hash'] . 'confirm' ) );
        $this->Player->save( );
        
        $data['url'] = Configure::read( 'Info.url' );
        $data['login'] = $player['email'];
        $this->set( 'data', $data );
        $this->Email->template = 'email/registration';
        $this->Email->to = $player['email'];
        $this->Email->subject = 'Welcome to End of Seasons';
        return $this->Email->send();
    }
    
    private function generateAndSendForgot( $player ) {
        $data['verify_hash'] = $this->generate_hash();
        
        $this->Player->set( 'id', $player['id'] );
        $this->Player->set( 'verify_hash', md5( $data['verify_hash'] . 'forgot' ) );
        $this->Player->save( );
        
        $data['url'] = Configure::read( 'Info.url' );
        $data['login'] = $player['email'];
        $this->set( 'data', $data );
        $this->Email->template = 'email/forgot';
        $this->Email->to = $player['email'];
        $this->Email->subject = 'Forgot password';
        return $this->Email->send();
    }
    
    function send() {
        $action = $this->params['pass'][0];
        if( $this->player ) { 
            if( $action == 'forgot' ) {
                $this->Session->setFlash( "Since you're already logged in, you can change your password here on the edit page" );
                $this->redirect( 'edit' );
            } elseif( $this->player['is_confirmed'] ) {
                $this->Player->set( 'id', $this->player['id'] );
                $this->Player->set( 'verify_hash', '' );
                $this->Player->save();
                $this->Session->setFlash( 'You have already confirmed your account.' );
                $this->redirect( 'view' );
            } else {
                $this->generateAndSendConfirm( $this->player );
                $this->Session->setFlash( 'The confirmation has been resent to ' . $this->player['email'] );
                $this->redirect( 'view' );                            
            }
        } elseif(! empty( $this->data ) ) {
            $email = Set::extract( '/Player/email', $this->data );
            $email = Sanitize::clean( $email[0] );
            //TODO: CAPTCHA CHECK
            $player_list = $this->Player->findByEmail( $email );
            $player = Set::extract( 'Player', $player_list );
            
            if(! $player ) {
                // user not found 
                $this->Session->setFlash( 'We can\'t find your account.  Please check the spelling and try again' );
                $this->render( $action );      
            } elseif( $player['is_confirmed'] && $action == 'confirm' ) {
                $this->Player->set( 'id', $player['id'] );
                $this->Player->set( 'verify_hash', '' );
                $this->Player->save();
                $this->Session->setFlash( 'You have already confirmed your account.  Please log in' );
                $this->redirect( 'login' );
            } else {
                // send the confirmation
                if( $action == 'forgot' ) {
                    $this->generateAndSendForgot( $player );
                    $this->Session->setFlash( 'An email has been sent to ' . $player['email'] . '.  Please follow the instructions' );
                } else {
                    $this->generateAndSendConfirm( $player );
                    $this->Session->setFlash( 'The confirmation has been resent to ' . $player['email'] );
                }
                
                $this->redirect( 'login' );
            }
        } else {
           $this->render( $action );
        }
    }

    function edit() {
        $action=null;
        if(isset( $this->params['pass'][0] ) ) {
            $action = $this->params['pass'][0];
        }
        $this->Player->id = $this->player['id'];
        
        switch( $action ) {
            case 'password':
                if(! empty( $this->data ) ) {
                    $data = Set::extract( 'Players', $this->data );
                    
                    $this->Player->set( 'id', $this->player['id'] );
                    $this->Player->set( 'password', $data['password'] );
                    $this->Player->save();
                    $this->Session->setFlash( 'Your password has been updated' );
                    $this->redirect( 'view' );
                } else {
                    $this->render( 'edit-password' );
                }
            break;
            
            default:
                if(! empty( $this->data ) ) {
                    $this->Player->save($this->data);
					$this->refreshAuth();
                    $this->Session->setFlash( 'Your profile has been updated' );
                    $this->redirect( array('action'=>'view') );
                } else {
                    $this->data = $this->Player->read();
                }
            break;

        }
    }
    
    function refreshPlayer() {
    	$this->refreshAuth();
    }


    function forgot() {
        $hash = md5( Sanitize::clean( $this->params['hash'] ) . 'forgot' );
        if(! $hash ) {
            $this->Session->setFlash( 'Sorry!  It looks like your forgot password code has expired or got garbled.', 'default', array( 'class'=>'error' ) );
            $this->redirect( 'send/forgot' );     
        }
        
        /** if they're already logged in, just send them to the edit profile screen **/
        if( $this->player ) {
            $this->Session->setFlash( 'Please change your password' );
            $this->redirect( 'edit/password' );
        } else {
            $player = $this->Player->findByVerifyHash( $hash );
             if( empty( $player ) ) {
                $this->Session->setFlash( 'Sorry!  It looks like your forgot password code has expired or got garbled.', 'default', array( 'class'=>'error' ) );
                $this->redirect( 'send/forgot' );
            } else {
                $player_id = Set::extract( '/Player/id', $player );
                /*** confirm them **/
                $this->Player->set( 'id', $player_id[0] );
                $this->Player->set( 'is_confirmed', 1 );
                $this->Player->set( 'verify_hash', '' );
                $this->Player->save();
                
                /*** log them in **/
                $this->Auth->login( Set::extract( 'Player', $player ) );
                $this->Session->setFlash( 'Please change your password' );
                $this->redirect( 'edit/password' );
              
            } 
        }
                
    }

    function confirm() {
        $hash = md5( Sanitize::clean( $this->params['hash'] ) . 'confirm' );
        if(! $hash ) {
            $this->Session->setFlash( 'Sorry!  It looks like your confirmation code has expired or got garbled.', 'default', array( 'class'=>'error' ) );
            $this->redirect( 'send/confirm' );     
        }
        
        // if they're already logged in, confirm them and redirect with message
        if( $this->player ) {
            $p_hash = $this->player['verify_hash'];
            if( strcmp( $p_hash, $hash ) !== 0 ) {
                $this->Session->setFlash( 'Sorry!  It looks like your confirmation code has expired or got garbled.', 'default', array( 'class'=>'error' ) );
                $this->redirect( 'send/confirm' );
            } else {
                $player_id = $this->player['id'];
                $this->Player->set( 'id', $player_id );
                $this->Player->set( 'is_confirmed', 1 );
                $this->Player->set( 'verify_hash', '' );
                $this->Player->save();
                $this->Session->setFlash( 'Account confirmed', 'default', array( 'class'=>'message' ) );
                $this->redirect( 'view' );
            }
        } else {
            $player = $this->Player->findByVerifyHash( $hash );
            if( empty( $player ) ) {
                $this->Session->setFlash( 'Sorry!  It looks like your confirmation code has expired or got garbled.', 'default', array( 'class'=>'error' ) );
                $this->redirect( 'send/confirm' );
            } else {
                $player_id = Set::extract( '/Player/id', $player );
                $this->Player->set( 'id', $player_id[0] );
                $this->Player->set( 'is_confirmed', 1 );
                $this->Player->set( 'verify_hash', '' );
                $this->Player->save();
                $this->Session->setFlash( 'Account confirmed. Please log in', 'default', array( 'class'=>'message' ) );
            } 
            $this->redirect( 'login' );                    
        }        
    }

    public function view( ) {
        $is_member=FALSE;
        $is_expired=FALSE;
        if(! empty( $this->player ) ) {
            if( $this->player['member_until'] ) {
                $is_member=TRUE;
            }
            
            if( time() > strtotime( $this->player['member_until'] ) ) {
                $is_expired = TRUE;
            }            
        }
        
        if(! isset( $this->params ) || ! isset( $this->params['id'] ) ) {
            if( empty( $this->player ) ) {
                $this->Session->setFlash( 
                        'You have to be logged in to see that page',
                        'default', 
                        array( 'class'=>'error' ) );
                $this->redirect( 'login' );
            } else {
            	$info=$this->Player->findAllById($this->player['id']);
            	$this->loadModel('Game');
            	$characters=Set::extract('/Character/.',$info);
            	$games = $this->Game->find( 'list', array( 'conditions'=>array( 'Game.game_active'=>1 ) ) );

                $gamesAll=$this->Game->findAll();
                $gamesAll=Set::extract( '/Game/.', $gamesAll );

                $this->set( 'characters', $characters );
                $this->set( 'games', $games);
                $this->set( 'gamesAll', $gamesAll);
                $page='view-private';
            }
        } else {
            $id = strip_tags( $this->params['id'] );
            $myid = 0;
            if(! empty( $this->player ) ) {
                $myid = $this->player['id'];
            }
            $page='view-public';
            
            if( is_numeric( $id ) ) {
                if( $myid == $id ) {
                    $this->redirect( 'view' );
                }
                $params = array( 
                           'conditions'=>array( 'Player.is_public'=>1, 'Player.is_active'=>1, 'Player.id'=>$id )
                            );
                $data = $this->Player->find( 'first', $params );
                if(! $data ) {
                     $this->cakeError( 'playerNotFound' );
                }
            } else {
                $params = array( 
                           'conditions'=>array( 'Player.is_active'=>1, 'Player.name'=>$id )
                          );
                
                $data = $this->Player->find( 'first', $params );              
                if(! $data ) {
                    $this->cakeError( 'playerNotFound', array( 'profile'=>$id ) );
                }

                if( $myid == $data['Player']['id'] ) {
                    $this->redirect( 'view' );
                }
                
                if( $data['Player']['is_public'] == 0 ) {
                     $this->cakeError( 'playerNotFound', array( 'profile'=>$id ) );
                }
                
                $this->set( 'player', $data['Player'] );
                $this->set( 'characters', $data['Character'] );
            }
        }
        
        $this->set('is_member',$is_member);
        $this->set('is_expired',$is_expired);
        $this->render($page);
    }

}

?>