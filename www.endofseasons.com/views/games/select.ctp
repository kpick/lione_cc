<?php
	$html->css( '2-column.css', 'stylesheet', array( 'media'=>'all'), false );
?>

<strong>Select a campaign to create a character</strong>
<table>

<tr>
	<th>Name</th>
	<th>Genre</th>
	<th>Next Event</th>
	<th>Description</th>
</tr>


<?php foreach( $games as $game ) { ?>
<tr>
<td><a href="/characters/add/<?=$game['abbr'] ?>"><?=$game['name'] ?></a></td>
<td><?=$game['type'] ?></td>
<td><? //TODO: NEXT EVENT ?></td>
<td><?=$game['description'] ?></td>
</tr>
<?php } ?>
</table>