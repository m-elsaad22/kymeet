<?php
/**
 * KYMEET app chrome — Step 3 (header, 5-tab nav, search, favorites, footer, announce).
 */
defined( 'ABSPATH' ) || exit;

function kymeet_app_shop_url() {
	if ( function_exists( 'kymeet_wc_active' ) && kymeet_wc_active() && function_exists( 'wc_get_page_permalink' ) ) {
		$url = wc_get_page_permalink( 'shop' );
		if ( $url ) {
			return $url;
		}
	}
	return home_url( '/shop/' );
}

function kymeet_app_account_url() {
	if ( function_exists( 'kymeet_wc_active' ) && kymeet_wc_active() && function_exists( 'wc_get_page_permalink' ) ) {
		$url = wc_get_page_permalink( 'myaccount' );
		if ( $url ) {
			return $url;
		}
	}
	return home_url( '/my-account/' );
}

function kymeet_app_en() {
	return function_exists( 'kymeet_store_is_en' ) && kymeet_store_is_en();
}

function kymeet_render_announce() {
	$en = kymeet_app_en();
	$text = $en
		? '[Placeholder] Announcement — replace this text in the theme when you have a real offer.'
		: '[نص تجريبي] شريط إعلان — استبدله بنص حقيقي من المتجر عند توفر العرض.';
	echo '<div class="kapp-announce" id="kapp-announce" role="region" aria-label="' . esc_attr( $en ? 'Announcement' : 'إعلان' ) . '">';
	echo '<p>' . esc_html( $text ) . '</p>';
	echo '<button type="button" class="kapp-announce__x" data-kapp-announce-close aria-label="' . esc_attr( $en ? 'Dismiss' : 'إغلاق' ) . '"><i class="fas fa-xmark" aria-hidden="true"></i></button>';
	echo '</div>';
}

function kymeet_render_app_header() {
	$en   = kymeet_app_en();
	$home = home_url( '/' );
	$logo = function_exists( 'kymeet_store_logo_url' ) ? kymeet_store_logo_url() : '';
	$name = get_bloginfo( 'name' );
	echo '<header class="kapp-hdr" id="kapp-hdr">';
	echo '<a class="kapp-brand" href="' . esc_url( $home ) . '">';
	if ( $logo ) {
		echo '<img class="kapp-brand__img" src="' . esc_url( $logo ) . '" alt="" width="36" height="36">';
	} else {
		echo '<span class="kapp-brand__mark" aria-hidden="true"><i class="fas fa-crown"></i></span>';
	}
	echo '<span class="kapp-brand__name">' . esc_html( $name ? $name : 'KYMEET' ) . '</span>';
	echo '</a>';
	echo '<div class="kapp-hdr__actions">';
	echo '<button type="button" class="kapp-icon-btn" data-kapp-open="search" aria-label="' . esc_attr( $en ? 'Search' : 'بحث' ) . '"><i class="fas fa-magnifying-glass" aria-hidden="true"></i></button>';
	echo '<button type="button" class="kapp-icon-btn" onclick="openCart()" aria-label="' . esc_attr( $en ? 'Cart' : 'السلة' ) . '"><i class="fas fa-bag-shopping" aria-hidden="true"></i><span class="kapp-count" id="kappCartCount">0</span></button>';
	echo '</div></header>';
	kymeet_render_search_panel();
	kymeet_render_wish_panel();
}

function kymeet_render_search_panel() {
	$en = kymeet_app_en();
	echo '<div class="kapp-sheet" id="kapp-search" hidden role="dialog" aria-modal="true" aria-labelledby="kapp-search-title">';
	echo '<div class="kapp-sheet__bar"><h2 id="kapp-search-title">' . esc_html( $en ? 'Search' : 'بحث' ) . '</h2>';
	echo '<button type="button" class="kapp-icon-btn" data-kapp-close aria-label="' . esc_attr( $en ? 'Close' : 'إغلاق' ) . '"><i class="fas fa-xmark"></i></button></div>';
	echo '<label class="kapp-search-field"><span class="screen-reader-text">' . esc_html( $en ? 'Search perfumes' : 'ابحث عن عطر' ) . '</span>';
	echo '<input type="search" id="kapp-q" autocomplete="off" placeholder="' . esc_attr( $en ? 'Search by name or brand…' : 'ابحث بالاسم أو العلامة…' ) . '"></label>';
	echo '<ul class="kapp-suggest" id="kapp-suggest" role="listbox"></ul>';
	echo '<div class="kapp-results" id="kapp-results"></div>';
	echo '</div>';
}

function kymeet_render_wish_panel() {
	$en = kymeet_app_en();
	echo '<div class="kapp-wish" id="kymeet-wish" hidden>';
	echo '<div class="wrap"><h1>' . esc_html( $en ? 'Favorites' : 'المفضلة' ) . '</h1>';
	echo '<p class="kapp-muted">' . esc_html( $en ? 'Saved on this device (localStorage).' : 'محفوظة على هذا الجهاز (localStorage).' ) . '</p>';
	echo '<div class="kapp-results" id="kapp-wish-grid"></div></div></div>';
}

