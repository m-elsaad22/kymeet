<?php
/**
 * KYMEET — production fixes (product routing, scripts, schema)
 */
defined( 'ABSPATH' ) || exit;

/**
 * Keep WooCommerce product singles on post_type product (avoid 404 from generic query hooks).
 */
function kymeet_fix_product_main_query( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! function_exists( 'kymeet_wc_active' ) || ! kymeet_wc_active() ) {
		return;
	}
	if ( ! empty( $query->query_vars['product'] ) ) {
		$query->set( 'post_type', 'product' );
	}
}
add_action( 'pre_get_posts', 'kymeet_fix_product_main_query', 6 );

/**
 * Hide default Uncategorized from product meta / lists.
 */
function kymeet_filter_product_cat_terms( $terms, $post_id, $taxonomy ) {
	if ( is_admin() || 'product_cat' !== $taxonomy || ! is_array( $terms ) ) {
		return $terms;
	}
	$out = array();
	foreach ( $terms as $term ) {
		if ( $term instanceof WP_Term && 'uncategorized' === $term->slug ) {
			continue;
		}
		$out[] = $term;
	}
	return $out;
}
add_filter( 'get_the_terms', 'kymeet_filter_product_cat_terms', 10, 3 );

/**
 * WC template compatibility marker (ThemeStatic still renders @single/product.php).
 */
function kymeet_wc_single_product_template_path() {
	return get_template_directory() . '/woocommerce/single-product.php';
}

/**
 * Omit empty aggregateRating from legacy schema pack (avoid fake review signals).
 */
function kymeet_schema_strip_empty_ratings( $data ) {
	if ( ! is_array( $data ) ) {
		return $data;
	}
	if ( isset( $data['aggregateRating'] ) && is_array( $data['aggregateRating'] ) ) {
		$rv = isset( $data['aggregateRating']['ratingValue'] ) ? trim( (string) $data['aggregateRating']['ratingValue'] ) : '';
		$rc = isset( $data['aggregateRating']['reviewCount'] ) ? trim( (string) $data['aggregateRating']['reviewCount'] ) : '';
		$cnt = isset( $data['aggregateRating']['ratingCount'] ) ? trim( (string) $data['aggregateRating']['ratingCount'] ) : '';
		if ( '' === $rv || ( '' === $rc && '' === $cnt ) ) {
			unset( $data['aggregateRating'] );
		}
	}
	return $data;
}
