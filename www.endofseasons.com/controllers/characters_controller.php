<?php
class CharactersController extends AppController {
    var $name = 'Characters';
	var $helpers = array('Session','Xml');
	var $components = array('Rules','RequestHandler'); 
	var $uses = array( 'Rule', 'Category', 'Character', 'Hacker' );
		
	var $stats = array( );
	var $tree  = array( );
	var $game_id = 0;
	var $level   = 1;
	var $character_info=array();
	var $game_info=array();

	
	function beforeFilter() {
	    $this->Auth->allow('view');
	    parent::beforeFilter();
    }

    /** private helpers **/
    private function initialize( $game_id ) {
        $this->Rule->kickoff( $game_id );
        $this->loadModel( 'Destiny' );
        $this->loadModel( 'Lifeforce' );
        
        $this->game_id  = $game_id;  
        $this->destiny = $this->Destiny->grab( $this->Rule->starting_destiny );
        $this->lifeforce = $this->Lifeforce->grab( $this->Rule->starting_lifeforce );
    }

    //*** Public View methods ***///
    public function view( ) {
        // Added for Admins to view any sheet
        if ($this->player['admin']) {
                $this->layout = 'doublepage';
                $this->Character->id = ( int ) $this->params['id'];
                $character = $this->Character->read();
                $character_info = Set::extract( 'Character', $character );

                $this->initialize( $character_info['game_id'] );

                $this->set( 'character', $character_info );
                $this->set( 'game', Set::extract( 'Game', $character ) );
                $this->set( 'player', Set::extract ('/Player/.', $character ) );
                $this->set( 'rules', $this->characterSheetArray( Set::extract( 'Rule', $character ) ) );
                $this->set( 'director', Set::extract( '/DirectorEdit/.', $character ));
        } else {
            if( $this->player ) {
                $my_characters = $this->Character->findAllByPlayerId( $this->player['id'] );
                $num_characters = 0;
                if(! empty( $my_characters ) ) {
                    $num_characters = count($my_characters);
                }
            }


            if(! isset( $this->params ) || ! isset( $this->params['id'] ) ) {
                if(! $this->player ) {
                    // this will essentially bounce them to login
                    $this->Session->setFlash('No character selected', 'default' );
                    $this->redirect( array( 'controller'=>'players','action'=>'view' ) );
                } else {
                    switch( $num_characters ) {
                        case 0:
                            $this->Session->setFlash('You don\'t have any characters in your account', 'default' );
                            $this->redirect( array( 'controller'=>'players','action'=>'view' ) );
                        break;

                        case 1:
                            //redirect to the character sheet
                        break;

                        default:
                            $characters = Set::extract( '/Character/.', $my_characters );
                            $this->set( 'characters', Set::extract( '/Character/.', $my_characters ) );
                            $this->render( 'chooser' );
                        break;
                    }
                }
            } else {
                $this->layout = 'doublepage';
                $this->Character->id = ( int ) $this->params['id'];
                $character = $this->Character->read();
                $character_info = Set::extract( 'Character', $character );
                if(! $character_info ) {
                    $this->Session->setFlash('You are\'t allowed to view that character', 'default' );
                    $this->redirect( array( 'controller'=>'players','action'=>'view' ) );
                }

                $this->initialize( $character_info['game_id'] );

                if(! $this->player ) {
                    if(! $character_info['is_public'] ) {
                        $this->Session->setFlash('You are\'t allowed to view that character. Attempt logged with admin.', 'default' );
                        $this->Hacker->set( array(
                                'player_id'=>$this->player['id'],
                                'attempt_type'=>'CHAR_ACCESS',
                                'description'=> 'Attempted to access unauthorized character sheet.'
                            )
                        );
                        $this->Hacker->save();
                        $this->redirect( array( 'controller'=>'players','action'=>'view' ) );
                    }
                } else {
                    if( $this->player['id'] != $character_info['player_id'] && ( ! $character_info['is_public'] ) ) {
                        $this->Session->setFlash('You are\'t allowed to view that character. Attempt logged with admin.', 'default' );
                        $this->Hacker->set( array( 
                                'player_id'=>$this->player['id'],
                                'attempt_type'=>'CHAR_ACCESS',
                                'description'=> 'Attempted to access unauthorized character sheet.'
                            )
                        );
                        $this->Hacker->save();
                        $this->redirect( array( 'controller'=>'players','action'=>'view' ) );
                    }
                }

                $this->set( 'character', $character_info );
                $this->set( 'game', Set::extract( 'Game', $character ) );
                $this->set( 'player', Set::extract ('/Player/.', $character ) );
                $this->set( 'rules', $this->characterSheetArray( Set::extract( 'Rule', $character ) ) );
                $this->set( 'director', Set::extract( '/DirectorEdit/.', $character ));
            }
        }
    }
    
