<?php
    $html->css( '2-column.css', 'stylesheet', array( 'media'=>'all'), false );
    echo $form->create('Player' );
    echo $form->input( 'first_name' );
    echo $form->input( 'last_name' );
    echo $form->input( 'address' );
    echo $form->input( 'city' );
    echo $form->input( 'state' );
    echo $form->input( 'zipcode' );
    echo $form->input( 'phone' );
    echo $form->input( 'emergency_contact', array( 'rows'=>'2' ) );
    echo $form->input( 'allergies', array( 'rows'=>'2' )  );
    echo $form->input( 'is_public' );
    
    echo $form->end('Submit');
    
?>