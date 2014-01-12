<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <?php echo $html->charset(); ?>
	<title>
		<?php __('LIONE Character/Event Manager'); ?>
		<?php echo $title_for_layout; ?>
	</title>
	
	<?php
		//echo $html->meta('icon');
        echo $html->css('default');
        echo $scripts_for_layout;
	?>
	
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
	<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.5/jquery-ui.min.js"></script>
</head>

<div id="header">
    <?php echo $this->element( 'navigation' ) ?>
</div>

<div class="colmask rightmenu">
	<div class="colleft">
		<div class="col1">
            <?php echo $this->element( 'messages' ) ?>
            <?php echo $content_for_layout ?>
         </div>
         <div class="col2">
               
         </div>
    </div>
</div>

<div id="footer">
	Copyright &copy; 2010 LIONE Corp.
</div>

</body>
</html>