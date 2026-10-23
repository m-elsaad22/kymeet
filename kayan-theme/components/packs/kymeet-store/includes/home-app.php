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

function kymeet_home_card_link( $row ) {
	$url = ! empty( $row['url'] ) ? $row['url'] : '#';
	$out = '<a class="kapp-card__link" href="' . esc_url( $url ) . '">';
	$out .= '<span class="kapp-card__media">' . kymeet_home_img( $row ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	$out .= '<span class="kapp-card__name">' . esc_html( $row['name'] ) . '</span>';
	if ( ! empty( $row['brand'] ) ) {
		$out .= '<span class="kapp-card__brand">' . esc_html( $row['brand'] ) . '</span>';
	}
	if ( isset( $row['price'] ) && function_exists( 'wc_price' ) ) {
		$out .= '<span class="kapp-card__price">' . wp_kses_post( wc_price( $row['price'] ) ) . '</span>';
	}
	return $out . '</a>';
}

function kymeet_home_card( $row, $extra_class = '' ) {
	$en     = function_exists( 'kymeet_app_en' ) && kymeet_app_en();
	$pid    = ! empty( $row['pid'] ) ? (int) $row['pid'] : 0;
	$wish_id = ! empty( $row['id'] ) ? (string) $row['id'] : ( $pid ? (string) $pid : '' );
	$label  = $en ? 'Add to favorites' : 'المفضلة';
	$out    = '<article class="kapp-card' . esc_attr( $extra_class ) . '"';
	if ( $wish_id ) {
		$out .= ' data-id="' . esc_attr( $wish_id ) . '" data-pid="' . esc_attr( (string) $pid ) . '"';
	}
	$out .= '>';
	if ( $wish_id ) {
		$out .= '<button type="button" class="prod-wish kapp-card__wish" data-id="' . esc_attr( $wish_id ) . '" aria-label="' . esc_attr( $label ) . '"><i class="far fa-heart" aria-hidden="true"></i></button>';
	}
	$out .= kymeet_home_card_link( $row ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	return $out . '</article>';
}

function kymeet_home_cards( $rows, $slider = true ) {
	$en = function_exists( 'kymeet_app_en' ) && kymeet_app_en();
	if ( ! $rows ) {
		echo '<p class="kapp-muted">' . esc_html( $en ? 'No products yet.' : 'لا توجد منتجات بعد.' ) . '</p>';
		return;
	}
	if ( $slider && count( $rows ) > 1 ) {
		echo '<div class="kslider kapp-slider" data-auto="0"><div class="kslider-track">';
		foreach ( $rows as $row ) {
			echo kymeet_home_card( $row, ' kapp-slider__item' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		echo '</div></div>';
		return;
	}
	echo '<div class="kapp-row">';
	foreach ( $rows as $row ) {
		echo kymeet_home_card( $row ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	echo '</div>';
}

function kymeet_home_head( $title, $link = '' ) {
	$en = function_exists( 'kymeet_app_en' ) && kymeet_app_en();
	echo '<div class="kapp-sec__head"><h2>' . esc_html( $title ) . '</h2>';
	if ( $link ) {
		echo '<a class="kapp-sec__all" href="' . esc_url( $link ) . '">';
		echo esc_html( $en ? 'View all' : 'عرض الكل' );
		echo ' <i class="fas fa-chevron-left" aria-hidden="true"></i></a>';
	}
	echo '</div>';
}

function kymeet_home_term_thumb( $term ) {
	if ( ! $term instanceof WP_Term ) {
		return '';
	}
	if ( 'product_cat' === $term->taxonomy ) {
		$thumb_id = (int) get_term_meta( $term->term_id, 'thumbnail_id', true );
		if ( $thumb_id ) {
			$url = wp_get_attachment_image_url( $thumb_id, 'woocommerce_thumbnail' );
			if ( $url ) {
				return $url;
			}
		}
	}
	$img = get_term_meta( $term->term_id, 'kymeet_brand_image', true );
	return is_string( $img ) ? trim( $img ) : '';
}

function kymeet_home_cat_tile( $label, $url, $img_url = '' ) {
	$out  = '<a class="kapp-cat-tile" href="' . esc_url( $url ) . '">';
	$out .= '<span class="kapp-cat-tile__img">';
	if ( $img_url ) {
		$out .= '<img src="' . esc_url( $img_url ) . '" alt="" loading="lazy" width="120" height="120">';
	} else {
		$out .= '<i class="fas fa-spray-can-sparkles" aria-hidden="true"></i>';
	}
	$out .= '</span><span class="kapp-cat-tile__label">' . esc_html( $label ) . '</span></a>';
	return $out;
}

function kymeet_home_cat_grid( $tiles ) {
	if ( ! $tiles ) {
		return;
	}
	echo '<div class="kapp-cat-grid">';
	foreach ( $tiles as $t ) {
		echo kymeet_home_cat_tile( $t['label'], $t['url'], isset( $t['img'] ) ? $t['img'] : '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	echo '</div>';
}

function kymeet_render_home_search_strip() {
	$en = function_exists( 'kymeet_app_en' ) && kymeet_app_en();
	$ph = $en ? 'Search perfumes or brands…' : 'ابحث عن عطر أو علامة…';
	echo '<div class="kapp-home-top">';
	echo '<button type="button" class="kapp-search-bar" data-kapp-open="search">';
	echo '<i class="fas fa-magnifying-glass kapp-search-bar__lead" aria-hidden="true"></i>';
	echo '<span class="kapp-search-bar__ph">' . esc_html( $ph ) . '</span>';
	echo '</button></div>';
}

function kymeet_render_home_quick_pills() {
	if ( ! function_exists( 'kymeet_app_shop_url' ) ) {
		return;
	}
	$en   = kymeet_app_en();
	$shop = kymeet_app_shop_url();
	$pills = array(
		array( $en ? 'All perfumes' : 'كل العطور', $shop, true ),
		array( $en ? 'Featured' : 'مميز', add_query_arg( 'kymeet_chip', 'feat', $shop ), false ),
		array( $en ? 'Men' : 'رجالي', add_query_arg( 'kymeet_chip', 'men', $shop ), false ),
		array( $en ? 'Women' : 'نسائي', add_query_arg( 'kymeet_chip', 'women', $shop ), false ),
		array( $en ? 'Gifts' : 'هدايا', $shop, false ),
	);
	echo '<div class="kapp-pill-nav" role="navigation" aria-label="' . esc_attr( $en ? 'Quick links' : 'اختصارات' ) . '">';
	foreach ( $pills as $i => $p ) {
		echo '<a class="kapp-pill' . ( 0 === $i ? ' kapp-pill--on' : '' ) . '" href="' . esc_url( $p[1] ) . '">' . esc_html( $p[0] ) . '</a>';
	}
	echo '</div>';
}

function kymeet_render_home_app() {
	$en   = function_exists( 'kymeet_app_en' ) && kymeet_app_en();
	$shop = function_exists( 'kymeet_app_shop_url' ) ? kymeet_app_shop_url() : home_url( '/shop/' );
	$hero_line = function_exists( 'kymeet_app_content_get' ) ? kymeet_app_content_get( 'hero_line_ar', 'hero_line_en' ) : '';

	echo '<main class="kapp-main">';
	kymeet_render_home_search_strip();
	kymeet_render_home_quick_pills();

	if ( function_exists( 'kymeet_store_hero_slider' ) ) {
		echo '<section class="kapp-banner">';
		if ( $hero_line ) {
			echo '<p class="kapp-banner__tag">' . esc_html( $hero_line ) . '</p>';
		}
		echo kymeet_store_hero_slider(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '</section>';
	}

	$cat_tiles = array();
	if ( taxonomy_exists( 'product_cat' ) ) {
		$cats = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => true,
				'number'     => 8,
				'exclude'    => array( (int) get_option( 'default_product_cat', 0 ) ),
			)
		);
		if ( is_array( $cats ) && ! is_wp_error( $cats ) ) {
			foreach ( $cats as $cat ) {
				if ( 'uncategorized' === $cat->slug ) {
					continue;
				}
				$link = get_term_link( $cat );
				if ( is_wp_error( $link ) ) {
					continue;
				}
				$cat_tiles[] = array(
					'label' => $cat->name,
					'url'   => $link,
					'img'   => kymeet_home_term_thumb( $cat ),
				);
			}
		}
	}
	if ( $cat_tiles ) {
		echo '<section class="kapp-sec kapp-sec--flush">';
		kymeet_home_head( $en ? 'Categories' : 'الأقسام', $shop );
		kymeet_home_cat_grid( $cat_tiles );
		echo '</section>';
	}

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
	$brand_tiles = array();
	if ( is_array( $terms ) && ! is_wp_error( $terms ) ) {
		foreach ( $terms as $term ) {
			$brand_tiles[] = array(
				'label' => $term->name,
				'url'   => add_query_arg(
					array(
						'kymeet_chip'  => 'brand',
						'kymeet_brand' => $term->slug,
					),
					$shop
				),
				'img'   => kymeet_home_term_thumb( $term ),
			);
		}
	}
	if ( $brand_tiles ) {
		kymeet_home_cat_grid( $brand_tiles );
	} else {
		echo '<p class="kapp-muted">' . esc_html( $en ? 'No brands yet.' : 'لا توجد علامات بعد.' ) . '</p>';
	}
	echo '</section>';

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
		echo '<section class="kapp-sec">';
		kymeet_home_head( $en ? 'Gifts' : 'الهدايا', $shop );
		kymeet_home_cards( $gifts );
		echo '</section>';
	}

	$story = function_exists( 'kymeet_app_content_get' ) ? kymeet_app_content_get( 'story_ar', 'story_en' ) : '';
	if ( $story ) {
		echo '<section class="kapp-sec kapp-sec--soft">';
		kymeet_home_head( $en ? 'Our story' : 'قصة العلامة' );
		echo '<p class="kapp-muted">' . esc_html( $story ) . '</p>';
		echo '</section>';
	}

	$trust = function_exists( 'kymeet_app_content_lines' ) ? kymeet_app_content_lines( 'trust_ar', 'trust_en' ) : array();
	if ( $trust ) {
		echo '<section class="kapp-sec kapp-trust">';
		kymeet_home_head( $en ? 'Trust' : 'عناصر الثقة' );
		echo '<ul class="kapp-trust">';
		foreach ( $trust as $line ) {
			echo '<li>' . esc_html( $line ) . '</li>';
		}
		echo '</ul></section>';
	}

	$news = function_exists( 'kymeet_app_content_get' ) ? kymeet_app_content_get( 'newsletter_ar', 'newsletter_en' ) : '';
	if ( $news ) {
		echo '<section class="kapp-sec kapp-sec--soft">';
		kymeet_home_head( $en ? 'Newsletter' : 'النشرة البريدية' );
		echo '<p class="kapp-muted">' . esc_html( $news ) . '</p>';
		echo '<form class="kapp-news" action="#" method="post" onsubmit="return false;"><label><span class="screen-reader-text">' . esc_html( $en ? 'Email address' : 'البريد الإلكتروني' ) . '</span><input type="email" placeholder="name@example.com"></label><button type="submit" class="kapp-news__btn">' . esc_html( $en ? 'Subscribe' : 'اشتراك' ) . '</button></form>';
		echo '</section>';
	}

	echo '</main>';
}
