<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class KYMEET_UI_Assets {

	public static function init(): void {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue' ), 999 );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'dequeue_legacy' ), 1000 );
		add_filter( 'body_class', array( __CLASS__, 'body_class' ) );
	}

	public static function body_class( array $classes ): array {
		if ( is_admin() ) {
			return $classes;
		}
		$classes[] = 'kymeet-app-ui';
		if ( wp_is_mobile() ) {
			$classes[] = 'kymeet-is-mobile';
		}
		return $classes;
	}

	public static function dequeue_legacy(): void {
		if ( is_admin() ) {
			return;
		}
		global $wp_scripts;
		if ( ! $wp_scripts instanceof WP_Scripts ) {
			return;
		}
		foreach ( $wp_scripts->registered as $handle => $script ) {
			$src = isset( $script->src ) ? (string) $script->src : '';
			if ( str_contains( $src, 'kymeet-store/assets/kymeet.js' ) ) {
				wp_dequeue_script( $handle );
				wp_deregister_script( $handle );
			}
		}
		// Theme bundles a second jQuery 3.4 — keep WordPress core jQuery only.
		wp_dequeue_script( 'yourcolor-script-js' );
	}

	public static function enqueue(): void {
		if ( is_admin() ) {
			return;
		}

		wp_enqueue_style(
			'kymeet-ui-fonts',
			'https://fonts.googleapis.com/css2?family=Alexandria:wght@300;400;500;600;700&family=Cinzel:wght@500;600;700&display=swap',
			array(),
			null
		);

		wp_enqueue_style(
			'kymeet-ui-app',
			KYMEET_UI_URL . 'assets/kymeet-app.css',
			array( 'kymeet-ui-fonts' ),
			KYMEET_UI_VERSION
		);

		wp_enqueue_script(
			'kymeet-ui-core',
			KYMEET_UI_URL . 'assets/kymeet-core.js',
			array(),
			KYMEET_UI_VERSION,
			true
		);

		wp_enqueue_script(
			'kymeet-ui-app',
			KYMEET_UI_URL . 'assets/kymeet-app.js',
			array( 'kymeet-ui-core' ),
			KYMEET_UI_VERSION,
			true
		);

		wp_localize_script(
			'kymeet-ui-app',
			'KYMEET_UI',
			array(
				'home'      => home_url( '/' ),
				'shop'      => function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' ),
				'account'   => function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : home_url( '/my-account/' ),
				'wishKey'   => 'kymeet_wish_v1',
				'i18n'      => array(
					'wishlist'  => 'المفضلة',
					'curated'   => 'مختارات كيميت',
					'categories'=> 'تصنيفات العطور',
				),
			)
		);
	}
}
