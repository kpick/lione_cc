<?php

class EventsCharacter extends AppModel {
	var $name = 'EventsCharacter';
	var $belongsTo = array('Character', 'Event' );
}


?>