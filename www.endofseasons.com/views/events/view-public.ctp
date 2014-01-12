<?php $html->css( '2-column.css', 'stylesheet', array( 'media'=>'all'), false ); ?>

<table>

<tr>
	<th>Name</th>
	<th>Date</th>
	<th>Location</th>
	<th>Description</th>
</tr>


<?php foreach( $events as $event ) { ?>
<tr>
<td><?=$event['name'] ?></td>
<td><?= date( 'm/d/Y', strtotime( $event['start_date'] ) ) ?> - <?= date( 'm/d/Y', strtotime( $event['end_date'] ) ) ?></td>
<td><?=$event['location_id'] ?></td>
<td><?=$event['description'] ?></td>
</tr>
<?php } ?>
</table>