    private function characterSheetArray( $rules ) {
        $nice_array = array();
        $pool = array();
        foreach( $rules as $rule ) {
            if( $rule['is_hidden'] ) continue;
            $category = $this->Category->grab( $rule['category_id'] );
            
            if( $category['is_unique'] ) {
                $nice_array[$category['name']]=$rule['name'];
                continue;
            }
            
            if( $category['name'] == 'pool' ) {
                preg_match( '/[0-9]+/', $rule['name'], $matches );
                $pool[] = $matches[0];
                continue;
            }
            
            if( $rule['is_essence'] ) {
                $nice_array['essence'][$rule['id']] = $rule['name'];
            } else {
                $nice_array['ability'][$rule['id']] = $rule['name'];
            }
 
        }
        
        $base_pool=$base_essence=0;
        if( count( $pool ) ) {
            rsort($pool);
            $base_pool = $pool[0] * $this->destiny['pool_multiplier'];
            $base_essence = $pool[0] * $this->destiny['essence_multiplier'];
        }
        
        $nice_array['pool'] = $base_pool;
        $nice_array['pool_essence'] = $base_essence;
        
        return($nice_array);
    }


    public function save() {
        if(! $this->data ) {
            $this->redirect( array( 'controller'=>'characters','action'=>'add' ) );
        }
        
        
        if(! $this->player['member_until'] || time() > strtotime( $this->player['member_until'] ) ) {
            $this->Session->setFlash( 'You have to be a member to save a character', 'default', array( 'class'=>'error' ) );
            $this->redirect( array( 'controller'=>'players','action'=>'view' ) );             
        }
        
        if( isset($this->data['Character']['id'])) {
            if ($this->player['admin']) {
                $c_dump = $this->Character->findById($this->data['Character']['id']);
            } else {
                $c_dump = $this->Character->find( 'first', array('conditions'=>array( 'Character.player_id'=>$this->player['id'], 'Character.id'=>$this->data['Character']['id'])));
                if( empty( $c_dump ) ) {
                        $this->Session->setFlash('You are\'t allowed to save that character. Attempt logged with admin.', 'default' );
                        $this->Hacker->set( array(
                                'player_id'=>$this->player['id'],
                                'attempt_type'=>'CHAR_SAVE',
                                'description'=> 'Attempted to access unauthorized character sheet.'
                            )
                        );
                        $this->Hacker->save();
                        $this->redirect( array( 'controller'=>'players','action'=>'view' ) );
                }
            }

            $game_info      = Set::extract( '/Game/id', $c_dump );
            $this->game_id = $game_info[0];
        } else {
        	$game_info = Set::extract( '/Game/id', $this->data );
        	$this->game_id = $game_info[0]['id'];
        }
        

        $this->Character->set( $this->data );
        $this->initialize( $this->game_id );  
        $setup = array( 'game_id'=>$this->game_id, 'starting_destiny'=>$this->destiny, 'starting_lifeforce'=>$this->lifeforce, 'level'=>1 );
        $this->Rules->setupGame( $setup );
        $character_in = $this->Character->findById($this->data['Character']['id']);
        $this->Rules->refreshCharacter(Set::extract( 'Character', $character_in));
        $data = $this->Rules->post( Set::extract( 'Character', $this->data));
        if( $data ) {
            if( isset( $data['Character']['id'] ) ) {
        	$this->Character->id = $data['Character']['id'];
                $this->Character->save( $data );
            } else {
                $this->Character->saveAll( $data );
            }
            
            $msg = "Character has been saved";
            $this->Session->setFlash( $msg, 'default' );
            $this->redirect( array( 'controller'=>'players','action'=>'view' ) );             
        } else {
            $this->Session->setFlash( 'Invalid Entry', 'default', array( 'class'=>'error' ) );
            $this->redirect( array( 'controller'=>'players','action'=>'view' ) );  
        }
        
        $this->Session->setFlash( 'Invalid character', 'default', array( 'class'=>'error' ) );
        $this->redirect( array( 'controller'=>'players','action'=>'view' ) );                          
    }
    