function kymeet_shop_chip_url( $args ) {
	return add_query_arg( $args, kymeet_app_shop_url() );
}

function kymeet_render_shop_chips() {
	if ( ! function_exists( 'kymeet_store_active' ) || ! kymeet_store_active() ) {
		return '';
	}
	$en   = kymeet_app_en();
	$base = kymeet_app_shop_url();
	$cur  = isset( $_GET['kymeet_chip'] ) ? sanitize_key( wp_unslash( $_GET['kymeet_chip'] ) ) : 'all'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$brand = isset( $_GET['kymeet_brand'] ) ? sanitize_title( wp_unslash( $_GET['kymeet_brand'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$chips = array(
		array( 'all', $en ? 'All' : 'الكل', $base ),
		array( 'men', $en ? 'Men' : 'رجالي', add_query_arg( 'kymeet_chip', 'men', $base ) ),
		array( 'women', $en ? 'Women' : 'نسائي', add_query_arg( 'kymeet_chip', 'women', $base ) ),
		array( 'feat', $en ? 'Featured' : 'مميز', add_query_arg( 'kymeet_chip', 'feat', $base ) ),
	);
	$out = '<div class="kapp-chips" role="tablist" aria-label="' . esc_attr( $en ? 'Filters' : 'تصفية' ) . '">';
	foreach ( $chips as $c ) {
		$on = ( ! $brand && $cur === $c[0] ) ? ' on' : '';
		$out .= '<a class="kapp-chip' . $on . '" role="tab" href="' . esc_url( $c[2] ) . '">' . esc_html( $c[1] ) . '</a>';
	}
	$tax = function_exists( 'kymeet_store_brand_tax' ) ? kymeet_store_brand_tax() : 'product_brand';
	if ( taxonomy_exists( $tax ) ) {
		$terms = get_terms(
			array(
				'taxonomy'   => $tax,
				'hide_empty' => true,
				'number'     => 12,
			)
		);
		if ( ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$on = ( $brand === $term->slug ) ? ' on' : '';
				$url = add_query_arg(
					array(
						'kymeet_chip'  => 'brand',
						'kymeet_brand' => $term->slug,
					),
					$base
				);
				$out .= '<a class="kapp-chip' . $on . '" role="tab" href="' . esc_url( $url ) . '">' . esc_html( $term->name ) . '</a>';
			}
		}
	}
	$out .= '</div>';
	return $out;
}

function kymeet_echo_shop_chips() {
	echo kymeet_render_shop_chips(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

add_action(
	'woocommerce_before_shop_loop',
	static function () {
		if ( function_exists( 'kymeet_store_active' ) && kymeet_store_active() ) {
			kymeet_echo_shop_chips();
		}
	},
	4
);

add_action(
	'pre_get_posts',
	static function ( $query ) {
		if ( is_admin() || ! $query->is_main_query() || ! function_exists( 'is_shop' ) ) {
			return;
		}
		if ( ! is_shop() && ! ( function_exists( 'is_product_taxonomy' ) && is_product_taxonomy() ) ) {
			return;
		}
		$chip = isset( $_GET['kymeet_chip'] ) ? sanitize_key( wp_unslash( $_GET['kymeet_chip'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( 'feat' === $chip ) {
			$query->set(
				'tax_query',
				array(
					array(
						'taxonomy' => 'product_visibility',
						'field'    => 'name',
						'terms'    => array( 'featured' ),
					),
				)
			);
		}
		if ( 'brand' === $chip && ! empty( $_GET['kymeet_brand'] ) && function_exists( 'kymeet_store_brand_tax' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$query->set(
				'tax_query',
				array(
					array(
						'taxonomy' => kymeet_store_brand_tax(),
						'field'    => 'slug',
						'terms'    => sanitize_title( wp_unslash( $_GET['kymeet_brand'] ) ),
					),
				)
			);
		}
		if ( 'men' === $chip ) {
			$query->set( 'kymeet_title_like', 'رجال' );
		}
		if ( 'women' === $chip ) {
			$query->set( 'kymeet_title_like', 'نسا' );
		}
	},
	20
);

add_filter(
	'posts_where',
	static function ( $where, $query ) {
		$like = $query->get( 'kymeet_title_like' );
		if ( ! $like ) {
			return $where;
		}
		global $wpdb;
		$where .= $wpdb->prepare( " AND {$wpdb->posts}.post_title LIKE %s", '%' . $wpdb->esc_like( $like ) . '%' );
		return $where;
	},
	10,
	2
);

function kymeet_render_app_footer() {
	$en   = kymeet_app_en();
	$shop = kymeet_app_shop_url();
	$acc  = kymeet_app_account_url();
	echo '<footer class="kymeet-site-footer">';
	echo '<div class="wrap kapp-foot">';
	echo '<div><p class="kapp-foot__brand">KYMEET</p><p>' . esc_html( $en ? '[Placeholder] Short store description — replace with the real about text.' : '[نص تجريبي] تعريف مختصر بالمتجر — استبدله بالنص الحقيقي.' ) . '</p></div>';
	echo '<div><h2>' . esc_html( $en ? 'Customer service' : 'خدمة العملاء' ) . '</h2><ul>';
	echo '<li><a href="' . esc_url( $acc ) . '">' . esc_html( $en ? 'My account' : 'حسابي' ) . '</a></li>';
	echo '<li><a href="' . esc_url( $acc ) . '">' . esc_html( $en ? 'Orders' : 'طلباتي' ) . '</a></li>';
	echo '<li><a href="#">' . esc_html( $en ? '[Placeholder] Contact page' : '[رابط تجريبي] صفحة تواصل' ) . '</a></li>';
	echo '</ul></div>';
	echo '<div><h2>' . esc_html( $en ? 'Shop' : 'الأقسام' ) . '</h2><ul>';
	echo '<li><a href="' . esc_url( $shop ) . '">' . esc_html( $en ? 'All perfumes' : 'كل العطور' ) . '</a></li>';
	echo '<li><a href="' . esc_url( add_query_arg( 'kymeet_chip', 'men', $shop ) ) . '">' . esc_html( $en ? 'Men' : 'رجالي' ) . '</a></li>';
	echo '<li><a href="' . esc_url( add_query_arg( 'kymeet_chip', 'women', $shop ) ) . '">' . esc_html( $en ? 'Women' : 'نسائي' ) . '</a></li>';
	echo '</ul></div>';
	echo '<div><h2>' . esc_html( $en ? 'Policies' : 'السياسات' ) . '</h2><ul>';
	echo '<li><a href="#">' . esc_html( $en ? '[Placeholder] Privacy' : '[رابط تجريبي] الخصوصية' ) . '</a></li>';
	echo '<li><a href="#">' . esc_html( $en ? '[Placeholder] Shipping & returns' : '[رابط تجريبي] الشحن والإرجاع' ) . '</a></li>';
	echo '</ul></div>';
	echo '<form class="kapp-news" action="#" method="post" onsubmit="return false;">';
	echo '<h2>' . esc_html( $en ? 'Newsletter' : 'النشرة البريدية' ) . '</h2>';
	echo '<p>' . esc_html( $en ? '[Placeholder] Newsletter is not connected yet.' : '[تجريبي] النشرة غير مربوطة بعد.' ) . '</p>';
	echo '<label><span class="screen-reader-text">Email</span><input type="email" name="kapp_news" placeholder="name@example.com" autocomplete="email"></label>';
	echo '<button type="submit" class="kapp-news__btn">' . esc_html( $en ? 'Subscribe' : 'اشتراك' ) . '</button>';
	echo '</form>';
	echo '</div></footer>';
}

function kymeet_store_appnav() {
	$en      = kymeet_app_en();
	$home    = home_url( '/' );
	$shop    = kymeet_app_shop_url();
	$acc     = kymeet_app_account_url();
	$fp      = is_front_page();
	$is_shop = ( function_exists( 'is_shop' ) && is_shop() ) || ( function_exists( 'is_product_taxonomy' ) && is_product_taxonomy() );
	$is_acc  = function_exists( 'is_account_page' ) && is_account_page();
	$items   = array(
		array( 'home', $home, 'fas fa-house', $en ? 'Home' : 'الرئيسية', $fp ),
		array( 'cats', $shop, 'fas fa-border-all', $en ? 'Categories' : 'التصنيفات', $is_shop ),
		array( 'search', '#kapp-search', 'fas fa-magnifying-glass', $en ? 'Search' : 'البحث', false ),
		array( 'wish', '#kymeet-wish', 'fas fa-heart', $en ? 'Favorites' : 'المفضلة', false ),
		array( 'account', $acc, 'fas fa-user', $en ? 'Account' : 'حسابي', $is_acc ),
	);
	$out = '<nav class="appnav kapp-tabbar" aria-label="' . esc_attr( $en ? 'Main' : 'التنقل الرئيسي' ) . '">';
	foreach ( $items as $i ) {
		$attrs = 'home' === $i[0] || 'cats' === $i[0] || 'account' === $i[0]
			? ' href="' . esc_url( $i[1] ) . '"'
			: ' href="' . esc_attr( $i[1] ) . '" data-kapp-open="' . esc_attr( $i[0] ) . '"';
		$out .= '<a class="an-i' . ( $i[4] ? ' on' : '' ) . '"' . $attrs . '><i class="' . esc_attr( $i[2] ) . '" aria-hidden="true"></i><span>' . esc_html( $i[3] ) . '</span></a>';
	}
	$out .= '</nav>';
	return $out;
}
