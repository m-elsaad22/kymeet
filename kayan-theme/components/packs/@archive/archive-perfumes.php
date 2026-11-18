<?php
$Styles = array();
$this->Part( 'header', array( 'Styles' => $Styles ) );
kymeet_store_render_archive( post_type_archive_title( '', false ) );
$this->Part( 'footer', array( 'Styles' => $Styles ) );
