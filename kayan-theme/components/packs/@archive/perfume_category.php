<?php
$Styles = array();
$this->Part( 'header', array( 'Styles' => $Styles ) );
$kx_obj = get_queried_object();
kymeet_store_render_archive( ( $kx_obj && isset( $kx_obj->name ) ) ? $kx_obj->name : '', ( $kx_obj && isset( $kx_obj->description ) ) ? $kx_obj->description : '' );
$this->Part( 'footer', array( 'Styles' => $Styles ) );
