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

/**
 * Minimal HTML document: wp_head/wp_footer + KYMEET chrome only (no KAYAN v3 loader/home).
 *
 * @param callable $render_main Callback that echoes main content.
 */
function kymeet_render_store_document( callable $render_main ): void {
	if ( ! headers_sent() ) {
		status_header( 200 );
		nocache_headers();
	}

	$lang = function_exists( 'kayan_homepage_is_english' ) && kayan_homepage_is_english() ? 'en' : 'ar';
	$dir  = 'en' === $lang ? 'ltr' : 'rtl';
	?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> dir="<?php echo esc_attr( $dir ); ?>" lang="<?php echo esc_attr( $lang ); ?>">
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class( 'kymeet-doc kymeet-home kymeet-store-active' ); ?> mode="dark">
<?php
	wp_body_open();
	/**
	 * KYMEET splash + header (see setup.php on yc_hook_body_start).
	 */
	do_action( 'yc_hook_body_start' );
	call_user_func( $render_main );
	wp_footer();
	?>
</body>
</html>
	<?php
}

/**
 * Disable KAYAN Homepage v3 option when KYMEET home is active.
 *
 * @param mixed $value Stored option.
 */
function kymeet_filter_kayan_v3_disable( $value ) {
	if ( is_admin() || wp_doing_ajax() || wp_doing_cron() ) {
		return $value;
	}
	if ( ! function_exists( 'kymeet_use_store_homepage' ) || ! kymeet_use_store_homepage() ) {
		return $value;
	}
	return '1';
}

function kymeet_homepage_takeover(): void {
	if ( ! kymeet_use_store_homepage() ) {
		return;
	}

	kymeet_render_store_document(
		static function () {
			echo '<main class="kymeet-home-main" id="kymeet-home">';
			kymeet_render_homepage_sections();
			echo '</main>';
		}
	);
	exit;
}

/**
 * Run before kayan-stabilization homepage v3 (template_redirect priority 0).
 */
function kymeet_homepage_takeover_early(): void {
	kymeet_homepage_takeover();
}

/**
 * WooCommerce shop archive — same KYMEET catalog UI as mockup (not kayan-inner services).
 */
function kymeet_shop_takeover(): void {
	if ( ! kymeet_store_active() || ! function_exists( 'is_shop' ) || ! is_shop() ) {
		return;
	}
	if ( is_search() || is_product_taxonomy() ) {
		return;
	}

	kymeet_render_store_document(
		static function () {
			echo '<main class="kymeet-home-main kymeet-shop-main" id="kymeet-shop">';
			Kymeet_Render::catalog();
			echo '</main>';
		}
	);
	exit;
}

function kymeet_store_takeover_early(): void {
	kymeet_homepage_takeover();
	kymeet_shop_takeover();
}
