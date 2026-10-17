<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class KYMEET_UI_Home {

	public static function init(): void {
		add_action( 'wp_body_open', array( __CLASS__, 'visually_primary_h1' ), 5 );
		add_filter( 'document_title_parts', array( __CLASS__, 'document_title' ), 20 );
	}

	/**
	 * SEO: H1 on homepage when theme omits title tag / H1.
	 */
	public static function visually_primary_h1(): void {
		if ( ! is_front_page() ) {
			return;
		}
		echo '<h1 class="kymeet-sr-h1">KYMEET Perfumes — عطور فاخرة مستوحاة من أصالة كيمت</h1>';
	}

	public static function document_title( array $parts ): array {
		if ( is_front_page() ) {
			$parts['title'] = 'KYMEET Perfumes | كيمت للعطور';
		} elseif ( function_exists( 'is_shop' ) && is_shop() ) {
			$parts['title'] = 'متجر العطور | KYMEET';
		}
		return $parts;
	}
}
