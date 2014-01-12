<?php
	$html->css( '2-column.css', 'stylesheet', array( 'media'=>'all'), false );
	
	if( empty( $cart_info ) ) { 
?>

    <i>Your cart is currently empty</i>
<?php } else {
        echo $form->create('Cart', array('action' => 'checkout'));
?>


<table width="50%">
    <?php foreach( $cart_info as $id=>$info ) { ?>
        
    <tr>
    <td align="right">
        <input type="text" name="data[Cart][<?=$info['value'] ?>]" size="1" value="<?=$info['qty'] ?>" />
    </td>
    
    <td align="left">
            @ $<?php echo $info['base_cost'] ?>
    </td>
    
    <td align="right">
    <?=$info['description'] ?>
    </td>

    
    <td align="right">
    $<?= number_format( ( $info['base_cost'] * $info['qty'] ), 2 ); ?>
    </td>
    
    <td>
     (<a href="/cart/remove/<?=$info['value'] ?>">remove</a>)
    </td>
    
    
    </tr>
   
    <?php } ?>
       <tr>
<td colspan="4" align="right" style="border-top:1px solid black;"> <b>Total:</b> $<?= number_format($cart_total,2) ?></td>
</tr> 
   
    </table>
    


<?php if( $character_message ) { ?>
    <p>You will be able to apply events and xp to your character after your<br />
    transaction has been approved.</p>
<?php } ?>

<?php    echo $form->end('Update'); 
}
?>

<?php if(! empty($cart_info) ) { ?>
<p style="margin-top: 40px; border-top:1px dotted grey;">
<a href="/cart/checkout" style="background:none">
<img src="<?=$pp_image ?>" />
</a>
</p>
<?php } ?>