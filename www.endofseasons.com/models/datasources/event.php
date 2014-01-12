<?php

class Event extends AppModel {
	var $name = 'Event';
	var $belongsTo= array('Location');
	var $hasAndBelongsToMany = array(
        'Character' =>
            array(
                'className'              => 'Character',
                'joinTable'              => 'events_characters',
                'foreignKey'             => 'event_id',
                'associationForeignKey'  => 'character_id',
                'unique'                 => true,
                'conditions'             => '',
                'fields'                 => '',
                'order'                  => '',
                'limit'                  => '',
                'offset'                 => '',
                'finderQuery'            => '',
                'deleteQuery'            => '',
                'insertQuery'            => ''
            ),
        'Player' =>
            array(
                'className'              => 'Player',
                'joinTable'              => 'events_players',
                'foreignKey'             => 'event_id',
                'associationForeignKey'  => 'player_id',
                'unique'                 => true,
                'conditions'             => '',
                'fields'                 => '',
                'order'                  => '',
                'limit'                  => '',
                'offset'                 => '',
                'finderQuery'            => '',
                'deleteQuery'            => '',
                'insertQuery'            => ''
            ),
        'Sku' =>
            array(
                'className'              => 'Sku',
                'joinTable'              => 'events_skus',
                'foreignKey'             => 'event_id',
                'associationForeignKey'  => 'sku_id',
                'unique'                 => true,
                'conditions'             => '',
                'fields'                 => '',
                'order'                  => '',
                'limit'                  => '',
                'offset'                 => '',
                'finderQuery'            => '',
                'deleteQuery'            => '',
                'insertQuery'            => ''
            )
    );
	
}


?>