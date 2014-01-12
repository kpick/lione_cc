<?php
class CartController extends AppController {
    var $name = 'Cart';
	var $helpers = array('Html','Session');
	var $uses = array( 'Transaction', 'Sku', 'Coupon', 'Storage');
	var $components = array( 'Paypal' );
		
	function beforeFilter() {	    
	    $this->Paypal->return_url = Configure::read( 'Info.url' ) . '/cart/checkout/auth';
	    $this->Paypal->cancel_url = Configure::read( 'Info.url' ) . '/cart/checkout/cancel';
            Configure::write('Paypal.live', true);

	    if( Configure::read( 'Paypal.live' ) ) {
	        $this->Paypal->environment = 'live';
	        $this->Paypal->username ='uname';
	        $this->Paypal->password = 'passwd';
	        $this->Paypal->signature = 'sig';
	    } else {
	        $this->Paypal->environment = 'sandbox';
	        $this->Paypal->username ='uname';
	        $this->Paypal->password = 'passwd';
	        $this->Paypal->signature = 'sig';
	    }
	    
	    parent::beforeFilter();
	}
	    

    
    
    function add() {
        if(! $this->params || ! isset( $this->params['pass'][0] ) ) {
            $this->redirect('view');
        }
        $sku = $this->params['pass'][0];
        $sku_info = $this->Sku->findByValue( $sku );
        if( count( $sku_info ) ) {
            $skus = $this->Session->read( 'Cart.skus' );
            if(! $skus ) {
                $skus = array();
                $skus[$sku]=1;
            } else {
                if( array_key_exists( $sku, $skus ) && $sku_info['Sku']['allow_multiple'] ) {
                    $skus[$sku] += 1;
                } else {
                    $skus[$sku] = 1;
                }
            }


            $this->Session->write( 'Cart.skus', $skus );
            
        }
        $this->redirect('view');

    }
    
    function remove() {
        $sku = $this->params['pass'][0];
        $skus = $this->Session->read( 'Cart.skus' );
        if( array_key_exists( $sku, $skus ) ) {
            unset( $skus[$sku] );
        }
        
        $this->Session->setFlash( 'Your cart has been updated' );
        $this->Session->write( 'Cart.skus', $skus );
        $this->redirect('view');
    }
    
    /** tally the amount of sale from the session **/
    private function getAmount($skus=array(),$coupons=array()) {
        $amt=0;
        if(! $skus ) return($amt);
        if( count($coupons ) ) {
            $this->loadModel( 'CouponsSku' );
            foreach( $coupons as $code=>$ignore ) {
                $coupon_info = $this->CouponsSku->find(array( 'Coupon.code'=>$code));
                if( $coupon_info ) {
                    //TODO: coupon price mod here
                }
            }
        }
        
        foreach( $skus as $sku=>$qty ) {
            $sku_info = Set::extract( 'Sku', $this->Sku->findByValue( $sku ) );
            $cost = $sku_info['base_cost'];
            if( $sku_info['allow_multiple'] ) {
                $amt += $cost * $qty;
            } else {
                $amt += $cost;
            }
        }
        
        return($amt);
        
    }
    
    private function updatePlayer( $paypal_info ) {
        $this->loadModel( 'Player' );
        $player_array = array( 'id'=>$this->player['id'] );
        
        if(! $this->player['first_name'] ) $player_array['first_name'] = $paypal_info['FIRSTNAME'];
        if(! $this->player['last_name'] ) $player_array['last_name'] = $paypal_info['LASTNAME'];
        if(! $this->player['address'] ) $player_array['address'] = $paypal_info['SHIPTOSTREET'];
        if(! $this->player['city'] ) $player_array['city'] = $paypal_info['SHIPTOCITY'];
        if(! $this->player['state'] ) $player_array['state'] = $paypal_info['SHIPTOSTATE'];
        if(! $this->player['zipcode'] ) $player_array['zipcode'] = $paypal_info['SHIPTOZIP'];
        
        if( count($player_array) > 1 ) {
            $this->Player->set( $player_array );
            $this->Player->save();
            $this->requestAction('/players/refreshPlayer/'.$this->player['id']);             
        }
        return;
    }
    
