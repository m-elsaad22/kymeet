<?php
/**
 * KYMEET Store pack — luxury WooCommerce storefront inside KAYAN Theme.
 */

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/products.php';
require_once __DIR__ . '/includes/class-kymeet-render.php';
require_once __DIR__ . '/includes/shell.php';
require_once __DIR__ . '/widgets.php';

add_action(
	'plugins_loaded',
	static function () {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}
		require_once KYMEET_STORE_PATH . 'woo.php';
	},
	5
);

add_filter(
	'body_class',
	static function ( $classes ) {
		if ( kymeet_store_active() ) {
			$classes[] = 'kymeet-store-active';
		}
		return $classes;
	}
);

add_action(
	'wp_enqueue_scripts',
	static function () {
		if ( ! kymeet_store_active() ) {
			return;
		}
		$ver = '2.0.1';
		wp_enqueue_style(
			'kymeet-store',
			KYMEET_STORE_URL . 'assets/kymeet.css',
			array(),
			$ver
		);
		wp_enqueue_style(
			'kymeet-store-luxury',
			KYMEET_STORE_URL . 'assets/kymeet-luxury.css',
			array( 'kymeet-store' ),
			$ver
		);
		$fa_path = get_template_directory() . '/components/styles/FontAwesome/css/all.min.css';
		if ( file_exists( $fa_path ) ) {
			wp_enqueue_style(
				'kymeet-fa',
				get_template_directory_uri() . '/components/styles/FontAwesome/css/all.min.css',
				array(),
				$ver
			);
		}
		wp_enqueue_script(
			'kymeet-store',
			KYMEET_STORE_URL . 'assets/kymeet.js',
			array(),
			$ver,
			true
		);
		wp_enqueue_script(
			'kymeet-store-ui',
			KYMEET_STORE_URL . 'assets/kymeet-ui.js',
			array( 'kymeet-store' ),
			$ver,
			true
		);
	},
	30
);

/**
 * Prefer KYMEET header over default homepage header when both exist.
 */
add_action(
	'yc_hook_body_start',
	static function () {
		if ( ! kymeet_store_active() ) {
			return;
		}
		echo '<div class="kymeet-chrome-root">';
		kymeet_render_header();
	},
	5
);

add_action(
	'wp_body_open',
	static function () {
		if ( ! kymeet_store_active() ) {
			return;
		}
		kymeet_sr_h1();
	},
	6
);

add_action(
	'wp_footer',
	static function () {
		if ( ! kymeet_store_active() ) {
			return;
		}
		kymeet_render_footer_shell();
		echo '</div><!-- .kymeet-chrome-root -->';
	},
	5
);

add_action( 'wp_footer', 'kymeet_single_sticky_buy', 40 );

add_action(
	'init',
	static function () {
		add_rewrite_rule( '^kymeet-manifest\.json$', 'index.php?kymeet_manifest=1', 'top' );
	},
	5
);

add_filter(
	'query_vars',
	static function ( $vars ) {
		$vars[] = 'kymeet_manifest';
		return $vars;
	}
);

add_action(
	'template_redirect',
	static function () {
		if ( ! get_query_var( 'kymeet_manifest' ) ) {
			return;
		}
		header( 'Content-Type: application/json; charset=utf-8' );
		echo wp_json_encode(
			array(
				'name'  => get_bloginfo( 'name' ),
				'short' => 'KYMEET',
				'start' => home_url( '/' ),
				'lang'  => 'ar',
			)
		);
		exit;
	}
);
