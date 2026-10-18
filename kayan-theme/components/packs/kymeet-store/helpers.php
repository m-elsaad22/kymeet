<?php
/**
 * KYMEET Store — helpers & paths.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'KYMEET_STORE_PATH' ) ) {
	define( 'KYMEET_STORE_PATH', trailingslashit( dirname( __FILE__ ) ) );
}
if ( ! defined( 'KYMEET_STORE_URL' ) ) {
	define( 'KYMEET_STORE_URL', trailingslashit( get_template_directory_uri() . '/components/packs/kymeet-store' ) );
}

/**
 * Whether KYMEET storefront chrome should load (header, assets, cart AJAX).
 */
/**
 * WooCommerce shop listing (archive or assigned shop page).
 */
function kymeet_is_shop_listing(): bool {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return false;
	}
	if ( is_search() ) {
		return false;
	}
	if ( function_exists( 'is_shop' ) && is_shop() && ! is_singular( 'product' ) ) {
		return true;
	}
	$shop_id = wc_get_page_id( 'shop' );
	return $shop_id > 0 && is_page( $shop_id );
}

function kymeet_store_active(): bool {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return false;
	}
	if ( is_admin() && ! wp_doing_ajax() ) {
		return false;
	}
	$scope = false;
	if ( function_exists( 'is_front_page' ) && is_front_page() ) {
		$scope = true;
	}
	if ( function_exists( 'is_woocommerce' ) && is_woocommerce() ) {
		$scope = true;
	}
	if ( kymeet_is_shop_listing() ) {
		$scope = true;
	}
	$widgets = yc_get_option( 'widgets_home__meta' );
	if ( is_array( $widgets ) ) {
		foreach ( $widgets as $row ) {
			if ( ! empty( $row['widget_id'] ) && 0 === strpos( (string) $row['widget_id'], 'kymeet_' ) ) {
				$scope = true;
				break;
			}
		}
	}
	return (bool) apply_filters( 'kymeet_store_active', $scope );
}

function kymeet_store_wish_key(): string {
	return 'kymeet_wish_v1';
}

function kymeet_money( $amount ): string {
	return number_format_i18n( (float) $amount, 0 );
}

function kymeet_esc( string $text ): string {
	return esc_html( $text );
}
