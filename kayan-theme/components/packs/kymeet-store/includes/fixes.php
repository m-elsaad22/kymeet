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
 * Root cause: global `h1,h2,h3,h4{color:var(--royal-800)}` from kymeet.css paints
 * `.v4-spot h3` / `.v4-strip h3` navy on navy. Live still inlines old #kymeet-v4-css
 * without element colors. Force #fff on the exact markup:
 *   <a class="v4-spot">…<div><small>بيت العطر</small><h3>…</h3>…
 *   <section class="v4-strip"><div><h3>توصيل داخل مصر</h3><p>…
 * Injected in head + footer so leftover inline/theme CSS cannot win.
 */
function kymeet_app_contrast_css() {
	$css  = 'html body.kymeet-app .v4-hero__copy,html body.kymeet-app .v4-hero__copy h2,html body.kymeet-app .v4-hero__copy p,html body.kymeet-app .v4-hero__copy .v4-kicker{color:#fff!important}';
	$css .= 'html body.kymeet-app a.v4-spot,html body.kymeet-app .v4-spot,html body.kymeet-app a.v4-spot.v4-spot--flip{color:#fff!important}';
	$css .= 'html body.kymeet-app a.v4-spot>div,html body.kymeet-app .v4-spot>div{background:transparent!important;color:#fff!important;box-shadow:none!important;padding:0!important}';
	$css .= 'html body.kymeet-app a.v4-spot small,html body.kymeet-app .v4-spot small,html body.kymeet-app a.v4-spot h3,html body.kymeet-app .v4-spot h3,html body.kymeet-app a.v4-spot>div small,html body.kymeet-app a.v4-spot>div h3{color:#fff!important;opacity:1!important;-webkit-text-fill-color:#fff!important}';
	$css .= 'html body.kymeet-app a.v4-spot .v4-btn,html body.kymeet-app .v4-spot .v4-btn,html body.kymeet-app a.v4-spot span.v4-btn{background:#fff!important;color:#0A1A3D!important;-webkit-text-fill-color:#0A1A3D!important}';
	$css .= 'html body.kymeet-app section.v4-strip,html body.kymeet-app .v4-strip,html body.kymeet-app section.v4-strip>div,html body.kymeet-app .v4-strip>div{color:#fff!important}';
	$css .= 'html body.kymeet-app section.v4-strip h3,html body.kymeet-app .v4-strip h3,html body.kymeet-app section.v4-strip>div h3,html body.kymeet-app .v4-strip>div h3,html body.kymeet-app section.v4-strip p,html body.kymeet-app .v4-strip p,html body.kymeet-app section.v4-strip>div p{color:#fff!important;opacity:1!important;-webkit-text-fill-color:#fff!important}';
	$css .= 'html body.kymeet-app .v4-modal__card{background:linear-gradient(180deg,#F7FAFF,#E7F1FF 55%,#D6E8FF)!important;color:#0A1A3D!important;border:1px solid rgba(15,76,168,.16)}';
	$css .= 'html body.kymeet-app .v4-modal__card .v4-kicker{color:#0F4CA8!important}';
	$css .= 'html body.kymeet-app .v4-modal__card h2{color:#0A1A3D!important}';
	$css .= 'html body.kymeet-app .v4-modal__card p{color:#33415C!important}';
	$css .= 'html body.kymeet-app .v4-modal__card .v4-btn{background:#0F4CA8!important;color:#fff!important}';
	$css .= 'html body.kymeet-app .v4-modal__x{background:rgba(15,76,168,.12)!important;color:#0A1A3D!important}';
	$css .= 'html body.kymeet-app .v4-deal small,html body.kymeet-app .v4-deal h3,html body.kymeet-app .v4-brand__copy,html body.kymeet-app .v4-brand__copy h2,html body.kymeet-app .v4-brand__copy p,html body.kymeet-app .v4-bcount,html body.kymeet-app .v4-foot,html body.kymeet-app .v4-foot h5,html body.kymeet-app .v4-foot a,html body.kymeet-app .v4-foot p,html body.kymeet-app .v4-foot li,html body.kymeet-app .v4-logo,html body.kymeet-app .v4-bag,html body.kymeet-app .v4-bag b,html body.kymeet-app .v4-bag small{color:#fff!important}';
	$css .= 'html body.kymeet-app .v4-brand__copy p{color:rgba(255,255,255,.92)!important}';
	$css .= 'html body.kymeet-app .v4-card h3,html body.kymeet-app .v4-card b,html body.kymeet-app .v4-sec h2{color:#0A1A3D!important}';
	$css .= 'html body.kymeet-app .v4-sec p{color:#5C6B8A!important}';
	$css .= 'html body.kymeet-app .v4-hnav a,html body.kymeet-app .v4-act{color:#0A1A3D!important}';
	$css .= '@media (max-width:720px){';
	$css .= 'html body.kymeet-app .v4-hero__stage{overflow:hidden!important}';
	$css .= 'html body.kymeet-app .v4-hero__view{min-height:0!important;height:auto!important}';
	$css .= 'html body.kymeet-app .v4-slide{position:relative!important;inset:auto!important;display:none!important;opacity:1!important;visibility:visible!important;grid-template-columns:1fr!important;padding:14px 12px 16px!important;gap:10px!important}';
	$css .= 'html body.kymeet-app .v4-slide.is-on{display:grid!important}';
	$css .= 'html body.kymeet-app .v4-hero__copy h2{color:#fff!important;font-size:clamp(15px,4.5vw,19px)!important;line-height:1.25!important;white-space:nowrap!important;overflow:hidden!important;text-overflow:ellipsis!important;max-width:100%!important}';
	$css .= 'html body.kymeet-app .v4-hero__copy p{color:rgba(255,255,255,.92)!important;font-size:12px!important;margin:6px 0 10px!important;white-space:nowrap!important;overflow:hidden!important;text-overflow:ellipsis!important;max-width:100%!important}';
	$css .= 'html body.kymeet-app .v4-hero__card{height:auto!important;max-height:none!important;padding:10px 12px 14px!important}';
	$css .= 'html body.kymeet-app .v4-hero__card img{max-height:112px!important}';
	$css .= 'html body.kymeet-app .v4-hero__card strong{display:block!important;line-height:1.3!important;white-space:nowrap!important;overflow:hidden!important;text-overflow:ellipsis!important;letter-spacing:.01em!important}';
	$css .= 'html body.kymeet-app .v4-cats{display:flex!important;overflow-x:auto!important;gap:10px!important}';
	$css .= 'html body.kymeet-app .v4-cat{flex:0 0 auto!important;width:78px!important;font-size:11px!important}';
	$css .= 'html body.kymeet-app .v4-cat b{white-space:nowrap!important;overflow:hidden!important;text-overflow:ellipsis!important;display:block!important}';
	$css .= 'html body.kymeet-app a.v4-spot>div,html body.kymeet-app .v4-spot>div{background:transparent!important;color:#fff!important}';
	$css .= 'html body.kymeet-app a.v4-spot h3,html body.kymeet-app .v4-spot h3{color:#fff!important;white-space:nowrap!important;overflow:hidden!important;text-overflow:ellipsis!important;-webkit-text-fill-color:#fff!important}';
	$css .= 'html body.kymeet-app a.v4-spot small,html body.kymeet-app .v4-spot small{color:#fff!important;-webkit-text-fill-color:#fff!important}';
	$css .= 'html body.kymeet-app section.v4-strip h3,html body.kymeet-app .v4-strip h3,html body.kymeet-app section.v4-strip>div h3,html body.kymeet-app .v4-strip p{color:#fff!important;-webkit-text-fill-color:#fff!important}';
	$css .= 'html body.kymeet-app .v4-card__img{overflow:hidden!important;height:140px!important}';
	$css .= 'html body.kymeet-app .v4-card__body{position:relative!important;z-index:1!important;background:#fff!important;padding-top:8px!important;min-width:0!important}';
	$css .= 'html body.kymeet-app .v4-card__body>a{display:block!important;min-width:0!important;max-width:100%!important;overflow:hidden!important}';
	$css .= 'html body.kymeet-app .v4-card small,html body.kymeet-app .v4-card h3{white-space:nowrap!important;overflow:hidden!important;text-overflow:ellipsis!important;min-height:0!important;max-width:100%!important}';
	$css .= 'html body.kymeet-app .v4-rail__btn{display:none!important}';
	$css .= 'html body.kymeet-app .v4-deals{grid-template-columns:1fr!important}';
	$css .= 'html body.kymeet-app .v4-deal{display:grid!important;grid-template-columns:1fr!important;grid-template-areas:"media" "kicker" "title" "cta"!important;min-height:0!important;padding:14px!important;gap:8px!important;justify-items:center!important;text-align:center!important}';
	$css .= 'html body.kymeet-app .v4-deal small{grid-area:kicker!important;color:#fff!important}';
	$css .= 'html body.kymeet-app .v4-deal h3{grid-area:title!important;color:#fff!important;font-size:16px!important;max-width:none!important;margin:0!important}';
	$css .= 'html body.kymeet-app .v4-deal .v4-btn{grid-area:cta!important;justify-self:center!important}';
	$css .= 'html body.kymeet-app .v4-deal img{grid-area:media!important;position:static!important;inset:auto!important;max-height:100px!important;max-width:100%!important;height:auto!important;width:auto!important;margin:0 auto!important}';
	$css .= '}';
	return $css;
}

function kymeet_app_contrast_overrides() {
	if ( ! function_exists( 'kymeet_store_active' ) || ! kymeet_store_active() ) {
		return;
	}
	echo '<style id="kymeet-contrast-fix">' . kymeet_app_contrast_css() . '</style>';
}
add_action( 'wp_head', 'kymeet_app_contrast_overrides', 9999 );

function kymeet_app_contrast_overrides_footer() {
	if ( ! function_exists( 'kymeet_store_active' ) || ! kymeet_store_active() ) {
		return;
	}
	echo '<style id="kymeet-contrast-fix-late">' . kymeet_app_contrast_css() . '</style>';
}
add_action( 'wp_footer', 'kymeet_app_contrast_overrides_footer', 9999 );

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
