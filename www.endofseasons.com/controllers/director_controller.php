<?php
class DirectorController extends AppController {
    var $name = 'Directors';
    var $helpers = array('Session','Xml','Javascript','Html', 'Form','TabDisplay');
    var $components = array('Rules','RequestHandler');
    var $uses = array( 'EventsPlayer', 'TransactionsSku','Event', 'Player',
        'Character', 'Audit', 'Training', 'Rule', 'Hacker', 'CharactersRule',
        'Cost', 'EventsCharacter', 'DirectorEdit' );

    private $r_buffer=array();


    function beforeFilter() {
        $this->Auth->allow('index','view');
        parent::beforeFilter();

        $admin = $this->player['admin'];
        if (!$admin) {
            $this->Session->setFlash('You are\'t allowed to view this page. Attempt logged with admin.', 'default' );
            $this->Hacker->set( array(
                    'player_id'=>$this->player['id'],
                    'attempt_type'=>'SEVERE_DIRECTOR',
                    'description'=> 'Player attempted to access Director page.'
                )
            );
            $this->Hacker->save();
            $this->redirect( array( 'controller'=>'players','action'=>'view' ) );
        }

        if(! empty( $this->r_buffer_info ) ) {
            foreach( $this->r_buffer_info as $key=>$value ) {
                    $this->r_buffer[$key]=$value;
            }
            $this->r_buffer_info=array();
        }
    }

    function view() {
        $all_players = $this->Player->find('all');
        $all_players = Set::extract( '/Player/.', $all_players);

        $all_characters = $this->Character->find('all');
        $all_characters = Set::extract( '/Character/.', $all_characters);
        
        $all_events = $this->Event->find('all');
        $all_events = Set::extract( '/Event/.', $all_events);

        $all_training = $this->Rule->find('all');
        $trained = Set::extract( "/Rule[is_trained=1]/.", $all_training);

        $this->set( 'all_players', $all_players );
        $this->set( 'all_characters', $all_characters );
        $this->set( 'all_events', $all_events );
        $this->set( 'trained_rules', $trained );
    }

    // Forces the blanket of an event.
    function blanketEvent() {
        $event_id = $this->r_buffer['blanket_event_id'];
        $event = $this->Event->findById($event_id);
        $char_list =  Set::extract( '/Character/.', $event);
        $ran_blanket = Set::extract( '/Event/.', $event);
        if (!$ran_blanket[0]['ran_blanket']) {
            $this->Audit->begin();
            $this->Audit->create(false);
            $this->Audit->set( array(
                'player_id'=>0,
                'director_id'=>$this->player['id'],
                'token_type'=>"NONE",
                'cp_added'=>0,
                'description'=>"BLANKET FORCED FOR EVENT ID ".$event_id,
                )
            );
            $this->Audit->save();
            $this->Audit->commit();

            foreach ($char_list as $char) {
                $level = $char['level'];

                $vp_u = $char['vp_unspent'];
                $vp_s = $char['vp_spent'];
                $vp = $vp_u + $vp_s;

                $this->loadModel( 'Xpchart' );
                $xp = $this->Xpchart->find("events_played = $level");
                $xp = Set::extract( '/Xpchart/cp_gained', $xp);
                $xp = $xp[0];
                $new_cp = $char['cp_unspent'] + $xp;

                $set_vp = false;
                if ($level % 2 == 1) {
                    $set_vp = true;
                }

                $this->Character->begin();
                $this->Character->create(false);
                $this->Character->set( 'id', $char['id']);
                $this->Character->set( 'cp_unspent', $new_cp);
                $this->Character->set('level', $level + 1);
                if ( $set_vp && $vp < 10) {
                    $this->Character->set('vp_unspent', $vp_u + 1);
                }
                $this->Character->save();
                $this->Character->commit();

                $name = $char['name'];
                $this->Audit->begin();
                $this->Audit->create(false);
                $this->Audit->set( array(
                    'player_id'=>$char['player_id'],
                    'director_id'=>$this->player['id'],
                    'token_type'=>"NONE",
                    'cp_added'=>0,
                    'description'=>"Blanket added for $name."
                    )
                );
                $this->Audit->save();
                $this->Audit->commit();

                $this->Event->begin();
                $this->Event->create(false);
                $this->Event->set( array(
                    'id'=>$event_id,
                    'ran_blanket'=>true
                    )
                );
                $this->Event->save();
                $this->Event->commit();
            }

            $params['title'] = "Event Blanketed";
            $params['message'] = "Force blanket for event id ".$event_id;
            return $this->sendMessage( $params );
        } else {
            $params['title'] = "Event Blanket NOT run";
            $params['message'] = "Event Blanket already run.";
            return $this->sendMessage( $params );
        }
    }

