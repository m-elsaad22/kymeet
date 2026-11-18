<?php
$Styles = array();
$this->Part( 'header', array( 'Styles' => $Styles ) );
$kx_r   = kymeet_store_row( $post );
$kx_en  = kymeet_store_is_en();
$kx_wa  = preg_replace( '/\D+/', '', (string) get_option( 'whatsapp_number' ) );
$kx_arc = get_post_type_archive_link( 'perfumes' );

echo '<section class="phero compact"><div class="wrap"><div class="crumb"><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html( $kx_en ? 'Home' : 'الرئيسية' ) . '</a><span>/</span><a href="' . esc_url( $kx_arc ) . '">' . esc_html( $kx_en ? 'Perfumes' : 'العطور' ) . '</a><span>/</span><span>' . esc_html( $kx_r['name'] ) . '</span></div>';
echo '<h1>' . esc_html( $kx_r['name'] ) . '</h1>';
if ( $kx_r['brand'] ) {
	echo '<p class="psub">' . esc_html( $kx_r['brand'] ) . '</p>';
}
echo '</div></section><section class="sec"><div class="wrap"><div class="kx-single"><div class="prod-img kx-img">';
if ( $kx_r['image'] ) {
	echo '<img src="' . esc_url( $kx_r['image'] ) . '" alt="' . esc_attr( $kx_r['name'] ) . '" data-brand="' . esc_attr( $kx_r['brand'] ) . '" onerror="imgErr(this)">';
}
echo '</div><div class="kx-info">';
if ( $kx_r['brand'] ) {
	$kx_terms = get_the_terms( $post->ID, 'perfume_brand' );
	$kx_link  = ( is_array( $kx_terms ) && $kx_terms ) ? get_term_link( $kx_terms[0] ) : '';
	echo '<a class="chip" href="' . esc_url( is_wp_error( $kx_link ) ? '' : $kx_link ) . '">' . esc_html( $kx_r['brand'] ) . '</a>';
}
if ( isset( $kx_r['tag'] ) ) {
	echo ' <span class="chip">' . esc_html( $kx_r['tag'] ) . '</span>';
}
echo '<div class="kx-price"><span class="now">' . esc_html( kymeet_store_money( $kx_r['price'] ) ) . '<small> ج.م</small></span>';
if ( isset( $kx_r['old'] ) ) {
	echo ' <s>' . esc_html( kymeet_store_money( $kx_r['old'] ) ) . '</s>';
}
echo '</div><div class="kx-actions"><button type="button" class="btn btn-gold" onclick="addToCart(\'' . esc_attr( $kx_r['id'] ) . '\',event)"><i class="fas fa-bag-shopping"></i> ' . esc_html( $kx_en ? 'Add to cart' : 'أضف للسلة' ) . '</button>';
if ( $kx_wa ) {
	echo '<a class="btn btn-wa" target="_blank" rel="noopener" href="' . esc_url( 'https://wa.me/' . $kx_wa . '?text=' . rawurlencode( $kx_r['name'] . ' ' . $kx_r['url'] ) ) . '"><i class="fab fa-whatsapp"></i> ' . esc_html( $kx_en ? 'Order on WhatsApp' : 'اطلب عبر واتساب' ) . '</a>';
}
echo '</div><div class="prose">';
the_content();
echo '</div></div></div>';

$kx_rel = get_posts( array( 'post_type' => 'perfumes', 'post__not_in' => array( $post->ID ), 'numberposts' => 4, 'orderby' => 'rand' ) );
if ( $kx_rel ) {
	echo '<h2 class="kx-h">' . esc_html( $kx_en ? 'You may also like' : 'قد يعجبك أيضًا' ) . '</h2><div class="kx-grid">';
	foreach ( $kx_rel as $kx_p ) {
		echo kymeet_store_card( $kx_p ); // phpcs:ignore
	}
	echo '</div>';
}
echo '</div></section>';
echo '<script type="application/ld+json">' . wp_json_encode(
	array(
		'@context' => 'https://schema.org',
		'@type'    => 'Product',
		'name'     => $kx_r['name'],
		'image'    => $kx_r['image'],
		'brand'    => array( '@type' => 'Brand', 'name' => $kx_r['brand'] ),
		'offers'   => array( '@type' => 'Offer', 'priceCurrency' => 'EGP', 'price' => $kx_r['price'], 'availability' => 'https://schema.org/InStock', 'url' => $kx_r['url'] ),
	)
) . '</script>';

$this->Part( 'footer', array( 'Styles' => $Styles ) );
