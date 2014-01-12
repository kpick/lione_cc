<?php
class EventsController extends AppController {
    var $name = 'Events';
	var $helpers = array('Session','Javascript','Html');
	var $components = array( 'RequestHandler' );
	private $r_buffer=array( 'current_queue_spot'=>-1);

	// in addition to normal auth callbacks, this also
	// grabs any information in the protected variable
	// reservation_info and passed it into r_buffer
	// @see AppController
	function beforeFilter() {
	    $this->Auth->allow('index','view');
	    parent::beforeFilter();
	    
	    if(! empty( $this->r_buffer_info ) ) {
	    	foreach( $this->r_buffer_info as $key=>$value ) {
	    		$this->r_buffer[$key]=$value;
	    	}
	    	$this->r_buffer_info=array();
	    }
	}

	// the reservation has passed error checking, but
	// needs to be stored in session for a step so that
	// it can be processed.
	// @see AppController
	// @see EventsController::beforeFilter()
    private function checkPassed() {
    	$this->r_buffer['check_passed']=true;
    	$this->Session->write( 'reservation.buffer', $this->r_buffer );
    	unset( $this->r_buffer );
    	return;
    }

    public function view() {
    	$id='';
    	$page = 'view-public';    	
    	$myid = 0;
    	if(! empty( $this->player ) ) {
    		$myid = $this->player['id'];
    		$page = 'view-private';
        }
        
        if( isset( $this->params['id'] ) ) {
    		$id = $this->params['id'];
    	}
    	
    	// select info to display
    	if( $id ) {
    		$this->loadModel( 'Game' );
                $this->loadModel( 'Character' );
    		if( intval( $id ) > 0 ) {
    			$info = $this->Game->findById( $id );
    		} else {
    			$info = $this->Game->findByAbbr($id);
    		}
    		
    		//unable to find game - redirect
    		if( empty( $info ) ) {
    			$this->Session->setFlash('Sorry!  We couldn\'t find that game.  Please select again.', 'default' );
    			$this->redirect( array( 'controller'=>'games','action'=>'view' ) );
    		}
    		
    		/** information about this game, and any characters the player has in this campaign **/
    		$game = Set::extract( '/Game/.', $info );

                $c_info = $this->Character->findAllByPlayerId( $this->player['id'] );
                $characters = Set::extract( '/Character/.', $c_info );

    		/** get events for this game **/
    		$conditions = array( 'Event.game_id'=>$game[0]['id'] );
    		$order = array( 'Event.start_date ASC' );
    		$events = $this->Event->find('all', array( 'conditions'=>$conditions, 'order'=>$order ) );
    		
    		/** make a hash table to relate character and event **/
    		$events_characters = Set::extract( '/Character/EventsCharacter/.', $events );
    		$play_info = array();
                foreach($events_characters as $info ) {
                    $play_info[$info['event_id']][] = $info['character_id'];
    		}
    		
    		
    		/** same at the player level **/
                $p_id = $this->player['id'];
    		$events_players = Set::extract( "/Player[id=$p_id]/EventsPlayer/.", $events );
    		$script_info = array();
                $food_info = array();
    		foreach($events_players as $info ) {
                    $script_info[$info['event_id']]=$info['current_queue_spot'];
                    $food_info[$info['event_id']]=$info['food'];
    		}

    		$this->set( 'events', Set::extract( '/Event/.', $events ) );
    		$this->set( 'locations', Set::extract( '/Location/.', $events ) );
    		$this->set( 'characters', $characters );
    		$this->set( 'play_info', $play_info );
    		$this->set( 'script_info', $script_info );
                $this->set( 'food_info', $food_info );
    		$this->set( 'game',  $game );
    	} else {
    		$this->Session->setFlash('Please select a game to see their events', 'default' );
    		$this->redirect( array( 'controller'=>'games','action'=>'view' ) );
    	}
    	
    	$this->render( $page );
    	
    	
    }
    

    
    