    function addDirectorComment(){
        $char_id = $this->r_buffer['character_id'];
        $comment = $this->r_buffer['comment'];

        $this->DirectorEdit->set('character_id', $char_id);
        $this->DirectorEdit->set('director_id', $this->player['id']);
        $this->DirectorEdit->set('description', $comment);
        $this->DirectorEdit->save();

        $character = $this->Character->findById($char_id);

        $params['title'] = "Added Comment";
    	$params['message'] = "Added comment to character id ".$char_id ;
    	return $this->sendMessage( $params );
    }

    function showAuditLog($id) {
        $audit;
        if ($id == 0) {
            $audit = $this->Audit->find('all');
        } else {
            $audit = $this->Audit->find('all',
                array('conditions' => array('Audit.director_id' => $id)));
        }
        $audit = Set::extract( '/Audit/.', $audit);
        
        $this->set( 'audit', $audit );
    }

    function addCP(){
        $char_id = $this->r_buffer['character_id'];
        $cp_number = $this->r_buffer['cp_number'];

        $character = $this->Character->findById($char_id);
        $curr_cp = Set::extract( '/Character/cp_unspent', $character);
        $curr_cp = $curr_cp[0];

        $name = Set::extract( '/Character/name', $character);
        $name = $name[0];

        $this->Character->set( 'id', $char_id);
        $this->Character->set( 'cp_unspent', $curr_cp + $cp_number);
        $this->Character->save();

        $player_id = Set::extract( '/Player/id', $character);
        $player_id = $player_id[0];

        $this->Audit->set( array(
            'player_id'=>$player_id,
            'director_id'=>$this->player['id'],
            'token_type'=>"NONE",
            'cp_added'=>$cp_number,
            'description'=>"Added ".$cp_number." CP to character ".$name,
            )
        );
        $this->Audit->save();

        $this->refreshAuth();

        $params['title'] = "Added CP";
    	$params['message'] = "Added ".$cp_number." CP to character ".$name ;
    	return $this->sendMessage( $params );
    }

    function addVP(){
        $char_id = $this->r_buffer['character_id'];
        $vp_number = $this->r_buffer['vp_number'];

        $character = $this->Character->findById($char_id);
        $curr_vp = Set::extract( '/Character/vp_unspent', $character);
        $curr_vp = $curr_vp[0];

        $name = Set::extract( '/Character/name', $character);
        $name = $name[0];

        $this->Character->set( 'id', $char_id);
        $this->Character->set( 'vp_unspent', $curr_vp + $vp_number);
        $this->Character->save();

        $player_id = Set::extract( '/Player/id', $character);
        $player_id = $player_id[0];

        $this->Audit->set( array(
            'player_id'=>$player_id,
            'director_id'=>$this->player['id'],
            'token_type'=>"NONE",
            'cp_added'=>$vp_number,
            'description'=>"Added ".$vp_number." VP to character ".$name,
            )
        );
        $this->Audit->save();

        $this->refreshAuth();

        $params['title'] = "Added VP";
    	$params['message'] = "Added ".$vp_number." VP to character ".$name ;
    	return $this->sendMessage( $params );
    }

    function deleteChar() {
        $char_id = $this->r_buffer['char_id'];

        $character = $this->Character->findById($char_id);
        $name = Set::extract( '/Character/name', $character);
        $name = $name[0];

        $player_id = Set::extract( '/Player/id', $character);
        $player_id = $player_id[0];
        
        $this->Character->delete($char_id);

        $this->Audit->set( array(
            'player_id'=>$player_id,
            'director_id'=>$this->player['id'],
            'token_type'=>"NONE",
            'cp_added'=>0,
            'description'=>"Deleted character $name",
            )
        );
        $this->Audit->save();

        $params['title'] = "Delete Character";
    	$params['message'] = "Deleted character $name" ;
    	return $this->sendMessage( $params );
    }

    function setLevel(){
        $char_id = $this->r_buffer['character_id'];
        $level = $this->r_buffer['number'];

        $this->Character->set( 'id', $char_id);
        $this->Character->set( 'level', $level);
        $this->Character->save();

        $character = $this->Character->findById($char_id);
        $name = Set::extract( '/Character/name', $character);
        $name = $name[0];

        $player_id = Set::extract( '/Player/id', $character);
        $player_id = $player_id[0];

        $this->Audit->set( array(
            'player_id'=>$player_id,
            'director_id'=>$this->player['id'],
            'token_type'=>"NONE",
            'cp_added'=>0,
            'description'=>"Set character level to $level for $name",
            )
        );
        $this->Audit->save();

        $this->refreshAuth();

        $params['title'] = "Set Level";
    	$params['message'] = "Set character level to $level for $name" ;
    	return $this->sendMessage( $params );
    }

