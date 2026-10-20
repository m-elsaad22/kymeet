<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class KYMEET_UI_WooCommerce {

	public static function init(): void {
		add_filter( 'woocommerce_locate_template', array( __CLASS__, 'locate_template' ), 20, 3 );
		add_action( 'wp_footer', array( __CLASS__, 'single_sticky_bar' ), 40 );
	}

	public static function locate_template( $template, $template_name, $template_path ) {
		if ( ! str_starts_with( $template_name, 'single-product' ) && 'archive-product.php' !== $template_name ) {
			return $template;
		}
		$plugin_file = KYMEET_UI_PATH . 'templates/woocommerce/' . $template_name;
		if ( file_exists( $plugin_file ) ) {
			return $plugin_file;
		}
		return $template;
	}

	public static function single_sticky_bar(): void {
		if ( ! function_exists( 'is_product' ) || ! is_product() ) {
			return;
		}
		global $product;
		if ( ! $product instanceof WC_Product ) {
			return;
		}
		?>
		<div class="kymeet-sticky-buy" aria-hidden="false">
			<div class="kymeet-sticky-buy__price"><?php echo wp_kses_post( $product->get_price_html() ); ?></div>
			<a class="btn btn-gold kymeet-sticky-buy__btn" href="<?php echo esc_url( $product->add_to_cart_url() ); ?>">
				<?php echo esc_html( $product->single_add_to_cart_text() ); ?>
			</a>
		</div>
		<?php
	}
}
