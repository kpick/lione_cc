<?php
class ReportsController extends AppController {
    var $name = 'Reports';
    var $helpers = array('Session','Xml','Javascript','Html', 'Form','TabDisplay','Ajax');
    var $uses = array( 'Transaction', 'Player', 'Character', 'Event', 'Hacker', 'Storage', 'Sku', 'TransactionsSku');

    function beforeFilter() {
        $this->Auth->allow('index','view');
        parent::beforeFilter();

        $admin = $this->player['admin'];
        if (!$admin) {
            $this->Session->setFlash('You are\'t allowed to view this page. Attempt logged with admin.', 'default' );
            $this->Hacker->set( array(
                    'player_id'=>$this->player['id'],
                    'attempt_type'=>'SEVERE_REPORT',
                    'description'=> 'Player attempted to access Reports page.'
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

    // Gets all Players blanketed for an event.
    function getPlayersForEvent($event) {
        $this->loadModel('EventsPlayer');
        $all_events_players = $this->EventsPlayer->find('all');
        $event_id = Set::extract('/Event/id', $event);
        $event_id = $event_id[0];

        $events_player = Set::extract( "/EventsPlayer[event_id=$event_id]", $all_events_players );
        $player_ids = Set::extract("/EventsPlayer/player_id", $events_player);

    }

    // Function for all the characters at en event
    function getCharactersForEvent($event) {
        $events_characters = Set::extract( '/Character/EventsCharacter/.', $event );
        $character_ids = Set::extract("/character_id", $events_characters);
    }

    function showAllSkus(){
        $tran = $this->Transaction->find('all');
        $this->set('transactions', $tran);
    }

    function showTransactions ($event_id) {
        $listed_players = array();

        $event = $this->Event->findById($event_id);
        $events_characters = Set::extract( '/Character/EventsCharacter/.', $event );
        $num_of_tokens=count($events_characters);

        $sku_cost_blanket_array = Set::extract( '/Sku[applies_to=event|blanket]/base_cost', $event );
        $sku_cost_blanket = $sku_cost_blanket_array[0];

        $sku_cost_food_array = Set::extract( '/Sku[applies_to=event|food]/base_cost', $event );
        $sku_cost_food = $sku_cost_food_array[0];

        $food_counter = 0;
        $players = Set::extract( '/Player/.', $event);
        foreach ($players as $player) {
            $p_id = $player['id'];
            if (!in_array( $p_id, $listed_players )) {
                $events_player =  Set::extract( '/EventsPlayer/.', $player);
                if ($events_player[0]['food'] ==  true) {
                    $food_counter = $food_counter + 1;
                }
                array_push($listed_players, $p_id);
            }
        }
        $this->set( 'event_cost', $sku_cost_blanket );
        $this->set( 'food_cost', $sku_cost_food );
        $this->set( 'num_event_tokens', $num_of_tokens );
        $this->set( 'num_food_tokens', $food_counter );
    }

    function showPlayers($event_id) {
        $space = " ";
        $boundary = "</a> ; ";

        $view_player_name=array();
        $view_char_names=array();
        $view_food=array();
        $view_player_id=array();
        $listed_players = array();

        $this->loadModel('EventsPlayer');
        $all_events_players = $this->EventsPlayer->find('all');
        $event = $this->Event->findById($event_id);

        $events_player = Set::extract( "/EventsPlayer[event_id=$event_id]", $all_events_players );
        $player_ids = Set::extract("/EventsPlayer/player_id", $events_player);

        $events_characters = Set::extract( '/Character/EventsCharacter/.', $event );
        $character_ids = Set::extract("/character_id", $events_characters);

        foreach($player_ids as $id) {
          if (!in_array( $id, $listed_players )) {
              $characters_big = $this->Character->findAllByPlayerId( $id );
              $characters = Set::extract('/Character', $characters_big);
              $total_string = null;
              foreach($characters as $character) {
                  $char_id =  Set::extract('/Character/id',$character);
                  $char_id = $char_id[0];
                  $link = "<a href=/characters/view/".$char_id.">";
                  if (in_array( $char_id, $character_ids )) {
                       $name = Set::extract('/Character/name',$character);
                       $charname = $name[0];
                       $charlink = $link.$charname.$boundary;
                       $total_string = $total_string.$charlink;
                  }
              }
              if (!$total_string) {
                  $total_string = "STAFF/SCRIPT";
                  $food = array(1);
              } else {
                  $food = Set::extract("/EventsPlayer[player_id=$id]/food", $events_player);
              }
              $player_local = $this->Player->findById($id);
              $player_f_name = Set::extract('/Player/first_name', $player_local);
              $player_l_name = Set::extract('/Player/last_name', $player_local);
              
              array_push($view_char_names, $total_string);
              array_push($view_player_name, $player_f_name[0].$space.$player_l_name[0]);
              array_push($view_food, $food[0]);
              array_push($listed_players, $id);
          } // end if
        } // end foreach

        $this->set( 'player_ids', $listed_players );
        $this->set( 'view_player_name', $view_player_name );
        $this->set( 'view_char_names', $view_char_names );
        $this->set( 'view_food', $view_food );
    }
    
    function kitchenCash() {
        $players = $this->Player->find('all', array(
                        'order'=>array( 'Player.last_name ASC')
                    )
	    	);
        $players = Set::extract('/Player/.', $players);
        $this->set( 'view_players', $players );
    }

    function listPlayers() {
        $players = $this->Player->find('all', array(
                        'order'=>array( 'Player.last_name ASC')
                    )
	    	);
        $players = Set::extract('/Player/.', $players);
        $this->set( 'view_players', $players );
    }

    function vocationReport() {
        $view_character = array();
        $view_rule = array();
        $allCharacters = $this->Character->find('all', array(
                        'order'=>array( 'Character.name ASC')));
        foreach ($allCharacters as $character) {
            $rules = Set::extract('/Rule[sorting_category_id=7]',$character);
            array_push($view_character, $character);
            array_push($view_rule, $rules);
        }
        $this->set( 'characters', $view_character );
        $this->set( 'rules', $view_rule );
    }

    function listFactions() {
        $ros = 156;
        $chosen = 157;
        $artificers = 158;
        $characters = $this->Character->find('all', array(
                'conditions'=>array('Character.npc'=>false),
                'order'=>array( 'Character.created DESC')));
        $names = array();
        $ids = array();
        $player_names = array();
        $factions = array();

        foreach ($characters as $character) {
            $name = Set::extract('/Character/name/.', $character);
            array_push($names, $name[0]);

            $id = Set::extract('/Character/id/.', $character);
            array_push($ids, $id[0]);

            $player_fname =  Set::extract('/Player/first_name/.', $character);
            $player_lname =  Set::extract('/Player/last_name/.', $character);
            array_push($player_names, $player_fname[0]." ".$player_lname[0]);

            $rules = Set::extract('/Rule/id/.', $character);
            if (in_array($ros,$rules)) {
                array_push($factions, "Realm of Seasons");
            } else if (in_array($chosen,$rules)) {
                array_push($factions, "Chosen");
            } else if (in_array($artificers,$rules)) {
                array_push($factions, "Artificer");
            } else {
                array_push($factions, "NONE");
            }
        }
        $this->set('player_names', $player_names );
        $this->set('characters', $names);
        $this->set('factions', $factions);
        $this->set('ids', $ids);
    }

    function listClasses() {
        $crusader=36;
        $combat_tech=27;
        $hunter=82;
        $nightblade=109;
        $ritualist=134;
        $runicguardian=135;
        $preserver=281;
        $weaponmaster=274;
        $warden=256;
        $magus=90;
        $scouge=136;
        $spellsword=144;

        $characters = $this->Character->find('all', array(
                'conditions'=>array('Character.npc'=>false),
                'order'=>array( 'Character.created DESC')));
        $names = array();
        $ids = array();
        $player_names = array();
        $classes = array();

        foreach ($characters as $character) {
            $name = Set::extract('/Character/name/.', $character);
            array_push($names, $name[0]);

            $id = Set::extract('/Character/id/.', $character);
            array_push($ids, $id[0]);

            $player_fname =  Set::extract('/Player/first_name/.', $character);
            $player_lname =  Set::extract('/Player/last_name/.', $character);
            array_push($player_names, $player_fname[0]." ".$player_lname[0]);

            $rules = Set::extract('/Rule/id/.', $character);
            if (in_array($crusader,$rules)) {
                array_push($classes, "Crusader");
            } else if (in_array($combat_tech,$rules)) {
                array_push($classes, "Combat Tech");
            } else if (in_array($hunter,$rules)) {
                array_push($classes, "Hunter");
            } else if (in_array($nightblade,$rules)) {
                array_push($classes, "Nightblade");
            } else if (in_array($ritualist,$rules)) {
                array_push($classes, "Ritualist");
            } else if (in_array($runicguardian,$rules)) {
                array_push($classes, "Runic Guardian");
            } else if (in_array($preserver,$rules)) {
                array_push($classes, "Preserver");
            } else if (in_array($weaponmaster,$rules)) {
                array_push($classes, "Weapon Master");
            } else if (in_array($warden,$rules)) {
                array_push($classes, "Warden");
            } else if (in_array($scouge,$rules)) {
                array_push($classes, "Scourge");
            } else if (in_array($spellsword,$rules)) {
                array_push($classes, "Spellsword");
            } else if (in_array($magus,$rules)) {
                array_push($classes, "Magus");
            } else {
                array_push($classes, "NONE");
            }
        }
        $this->set('player_names', $player_names );
        $this->set('characters', $names);
        $this->set('classes', $classes);
        $this->set('ids', $ids);
    }

    function listLives() {
        $players = $this->Player->find('all', array(
                        'order'=>array( 'Player.last_name ASC')
                    )
	    	);
        $players = Set::extract('/Player/.', $players);

        $view_players = array();
        $view_characters = array();
        $view_gifts = array();
        $view_godsends = array();

        foreach($players as $player) {
            $characters = $this->Character->findAllByPlayerId( $player['id'] );
            foreach ($characters as $character) {
                array_push($view_players, $player['first_name']." ".$player['last_name']);
                $name = Set::extract('/Character/name',$character);
                $charname = $name[0];
                array_push($view_characters, $charname);

                $gifts = Set::extract('/Character/lives',$character);
                $gifts = $gifts[0];
                array_push($view_gifts, $gifts);

                $godsends = Set::extract('/Character/godsends',$character);
                $godsends = $godsends[0];
                array_push($view_godsends, $godsends);
            }
        }
        $this->set( 'players', $view_players );
        $this->set( 'characters', $view_characters );
        $this->set( 'gifts', $view_gifts );
        $this->set( 'godsends', $view_godsends );
    }

    function listPlayersCharacters() {
        $boundary = "</a>".' ; ';
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
                  $link = "<a href=/characters/view/".$char_id.">";
                  $name = Set::extract('/Character/name',$character);
                  $charname = $name[0];
                  $charlink = $link.$charname.$boundary;
                  $total_string = $total_string.$charlink;
            }
            array_push($view_characters, $total_string);
        }
        $this->set( 'view_players', $view_players );
        $this->set( 'view_characters', $view_characters );
    }

    function listCharactersLevels() {
        $boundary = "</a>".' ; ';
        $players = $this->Player->find('all', array(
                        'order'=>array( 'Player.last_name ASC')
                    )
	    	);
        $players = Set::extract('/Player/.', $players);

        $view_players = array();
        $view_characters = array();

        foreach($players as $player) {
            $characters = $this->Character->findAllByPlayerId( $player['id'] );
            $total_string = null;
            foreach ($characters as $character) {
                  array_push($view_players, $player['first_name']." ".$player['last_name']);
                  array_push($view_characters, $character);
            }
        }
        $this->set( 'view_players', $view_players );
        $this->set( 'view_characters', $view_characters );
    }

    function listStorage($year) {
        $storage_all = $this->Storage->findAll();
        $storage_all = Set::extract("/Storage[year=$year]", $storage_all);
        $players = array();
        $sku_names = array();
        $quantities = array();
        foreach ($storage_all as $storage) {
            $storage = Set::extract("/Storage/.", $storage);
            $player = $this->Player->findById($storage[0]['player_id']);
            $player =  Set::extract("/Player/.", $player);
            $sku = $this->Sku->findById($storage[0]['sku_id']);
            $sku = Set::extract("/Sku/.", $sku);
            array_push($players, $player[0]['first_name'].' '.$player[0]['last_name']);
            array_push($sku_names, $sku[0]['description']);
            array_push($quantities,$storage[0]['quantity']);
        }
        $this->set( 'view_players', $players );
        $this->set('view_skus', $sku_names);
        $this->set('view_quant', $quantities);
        $this->set('year', $year);
    }

    function view() {
        $this->loadModel('Event');
        $events = $this->Event->find('all');
        $events = Set::extract('/Event/.', $events);

        $this->set('events', $events);
    }
}
?>
