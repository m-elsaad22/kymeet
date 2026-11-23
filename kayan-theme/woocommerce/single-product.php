<?php
/**
 * WooCommerce single product — theme body rendered via @single/product.php (ThemeStatic::Locate).
 *
 * @package WooCommerce\Templates
 * @version 1.6.0
 */

defined( 'ABSPATH' ) || exit;

while ( have_posts() ) {
	the_post();
	wc_get_template_part( 'content', 'single-product' );
}