    public function check() {
    	if(! $this->RequestHandler->isAjax() ) {
    		return;
    	}
    	
    	$this->autoRender=false;
    	if(! $this->player ) {
    		return;
    	}
    	
    	if( empty($_POST ) ) {
    		return;
    	}

    	$valid_methods = array( 'play', 'script', 'play-remove', 'script-remove', 'food' );
    	if(! in_array( $_POST['method'], $valid_methods ) ) {
    		return;
    	}
    	
    	$this->r_buffer['r_type'] = $_POST['method'];
    	$this->r_buffer['event_id'] = ( int ) $_POST['event'];
    	$this->r_buffer['character_id'] = ( int ) $_POST['character'];
    	
    	if( $this->r_buffer['event_id'] > 0 ) {
    		print $this->checkReservation( );
    	} 
    	
    	return;
    }

    public function reserve() {
        if(! $this->RequestHandler->isAjax() ) {
    		return;
    	}

    	$this->autoRender=false;
    	
        if(! $this->r_buffer['check_passed'] ) {
        	return;
    	}

    	
    	print $this->makeReservation();
    }
    
    
    
    private function checkReservation( ) {
     	if(! $this->player['is_active'] || ! $this->player['is_confirmed'] ) {
     		$params['title'] = "Invalid player";
    		$params['message'] = "Sorry!  This player isn't currently active.";
    		return $this->sendMessage( $params );
    	}
     	$this->loadModel('Player');
    	$this->loadModel('Game');
    	
    	
     	$player_info = $this->Player->findById($this->player['id'] );
        $event_id = $this->r_buffer['event_id'];
    	$event = $this->Event->findById( $this->r_buffer['event_id'] );
    	$event_info = Set::extract( '/Event/.', $event );

    	$game = $this->Game->findById( $event_info[0]['game_id'] );
    	$game_info = Set::extract( '/Game/.', $game );

    	if(! $game_info[0]['game_active'] ) {
    		$params['title'] = "Invalid game";
    		$params['message'] = "Sorry!  This game isn't currently active";
    		return $this->sendMessage( $params );
    	} 

    	// see if this is a valid event, in the past or future
    	$script_ok=false;
    	$play_ok=false;
     	if( time() < strtotime( $event_info[0]['start_date'] ) ) { // event in future
    		$script_ok=true;
    		$play_ok=true;
    	} else { // event in past
    		$events = $this->Event->find( 'all', array(
    			'fields'=>array( 'Event.id' ),
    			'conditions'=>array( 'Event.end_date <'=> date('Y-m-d') ),
    			'limit'=>$game_info[0]['event_buyback'],
    			'order'=>array( 'Event.end_date DESC' )
	    		)
	    	);
	    	
	    	$e = Set::extract( '/Event/id', $events );
	    	if( in_array( $this->r_buffer['event_id'], $e ) ) {
	    		$play_ok=true;
	    	}	
    	}
    	
    	
    	/** see if the player exists **/
    	$player_exists = Set::extract( "/Player[id={$this->player['id']}]", $event );


    	switch( $this->r_buffer['r_type'] ) {
    		case 'play':
    			if(! $play_ok ) {
    				$params['title'] = "Event Expired";
    				$params['message'] = "Sorry!  You can't spend a token on this event";
    				return $this->sendMessage( $params );
    			}    		
    			
    			if( $event_info[0]['event_sp'] > $this->player['script_points'] ) {
    				$params['title'] = "Insufficient SP";
    				$params['message'] = "Sorry!  You only have {$this->player['script_points']} script points, and you need {$event_info[0]['event_sp']} for this event.";
    				return $this->sendMessage( $params );
    			}
    	    	
    			$characters = Set::extract( "/Character[game_id={$game_info[0]['id']}]", $player_info );

    			if(! count($characters) ) {
		    		$params['title'] = "No Characters";
		    		$params['message'] = "Sorry!  You don't have any characters created for this game yet.";
		    		return $this->sendMessage( $params );
    			}
    			
    			$character_id= $this->r_buffer['character_id'];
    	
    			if( $character_id ) {
    				$characters = Set::extract( "/Character[player_id={$this->player['id']}]", $player_info );
    				if(! count( $characters ) ) {
    					$params['title'] = "Invalid Character";
    					$params['message'] = "Sorry!  That character doesn't seem to belong to you";
    					return $this->sendMessage( $params );
    				}
    			} else { // try to derive character id
    				if( count($characters) > 1 ) {
                                        $params['title'] = "Unknown Character";
			    		$params['message'] = "Sorry!  You have " . count($characters)." characters, and I don't know who to apply this to.  Please select a character to apply the token to";
                                        return $this->sendMessage( $params );
    				}
    				
    				$char_tmp_array =  Set::extract( "/Character/id", $player_info );
    				$character_id = $char_tmp_array[0];
    			}
    			
    			
    			/** is this person already in the db for this event? **/
    			if( count( $player_exists ) ) { // are they already playing?
    				$character_exists = Set::extract( "/Character/EventsCharacter[character_id=$character_id]", $event );
    				$character_playing = Set::extract( "/EventsCharacter[event_id=$event_id]", $character_exists );
    				if( count( $character_playing) ) {
    					$params['title'] = "Reservation confirmed";
		    			$params['message'] = "You're already registered to play/earn xp for this event";
		    			return $this->sendMessage( $params );
    				}
    			}

    			// do they have enough of the right kind of token?
   			$this->loadModel('Transaction');
    			$transaction = $this->Transaction->findAllByPlayerId( $this->player['id'] );
    			$active_skus = Set::extract( "/Skus/TransactionsSkus[character_event_id=0]/sku_id", $transaction );
    			$applies_to = Set::extract( '/Sku[applies_to=event|blanket]/id', $event );
    			$applies_to = $applies_to[0];
    			
                        $sku_name = Set::extract( "/Sku/.[id=$applies_to]/description", $event );
                        $sku_value = Set::extract( "/Sku/.[id=$applies_to]/value", $event );

                        //This was removed because we switched to Event Tokens in the Player object
                        /*
                        if(! count( $active_skus ) ) {
				$sku_value = Set::extract( "/Sku/.[id=$applies_to]/value", $event );
    				$params['title'] = "No tokens";
    				$href = "/cart/add/$sku_value[0]";
			    	$params['message'] = "Sorry!  You don't have any event/xp tokens.  <a href=\"$href\">Buy more?</a>";
			    	return $this->sendMessage( $params );
    			}
    			if(! in_array( $applies_to, $active_skus ) ) {
                                $params['title'] = "No token";
                                $params['message'] = "Sorry!  You don't have a $sku_name[0].  <a href=\"/cart/add/$sku_value[0]\">Buy more?</a>";
                                return $this->sendMessage( $params );
    			}
                        */

                        switch($sku_value[0]){
                            case '03D-EVT':
                                if ($this->player['event_token_3_day'] < 1){
                                    $params['title'] = "No Required Token";
                                    $params['message'] = "Sorry!  You don't have a $sku_name[0].  <a href=\"/cart/add/$sku_value[0]\">Buy more?</a>";
                                    return $this->sendMessage( $params );
                                }
                            break;
                            case '04D-EVT':
                                if ($this->player['event_token_4_day'] < 1){
                                    $params['title'] = "No Required Token";
                                    $params['message'] = "Sorry!  You don't have a $sku_name[0].  <a href=\"/cart/add/$sku_value[0]\">Buy more?</a>";
                                    return $this->sendMessage( $params );
                                }
                            break;
                            case '01D-EVT':
                                if ($this->player['event_token_1_day'] < 1){
                                    $params['title'] = "No Required Token";
                                    $params['message'] = "Sorry!  You don't have a $sku_name[0].  <a href=\"/cart/add/$sku_value[0]\">Buy more?</a>";
                                    return $this->sendMessage( $params );
                                }
                            break;
                        }

                        $this->checkPassed();
    			$this->r_buffer['player_reservation_info'] = $player_exists;
    			$this->r_buffer['transaction_reservation_info'] = $transaction;
    			$this->r_buffer['character_id'] = $character_id;
    			$this->r_buffer['applies_to'] = $applies_to[0];

                        $char_name = Set::extract( "/Character/.[id=$character_id]", $player_info );
    			$char_name = $char_name[0]['name'];
    			
    			$params['title'] = "Confirm";
    			$params['message'] = "You are about to spend a $sku_name[0] on $char_name.  Is this OK?";
    			return $this->sendMessage( $params, true );
   			break;
   			
    		case 'script':

    			if(! $script_ok ) {
		    		$params['title'] = "Expired event";
		    		$params['message'] = "Sorry!  You can't script that event";
		    		return $this->sendMessage( $params );
    			}
    			
    			/** is this person already in the db for this event? **/
    			if( count( $player_exists ) ) { // are they already scripting?
    				$p_check = Set::extract( "/Player/EventsPlayer[current_queue_spot>=-1]", $player_exists );
    				if( count( $p_check ) ) {
    					$params['title'] = "Reservation confirmed";
		    			$params['message'] = "You're already in the play/script queue for this event";
		    			return $this->sendMessage( $params );
    				}
    			}

    			//$cnt_scripts = count( Set::extract( '/Player/id', $event ) );
                        $players = Set::extract( '/Player/', $event );
                        $cnt_scripts = count (Set::extract( "/Player/EventsPlayer[current_queue_spot>-1]", $players ));
    			$current_queue_spot=0;
    			if( $cnt_scripts >= $event_info[0]['max_scripts'] ) {
                            $current_queue_spot = ( $cnt_scripts - $event_info[0]['max_scripts'] ) +1;
    			}
    			
    			$this->r_buffer['current_queue_spot'] = $current_queue_spot;
    			$this->checkPassed();
    			
    			if( $current_queue_spot == 0 ) {
                            $params['title'] = "Confirm";
    				$params['message'] = "Looks like we have an opening!  OK to reserve this spot?";
    				return $this->sendMessage( $params, true );
    			} else {
    				$params['title'] = "Confirm";
    				$params['message'] = "Script slots are full.  You will be alternate #$current_queue_spot. Is this OK?";
    				return $this->sendMessage( $params, true );
    				
    			}
    		break;
    		
    		case 'play-remove':
    				//TODO: add removal of future events as player
    		break;
    		
    		case 'script-remove':
    				//TODO: add removal of future events as script
    		break;
    		
                case 'food':

                    if(! $play_ok ) {
                        $params['title'] = "Event Expired";
                        $params['message'] = "Sorry!  You can't spend a token on this event";
                        return $this->sendMessage( $params );
                    }    

                    $applies_to = Set::extract( '/Sku[applies_to=event|food]/id', $event );
                    $applies_to = $applies_to[0];

                    $sku_name = Set::extract( "/Sku/.[id=$applies_to]/description", $event );

                    $sku_value = Set::extract( "/Sku/.[id=$applies_to]/value", $event );
                    $sku_value = $sku_value[0];

                    switch($sku_value){
                       case '03D-FOD':
                            if ($this->player['food_token_3_day'] < 1){
                                $params['title'] = "No Required Token";
                                $params['message'] = "Sorry!  You don't have a $sku_name[0].  <a href=\"/cart/add/$sku_value\">Buy more?</a>";
                                return $this->sendMessage( $params );
                            }
                        break;
                        case '04D-FOD':
                            if ($this->player['food_token_4_day'] < 1){
                                $params['title'] = "No Required Token";
                                $params['message'] = "Sorry!  You don't have a $sku_name[0].  <a href=\"/cart/add/$sku_value\">Buy more?</a>";
                                return $this->sendMessage( $params );
                            }
                        break;
                        case '01D-FOD':
                            if ($this->player['food_token_1_day'] < 1){
                                $params['title'] = "No Required Token";
                                $params['message'] = "Sorry!  You don't have a $sku_name[0].  <a href=\"/cart/add/$sku_value\">Buy more?</a>";
                                return $this->sendMessage( $params );
                            }
                        break;
                    }

                    $this->checkPassed();
                    $this->r_buffer['player_reservation_info'] = $player_exists;
                    $this->r_buffer['applies_to'] = $applies_to[0];

                    $params['title'] = "Confirm";
                    $params['message'] = "You are about to spend a $sku_name[0].  Is this OK?";
                    return $this->sendMessage( $params, true );
                    break;
    		default:
    			return;
    		break;
    	}
    	
    	
    }
    
