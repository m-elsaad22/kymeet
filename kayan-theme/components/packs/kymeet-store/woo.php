<?php
/**
 * WooCommerce cart AJAX for KYMEET drawer (wc-ajax=kymeet_cart).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'woocommerce_before_main_content',
	static function () {
		if ( ! kymeet_store_active() || is_shop() ) {
			return;
		}
		echo '<div class="kx-wc kymeet-wc-shell"><div class="wrap">';
	},
	2
);

add_action(
	'woocommerce_after_main_content',
	static function () {
		if ( ! kymeet_store_active() || is_shop() ) {
			return;
		}
		echo '</div></div>';
	},
	98
);

function kymeet_cart_json(): array {
	if ( ! WC()->cart ) {
		wc_load_cart();
	}
	$items = array();
	foreach ( WC()->cart->get_cart() as $key => $item ) {
		$product = $item['data'];
		if ( ! $product instanceof WC_Product ) {
			continue;
		}
		$brand = kymeet_product_brand( $product );
		$img   = wp_get_attachment_image_url( $product->get_image_id(), 'thumbnail' );
		$items[] = array(
			'key'   => $key,
			'name'  => $product->get_name(),
			'brand' => $brand,
			'url'   => get_permalink( $product->get_id() ),
			'img'   => $img ? $img : '',
			'qty'   => (int) $item['quantity'],
			'line'  => wc_price( $product->get_price() * $item['quantity'] ),
		);
	}
	return array(
		'count'    => WC()->cart->get_cart_contents_count(),
		'subtotal' => WC()->cart->get_cart_subtotal(),
		'items'    => $items,
		'checkout' => wc_get_checkout_url(),
		'cart'     => wc_get_cart_url(),
		'msg'      => '',
		'redirect' => '',
	);
}

function kymeet_cart_handler(): void {
	if ( ! class_exists( 'WooCommerce' ) ) {
		wp_send_json( array( 'error' => 'no_wc' ) );
	}
	$op = isset( $_POST['op'] ) ? sanitize_text_field( wp_unslash( $_POST['op'] ) ) : 'get';

	if ( 'add' === $op ) {
		$pid = isset( $_POST['pid'] ) ? absint( $_POST['pid'] ) : 0;
		$qty = isset( $_POST['qty'] ) ? max( 1, absint( $_POST['qty'] ) ) : 1;
		if ( ! $pid ) {
			wp_send_json( array( 'error' => 'missing_pid' ) );
		}
		$added = WC()->cart->add_to_cart( $pid, $qty );
		if ( ! $added ) {
			wp_send_json(
				array(
					'msg' => __( 'تعذّرت الإضافة', 'yourcolor' ),
				) + kymeet_cart_json()
			);
		}
		wp_send_json( kymeet_cart_json() );
	}

	if ( 'set' === $op ) {
		$key = isset( $_POST['key'] ) ? sanitize_text_field( wp_unslash( $_POST['key'] ) ) : '';
		$qty = isset( $_POST['qty'] ) ? max( 0, absint( $_POST['qty'] ) ) : 0;
		if ( $key ) {
			if ( 0 === $qty ) {
				WC()->cart->remove_cart_item( $key );
			} else {
				WC()->cart->set_quantity( $key, $qty, true );
			}
		}
		wp_send_json( kymeet_cart_json() );
	}

	if ( 'remove' === $op ) {
		$key = isset( $_POST['key'] ) ? sanitize_text_field( wp_unslash( $_POST['key'] ) ) : '';
		if ( $key ) {
			WC()->cart->remove_cart_item( $key );
		}
		wp_send_json( kymeet_cart_json() );
	}

	wp_send_json( kymeet_cart_json() );
}

add_action( 'wc_ajax_kymeet_cart', 'kymeet_cart_handler' );
add_action( 'wc_ajax_nopriv_kymeet_cart', 'kymeet_cart_handler' );
