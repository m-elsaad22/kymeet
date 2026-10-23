<?php
$Styles = array();
$this->Part( 'header', array( 'Styles' => $Styles ) );
$kx_en = kymeet_store_is_en();
echo '<section class="phero compact"><div class="wrap"><div class="crumb"><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html( $kx_en ? 'Home' : 'الرئيسية' ) . '</a><span>/</span><a href="' . esc_url( wc_get_page_permalink( 'shop' ) ) . '">' . esc_html( $kx_en ? 'Shop' : 'المتجر' ) . '</a><span>/</span><span>' . esc_html( get_the_title() ) . '</span></div>';
echo '<h1>' . esc_html( get_the_title() ) . '</h1></div></section>';
echo '<section class="sec"><div class="wrap"><div class="woocommerce kx-wc">';
while ( have_posts() ) {
	the_post();
	wc_get_template_part( 'content', 'single-product' );
}
echo '</div></div></section>';
$this->Part( 'footer', array( 'Styles' => $Styles ) );