    private function makeReservation( ) {
    	$this->loadModel('EventsPlayer');
        $this->loadModel('Player');
        $this->loadModel('Event');
        
    	$player_exists = $this->r_buffer['player_reservation_info'];
    	$params = array();
    	$params['success'] = true;

        $event = $this->Event->findById( $this->r_buffer['event_id'] );
        $applies_to = Set::extract( '/Sku[applies_to=event|blanket]/id', $event );
        $applies_to = $applies_to[0];
        $sku_value = Set::extract( "/Sku/.[id=$applies_to]/value", $event );

    	switch( $this->r_buffer['r_type'] ) {
    		case 'play':
                        if( count($player_exists ) ) {
    				$ep_ids = Set::extract( "/Player/EventsPlayer/id", $player_exists );
    				$this->EventsPlayer->set('id', $ep_ids[0] );
    				$this->EventsPlayer->save();
    			} else {
    				$data = array( 'EventsPlayer'=>array(
	    				'event_id'=>$this->r_buffer['event_id'],
	    				'player_id'=>$this->player['id'],
    					'current_queue_spot'=>$this->r_buffer['current_queue_spot']
    				));

    				$this->EventsPlayer->save($data);
    			}
    			
    			$this->loadModel('EventsCharacter');
				$applies_to = $this->r_buffer['applies_to'];
				
    			$data = array( 'EventsCharacter'=>array(
    				'event_id'=>$this->r_buffer['event_id'],
    				'character_id'=>$this->r_buffer['character_id']
    			));
    			
    			$this->EventsCharacter->save($data);
    			$char_event_id = $this->EventsCharacter->id;

                        switch($sku_value[0]){
                            case '03D-EVT':
                                $token = $this->player['event_token_3_day'] - 1;
                                $this->Player->set( array(
                                    'id'=>$this->player['id'],
                                    'event_token_3_day'=>$token
                                ));
                                $this->Player->save();
                                $this->requestAction('/players/refreshPlayer/'.$this->player['id']);
                            break;
                            case '04D-EVT':
                                $token = $this->player['event_token_4_day'] - 1;
                                $this->Player->set( array(
                                    'id'=>$this->player['id'],
                                    'event_token_4_day'=>$token
                                ));
                                $this->Player->save();
                                $this->requestAction('/players/refreshPlayer/'.$this->player['id']);
                            break;
                            case '01D-EVT':
                                $token = $this->player['event_token_1_day'] - 1;
                                $this->Player->set( array(
                                    'id'=>$this->player['id'],
                                    'event_token_1_day'=>$token
                                ));
                                $this->Player->save();
                                $this->requestAction('/players/refreshPlayer/'.$this->player['id']);
                            break;
                        }
    			// Debit their account
    			$ts_ids = Set::extract( 
    				"/Skus/TransactionsSkus[character_event_id=0]/.[sku_id=$applies_to]", 
    				$this->r_buffer['transaction_reservation_info'] 
    			);

    			$this->loadModel( 'TransactionsSku' );
    			
    			$this->TransactionsSku->set( 'id', $ts_ids[0]['id'] );
    			$this->TransactionsSku->set( 'character_event_id', $char_event_id );
    			$this->TransactionsSku->save();

    			$params['title'] = 'Success!';
    			$params['message'] = 'Your token was successfully applied';
    			return $this->sendMessage( $params );
    		break;
    		
    		case 'script':    			
                    $this->loadModel('EventsPlayer');

                    if( count($player_exists ) ) {
                            $ep_ids = Set::extract( "/Player/EventsPlayer/id", $player_exists );
                            $this->EventsPlayer->set('id', $ep_ids[0] );
                            $this->EventsPlayer->set('current_queue_spot', $this->r_buffer['current_queue_spot']  );
                            $this->EventsPlayer->save();
                    } else {
                            $data = array( 'EventsPlayer'=>array(
                                    'event_id'=>$this->r_buffer['event_id'],
                                    'player_id'=>$this->player['id'],
                                    'current_queue_spot'=>$this->r_buffer['current_queue_spot']
                            ));

                            $this->EventsPlayer->save($data);
                    }

                    $params['title'] = "Spot reserved";
                    $params['message'] = "Your spot has been reserved";
                    return $this->sendMessage( $params );
    		break;

                case 'food':
                    $player_in_event = Set::extract( "/Player[id={$this->player['id']}]", $event );

                    $applies_to = Set::extract( '/Sku[applies_to=event|food]/id', $event );
                    $applies_to = $applies_to[0];
                    $sku_value = Set::extract( "/Sku/.[id=$applies_to]/value", $event );

                    if( count($player_in_event ) ) {
                        $sku_value = Set::extract( "/Sku/.[id=$applies_to]/value", $event );

                        switch($sku_value[0]){
                            case '03D-FOD':
                                $token = $this->player['food_token_3_day'] - 1;
                                $this->Player->set( array(
                                    'id'=>$this->player['id'],
                                    'food_token_3_day'=>$token
                                ));
                                $this->Player->save();
                                $this->requestAction('/players/refreshPlayer/'.$this->player['id']);
                            break;
                            case '04D-FOD':
                                $token = $this->player['food_token_4_day'] - 1;
                                $this->Player->set( array(
                                    'id'=>$this->player['id'],
                                    'food_token_4_day'=>$token
                                ));
                                $this->Player->save();
                                $this->requestAction('/players/refreshPlayer/'.$this->player['id']);
                            break;
                            case '01D-FOD':
                                $token = $this->player['food_token_1_day'] - 1;
                                $this->Player->set( array(
                                    'id'=>$this->player['id'],
                                    'food_token_1_day'=>$token
                                ));
                                $this->Player->save();
                                $this->requestAction('/players/refreshPlayer/'.$this->player['id']);
                            break;
                        }

    			$this->loadModel( 'TransactionsSku' );
    			$this->TransactionsSku->set( 'player_id', $this->player['id'] );
    			$this->TransactionsSku->save();

                        foreach ($player_in_event as $player_food) {
                            $ep_id = Set::extract( "/Player/EventsPlayer/id", $player_food );
                            $this->EventsPlayer->begin();
                            $this->EventsPlayer->create(false);
                            $this->EventsPlayer->set('id', $ep_id[0]);
                            $this->EventsPlayer->set('food', true  );
                            $this->EventsPlayer->save();
                            $this->EventsPlayer->commit();
                        }

                        $params['title'] = "Spent Token";
                        $params['message'] = "Spent Token on this Event.";
                    } else {
                        $params['title'] = "Book Event First";
                        $params['message'] = "Book event as a player before spending a token.";
                    }
                    return $this->sendMessage( $params );
                break;

    		default:
    			return;
    		break;
    	}
    }
    
  
    private function sendMessage( $array, $need_confirm=FALSE ) {
    	$array['need_confirm']=$need_confirm;
    	return json_encode( $array );
    }
  
    	
    
 
}

?>