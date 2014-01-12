<?php

class EventsPlayer extends AppModel {
	var $name = 'EventsPlayer';
	var $belongsTo = array('Player', 'Event' );
}


?>