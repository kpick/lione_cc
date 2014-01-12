<?php
	$html->css( '2-column.css', 'stylesheet', array( 'media'=>'all'), false );
?>
<fieldset>
    <legend>Sign Up!</legend>

<?php echo $form->create('Player', array('action' => 'signup')); ?>


<?php
    echo $form->input('email' );
    echo $form->input('password', array('type'=>'password' ) );
    $options['minYear'] = date( 'Y', strtotime( '-80 years' ) );
    $options['maxYear'] = date( 'Y' );
    echo $form->input('dob', $options );
    $recaptcha->display_form('echo');
    echo $form->end('Submit');
?>

</fieldset>