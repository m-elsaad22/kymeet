<?php
/**
 * Plugin Name: KYMEET Perfumes UI
 * Description: واجهة تطبيق فاخرة لمتجر KYMEET — تصميم كحلي/ذهبي، تنقل سفلي، وتحسينات UX دون تعديل قالب KAYAN مباشرة.
 * Version: 1.0.0
 * Author: KYMEET
 * Text Domain: kymeet-ui
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'KYMEET_UI_VERSION', '1.0.0' );
define( 'KYMEET_UI_PATH', plugin_dir_path( __FILE__ ) );
define( 'KYMEET_UI_URL', plugin_dir_url( __FILE__ ) );

require_once KYMEET_UI_PATH . 'includes/class-assets.php';
require_once KYMEET_UI_PATH . 'includes/class-home.php';
require_once KYMEET_UI_PATH . 'includes/class-woocommerce.php';

KYMEET_UI_Assets::init();
KYMEET_UI_Home::init();
KYMEET_UI_WooCommerce::init();
