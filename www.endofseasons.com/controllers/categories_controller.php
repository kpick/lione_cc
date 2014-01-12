<?php
class CategoriesController extends AppController {
    var $name = 'Categories';
	var $helpers = array('Session','Javascript','Html');
	var $components = array( 'RequestHandler' );
	private $r_buffer=array( 'current_queue_spot'=>-1);
	
}

?>