    function resetPassword() {
        $player_id = $this->r_buffer['pass_player_id'];
        $password = $this->r_buffer['password'];

        $this->Player->set( 'id', $player_id);
        $this->Player->set( 'password', $password);
        $this->Player->save();
        
        $params['title'] = "Changed Password";
    	$params['message'] = "Changed Password for user id $player_id to $password" ;
    	return $this->sendMessage( $params );
    }

    function listPlayersCharacters() {
        $boundary = "</a>".' ; <br>';
        $players = $this->Player->find('all', array(
                        'order'=>array( 'Player.last_name ASC')
                    )
	    	);
        $players = Set::extract('/Player/.', $players);

        $view_players = array();
        $view_characters = array();

        foreach($players as $player) {
            array_push($view_players, $player['first_name']." ".$player['last_name']);
            $characters = $this->Character->findAllByPlayerId( $player['id'] );
            $total_string = null;
            foreach ($characters as $character) {
                  $char_id =  Set::extract('/Character/id',$character);
                  $char_id = $char_id[0];
                  $link = "<a href=/director/showCharRules/".$char_id.">";
                  $link2=" <a href=/director/showCharDirectorEdits/".$char_id.">";
                  $name = Set::extract('/Character/name',$character);
                  $charname = $name[0];
                  $charlink1 = $link."REMOVE RULE".$boundary;
                  $charlink2 = $link2."REMOVE QUALITY".$boundary;
                  $total_string = $total_string.$charname."<br>".$charlink1.$charlink2;
            }
            array_push($view_characters, $total_string);
        }
        $this->set( 'view_players', $view_players );
        $this->set( 'view_characters', $view_characters );
    }


    function showCharRules($char_id) {
        $character = $this->Character->findById($char_id);
        $rules = Set::extract( '/Rule/.', $character);
        $character = Set::extract('/Character/.',$character );
        $character = $character[0];

        $this->set( 'character', $character );
        $this->set( 'rules', $rules );
    }

    function showCharDirectorEdits($char_id) {
        $character = $this->Character->findById($char_id);
        $edits = Set::extract( '/DirectorEdit/.', $character );
        $character = Set::extract('/Character/.',$character );
        $character = $character[0];

        $this->set( 'character', $character );
        $this->set( 'edits', $edits );
    }

    function assignChar() {
        $player_id = $this->r_buffer['player_id'];
        $char_id = $this->r_buffer['char_id'];

        $character = $this->Character->findById($char_id);
        $name = Set::extract( '/Character/name', $character);
        $name = $name[0];

        $player = $this->Player->findById($player_id);
        $f_name = Set::extract( '/Player/first_name', $player);
        $f_name = $f_name[0];
        $l_name = Set::extract( '/Player/last_name', $player);
        $l_name = $l_name[0];

        $this->Character->set( 'id', $char_id);
        $this->Character->set( 'player_id', $player_id );
        $this->Character->save();

        $this->Audit->set( array(
            'player_id'=>$player_id,
            'director_id'=>$this->player['id'],
            'token_type'=>null,
            'cp_added'=>0,
            'description'=>"Reassigned $name to $f_name $l_name",
            )
        );
        $this->Audit->save();

        $params['title'] = "Reassigned Character";
    	$params['message'] = "Reassigned $name to $f_name $l_name" ;
    	return $this->sendMessage( $params );
    }