    public function edit( ) {
            $this->layout = 'xml/default';
            $c_dump = null;

            if ($this->player['admin']) {
                $c_dump = $this->Character->findById($this->params['id']);
            } else {
                $c_dump = $this->Character->find( 'first', array('conditions'=>array( 'Character.player_id'=>$this->player['id'], 'Character.id'=>$this->params['id'])) );
                if( empty( $c_dump ) ) {
                    $msg = "You are not allowed to edit that character";
                    $this->Session->setFlash( $msg, 'default' );
                    $this->redirect( array( 'controller'=>'players','action'=>'view' ) );
                }
            }
            
            $character = Set::extract( 'Character', $c_dump );
            $rules     = Set::extract( '/Rule/id', $c_dump );
            $game_info      = Set::extract( 'Game', $c_dump );
            $this->game_info = $game_info;
            $this->game_id = $game_info['id'];
            
            $this->initialize( $this->game_id );
            $setup = array( 'game_id'=>$this->game_id, 'starting_destiny'=>$this->destiny, 'starting_lifeforce'=>$this->lifeforce, 'level'=>1 );
            $this->Rules->setupGame( $setup );
            $this->Rules->generate($rules,array(),$character);
            
            Configure::write( 'debug', 0 );
            $this->set( 'xsl_display', 'edit.xsl' );
            $this->set( 'xml_output', $this->Rules->getXmlArray());
            $this->render( 'xsl_render' );
        
    }
            
            

    public function add( ) {
    	// always expects something passed in - either game or aspect
    	if(! isset( $this->params['pass'] ) ) {
    		$this->redirect( array( 'controller'=>'games','action'=>'select' ) );
    	}
    	
    	$game_id = $this->Session->read( 'Chartool.game_id' );
    	$this->loadModel( 'Game' );
    	if(! $game_id ) {
	    	// expects game.abbr
	    	$game_name = $this->params['pass'];
	    	$games = $this->Game->findByAbbr( $game_name );
	    	if(! $games ) {
	    		$this->redirect( array( 'controller'=>'games','action'=>'select' ) );
	    	}
	    	$game_info = Set::extract( '/Game/.', $games );
	    	$this->game_id = $game_info[0]['id'];
	    	$this->game_info = $game_info[0];
	    	$this->Session->write( 'Chartool.game_id', $this->game_id );
    	} else {
    		$games = $this->Game->findById( $game_id );
	    	if(! $games ) {
	    		$this->Session->del( 'Chartool.game_id' );
	    		$this->redirect( array( 'controller'=>'games','action'=>'select' ) );
	    	}
	    	$game_info = Set::extract( '/Game/.', $games );
	    	$this->game_id = $game_info[0]['id'];
	    	$this->game_info = $game_info[0];
	    	$this->Session->write( 'Chartool.game_id', $this->game_id );
    	}
    	
    	
    	$this->initialize( $this->game_id );  
        $setup = array( 'game_id'=>$this->game_id, 'starting_destiny'=>$this->destiny, 'starting_lifeforce'=>$this->lifeforce, 'level'=>1 );
        $this->Rules->setupGame( $setup );
        
        if( empty( $this->data ) ) {
            $mask = array( 'faction', 'pool', 'aspect', 'character', 'vocation' );
            $this->Rules->generate( $this->Rule->loadDefaults(), $mask );
            $xsl = 'add.xsl';
        } else {
            $ids = Set::extract( 'Character.rule', $this->data );
            $this->Rules->generate( $ids );
            $xsl = 'aspects.xsl';
            $this->Session->del( 'Chartool.game_id' );
        }
        
        
    	Configure::write( 'debug', 0 ); // only necessary for non-production
        $this->layout = 'xml/default';
        $this->set( 'xsl_display', $xsl );
        $this->set( 'xml_output', $this->Rules->getXmlArray());
        $this->render( 'xsl_render' );
    }
    


    
}

?>