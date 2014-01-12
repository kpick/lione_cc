<?php


class Character extends AppModel {
    var $name = 'Character';
    var $hasOne = array( 'Bank' );
    var $hasMany = array ('DirectorEdit', 'VocationUpdate');
    var $belongsTo = array( 'Player', 'Game' );
    
    var $hasAndBelongsToMany = array(
        'Rule' =>
            array(
                'className'              => 'Rule',
                'joinTable'              => 'characters_rules',
                'foreignKey'             => 'character_id',
                'associationForeignKey'  => 'rule_id',
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