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
function kymeet_store_active(): bool {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return false;
	}
	$widgets = yc_get_option( 'widgets_home__meta' );
	if ( is_array( $widgets ) ) {
		foreach ( $widgets as $row ) {
			if ( ! empty( $row['widget_id'] ) && 0 === strpos( (string) $row['widget_id'], 'kymeet_' ) ) {
				return true;
			}
		}
	}
	/**
	 * Allow forcing on shop/product/cart when homepage widgets are empty.
	 */
	$scope = false;
	if ( function_exists( 'is_front_page' ) && is_front_page() ) {
		$scope = true;
	}
	if ( function_exists( 'is_shop' ) ) {
		$scope = $scope || is_shop() || is_product() || is_cart() || is_checkout() || is_account_page();
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
