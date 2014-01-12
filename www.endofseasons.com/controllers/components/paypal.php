<?php

 class PaypalComponent extends Object {     
        private static $version = '52.0';
        private static $currency = 'USD';
                          
        public function startup( &$controller ) {
            $this->controller =& $controller;
            if($this->environment=='live') {
                $this->link_sep = '';
            } else {
                $this->link_sep=$this->environment .'.';
            }
        }
        
        
        private function buildPostfields( $amt=0,$token='',$payer_id='' ) {
            $arr['METHOD'] = $this->method;
            $arr['VERSION'] = urlencode( PaypalComponent::$version );
            $arr['PWD'] = $this->password;
            $arr['USER'] = $this->username;
            $arr['SIGNATURE'] = $this->signature;
            $arr['ReturnUrl'] = $this->return_url;
            $arr['CANCELURL'] = $this->cancel_url;
            $arr['PAYMENTACTION'] = $this->transaction_type;
            $arr['CURRENCYCODE'] = urlencode( PaypalComponent::$currency );
            
            if( $amt > 0 ) {
                $arr['Amt'] = number_format($amt,2);
            }
            
            if( $token ) {
                $arr['TOKEN'] = urlencode($token);
            }
            
            if( $payer_id ) {
                $arr['PayerID'] = urlencode($payer_id);
            }
            
            
            $tmp = array( );
            foreach( $arr as $key=>$val ) {
                $tmp[] = "$key=$val";
            }
            
            return( implode( '&', $tmp ) );
        }

        private function parseResponse( $resp ) {
            $response_array = explode( '&', $resp );
            $return_array = array( );
            foreach( $response_array as $r ) {
                $i = explode( '=', $r );
                if( sizeof( $i ) > 1 ) {
                    $return_array[$i[0]] = urldecode($i[1]);
                }
            }
            
            if(! sizeof( $return_array ) || ! array_key_exists( 'ACK', $return_array ) ) {
                //TODO: SET ERROR - invalid response
                return( FALSE );
            }
            
            return($return_array);

        }
        
        private function sendToPaypal($postfields) {
            $ch = curl_init( );
            curl_setopt( $ch, CURLOPT_URL, 'https://api-3t.'.$this->link_sep.'paypal.com/nvp' );
            curl_setopt($ch, CURLOPT_VERBOSE, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	        curl_setopt($ch, CURLOPT_POST, 1);
	        curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields); 
	        $httpResponse = curl_exec($ch);
            if(! $httpResponse ) {
                exit("$methodName_ failed: ".curl_error($ch).'('.curl_errno($ch).')');
            }
            
            return($httpResponse);
        }
            
        
        public function initiateExpressCheckout( $amt ) {
            $this->method = 'SetExpressCheckout';
            $this->transaction_type = 'Authorization'; // or 'Sale' or 'Order'
            $nvpreq = $this->buildPostfields($amt);
            $raw_response = $this->sendToPaypal($nvpreq);
            $response = $this->parseResponse($raw_response);
            
            switch( strtoupper( $response['ACK'] ) ) {
                case 'SUCCESS':
                case 'SUCCESSWITHWARNING':
                    //TODO: HANDLE WARNING
                    $token = $response['TOKEN'];
                break;
                
                default:
                    return( FALSE );
                break;
            }
            
            $url = "https://www.".$this->link_sep."paypal.com/webscr?cmd=_express-checkout&token=$token&$nvpreq";
            header("Location:$url");
            exit(0);    
        }
        
        public function getCheckoutDetails($token,$payer_id) {
            $this->method = 'GetExpressCheckoutDetails';
            $this->transaction_type = '';
            $nvpreq = $this->buildPostfields(0,$token,$payer_id);
            $raw_response = $this->sendToPaypal($nvpreq);
            $response = $this->parseResponse($raw_response);
            return($response);
        }
        
        public function doExpressCheckout( $amt, $token, $payer_id ) {            
            $this->method = 'DoExpressCheckoutPayment';
            $this->transaction_type = 'Sale';
            $nvpreq = $this->buildPostfields($amt,$token,$payer_id);
            $raw_response = $this->sendToPaypal($nvpreq);
            $response = $this->parseResponse($raw_response);
            
            switch( strtoupper( $response['ACK'] ) ) {
                case 'SUCCESS':
                case 'SUCCESSWITHWARNING':
                    return($response);
                break;
                
                default:
                    // TODO: SET ERROR
                    //$response['REASONCODE']
                    return( FALSE );
                break;
            }
        }
    }

?>