    function addToken() {
        $player_id = $this->r_buffer['token_player_id'];
        $number = $this->r_buffer['token_number'];
        $type = $this->r_buffer['token_type'];

        $player = $this->Player->findById($player_id);

        switch($type){
            case '03D-EVT':
                $curr_token = Set::extract( '/Player/event_token_3_day', $player);
                $curr_token = $curr_token[0];
                $this->Player->set('id', $player_id);
                $this->Player->set('event_token_3_day', $curr_token + $number);
                $this->Player->save();
                $this->requestAction('/players/refreshPlayer/'.$player_id);
            break;
            case '04D-EVT':
                $curr_token = Set::extract( '/Player/event_token_4_day', $player);
                $curr_token = $curr_token[0];
                $this->Player->set( 'id', $player_id);
                $this->Player->set( 'event_token_4_day', $curr_token + $number);
                $this->Player->save();
                $this->requestAction('/players/refreshPlayer/'.$player_id);
            break;
            case '01D-EVT':
                $curr_token = Set::extract( '/Player/event_token_1_day', $player);
                $curr_token = $curr_token[0];
                $this->Player->set( 'id', $player_id);
                $this->Player->set( 'event_token_1_day', $curr_token + $number);
                $this->Player->save();
                $this->requestAction('/players/refreshPlayer/'.$player_id);
            break;
            case '03D-FOD':
                $curr_token = Set::extract( '/Player/food_token_3_day', $player);
                $curr_token = $curr_token[0];
                $this->Player->set( 'id', $player_id);
                $this->Player->set( 'food_token_3_day', $curr_token + $number);
                $this->Player->save();
                $this->requestAction('/players/refreshPlayer/'.$player_id);
            break;
            case '04D-FOD':
                $curr_token = Set::extract( '/Player/food_token_4_day', $player);
                $curr_token = $curr_token[0];
                $this->Player->set( 'id', $player_id);
                $this->Player->set( 'food_token_4_day', $curr_token + $number);
                $this->Player->save();
                $this->requestAction('/players/refreshPlayer/'.$player_id);
            break;
            case '01D-FOD':
                $curr_token = Set::extract( '/Player/food_token_1_day', $player);
                $curr_token = $curr_token[0];
                $this->Player->set( 'id', $player_id);
                $this->Player->set( 'food_token_1_day', $curr_token + $number);
                $this->Player->save();
                $this->requestAction('/players/refreshPlayer/'.$player_id);
            break;
        }

        $first_name = Set::extract( '/Player/first_name', $player);
        $first_name = $first_name[0];

        $last_name = Set::extract( '/Player/last_name', $player);
        $last_name = $last_name[0];

        $name = $first_name." ".$last_name;

        $params['title'] = "Added Tokens";
    	$params['message'] = "Added ".$number." tokens to character ".$name ;

        $this->Audit->set( array(
            'player_id'=>$player_id,
            'director_id'=>$this->player['id'],
            'token_type'=>$type,
            'cp_added'=>0,
            'description'=>"Added ".$number." tokens of type ".$type." to ".$name,
            )
        );
        $this->Audit->save();

    	return $this->sendMessage( $params );
    }

        function removeToken() {
        $player_id = $this->r_buffer['token_player_id'];
        $number = $this->r_buffer['token_number'];
        $type = $this->r_buffer['token_type'];

        $player = $this->Player->findById($player_id);

        switch($type){
            case '03D-EVT':
                $curr_token = Set::extract( '/Player/event_token_3_day', $player);
                $curr_token = $curr_token[0];
                if (($curr_token > 0) && ($curr_token - $number >=0) ) {
                    $this->Player->set('id', $player_id);
                    $this->Player->set('event_token_3_day', $curr_token - $number);
                    $this->Player->save();
                    $this->requestAction('/players/refreshPlayer/'.$player_id);
                } else {
                    $params['title'] = "Error Removing Tokens";
                    $params['message'] = "Player has zero or fewer than $number tokens." ;
                    return $this->sendMessage( $params );
                }
            break;
            case '04D-EVT':
                $curr_token = Set::extract( '/Player/event_token_4_day', $player);
                $curr_token = $curr_token[0];
                if (($curr_token > 0) && ($curr_token - $number >=0) ) {
                    $this->Player->set( 'id', $player_id);
                    $this->Player->set( 'event_token_4_day', $curr_token - $number);
                    $this->Player->save();
                    $this->requestAction('/players/refreshPlayer/'.$player_id);
                } else {
                    $params['title'] = "Error Removing Tokens";
                    $params['message'] = "Player has zero or fewer than $number tokens." ;
                    return $this->sendMessage( $params );
                }
            break;
            case '01D-EVT':
                $curr_token = Set::extract( '/Player/event_token_1_day', $player);
                $curr_token = $curr_token[0];
                if (($curr_token > 0) && ($curr_token - $number >=0) ) {
                    $this->Player->set( 'id', $player_id);
                    $this->Player->set( 'event_token_1_day', $curr_token - $number);
                    $this->Player->save();
                    $this->requestAction('/players/refreshPlayer/'.$player_id);
                } else {
                    $params['title'] = "Error Removing Tokens";
                    $params['message'] = "Player has zero or fewer than $number tokens." ;
                    return $this->sendMessage( $params );
                }
            break;
            case '03D-FOD':
                $curr_token = Set::extract( '/Player/food_token_3_day', $player);
                $curr_token = $curr_token[0];
                if (($curr_token > 0) && ($curr_token - $number >=0) ) {
                    $this->Player->set( 'id', $player_id);
                    $this->Player->set( 'food_token_3_day', $curr_token - $number);
                    $this->Player->save();
                    $this->requestAction('/players/refreshPlayer/'.$player_id);
                } else {
                    $params['title'] = "Error Removing Tokens";
                    $params['message'] = "Player has zero or fewer than $number tokens." ;
                    return $this->sendMessage( $params );
                }
            break;
            case '04D-FOD':
                $curr_token = Set::extract( '/Player/food_token_4_day', $player);
                $curr_token = $curr_token[0];
                if (($curr_token > 0) && ($curr_token - $number >=0) ) {
                    $this->Player->set( 'id', $player_id);
                    $this->Player->set( 'food_token_4_day', $curr_token - $number);
                    $this->Player->save();
                    $this->requestAction('/players/refreshPlayer/'.$player_id);
                } else {
                    $params['title'] = "Error Removing Tokens";
                    $params['message'] = "Player has zero or fewer than $number tokens." ;
                    return $this->sendMessage( $params );
                }
            break;
            case '01D-FOD':
                $curr_token = Set::extract( '/Player/food_token_1_day', $player);
                $curr_token = $curr_token[0];
                if (($curr_token > 0) && ($curr_token - $number >=0) ) {
                    $this->Player->set( 'id', $player_id);
                    $this->Player->set( 'food_token_1_day', $curr_token - $number);
                    $this->Player->save();
                    $this->requestAction('/players/refreshPlayer/'.$player_id);
                } else {
                    $params['title'] = "Error Removing Tokens";
                    $params['message'] = "Player has zero or fewer than $number tokens." ;
                    return $this->sendMessage( $params );
                }
            break;
        }

        $first_name = Set::extract( '/Player/first_name', $player);
        $first_name = $first_name[0];

        $last_name = Set::extract( '/Player/last_name', $player);
        $last_name = $last_name[0];

        $name = $first_name." ".$last_name;

        $params['title'] = "Removed Tokens";
    	$params['message'] = "Removed ".$number." tokens from player ".$name ;

        $this->Audit->set( array(
            'player_id'=>$player_id,
            'director_id'=>$this->player['id'],
            'token_type'=>$type,
            'cp_added'=>0,
            'description'=>"Removed ".$number." tokens of type ".$type." to ".$name,
            )
        );
        $this->Audit->save();

    	return $this->sendMessage( $params );
    }


