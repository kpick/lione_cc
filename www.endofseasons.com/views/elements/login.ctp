<?php
    echo $form->create('Players', array('action' => 'login'));
	echo $form->input('email', array('label'=>'Email'));
	echo $form->input('password', array('label'=>'Password', 'type'=>'password'));
	echo $form->end('Sign In');
?>

<p><a href="signup">Sign up</a> | <a href="forgot">Forgot password</a></p>