    private function writeTransaction( $paypal_info ) {
        //write transaction first
        $this->Transaction->set( array( 
            'player_id'=>$this->player['id'],
            'coupon_id'=>0,
            'pride_points_used'=>0,
            'pp_payer_id'=>$paypal_info['payer_id'],
            'pp_trxn_id'=>$paypal_info['TRANSACTIONID'],
            'pp_correlation_id'=>$paypal_info['CORRELATIONID'],
            'transaction_amt'=>$paypal_info['AMT'],
            'fee_amt'=>$paypal_info['FEEAMT'],
            'tax_amt'=>$paypal_info['TAXAMT']
            )
        );
        $this->Transaction->save();
        $txn_id=$this->Transaction->id;
        
        //write transaction skus
        $session_skus = $this->Session->read( 'Cart.skus' );
        
        foreach( $session_skus as $sku=>$qty ) {
            $this->loadModel( 'TransactionsSku' );
            $this->TransactionsSku->begin();
            $this->TransactionsSku->create(false);
            $sku_info = $this->Sku->grab( $sku );
            $data=array();
            $data['transaction_id']=$txn_id;
            $data['sku_id']=$sku_info['id'];
            $data['character_event_id']=0;
            $data['subtotal'] = $sku_info['base_cost'];
            $data['quantity']=$qty;
            $this->TransactionsSku->save($data);
            $this->TransactionsSku->commit();

            $id = $this->player['id'];
            $refresh_player = $this->Player->findById($id);
            $refresh_player = Set::extract('/Player/.', $refresh_player);
            $refresh_player = $refresh_player[0];
            $this->player = $refresh_player;

            $sku_value = $sku_info['value'];
            //handle specific actions for skus
            $applies_to = explode( '|', $sku_info['applies_to'] );
            switch($applies_to[0] ) {
                case 'character':
                    $this->Session->write( 'Receipt.show_character_screen', 1 );
                break;

                case 'player':
                    $action = $applies_to[1];
                    $this->loadModel('Player');
                    switch($action) {
                        case 'membership':
                            $s=explode('-',$sku_info['value']);
                            $time=$s[0];
                            $time=str_replace('Y', ' year', $time );
                            $time=str_replace('M', ' month', $time );
                            $time=str_replace('D', ' day', $time );
                            
                            $member_until = date( 'Y-m-d', strtotime( $time ) );
                            $this->Player->set( array( 
                                'id'=>$this->player['id'],
                                'member_until'=>$member_until
                                )
                            );
                        break;
                        
                        case 'food':
                            $kitchen_cash = $this->player['kitchen_cash'] + $data['quantity'];
                            $this->Player->set(array(
                                'id'=>$this->player['id'],
                                'kitchen_cash'=>$kitchen_cash
                            ));
                        break;

                        case 'storage':
                            $this->Storage->begin();
                            $this->Storage->create(false);
                            $this->Storage->set(array(
                                'player_id'=>$this->player['id'],
                                'sku_id'=>$sku_info['id'],
                                'year'=>2011,
                                'quantity'=>$data['quantity']
                            ));
                            $this->Storage->save();
                            $this->Storage->commit();
                        break;
                        
                        case 'script':
                            $script_points = $this->player['script_points'] + $data['quantity'];
                            $this->Player->set( array( 
                                'id'=>$this->player['id'],
                                'script_points'=>$script_points
                            ));
                        break;
                    }
                    $this->Player->save();
                    $this->requestAction('/players/refreshPlayer/'.$this->player['id']);
                break;
                
                case 'event':                    
                    $action = $applies_to[1];
                    $this->loadModel('Player');
                    $this->loadModel('Transaction');
                    $transaction = $this->Transaction->findByPlayerId( $this->player['id'] );
                    $sku_info['id'];


                    switch($action) {
                        case 'food':
                            switch($sku_value){
                                case '03D-FOD':
                                    $token = $this->player['food_token_3_day'] + $data['quantity'];
                                    $this->Player->set( array(
                                        'id'=>$this->player['id'],
                                        'food_token_3_day'=>$token
                                    ));
                                break;
                                case '04D-FOD':
                                    $token = $this->player['food_token_4_day'] + $data['quantity'];
                                    $this->Player->set( array(
                                        'id'=>$this->player['id'],
                                        'food_token_4_day'=>$token
                                    ));
                                break;
                                case '01D-FOD':
                                    $token = $this->player['food_token_1_day'] + $data['quantity'];
                                    $this->Player->set( array(
                                        'id'=>$this->player['id'],
                                        'food_token_1_day'=>$token
                                    ));
                                break;
                                // 2011 Blanket.
                                case '02-IMP':
                                    $token = $this->player['food_token_3_day'] + 5;
                                    $this->Player->set( array(
                                        'id'=>$this->player['id'],
                                        'food_token_3_day'=>$token
                                    ));
                                break;
                            }
                        break;
                        case 'blanket':
                            switch($sku_value){
                                case '03D-EVT':
                                    $token = $this->player['event_token_3_day'] + $data['quantity'];
                                    $this->Player->set( array(
                                        'id'=>$this->player['id'],
                                        'event_token_3_day'=>$token
                                    ));
                                break;
                                case '04D-EVT':
                                    $token = $this->player['event_token_4_day'] + $data['quantity'];
                                    $this->Player->set( array(
                                        'id'=>$this->player['id'],
                                        'event_token_4_day'=>$token
                                    ));
                                break;
                                case '01D-EVT':
                                    $token = $this->player['event_token_1_day'] + $data['quantity'];
                                    $this->Player->set( array(
                                        'id'=>$this->player['id'],
                                        'event_token_1_day'=>$token
                                    ));
                                break;
                                // FOR 2011, this is all 3 Day tokens.
                                case '01-IMP':
                                    $token = $this->player['event_token_3_day'] + 5;
                                    $this->Player->set( array(
                                        'id'=>$this->player['id'],
                                        'event_token_3_day'=>$token
                                    ));
                                break;
                            }
                        break;
                     }
                $this->Player->save();
                $this->requestAction('/players/refreshPlayer/'.$this->player['id']);
            }
        }
    }
    
