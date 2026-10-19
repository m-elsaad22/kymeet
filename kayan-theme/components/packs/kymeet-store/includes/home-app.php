<?php
/**
 * Homepage sections from WooCommerce products (Step 4).
 */
defined( 'ABSPATH' ) || exit;

function kymeet_home_query( $extra = array() ) {
	if ( ! function_exists( 'kymeet_wc_active' ) || ! kymeet_wc_active() ) {
		return array();
	}
	$q = new WP_Query(
		array_merge(
			array(
				'post_type'      => 'product',
				'post_status'    => 'publish',
				'posts_per_page' => 8,
				'no_found_rows'  => true,
			),
			$extra
		)
	);
	$rows = array();
	foreach ( $q->posts as $post ) {
		if ( function_exists( 'kymeet_store_row' ) ) {
			$rows[] = kymeet_store_row( $post );
		}
	}
	wp_reset_postdata();
	return $rows;
}

function kymeet_home_img( $row, $priority = false ) {
	$alt = isset( $row['name'] ) ? $row['name'] : '';
	if ( empty( $row['pid'] ) ) {
		return '<span class="kapp-ph" role="img" aria-label="' . esc_attr( $alt ) . '"></span>';
	}
	$html = get_the_post_thumbnail(
		(int) $row['pid'],
		'woocommerce_thumbnail',
		array(
			'alt'           => $alt,
			'loading'       => $priority ? 'eager' : 'lazy',
			'fetchpriority' => $priority ? 'high' : 'low',
			'class'         => 'kapp-card__img',
		)
	);
	if ( $html ) {
		return $html;
	}
	$external = ! empty( $row['image'] ) ? esc_url( $row['image'] ) : '';
	if ( $external ) {
		return '<img class="kapp-card__img" src="' . $external . '" alt="' . esc_attr( $alt ) . '" loading="lazy" width="300" height="375">';
	}
	$src = '';
	if ( function_exists( 'wc_placeholder_img_src' ) ) {
		$src = wc_placeholder_img_src( 'woocommerce_thumbnail' );
		if ( ! $src ) {
			$src = wc_placeholder_img_src();
		}
	}
	if ( $src ) {
		return '<img class="kapp-card__img" src="' . esc_url( $src ) . '" alt="" loading="lazy" width="300" height="375">';
	}
	return '<span class="kapp-ph"></span>';
}

