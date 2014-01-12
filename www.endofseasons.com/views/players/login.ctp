<?php
	$html->css( '2-column.css', 'stylesheet', array( 'media'=>'all'), false );
?>

<fieldset>
    <legend>Log In</legend>
<?php
    echo $form->create('Players', array('action' => 'login'));
	echo $form->input('email', array('label'=>'Email'));
	echo $form->input('password', array('label'=>'Password', 'type'=>'password'));
	echo $form->end('Sign In');
?>
</fieldset>

<p><a href="signup">Sign up</a> | <a href="send/forgot">Forgot password</a></p>