    public function addRule() {
        $char_id = $this->r_buffer['rule_char_id'];
        $rule_option = $this->r_buffer['rule_option'];

        $character = $this->Character->findById($char_id);
        $name = Set::extract( '/Character/name', $character);
        $name = $name[0];

        $this->CharactersRule->set('character_id', $char_id);
        $this->CharactersRule->set('rule_id', $rule_option);
        $this->CharactersRule->save();
    
        $params['title'] = "Added Racial Training";
    	$params['message'] = "Added training to character ".$name ;

        $this->Audit->set( array(
            'player_id'=>$player_id,
            'director_id'=>$this->player['id'],
            'token_type'=>$type,
            'cp_added'=>0,
            'description'=>"Added racial training to $name",
            )
        );
        $this->Audit->save();

    	return $this->sendMessage( $params );
    }

    public function addTraining () {
        $char_id = $this->r_buffer['t_char_id'];
        $rule_id = $this->r_buffer['t_training_id'];

        $character = $this->Character->findById($char_id);
        $name = Set::extract( '/Character/name', $character);
        $name = $name[0];

        $data = array();
        $data['character_id'] = $char_id;
        $data['rule_id'] = $rule_id;
        if( $data ) {
            $this->Training->saveAll( $data );
        }

        $params['title'] = "Trained Character";
    	$params['message'] = "Added training to character ".$name ;
        return $this->sendMessage( $params );
    }

