<?php
$Styles = array();
$this->Part( 'header', array( 'Styles' => $Styles ) );
kymeet_wc_render_archive();
$this->Part( 'footer', array( 'Styles' => $Styles ) );