function kymeet_home_cards( $rows ) {
	$en = function_exists( 'kymeet_app_en' ) && kymeet_app_en();
	if ( ! $rows ) {
		echo '<p class="kapp-muted">' . esc_html( $en ? 'No products yet.' : 'لا توجد منتجات بعد.' ) . '</p>';
		return;
	}
	echo '<div class="kapp-row">';
	foreach ( $rows as $row ) {
		$url = ! empty( $row['url'] ) ? $row['url'] : '#';
		echo '<article class="kapp-card"><a href="' . esc_url( $url ) . '">';
		echo '<span class="kapp-card__media">' . kymeet_home_img( $row ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<span class="kapp-card__name">' . esc_html( $row['name'] ) . '</span>';
		if ( ! empty( $row['brand'] ) ) {
			echo '<span class="kapp-card__brand">' . esc_html( $row['brand'] ) . '</span>';
		}
		if ( isset( $row['price'] ) && function_exists( 'wc_price' ) ) {
			echo '<span class="kapp-card__price">' . wp_kses_post( wc_price( $row['price'] ) ) . '</span>';
		}
		echo '</a></article>';
	}
	echo '</div>';
}

function kymeet_home_head( $title, $link = '' ) {
	echo '<div class="kapp-sec__head"><h2>' . esc_html( $title ) . '</h2>';
	if ( $link ) {
		echo '<a href="' . esc_url( $link ) . '">' . esc_html( kymeet_app_en() ? 'View all' : 'عرض الكل' ) . '</a>';
	}
	echo '</div>';
}

function kymeet_render_home_app() {
	$en   = function_exists( 'kymeet_app_en' ) && kymeet_app_en();
	$shop = function_exists( 'kymeet_app_shop_url' ) ? kymeet_app_shop_url() : home_url( '/shop/' );
	$hero = kymeet_home_query( array( 'posts_per_page' => 1, 'orderby' => 'date', 'order' => 'DESC' ) );
	$hero = $hero ? $hero[0] : array();

	echo '<section class="kapp-hero">';
	echo '<div class="kapp-hero__copy">';
	echo '<p class="kapp-hero__kicker">KYMEET</p>';
	echo '<h1>' . esc_html( ! empty( $hero['name'] ) ? $hero['name'] : ( $en ? 'Luxury scents' : 'عطور فاخرة' ) ) . '</h1>';
	echo '<p>' . esc_html( $en ? '[Placeholder] Short hero line.' : '[نص تجريبي] سطر قصير عن العطر.' ) . '</p>';
	echo '<div class="kapp-hero__actions">';
	$primary = ! empty( $hero['url'] ) ? $hero['url'] : $shop;
	echo '<a class="kapp-btn kapp-btn--primary" href="' . esc_url( $primary ) . '">' . esc_html( $en ? 'Shop now' : 'تسوق الآن' ) . '</a>';
	echo '<a class="kapp-btn kapp-btn--ghost" href="' . esc_url( $shop ) . '">' . esc_html( $en ? 'All perfumes' : 'كل العطور' ) . '</a>';
	echo '</div></div>';
	echo '<div class="kapp-hero__media">' . ( $hero ? kymeet_home_img( $hero, true ) : '<span class="kapp-ph"></span>' ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '</section>';

	echo '<section class="kapp-sec">';
	kymeet_home_head( $en ? 'Latest' : 'أحدث العطور', $shop );
	kymeet_home_cards( kymeet_home_query( array( 'orderby' => 'date', 'order' => 'DESC' ) ) );
	echo '</section>';

	echo '<section class="kapp-sec kapp-sec--soft">';
	kymeet_home_head( $en ? 'Bestsellers' : 'الأكثر مبيعًا', add_query_arg( 'kymeet_chip', 'feat', $shop ) );
	$best = array();
	if ( taxonomy_exists( 'product_visibility' ) ) {
		$best = kymeet_home_query(
			array(
				'tax_query' => array(
					array(
						'taxonomy' => 'product_visibility',
						'field'    => 'name',
						'terms'    => array( 'featured' ),
					),
				),
			)
		);
	}
	kymeet_home_cards( $best );
	echo '</section>';

	echo '<section class="kapp-sec">';
	kymeet_home_head( $en ? 'Brands' : 'العلامات', $shop );
	$tax   = function_exists( 'kymeet_store_brand_tax' ) ? kymeet_store_brand_tax() : 'product_brand';
	$terms = taxonomy_exists( $tax ) ? get_terms( array( 'taxonomy' => $tax, 'hide_empty' => true, 'number' => 12 ) ) : array();
	echo '<div class="kapp-chips">';
	if ( is_array( $terms ) && ! is_wp_error( $terms ) && $terms ) {
		foreach ( $terms as $term ) {
			$url = add_query_arg( array( 'kymeet_chip' => 'brand', 'kymeet_brand' => $term->slug ), $shop );
			echo '<a class="kapp-chip" href="' . esc_url( $url ) . '">' . esc_html( $term->name ) . '</a>';
		}
	} else {
		echo '<p class="kapp-muted">' . esc_html( $en ? 'No brands yet.' : 'لا توجد علامات بعد.' ) . '</p>';
	}
	echo '</div></section>';

	echo '<section class="kapp-sec kapp-sec--soft">';
	kymeet_home_head( $en ? 'Categories' : 'الأقسام', $shop );
	if ( function_exists( 'kymeet_echo_shop_chips' ) ) {
		kymeet_echo_shop_chips();
	}
	echo '</section>';

	echo '<section class="kapp-sec">';
	kymeet_home_head( $en ? 'Gifts' : 'الهدايا', $shop );
	$gift_slugs = array();
	if ( taxonomy_exists( 'product_tag' ) ) {
		foreach ( array( 'gift', 'gifts' ) as $slug ) {
			$term = get_term_by( 'slug', $slug, 'product_tag' );
			if ( $term instanceof WP_Term ) {
				$gift_slugs[] = $slug;
			}
		}
	}
	$gifts = $gift_slugs ? kymeet_home_query(
		array(
			'tax_query' => array(
				array(
					'taxonomy' => 'product_tag',
					'field'    => 'slug',
					'terms'    => $gift_slugs,
				),
			),
		)
	) : array();
	if ( $gifts ) {
		kymeet_home_cards( $gifts );
	} else {
		echo '<p class="kapp-muted">' . esc_html( $en ? '[Placeholder] Tag products with slug gift to list them here.' : '[نص تجريبي] أضف وسم gift للمنتجات لتظهر هنا. لا توجد هدايا مصنّفة.' ) . '</p>';
	}
	echo '</section>';

	echo '<section class="kapp-sec kapp-sec--soft">';
	kymeet_home_head( $en ? 'Our story' : 'قصة العلامة' );
	echo '<p class="kapp-muted">' . esc_html( $en ? '[Placeholder] Brand story — replace with the real KYMEET text.' : '[نص تجريبي] قصة KYMEET — استبدلها بالنص الحقيقي للعلامة.' ) . '</p>';
	echo '</section>';

	echo '<section class="kapp-sec kapp-trust">';
	kymeet_home_head( $en ? 'Trust' : 'عناصر الثقة' );
	echo '<ul class="kapp-trust">';
	$trust = $en
		? array( '[Placeholder] Authentic stock', '[Placeholder] Careful packing', '[Placeholder] Support via your account' )
		: array( '[نص تجريبي] منتجات أصلية', '[نص تجريبي] تغليف بعناية', '[نص تجريبي] متابعة الطلب من حسابك' );
	foreach ( $trust as $line ) {
		echo '<li>' . esc_html( $line ) . '</li>';
	}
	echo '</ul></section>';

	echo '<section class="kapp-sec kapp-sec--soft">';
	kymeet_home_head( $en ? 'Newsletter' : 'النشرة البريدية' );
	echo '<p class="kapp-muted">' . esc_html( $en ? '[Placeholder] Not connected yet.' : '[تجريبي] النشرة غير مربوطة بعد.' ) . '</p>';
	echo '<form class="kapp-news" action="#" method="post" onsubmit="return false;"><label><span class="screen-reader-text">Email</span><input type="email" placeholder="name@example.com"></label><button type="submit" class="kapp-news__btn">' . esc_html( $en ? 'Subscribe' : 'اشتراك' ) . '</button></form>';
	echo '</section>';
}
