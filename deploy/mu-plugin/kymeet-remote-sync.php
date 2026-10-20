<?php
/**
 * Plugin Name: KYMEET Remote Theme Sync
 * Description: One-time bootstrap — syncs kayan-theme from GitHub using Application Password. Remove after deploy.
 * Version: 1.0.0
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

function kymeet_remote_sync_theme_callback( WP_REST_Request $request ) {
	$url  = 'https://github.com/m-elsaad22/kymeet/raw/cursor/kymeet-app-ui-9531/dist/kayan-theme.zip';
	$tmp  = download_url( $url, 300 );
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

	$dest = get_theme_root() . '/kayan-theme';
	if ( ! wp_mkdir_p( $dest ) ) {
		$zip->close();
		@unlink( $tmp );
		return new WP_REST_Response( array( 'ok' => false, 'error' => 'Cannot create theme dir' ), 500 );
	}

	$zip->extractTo( get_theme_root() );
	$zip->close();
	@unlink( $tmp );

	clean_theme_cache();
	wp_clean_themes_cache( true );

	return new WP_REST_Response(
		array(
			'ok'      => true,
			'version' => wp_get_theme( 'kayan-theme' )->get( 'Version' ),
			'path'    => $dest,
		),
		200
	);
}
