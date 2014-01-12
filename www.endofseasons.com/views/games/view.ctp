<?php
	$html->css( '2-column.css', 'stylesheet', array( 'media'=>'all'), false );
?>


<table>
<tr>
	<th>Name</th>
	<th>Genre</th>
	<th>Active?</th>
	<th>Next Event</th>
	<th>Description</th>
</tr>


<?php foreach( $games as $game ) { ?>
<tr>
<td><a href="/events/<?=$game['abbr'] ?>"><?=$game['name'] ?></a></td>
<td><?=$game['type'] ?></td>
<td><?= $game['game_active'] ? 'Yes' : 'No' ?></td>
<td><? //TODO: NEXT EVENT ?></td>
<td><?=$game['description'] ?></td>
</tr>
<?php } ?>
</table>