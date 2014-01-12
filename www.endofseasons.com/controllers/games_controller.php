<?php
class GamesController extends AppController {
    var $name = 'Games';
	var $helpers = array();
	
	
	function beforeFilter() {
	    $this->Auth->allow('view');
	    parent::beforeFilter();
    }
    
    function view()  {
        $games = $this->Game->find('all');
        $games = Set::extract( '/Game/.', $games );
        $this->set( 'games', $games );
        
    }
    
    public function select() {
    	$games = $this->Game->find('all', array('conditions'=>array('Game.game_active'=>1 ), 'order'=>array( 'name ASC' ) ) );
    	$games = Set::extract( '/Game/.', $games );
    	$this->set( 'games', $games );
    }
    
    
 
}

?>