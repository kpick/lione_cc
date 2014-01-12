<?php
	$html->css( '2-column.css', 'stylesheet', array( 'media'=>'all'), false );
?>
<strong>Thank you for your purchase!</strong>

<p>Your purchase details are below - please print this page out for your records</p>

<table width="100%">
    <?php foreach( $cart_info as $id=>$info ) { ?>
        
    <tr>
    <td>
    <?=$info['qty'] ?>
    </td>
    
    <td>
    <?=$info['description'] ?>
    </td>

    
    <td align="right">
    $<?=$info['base_cost'] ?>
    </td>
        
    </tr>
    <?php } ?>
<tr>
<td colspan="3" align="right"> <b>Total:</b> $<?= number_format($cart_total,2) ?></td>
</tr>
</table>
    

   

<?php if( $character_message ) { ?>
    <p><a href="#">Apply events and XP to your character</a>
<?php } ?>