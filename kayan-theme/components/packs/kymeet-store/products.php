<?php
/**
 * Build KYMEET_DATA from WooCommerce products.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function kymeet_product_segment( $product ): string {
	if ( ! is_object( $product ) || ! method_exists( $product, 'get_id' ) ) {
		return '';
	}
	$terms = get_the_terms( $product->get_id(), 'product_cat' );
	if ( ! is_array( $terms ) ) {
		return '';
	}
	foreach ( $terms as $term ) {
		$blob = mb_strtolower( $term->slug . ' ' . $term->name );
		if ( preg_match( '/men|male|رجال|رجالي/u', $blob ) ) {
			return 'men';
		}
		if ( preg_match( '/women|female|نساء|نسائي/u', $blob ) ) {
			return 'women';
		}
	}
	return '';
}

function kymeet_product_brand( $product ): string {
	if ( ! is_object( $product ) || ! method_exists( $product, 'get_meta' ) ) {
		return '';
	}
	$meta = $product->get_meta( '_kymeet_brand' );
	if ( $meta ) {
		return (string) $meta;
	}
	$name = trim( $product->get_name() );
	if ( preg_match( '/^([A-Z][A-Z0-9&\-\']+)/u', $name, $m ) ) {
		return $m[1];
	}
	$parts = preg_split( '/\s+/u', $name );
	return $parts[0] ?? '';
}

function kymeet_map_product( $product ): array {
	if ( ! is_object( $product ) || ! method_exists( $product, 'get_id' ) ) {
		return array();
	}
	$slug   = $product->get_slug();
	$image  = wp_get_attachment_image_url( $product->get_image_id(), 'woocommerce_single' );
	if ( ! $image ) {
		$image = wc_placeholder_img_src( 'woocommerce_single' );
	}
	$regular = (float) $product->get_regular_price();
	$sale    = (float) $product->get_sale_price();
	$price   = $product->is_on_sale() && $sale > 0 ? $sale : (float) $product->get_price();
	$tag     = '';
	if ( $product->is_on_sale() && $regular > $price ) {
		$tag = 'عرض';
	} elseif ( $product->get_date_created() && $product->get_date_created()->getTimestamp() > time() - 60 * DAY_IN_SECONDS ) {
		$tag = 'جديد';
	}
	$row = array(
		'id'     => $slug,
		'pid'    => $product->get_id(),
		'name'   => $product->get_name(),
		'brand'  => kymeet_product_brand( $product ),
		'price'  => $price,
		'image'  => $image,
		'url'    => get_permalink( $product->get_id() ),
		'tag'    => $tag,
		'seg'    => kymeet_product_segment( $product ),
	);
	if ( $product->is_on_sale() && $regular > $price ) {
		$row['regular'] = $regular;
	}
	return $row;
}

function kymeet_get_product_rows(): array {
	static $cache = null;
	if ( null !== $cache ) {
		return $cache;
	}
	$cache = array();
	if ( ! class_exists( 'WooCommerce' ) ) {
		return $cache;
	}
	$q = new WP_Query(
		array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'fields'         => 'ids',
		)
	);
	foreach ( $q->posts as $pid ) {
		$p = wc_get_product( $pid );
		if ( is_object( $p ) && method_exists( $p, 'get_id' ) ) {
			$mapped = kymeet_map_product( $p );
			if ( ! empty( $mapped['id'] ) ) {
				$cache[] = $mapped;
			}
		}
	}
	return $cache;
}

function kymeet_get_data_payload(): array {
	$products = kymeet_get_product_rows();
	$brands   = array_values(
		array_unique(
			array_filter(
				array_map(
					static function ( $p ) {
						return $p['brand'] ?? '';
					},
					$products
				)
			)
		)
	);
	sort( $brands, SORT_STRING );

	$new_slugs = array();
	$q_new     = new WP_Query(
		array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => 8,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'fields'         => 'ids',
		)
	);
	foreach ( $q_new->posts as $pid ) {
		$p = wc_get_product( $pid );
		if ( $p ) {
			$new_slugs[] = $p->get_slug();
		}
	}

	$best_slugs = array();
	$q_best     = new WP_Query(
		array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => 8,
			'meta_key'       => 'total_sales',
			'orderby'        => 'meta_value_num',
			'order'          => 'DESC',
			'fields'         => 'ids',
		)
	);
	foreach ( $q_best->posts as $pid ) {
		$p = wc_get_product( $pid );
		if ( $p ) {
			$best_slugs[] = $p->get_slug();
		}
	}
	if ( empty( $best_slugs ) ) {
		$best_slugs = array_slice( array_column( $products, 'id' ), 0, 8 );
	}

	$prices = array_column( $products, 'price' );
	$max    = $prices ? (int) ( ceil( max( $prices ) / 100 ) * 100 ) : 5300;

	return array(
		'products' => $products,
		'BR'       => $brands,
		'BEST'     => $best_slugs,
		'NEW'      => $new_slugs,
		'maxPrice' => max( 800, $max ),
	);
}

function kymeet_print_config_scripts(): void {
	if ( ! kymeet_store_active() ) {
		return;
	}
	$payload = kymeet_get_data_payload();
	$wa      = yc_get_option( 'kymeet_wa' );
	$tel     = yc_get_option( 'kymeet_tel' );
	if ( ! $wa ) {
		$wa = yc_get_option( 'whatsapp_number' );
	}
	if ( ! $tel ) {
		$tel = yc_get_option( 'contact_number' );
	}
	$cfg = array(
		'WA'       => preg_replace( '/\D+/', '', (string) $wa ),
		'TEL'      => preg_replace( '/\D+/', '', (string) $tel ),
		'HOME'     => home_url( '/' ),
		'SHOP'     => wc_get_page_permalink( 'shop' ),
		'WC'       => true,
		'AJAX'     => home_url( '/?wc-ajax=kymeet_cart' ),
		'wishKey'  => kymeet_store_wish_key(),
		'account'  => wc_get_page_permalink( 'myaccount' ),
	);
	echo '<script>window.KYMEET_CFG=' . wp_json_encode( $cfg ) . ';</script>';
	echo '<script>window.KYMEET_DATA=' . wp_json_encode( $payload ) . ';</script>';
	echo '<script>window.KYMEET_UI=' . wp_json_encode( $cfg ) . ';</script>';
}
