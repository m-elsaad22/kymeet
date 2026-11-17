<?php
$Styles = array();
$YC__WidgetsMachine = new YC__WidgetsMachine;
$hide__sidebar__single = get_option( 'hide__sidebar__pages' );
$widgets_pages__meta = array();
if ( empty( $hide__sidebar__single ) ) {
	$widgets_pages__meta = is_array( get_option( 'widgets_pages__meta' ) ) ? get_option( 'widgets_pages__meta' ) : array();
	if ( ! empty( $widgets_pages__meta ) ) {
		$Styles = array_merge( $Styles, $YC__WidgetsMachine->widgets__Enqueues( $widgets_pages__meta ) );
	}
}

$this->Part( 'header', array( 'Styles' => $Styles ) );

$kymeet_app = function_exists( 'kymeet_store_active' ) && kymeet_store_active();
$kx_auth_page = function_exists( 'is_account_page' ) && is_account_page() && ! is_user_logged_in();
if ( ! $kx_auth_page ) {
	$sub = $kymeet_app ? '' : kayan_kit_page_excerpt( $post );
	kayan_kit_hero( $post->post_title, $sub );
}

ob_start();
the_content();
$body = ob_get_clean();

$kx_wc_page = function_exists( 'is_woocommerce' ) && ( ( function_exists( 'is_cart' ) && is_cart() ) || ( function_exists( 'is_checkout' ) && is_checkout() ) || ( function_exists( 'is_account_page' ) && is_account_page() ) );
if ( $kx_auth_page ) {
	echo '<section class="kx-auth"><div class="kx-auth-rings" aria-hidden="true"></div><div class="wrap woocommerce kx-wc kx-auth-wrap">' . $body . '</div></section>';
	$this->Part( 'footer', array( 'Styles' => $Styles ) );
	return;
}
if ( $kx_wc_page ) {
	echo '<section class="sec"><div class="wrap"><div class="woocommerce kx-wc kx-page">' . $body . '</div></div></section>';
	$this->Part( 'footer', array( 'Styles' => $Styles ) );
	return;
}
if ( $kymeet_app ) {
	echo '<section class="sec"><div class="wrap"><div class="article-body prose kx-page">' . $body . '</div></div></section>';
	$this->Part( 'footer', array( 'Styles' => $Styles ) );
	return;
}
echo '<section class="sec"><div class="wrap article-layout">';
echo '<div class="article-body prose">' . $body . '</div>';
echo '<div>';
kayan_kit_side_cta();
if ( empty( $hide__sidebar__single ) && ! empty( $widgets_pages__meta ) ) {
	$YC__WidgetsMachine->widgets___UI(
		array(
			'Widgets_data'            => $widgets_pages__meta,
			'WidgetID'                => 'widgets_pages__meta',
			'Parent__section__class'  => 'side-w',
			'Single__section__class'  => 'side-w',
			'section_InnerRow_class'  => '',
		)
	);
}
echo '</div></div></section>';

$this->Part( 'footer', array( 'Styles' => $Styles ) );
