<div style="margin:10px">
<?php if( $login_info ) { ?>
    <a href="/players/view">My Profile</a> | 
    <a href="/players/logout">Log Out</a>
    <?php if( isset( $cart_count ) && $cart_count > 0 ) {?>
    <a href="/cart/view"><img src="/img/cart.gif" /></a>
    <?php } ?>
<?php } else { ?>
    <a href="/players/login">Login</a> | 
    <a href="/players/signup">Sign Up</a>
<?php } ?>

</div>