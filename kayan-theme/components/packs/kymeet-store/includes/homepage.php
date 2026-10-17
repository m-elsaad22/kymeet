<?php
/**
 * Replace KAYAN Homepage v3 (services demo) with KYMEET store home.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Use KYMEET perfume storefront on the site front page (not kayan-homepage-v3).
 */
function kymeet_use_store_homepage(): bool {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return false;
	}
	if ( is_admin() || wp_doing_ajax() || wp_doing_cron() ) {
		return false;
	}
	$front = is_front_page() || ( is_home() && 'posts' === get_option( 'show_on_front' ) );
	if ( ! $front ) {
		return false;
	}
	if ( ! empty( yc_get_option( 'kymeet_use_kayan_home' ) ) ) {
		return false;
	}
	return (bool) apply_filters( 'kymeet_use_store_homepage', true );
}

/**
 * Default KYMEET homepage sections (matches mockup order).
 */
function kymeet_render_homepage_sections(): void {
	Kymeet_Render::hero();
	Kymeet_Render::product_slider(
		'new',
		'NEW',
		'أحدث <em>العطور</em>',
		'وصل حديثًا إلى المتجر.',
		'newGrid',
		home_url( '/#catalog' )
	);
	Kymeet_Render::discover();
	Kymeet_Render::catalog();
}

function kymeet_homepage_takeover(): void {
	if ( ! kymeet_use_store_homepage() ) {
		return;
	}

	global $ThemeStatic;
	$theme = ( isset( $ThemeStatic ) && $ThemeStatic instanceof ThemeStatic ) ? $ThemeStatic : new ThemeStatic();

	$body_class = 'kymeet-home kymeet-store-active rtl';
	if ( function_exists( 'kayan_homepage_is_english' ) && kayan_homepage_is_english() ) {
		$body_class .= ' kymeet-home-en';
	}

	$theme->Part(
		'header',
		array(
			'bodyClass' => $body_class,
		)
	);

	echo '<main class="kymeet-home-main" id="kymeet-home">';
	kymeet_render_homepage_sections();
	echo '</main>';

	$theme->Part( 'footer' );
	exit;
}
