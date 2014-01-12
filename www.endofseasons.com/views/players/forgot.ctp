<?php
	$html->css( '2-column.css', 'stylesheet', array( 'media'=>'all'), false );
?>
<fieldset>
    <legend>Forgot password</legend>

<?php echo $form->create('Player', array('action' => 'send/forgot')); ?>

<strong>Please enter your email address:</strong>
<?php
    echo $form->input('email' );
    $recaptcha->display_form('echo');
    echo $form->end('Submit');
?>

</fieldset>