    public function spendToken () {
        $char_id = $this->r_buffer['char_id'];
        $event_id = $this->r_buffer['event'];
        $type = $this->r_buffer['type'];

        $character = $this->Character->findById($char_id);
        
        $player_id = Set::extract('/Character/player_id', $character);
        $player_id = $player_id[0];
        
        $player = $this->Player->findById($player_id);
        $player = Set::extract('/Player/.', $player);
        $player = $player[0];

        $event = $this->Event->findById($event_id);

        //DEBIT PLAYER ACCOUNT
        switch($type){
            case '03D-EVT':
                $token = $player['event_token_3_day'] - 1;
                $this->Player->set( array(
                    'id'=>$player_id,
                    'event_token_3_day'=>$token
                ));
                $this->Player->save();
                $this->requestAction('/players/refreshPlayer/'.$player_id);
            break;
            case '04D-EVT':
                $token = $player['event_token_4_day'] - 1;
                $this->Player->set( array(
                    'id'=>$player_id,
                    'event_token_4_day'=>$token
                ));
                $this->Player->save();
                $this->requestAction('/players/refreshPlayer/'.$player_id);
            break;
            case '01D-EVT':
                $token = $player['event_token_1_day'] - 1;
                $this->Player->set( array(
                    'id'=>$player_id,
                    'event_token_1_day'=>$token
                ));
            $this->Player->save();
            $this->requestAction('/players/refreshPlayer/'.$player_id);
            break;
            case '03D-FOD':
                $token = $player['food_token_3_day'] - 1;
                $this->Player->set( array(
                    'id'=>$player_id,
                    'food_token_3_day'=>$token
                ));
                $this->Player->save();
                $this->requestAction('/players/refreshPlayer/'.$player_id);
            break;
            case '04D-FOD':
                $token = $player['food_token_4_day'] - 1;
                $this->Player->set( array(
                    'id'=>$player_id,
                    'food_token_4_day'=>$token
                ));
                $this->Player->save();
                $this->requestAction('/players/refreshPlayer/'.$player_id);
            break;
            case '01D-FOD':
                $token = $player['food_token_1_day'] - 1;
                $this->Player->set( array(
                    'id'=>$player_id,
                    'food_token_1_day'=>$token
                ));
                $this->Player->save();
                $this->requestAction('/players/refreshPlayer/'.$player_id);
            break;
        }
 
        if ($this->endswith($type, 'EVT')) {
            $data = array( 'EventsPlayer'=>array(
                'event_id'=>$event_id,
                'player_id'=>$player_id,
                'current_queue_spot'=> -1
            ));
            $this->EventsPlayer->save($data);

            $data = array( 'EventsCharacter'=>array(
                'event_id'=>$event_id,
                'character_id'=>$char_id
            ));
            $this->EventsCharacter->save($data);

            $char_event = $this->EventsCharacter->find('first',
                    array('conditions' => array(
                        'EventsCharacter.event_id' => $event_id,
                        'EventsCharacter.character_id'=> $char_id
                    )));
            $char_event_id = Set::extract('/EventsCharacter/.', $char_event);
            $char_event_id = $char_event_id[0]['id'];

            $this->loadModel( 'TransactionsSku' );
            $this->TransactionsSku->set( 'character_event_id', $char_event_id );
            $this->TransactionsSku->save();
        } else {
            $this->loadModel( 'TransactionsSku' );
            $this->TransactionsSku->set( 'player_id', $player_id);
            $this->TransactionsSku->save();

            $player_in_event = Set::extract( "/Player[id={$player_id}]", $event );
            foreach ($player_in_event as $player_food) {
                $ep_id = Set::extract( "/Player/EventsPlayer/id", $player_food );
                $this->EventsPlayer->begin();
                $this->EventsPlayer->create(false);
                $this->EventsPlayer->set('id', $ep_id[0]);
                $this->EventsPlayer->set('food', true  );
                $this->EventsPlayer->save();
                $this->EventsPlayer->commit();
            }
        }
        $this->Audit->set( array(
            'player_id'=>$player_id,
            'director_id'=>$this->player['id'],
            'token_type'=>$type,
            'cp_added'=>0,
            'description'=>"Spent token for Player",
        ));
        $this->Audit->save();

        // PROCESS RETURN
        $params['title'] = "Forced Token Spending.";
        $params['message'] = "Forced Token for player." ;
        return $this->sendMessage( $params );
    }

    private function endswith($string, $test) {
        $strlen = strlen($string);
        $testlen = strlen($test);
        if ($testlen > $strlen) return false;
        return substr_compare($string, $test, -$testlen) === 0;
    }

    function fixName() {
        $char_id = $this->r_buffer['char_id'];
        $name = $this->r_buffer['name'];

        $this->Character->set( 'id', $char_id);
        $this->Character->set( 'name', $name );
        $this->Character->save();

        $params['title'] = "Fixed Character";
    	$params['message'] = "Fixed name for Character." ;
        return $this->sendMessage( $params );
    }

    function addMembership() {
        $player_id = $this->r_buffer['player_id'];
        $type = $this->r_buffer['membership'];

        $s=explode('-', $type );
        $time=$s[0];
        $time=str_replace('Y', ' year', $time );
        $time=str_replace('M', ' month', $time );
        $time=str_replace('D', ' day', $time );

        $member_until = date( 'Y-m-d', strtotime( $time ) );
        $this->Player->set( array(
            'id'=>$player_id,
            'member_until'=>$member_until
            )
        );
        $this->Player->save();
        $player = $this->Player->findById($player_id);

        // Get Name
        $first_name = Set::extract( '/Player/first_name', $player);
        $first_name = $first_name[0];
        $last_name = Set::extract( '/Player/last_name', $player);
        $last_name = $last_name[0];
        $name = $first_name." ".$last_name;

        // Perform Audit
        $this->Audit->set( array(
            'player_id'=>$player_id,
            'director_id'=>$this->player['id'],
            'token_type'=>$type,
            'cp_added'=>0,
            'description'=>"Added Membership of type ".$type." to ".$name,
            )
        );
        $this->Audit->save();

        $params['title'] = "Added Tokens";
    	$params['message'] = "Added ".$number." tokens to character ".$name ;
        return $this->sendMessage( $params );
    }

