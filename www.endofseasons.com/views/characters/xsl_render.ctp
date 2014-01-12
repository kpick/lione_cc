<?php 
    echo $xml->header();
    echo "<?xml-stylesheet type=\"text/xsl\" href=\"".Router::url('/',true) ."xsl/" . $xsl_display . "\" ?>";
    echo $xml->serialize( $xml_output );

?>