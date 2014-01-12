<?php
	$html->css( '3-column.css', 'stylesheet', array( 'media'=>'all'), false );
?>

<table style="margin-left: 50px; margin-top: 20px; margin-bottom:20px;width:75%; border-bottom:1px solid black;">
<tr><td colspan="2"><strong><?=$character['name'] ?></strong> 
<?php if( $character['cp_unspent'] > 0 || $character['vp_unspent'] > 0 ) { ?>
    [<a href="/characters/edit/<?=$character['id'] ?>">edit</a>]
<?php } ?>

<?php
$level = $character['cp_spent'] + $character['cp_unspent'];
?>
<br />
Player Name: <?=$player[0]['first_name'] ?> <?=$player[0]['last_name'] ?>
<br />
<?=$rules['race'] ?> <?=$rules['archetype'] ?> of the <?=$rules['kinship'] ?>
</td></tr>

<tr>
<td>CP UNSPENT: <?=$character['cp_unspent'] ?> (<?=$character['cp_spent'] ?> spent)</td>
<td>VP UNSPENT: <?=$character['vp_unspent'] ?> (<?=$character['vp_spent'] ?> spent)</td>
</tr>

<tr>
<td>TOTAL CP: <?=$character['cp_unspent']+$character['cp_spent'] ?></td>
<td>BP: <?=$character['bp'] ?></td>
</tr>

<tr>
<td>LIVES: <?=$character['lives'] ?></td>
<td>GODSENDS: <?=$character['godsends'] ?></td>
</tr>

<tr>
<td>POOL: <?=$rules['pool'] ?></td>
<td>ESSENCE: <?=$rules['pool_essence'] ?></td>
</tr>
</table>

<div class="colleft" >
    <div class="col1">
        <strong>Essence Qualities</strong>
        <ul>
        <?php foreach( $rules['essence'] as $id=>$name ) { ?>
         <li><?=$name ?></li>
        <?php } ?>
        </ul>
    </div>
    
    <div class="col2">
       <strong>Abilities</strong>
        <ul>
        <?php 
            if( array_key_exists( 'ability', $rules ) ) {
            foreach( $rules['ability'] as $id=>$name ) { ?>
             <li><?=$name?></li>
            <?php } ?>
        <?php } ?>
        </ul>
    </div>
</div>
<div class="col3">
    <strong>Other</strong>
    <ul>
<?php
    if ($director) {
        foreach ($director as $edit) {
            if ($edit) {
?>
                <li><?=$edit['description'] ?></li>
<?php
            }
        }
    }
    ?>
    </ul>
</div>