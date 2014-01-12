<?php


class Game extends AppModel {
    var $name = 'Game';
    var $hasMany = array( 'Character' );
    //var $cacheQueries = true;
    

}

?>