    public function removeRule() {
    	$char_id = $this->r_buffer['rule_char_id'];
        $rule = $this->r_buffer['rule_rem'];
        $rule_obj = $this->Rule->findById($rule);

        // Get CP/VP cost
        $cp = Set::extract('/Rule/cp_cost', $rule_obj);
        $cp = $cp[0];
        $vp = Set::extract('/Rule/vp_cost', $rule_obj);
        $vp = $vp[0];

        // Restore CP/VP
        $character = $this->Character->findById($char_id);
        $curr_cp = Set::extract( '/Character/cp_unspent', $character);
        $curr_cp = $curr_cp[0];
        $curr_vp = Set::extract( '/Character/vp_unspent', $character);
        $curr_vp = $curr_vp[0];

        // remove 'spent' CP/VP
        $spent_cp = Set::extract( '/Character/cp_spent', $character);
        $spent_cp = $spent_cp[0];
        $spent_vp = Set::extract( '/Character/vp_spent', $character);
        $spent_vp = $spent_vp[0];

        $this->Character->set( 'id', $char_id);
        $this->Character->set( 'cp_unspent', $curr_cp + $cp);
        $this->Character->set( 'vp_unspent', $curr_vp + $vp);
        $this->Character->set( 'cp_spent', $spent_cp - $cp);
        $this->Character->set( 'vp_spent', $spent_vp - $vp);
        $this->Character->save();

        $this->CharactersRule->deleteAll(array(
           'CharactersRule.character_id' =>$char_id,
           'CharactersRule.rule_id' => $rule ));

        $params['title'] = "Removed Rule";
    	$params['message'] = "Removed Rule" ;
        return $this->sendMessage( $params );
    }

    public function removeEdit() {
    	$char_id = $this->r_buffer['char_id'];
        $edit = $this->r_buffer['edit'];

        $this->DirectorEdit->deleteAll(array(
           'DirectorEdit.character_id' =>$char_id,
           'DirectorEdit.id' => $edit ));

        $params['title'] = "Removed Quality";
    	$params['message'] = "Removed Quality" ;
        return $this->sendMessage( $params );
    }

    public function removeLives() {
    	$char_id = $this->r_buffer['char_id'];
        $gifts = $this->r_buffer['gifts'];
        $godsends = $this->r_buffer['godsends'];

        $character = $this->Character->findById($char_id);
        $c_gifts = Set::extract( '/Character/lives', $character);
        $c_gifts = $c_gifts[0];
        $c_gods = Set::extract( '/Character/godsends', $character);
        $c_gods = $c_gods[0];

        $this->Character->set( 'id', $char_id);
        $this->Character->set( 'lives', $c_gifts - $gifts );
        $this->Character->set( 'godsends', $c_gods - $godsends );
        $this->Character->save();

        $params['title'] = "Removed gifts and Godsends";
    	$params['message'] = "Removed gifts and godsends for character" ;
        return $this->sendMessage( $params );
    }

    public function checkCP() {
        
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

    	$this->r_buffer['cp_number'] = ( int ) $_POST['number'];
    	$this->r_buffer['character_id'] = ( int ) $_POST['character'];

    	if( $this->r_buffer['cp_number'] > 0 && $this->r_buffer['character_id'] > 0) {
            print $this->addCP();
    	}

    	return;
    }

    public function checkVP() {

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

    	$this->r_buffer['vp_number'] = ( int ) $_POST['number'];
    	$this->r_buffer['character_id'] = ( int ) $_POST['character'];

    	if( $this->r_buffer['vp_number'] > 0 && $this->r_buffer['character_id'] > 0) {
            print $this->addVP();
    	}

    	return;
    }

    public function checkLevel() {

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

    	$this->r_buffer['number'] = ( int ) $_POST['number'];
    	$this->r_buffer['character_id'] = ( int ) $_POST['character'];

    	if( $this->r_buffer['number'] > 0 && $this->r_buffer['character_id'] > 0) {
            print $this->setLevel();
    	}

    	return;
    }
    public function checkToken() {

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

    	$this->r_buffer['token_number'] = ( int ) $_POST['number'];
    	$this->r_buffer['token_player_id'] = ( int ) $_POST['player'];
        $this->r_buffer['token_type'] = ( string ) $_POST['type'];

    	if( $this->r_buffer['token_number'] > 0 && $this->r_buffer['token_player_id'] > 0) {
            print $this->addToken();
    	}

    	return;
    }

    public function checkRemoveToken() {
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
    	$this->r_buffer['token_number'] = ( int ) $_POST['number'];
    	$this->r_buffer['token_player_id'] = ( int ) $_POST['player'];
        $this->r_buffer['token_type'] = ( string ) $_POST['type'];

    	if( $this->r_buffer['token_number'] > 0 && $this->r_buffer['token_player_id'] > 0) {
            print $this->removeToken();
    	}
    	return;
    }