    function receipt() {
    	$skus = $this->Session->read( 'Receipt.skus' );
    	$coupons = $this->Session->read( 'Receipt.coupons' );
        $this->Session->del( 'Receipt' );
        if( empty( $skus ) ) {
            $this->redirect( '/players/view');
        }
        
        $sku_return=array();
        $total=$this->getAmount($skus, $coupons);
        $show_character_message=FALSE;
        if( count( $skus ) ) { 
            foreach( $skus as $sku=>$qty ) {
                $s = $this->Sku->grab( $sku );
                $keys = array_keys( $s );
                foreach($keys as $key) {
                    $sku_return[$s['id']][$key] = $s[$key];
                }
                
                $sku_return[$s['id']]['qty'] = ( int ) $qty;
                $applies_to = explode('|',$s['applies_to'] );
                if( $applies_to[0] == 'character' ) $show_character_message=TRUE;
                
            }
        }
        $this->set( 'character_message', $show_character_message );
        $this->set( 'cart_info', $sku_return );
        $this->set( 'cart_total', $total );     
        
        
    }
    
    function checkout( ) {
        if(isset($this->params['pass'])) {
            $pass = $this->params['pass'];         
            if( in_array( 'cancel', $pass ) ) {
                $this->Session->setFlash( 'Your transaction was cancelled' );
                $this->redirect('view');
            }
            
            if( in_array( 'auth', $pass ) ) {
                $session_skus = $this->Session->read( 'Cart.skus' );
                $session_coupons = $this->Session->read( 'Cart.coupons' );
                $amt = $this->getAmount($session_skus, $session_coupons);
                $token = urldecode($this->params['url']['token']);
                $payer_id = urldecode($this->params['url']['PayerID'] );
                $details = $this->Paypal->getCheckoutDetails($token,$payer_id);
                $this->updatePlayer($details);
                $success=$this->Paypal->doExpressCheckout($amt,$token,$payer_id);
                if( $success ) {
                    $success['payer_id']=$payer_id;
                    $this->writeTransaction( $success );       
                    // clear the cart and push the info into receipt
                    $this->Session->delete( 'Cart' );
                    $this->Session->write( 'Receipt.skus', $session_skus );
                    $this->Session->write( 'Receipt.coupons', $session_coupons );
                    $this->redirect('receipt');
                } else {
                    $this->Session->setFlash( 'There was an error in your transaction.' );
                    $this->redirect('view');          
                }
            }
        }
        
        if( $this->data ) {
            $post_skus = Set::extract( 'Cart', $this->data );
            $post_coupons = Set::extract( 'Coupons', $this->data );
            $session_skus = $this->Session->read( 'Cart.skus' );
            $session_coupons = $this->Session->read( 'Cart.coupons' );
            
            if( empty( $post_skus ) ) {
                $this->Session->setFlash( 'There was an error in your transaction.');
                $this->redirect('view');
            }
            
            foreach($post_skus as $sku=>$qty ) {
                $sku_info = $this->Sku->grab( $sku );
                
                if( array_key_exists( $sku, $session_skus ) ) {
                    if( $qty == 0 ) {
                        unset( $session_skus[$sku] );
                    } elseif( $sku_info['allow_multiple'] ) {
                        $session_skus[$sku] = $qty;
                    }
                }
            }

            if( $post_coupons ) {
                foreach( $post_coupons as $key=>$value ) {
                    //TODO: VALIDATE COUPON HERE
                    $session_coupons[$code]=1;    
                }
            } else {
                $session_coupons = array();
            }
            
            $this->Session->setFlash( 'Your cart has been updated' );
            $this->Session->write( 'Cart.skus', $session_skus );
            $this->Session->write( 'Cart.coupons', $session_coupons );
            $this->redirect('view');
        } else {
            // send them to paypal
            $amt = $this->getAmount($this->Session->read( 'Cart.skus' ), $this->Session->read( 'Cart.coupons' ) );
            if(! $amt ) {
                $this->Session->setFlash( 'Your cart is empty' );
                $this->Session->delete( 'Cart' );
                $this->redirect('view');
            }
            
            $success = $this->Paypal->initiateExpressCheckout( $amt );
            if(! $success ) {
                $this->Session->setFlash( 'There was an error trying to contact Paypal.  Please wait a minute and try again.' );
                $this->redirect('view');               
            }
        }
        
    }
    

    
    function view() {
        $skus = $this->Session->read( 'Cart.skus' );
        $coupons = $this->Session->read( 'Cart.coupons');
        $sku_return=array();
        $coupon_return=array();
        
        $total=$this->getAmount($skus,$coupons);
        $show_character_message=FALSE;
 
        if( count( $skus ) ) { 
            foreach( $skus as $sku=>$qty ) {
                $s = $this->Sku->grab( $sku );
                $keys = array_keys( $s );
                foreach($keys as $key) {
                    $sku_return[$s['id']][$key] = $s[$key];
                }
                
                $sku_return[$s['id']]['qty'] = ( int ) $qty;
                $applies_to = explode('|',$s['applies_to'] );
                if( $applies_to[0] == 'character' ) $show_character_message=TRUE;
            }
        }

        /** character select box **/
        $this->set( 'character_message', $show_character_message );
        $this->set( 'cart_info', $sku_return );
        $this->set( 'cart_total', $total );

        if( Configure::read( 'Paypal.live' ) ) {
            $this->set( 'pp_image', 'https://fpdbs.paypal.com/dynamicimageweb?cmd=_dynamic-image' );
        } else {
            $this->set( 'pp_image', 'https://fpdbs.sandbox.paypal.com/dynamicimageweb?cmd=_dynamic-image' );
        }
    }
    
    
}

?>