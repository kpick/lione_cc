<?php


class Skill extends AppModel {
    var $name = 'Skill';
    var $belongsTo = array(
        'Character' => array(
            'className'    => 'Character',
            'foreignKey'    => 'character_id'
        ),
        'Rule'=>array( 
            'className'=>'Rule',
            'foreignKey'=>'rule_id'
         )
        
    );  



    
}

?>