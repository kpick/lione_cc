<?php
	$html->css( '2-column.css', 'stylesheet', array( 'media'=>'all'), false );
?>
<fieldset>
    <legend>Edit Password</legend>
<?php
    echo $form->create('Players', array('action' => 'edit/password'));
	echo $form->input('password', array('label'=>'Enter a new password', 'type'=>'password'));
	echo $form->end('Update');
?>
</fieldset>