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
 * Late contrast overrides for dark v4 blocks.
 * Global h1–h4 navy from inlined kymeet.css beats inherited white; force readable text
 * or light nested panels without changing brand navy/blue surfaces.
 */
function kymeet_app_contrast_overrides() {
	if ( ! function_exists( 'kymeet_store_active' ) || ! kymeet_store_active() ) {
		return;
	}
	echo '<style id="kymeet-contrast-fix">';
	echo 'body.kymeet-app .v4-hero__copy,body.kymeet-app .v4-hero__copy h2,body.kymeet-app .v4-hero__copy p,body.kymeet-app .v4-hero__copy .v4-kicker{color:#fff!important}';
	echo 'body.kymeet-app .v4-spot{background:radial-gradient(120% 140% at 100% 0%,#5E9EF5,#1A6BD8 42%,#0B3A85)!important}';
	echo 'body.kymeet-app .v4-spot--flip{background:radial-gradient(120% 140% at 0% 0%,#3D8AF0,#12306B 52%,#0A1A3D)!important}';
	echo 'body.kymeet-app .v4-spot>div{background:rgba(255,255,255,.96)!important;color:#0A1A3D!important;border-radius:16px;padding:16px 18px;box-shadow:0 10px 28px rgba(5,16,40,.12)}';
	echo 'body.kymeet-app .v4-spot small{color:#0F4CA8!important;opacity:1!important}';
	echo 'body.kymeet-app .v4-spot h3{color:#0A1A3D!important}';
	echo 'body.kymeet-app .v4-spot .v4-btn{background:#0F4CA8!important;color:#fff!important}';
	echo 'body.kymeet-app .v4-modal__card{background:linear-gradient(180deg,#F7FAFF,#E7F1FF 55%,#D6E8FF)!important;color:#0A1A3D!important;border:1px solid rgba(15,76,168,.16)}';
	echo 'body.kymeet-app .v4-modal__card .v4-kicker{color:#0F4CA8!important}';
	echo 'body.kymeet-app .v4-modal__card h2{color:#0A1A3D!important}';
	echo 'body.kymeet-app .v4-modal__card p{color:#33415C!important}';
	echo 'body.kymeet-app .v4-modal__card .v4-btn{background:#0F4CA8!important;color:#fff!important}';
	echo 'body.kymeet-app .v4-modal__x{background:rgba(15,76,168,.12)!important;color:#0A1A3D!important}';
	echo 'body.kymeet-app .v4-strip h3,body.kymeet-app .v4-strip p,body.kymeet-app .v4-deal small,body.kymeet-app .v4-deal h3,body.kymeet-app .v4-brand__copy,body.kymeet-app .v4-brand__copy h2,body.kymeet-app .v4-brand__copy p,body.kymeet-app .v4-bcount,body.kymeet-app .v4-foot,body.kymeet-app .v4-foot h5,body.kymeet-app .v4-foot a,body.kymeet-app .v4-foot p,body.kymeet-app .v4-foot li,body.kymeet-app .v4-logo,body.kymeet-app .v4-bag,body.kymeet-app .v4-bag b,body.kymeet-app .v4-bag small{color:#fff!important}';
	echo 'body.kymeet-app .v4-brand__copy p{color:rgba(255,255,255,.92)!important}';
	echo 'body.kymeet-app .v4-card h3,body.kymeet-app .v4-card b,body.kymeet-app .v4-sec h2{color:#0A1A3D!important}';
	echo 'body.kymeet-app .v4-sec p{color:#5C6B8A!important}';
	echo 'body.kymeet-app .v4-hnav a,body.kymeet-app .v4-act{color:#0A1A3D!important}';
	echo '</style>';
}
add_action( 'wp_head', 'kymeet_app_contrast_overrides', 999 );

/**
 * Shop/archive: no giant Woo category tiles or banner images (chips + grid on home cover categories).
 */
function kymeet_wc_app_shop_layout() {
	if ( ! function_exists( 'kymeet_store_active' ) || ! kymeet_store_active() || ! function_exists( 'kymeet_wc_active' ) || ! kymeet_wc_active() ) {
		return;
	}
	if ( ! function_exists( 'is_shop' ) || ( ! is_shop() && ! is_product_taxonomy() ) ) {
		return;
	}
	remove_filter( 'woocommerce_product_loop_start', 'woocommerce_maybe_show_product_subcategories', 10 );
	remove_action( 'woocommerce_archive_description', 'woocommerce_taxonomy_archive_description', 10 );
	remove_action( 'woocommerce_archive_description', 'woocommerce_product_archive_description', 10 );
}
add_action( 'wp', 'kymeet_wc_app_shop_layout', 20 );

function kymeet_wc_strip_archive_banner_images( $html ) {
	if ( ! function_exists( 'kymeet_app_ui_active' ) || ! kymeet_app_ui_active() ) {
		return $html;
	}
	if ( ! is_string( $html ) || '' === $html ) {
		return $html;
	}
	$html = preg_replace( '/<img[^>]*>/i', '', $html );
	$html = preg_replace( '/<figure[^>]*>\\s*<\\/figure>/i', '', $html );
	return $html;
}
add_filter( 'woocommerce_taxonomy_archive_description', 'kymeet_wc_strip_archive_banner_images' );
add_filter( 'woocommerce_product_archive_description', 'kymeet_wc_strip_archive_banner_images' );

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