    public function checkBlanket() {

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

    	$this->r_buffer['blanket_event_id'] = ( int ) $_POST['event'];

    	if( $this->r_buffer['blanket_event_id'] > 0 ) {
            print $this->blanketEvent();
    	}

    	return;
    }

    public function checkTraining() {
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

    	$this->r_buffer['t_char_id'] = ( int ) $_POST['character'];
        $this->r_buffer['t_training_id'] = ( int ) $_POST['train'];

    	if( $this->r_buffer['t_char_id'] > 0 && $this->r_buffer['t_training_id']) {
            print $this->addTraining();
    	}

    	return;
    }

    public function checkAddRule() {
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

    	$this->r_buffer['rule_char_id'] = ( int ) $_POST['character'];
        $this->r_buffer['rule_option'] = ( int ) $_POST['rule'];

    	if( $this->r_buffer['rule_char_id'] > 0 && $this->r_buffer['rule_option']) {
            print $this->addRule();
    	}

    	return;
    }

    public function checkRemoveRule() {
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

    	$this->r_buffer['rule_char_id'] = ( int ) $_POST['character'];
        $this->r_buffer['rule_rem'] = ( int ) $_POST['rule'];

    	if( $this->r_buffer['rule_char_id'] > 0 && $this->r_buffer['rule_rem']) {
            print $this->removeRule();
    	}

    	return;
    }

    public function checkRemoveEdit() {
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

    	$this->r_buffer['char_id'] = ( int ) $_POST['character'];
        $this->r_buffer['edit'] = ( int ) $_POST['edit'];

    	if( $this->r_buffer['char_id'] > 0 && $this->r_buffer['edit'] > 0) {
            print $this->removeEdit();
    	}

    	return;
    }

    public function checkRemoveLives() {
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
    	$this->r_buffer['char_id'] = ( int ) $_POST['character'];
        $this->r_buffer['godsends'] = ( int ) $_POST['godsends'];
        $this->r_buffer['gifts'] = ( int ) $_POST['gifts'];

    	if( $this->r_buffer['char_id'] > 0
                && $this->r_buffer['godsends'] >= 0
                && $this->r_buffer['gifts'] >= 0) {
            print $this->removeLives();
    	}
    	return;
    }

    public function checkChangeName() {
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

    	$this->r_buffer['char_id'] = ( int ) $_POST['character'];
        $this->r_buffer['name'] = ( string ) $_POST['name'];

    	if( $this->r_buffer['char_id'] > 0 && $this->r_buffer['name']) {
            print $this->fixName();
    	}

    	return;
    }

    public function checkResetPassword() {
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

    	$this->r_buffer['pass_player_id'] = ( int ) $_POST['player'];
        $this->r_buffer['password'] = ( string ) $_POST['password'];

    	if( $this->r_buffer['pass_player_id'] > 0 && $this->r_buffer['password']) {
            print $this->resetPassword();
    	}
    	return;
    }

    public function checkDeleteChar() {
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

    	$this->r_buffer['char_id'] = ( int ) $_POST['character'];

    	if( $this->r_buffer['char_id'] > 0) {
            print $this->deleteChar();
    	}

    	return;
    }

    public function checkDirectorComment() {
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
        $this->r_buffer['comment'] = ( String ) $_POST['comment'];
        $this->r_buffer['character_id'] = ( int ) $_POST['character'];

        if( $this->r_buffer['character_id'] > 0) {
            print $this->addDirectorComment();
        }
    	return;
    }

    public function checkMembership() {
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

    	$this->r_buffer['player_id'] = ( int ) $_POST['player'];
        $this->r_buffer['membership'] = ( string ) $_POST['membership'];

    	if( $this->r_buffer['player_id'] > 0 && $this->r_buffer['membership']) {
            print $this->addMembership();
    	}

    	return;
    }

    public function checkAssign() {
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

    	$this->r_buffer['player_id'] = ( int ) $_POST['player'];
        $this->r_buffer['char_id'] = ( string ) $_POST['character'];

    	if( $this->r_buffer['player_id'] > 0 && $this->r_buffer['char_id'] > 0) {
            print $this->assignChar();
    	}

    	return;
    }

    public function checkSpendToken() {
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

    	$this->r_buffer['event'] = ( int ) $_POST['event'];
        $this->r_buffer['char_id'] = ( int ) $_POST['character'];
        $this->r_buffer['type'] = (String) $_POST['type'];

    	if( $this->r_buffer['type']
                && $this->r_buffer['char_id'] > 0
                && $this->r_buffer['event'] > 0) {
            print $this->spendToken();
    	}
    	return;
    }

    private function sendMessage( $array, $need_confirm=FALSE ) {
    	$array['need_confirm']=$need_confirm;
    	return json_encode( $array );
    }
}
?>
