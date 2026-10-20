<?php
/**
 * REST: sync theme from GitHub (Application Password). Same as deploy/mu-plugin bootstrap.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'rest_api_init',
	static function () {
		register_rest_route(
			'kymeet/v1',
			'/sync-theme',
			array(
				'methods'             => 'POST',
				'permission_callback' => static function () {
					return current_user_can( 'update_themes' );
				},
				'callback'            => 'kymeet_remote_sync_theme_callback',
			)
		);
	}
);

if ( ! function_exists( 'kymeet_remote_sync_theme_callback' ) ) {
	function kymeet_remote_sync_theme_callback( WP_REST_Request $request ) {
		$branch = apply_filters( 'kymeet_sync_branch', 'cursor/kymeet-app-ui-9531' );
		$url    = 'https://github.com/m-elsaad22/kymeet/raw/' . $branch . '/dist/kayan-theme.zip';
		$tmp    = download_url( $url, 300 );
		if ( is_wp_error( $tmp ) ) {
			return new WP_REST_Response( array( 'ok' => false, 'error' => $tmp->get_error_message() ), 500 );
		}

		if ( ! class_exists( 'ZipArchive' ) ) {
			@unlink( $tmp );
			return new WP_REST_Response( array( 'ok' => false, 'error' => 'ZipArchive missing' ), 500 );
		}

		$zip = new ZipArchive();
		if ( true !== $zip->open( $tmp ) ) {
			@unlink( $tmp );
			return new WP_REST_Response( array( 'ok' => false, 'error' => 'Cannot open zip' ), 500 );
		}

		$zip->extractTo( get_theme_root() );
		$zip->close();
		@unlink( $tmp );

		if ( function_exists( 'clean_theme_cache' ) ) {
			clean_theme_cache();
		}
		wp_clean_themes_cache( true );

		$theme = wp_get_theme( 'kayan-theme' );

		return new WP_REST_Response(
			array(
				'ok'      => true,
				'version' => $theme->exists() ? $theme->get( 'Version' ) : '',
			),
			200
		);
	}
}
