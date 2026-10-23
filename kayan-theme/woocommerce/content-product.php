<?php
/**
 * بطاقة المنتج في حلقة المتجر بتصميم KYMEET.
 *
 * @package WooCommerce\Templates
 * @version 9.4.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! is_a( $product, WC_Product::class ) || ! $product->is_visible() ) {
	return;
}

if ( function_exists( 'kymeet_store_card' ) ) {
	echo '<li class="kx-li">' . kymeet_store_card( get_post( $product->get_id() ) ) . '</li>'; // phpcs:ignore
}
