<?php
	$html->css( '2-column.css', 'stylesheet', array( 'media'=>'all'), false );
?>

<h3>This is the public view</h3>

<?php 


foreach( $characters as $character ) {  ?>

<a href="/games/<?=$character['Game']['id'] ?>"><?=$character['Game']['name'] ?></a>  <a href="/characters/<?=$character['Character']['id'] ?>"><?=$character['Character']['name'] ?></a>
<?=$character['Character']['level'] ?> <?=$character['Character']['xp'] ?><br />
<?php } ?>