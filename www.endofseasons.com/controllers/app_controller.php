<?php

class AppController extends Controller {
    var $components = array( 'Auth', 'RequestHandler', 'Session', 'Email');
    var $scaffold = 'admin';
    var $layout = 'default';
    
    protected $r_buffer_info = array();
    
    function make_serial($array) {
            if(is_array($array)) {
                    return(base64_encode(serialize($array)));
            }
    }

    function unmake_serial($str) {
            $arr = @unserialize(base64_decode($str));
            if(is_array($arr ) ) {
                    return($arr);
            }

            /** temporary check since we have non-encoded arrays all over the place **/
            $arr = unserialize($str);
            if(is_array($arr ) ) {
                    return($arr);
            }
    }
    
    function beforeFilter() {
        Security::setHash('md5');
        
	// Authenticate
        $this->Auth->userModel = 'Players';
        $this->Auth->fields = array( 
            'username'=>'email',
            'password'=>'password'
        );
		
        $this->Auth->loginAction = array('controller' => 'players', 'action' => 'login');
        $this->Auth->loginRedirect = array('controller' => 'players', 'action' => 'view');
        $this->Auth->loginError = 'Whoops! Wrong email or password';
        $this->Auth->userScope = array('Players.is_active' => 1);


        if( $this->RequestHandler->isAjax() ) {
            $this->RequestHandler->setContent( 'json', 'text/x-json' );
            Configure::write('debug', 0);
        }

        if( $this->RequestHandler->isXml() ) {
            $this->RequestHandler->setContent( 'xml', 'text/xml' );
            Configure::write( 'debug', 0 );
        }

        $this->player = Set::extract( 'Players', $this->Auth->user( ) );
        $this->set( 'login_info', $this->player );

        //Specific to cart info
        //@see CartController
        $cart_session = $this->Session->read('Cart.skus');
        $cart_count=0;
        if(isset($cart_session) ) {
            foreach($cart_session as $sku=>$qty ) {
                $cart_count++;
            }
        }
        $this->set('cart_count',$cart_count);
        
        //Specific to making an event registration
        //@see EventsController::checkReservation()
        if( $this->Session->check( 'reservation.buffer' ) ) {
            $this->r_buffer_info = $this->Session->read( 'reservation.buffer' );
            $this->Session->del( 'reservation.buffer' );
        }

        if (in_array('prefix', $this->params) ) {
            $prefix = $this->params['prefix'];
            if ($prefix == 'admin' && !$this->player['admin']) {
                $this->redirect("/players/view");
            }
        }
    }
	
    /**
    * Refreshes the Auth session
    * @param string $field
    * @param string $value
    * @return void
    */
    function refreshAuth($field = '', $value = '') {
        Security::setHash('');
        if (!empty($field) && !empty($value)) {
            $this->Session->write($this->Auth->sessionKey .'.'. $field, $value);
        } else {
            if (isset($this->Player)) {
                $this->Session->write('Auth.Players',Set::extract('Player',$this->Player->read(false,$this->Auth->user('id'))));
            } else {
                $this->Auth->login(ClassRegistry::init('Player')->findById($this->Auth->user('id')));
            }
        }
    }
}
?>