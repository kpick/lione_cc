<?php 
    if( $profile ) {
?>

The player <strong><?=$profile ?></strong> does not exist, or has set their profile to be private.

<?php } else { ?>

This player does not exist, or has set their profile to be private.

<?php } ?>