<?php


class Player extends AppModel {
    var $name = 'Player';
    //var $cacheQueries = true;
    var $hasMany = array( 'Character', 'Transaction');
    
	var $validate = array(
	    'email' => array( 
	        'email-r0'=>array( 'rule'=>'notEmpty', 'message'=>'Please provide an email address' ),
	        'email-r1'=>array( 'rule'=>'email', 'message'=>'Invalid email'),
	        'email-r2'=>array( 'rule'=>'isUnique', 'message'=>'Sorry! This email is already taken' )
	        ),
	    'password'=>array(
	         'pass-r0'=>array( 'rule'=>'notEmpty', 'message'=>'Please provide a password' )
	        ),
	    
	    'dob'=>array( 
	        'dob-r0'=>array( 'rule'=>'notEmpty', 'message'=>'Please provide a birthdate' ),
	        'dob-r1'=>array( 'rule'=>'date', 'message'=>'Not a valid birthdate' ),
	        'dob-r2'=>array( 'rule'=>'minAge', 'message'=>'Sorry! You must be at least 16 to sign up' )
	        )
	);
	
	
    function beforeSave() {
        if( isset( $this->data['Player']['password'] ) ) {
            $this->data['Player']['password'] = md5($this->data['Player']['password'] );
        }
       
        return( TRUE );
    }

	
	function minAge($check) {
	    $bd = strtotime( $check['dob'] );
	    if( strtotime( "-18 years" ) <= strtotime( $check['dob'] ) ) {
                if( strtotime( "-16 years" ) >= strtotime( $check['dob'] ) ) {
                     $this->data['Player']['minor'] = true;
                     return (TRUE) ;
                }
	        return( FALSE );
            }
	    return( TRUE );
	}


}

?>