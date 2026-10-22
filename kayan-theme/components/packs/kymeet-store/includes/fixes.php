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
 * KYMEET app: suppress legacy services / RUKN chrome on the storefront.
 */
function kymeet_app_suppress_legacy_chrome() {
	if ( ! function_exists( 'kymeet_store_active' ) || ! kymeet_store_active() ) {
		return;
	}
	echo '<style id="kymeet-app-legacy-hide">';
	echo 'body.kymeet-app #rukn-toc-tab,body.kymeet-app #rukn-toc-panel,body.kymeet-app #rukn-toc-backdrop{display:none!important}';
	echo 'body.kymeet-app .order-services-phonenumber,body.kymeet-app .order-services--overlay,body.kymeet-app .-order-services--single--popoover,body.kymeet-app .services-grid{display:none!important}';
	echo 'body.kymeet-app footer:not(.kymeet-site-footer){display:none!important}';
	echo 'body.kymeet-app #loader{display:none!important;pointer-events:none!important}';
	echo 'body.kymeet-app.before-start{opacity:1!important;visibility:visible!important}';
	echo '</style>';
}
add_action( 'wp_head', 'kymeet_app_suppress_legacy_chrome', 99 );

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
