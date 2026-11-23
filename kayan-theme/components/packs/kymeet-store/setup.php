<?php
/**
 * KYMEET Store — متجر العطور (تصميم KYMEET3)
 * - قوالب الأقسام: templates/*.html (كل نص/صورة/أيقونة قابل للتعديل عبر manifest.json)
 * - المنتجات: نوع المحتوى perfumes (products.php)
 * - الهيدر والفوتر: صفحة KYMEET في لوحة التحكم (admin.php)
 * التعطيل: update_option( 'kymeet_store_mode', 'off' );
 */
defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'kymeet_store_active' ) ) {

	function kymeet_store_active() {
		return (bool) apply_filters( 'kymeet_store_active', get_option( 'kymeet_store_mode', 'on' ) !== 'off' );
	}
	function kymeet_store_dir() {
		return trailingslashit( dirname( __FILE__ ) );
	}
	function kymeet_store_url() {
		return get_template_directory_uri() . '/components/packs/kymeet-store/';
	}
	function kymeet_store_sections() {
		/* كل أقسام KYMEET متاحة في إعدادات الرئيسية — التحكم من لوحة التحكم فقط */
		return array( 'kymeet_hero', 'kymeet_discover', 'kymeet_bestsellers', 'kymeet_brands', 'kymeet_curated', 'kymeet_new', 'kymeet_catalog', 'kymeet_story', 'kymeet_faq', 'kymeet_cta' );
	}

	# مصدر المنتجات: WooCommerce إن كان مفعّلًا، وإلا نوع المحتوى perfumes القديم
	function kymeet_wc_active() {
		return class_exists( 'WooCommerce' ) && function_exists( 'WC' );
	}
	function kymeet_store_pt() {
		return kymeet_wc_active() ? 'product' : 'perfumes';
	}
	function kymeet_store_brand_tax() {
		return ( kymeet_wc_active() && taxonomy_exists( 'product_brand' ) ) ? 'product_brand' : 'perfume_brand';
	}
	function kymeet_store_is_en() {
		return function_exists( 'kayan_i18n_is_english' ) && kayan_i18n_is_english();
	}

	# ═══ manifest: كل عنصر قابل للتعديل (نص/صورة/أيقونة/رابط) ═══
	function kymeet_store_manifest() {
		static $m = null;
		if ( null === $m ) {
			$f = kymeet_store_dir() . 'manifest.json';
			$m = is_readable( $f ) ? json_decode( file_get_contents( $f ), true ) : array();
			if ( ! is_array( $m ) ) {
				$m = array();
			}
		}
		return $m;
	}

	# حقول لوحة التحكم لقسم معيّن (عربي + إنجليزي لكل نص)
	function kymeet_store_fields( $sec ) {
		$m      = kymeet_store_manifest();
		$fields = array();
		if ( empty( $m[ $sec ] ) ) {
			return $fields;
		}
		foreach ( $m[ $sec ] as $t ) {
			$title = $t['label'];
			if ( 'img' === $t['kind'] ) {
				$fields[] = array( 'type' => 'File', 'id' => $t['k'], 'title' => $title . ' (فارغ = الافتراضي)' );
			} elseif ( 'icon' === $t['kind'] ) {
				$fields[] = array( 'type' => 'Text', 'id' => $t['k'], 'title' => $title . ' — كلاس Font Awesome مثل: fas fa-gem (فارغ = الافتراضي)' );
			} elseif ( 'link' === $t['kind'] ) {
				$fields[] = array( 'type' => 'Text', 'id' => $t['k'], 'title' => $title . ' (فارغ = الافتراضي)' );
			} else {
				$type     = ( function_exists( 'mb_strlen' ) ? mb_strlen( $t['ar'] ) : strlen( $t['ar'] ) ) > 90 ? 'TextArea' : 'Text';
				$fields[] = array( 'type' => $type, 'id' => $t['k'], 'title' => $title . ' — عربي (فارغ = الافتراضي)' );
				$fields[] = array( 'type' => $type, 'id' => $t['k'] . '_en', 'title' => $title . ' — English' );
			}
		}
		$fields[] = array( 'type' => 'SwitchBox', 'id' => 'hide_section__switch', 'title' => 'إخفاء هذا القسم مؤقتًا' );
		$fields[] = array( 'type' => 'SwitchBox', 'id' => 'mobile_hide_section__switch', 'title' => 'إخفاء هذا القسم في الموبايل' );
		return $fields;
	}

	# قيمة عنصر: المحفوظ من لوحة التحكم، وإلا الافتراضي (مع اللغة)
	function kymeet_store_val( $t, $vars ) {
		$k  = $t['k'];
		$ar = ( isset( $vars[ $k ] ) && is_scalar( $vars[ $k ] ) && '' !== $vars[ $k ] ) ? (string) $vars[ $k ] : '';
		if ( in_array( $t['kind'], array( 'img', 'icon', 'link' ), true ) ) {
			return '' !== $ar ? $ar : $t['ar'];
		}
		if ( ! kymeet_store_is_en() ) {
			return '' !== $ar ? $ar : $t['ar'];
		}
		$en = ( isset( $vars[ $k . '_en' ] ) && is_scalar( $vars[ $k . '_en' ] ) ) ? (string) $vars[ $k . '_en' ] : '';
		if ( '' !== $en ) {
			return $en;
		}
		if ( '' !== $ar ) {
			return $ar;
		}
		return '' !== $t['en'] ? $t['en'] : $t['ar'];
	}

	function kymeet_store_img_url( $v ) {
		if ( is_numeric( $v ) ) {
			$u = wp_get_attachment_url( (int) $v );
			return $u ? esc_url( $u ) : '';
		}
		if ( 0 === strpos( $v, 'data:image/' ) ) {
			return esc_attr( $v );
		}
		return esc_url( $v );
	}

	function kymeet_store_lang_vars() {
		$en  = kymeet_store_is_en();
		$url = function_exists( 'kayan_i18n_build_url' ) ? kayan_i18n_build_url( '', $en ? 'ar' : 'en' ) : home_url( '/' );
		return array(
			'lang_url'        => esc_url( $url ),
			'lang_other'      => $en ? 'ar' : 'en',
			'lang_label'      => $en ? 'AR' : 'EN',
			'lang_label_full' => $en ? 'العربية' : 'English',
			'search_ph'       => $en ? 'Search perfumes or brands…' : 'ابحث عن عطر أو علامة…',
		);
	}

	# سلاسل العلامات التجارية (من تصنيف العلامات) للشريط المتحرك وقسم العلامات
	function kymeet_store_brand_names() {
		$names = array();
		$terms = get_terms( array( 'taxonomy' => kymeet_store_brand_tax(), 'hide_empty' => false, 'orderby' => 'term_id' ) );
		if ( ! is_wp_error( $terms ) && $terms ) {
			foreach ( $terms as $t ) {
				$names[] = $t->name;
			}
		}
		if ( ! $names && function_exists( 'kymeet_seed_data' ) ) {
			$d     = kymeet_seed_data();
			$names = isset( $d['brands'] ) ? $d['brands'] : array();
		}
		return $names;
	}
	function kymeet_store_brand_html( $type ) {
		$out = '';
		for ( $r = 0; $r < 2; $r++ ) {
			foreach ( kymeet_store_brand_names() as $b ) {
				if ( 'trust' === $type ) {
					$out .= '<span class="ti en"><i class="fas fa-gem"></i> ' . esc_html( $b ) . '</span>';
				} else {
					$out .= '<span class="brand-chip-x en" onclick="pickBrand(this.textContent.trim())"><i class="fas fa-gem"></i> ' . esc_html( $b ) . '</span>';
				}
			}
		}
		return $out;
	}

	# ═══ طباعة قالب ═══
	function kymeet_store_tpl( $name, $vars = array() ) {
		$name = preg_replace( '/[^a-z0-9_-]/i', '', $name );
		$file = kymeet_store_dir() . 'templates/' . $name . '.html';
		if ( ! is_readable( $file ) ) {
			return;
		}
		$html = file_get_contents( $file );
		if ( in_array( $name, array( 'header', 'footer' ), true ) ) {
			$opt  = get_option( 'kymeet_hf', array() );
			$vars = is_array( $opt ) ? $opt : array();
			if ( ! is_front_page() ) {
				$html = str_replace( 'href="#', 'href="' . esc_url( home_url( '/' ) ) . '#', $html );
			}
		}
		$m   = kymeet_store_manifest();
		$map = array();
		if ( isset( $m[ $name ] ) ) {
			foreach ( $m[ $name ] as $t ) {
				$map[ $t['k'] ] = $t;
			}
		}
		$lang = kymeet_store_lang_vars();
		$html = preg_replace_callback(
			'/\{\{([a-z0-9_]+)\}\}/',
			function ( $mm ) use ( $map, $vars, $lang ) {
				$k = $mm[1];
				if ( isset( $lang[ $k ] ) ) {
					return $lang[ $k ];
				}
				if ( 'brands_trust' === $k ) {
					return kymeet_store_brand_html( 'trust' );
				}
				if ( 'brands_chips' === $k ) {
					return kymeet_store_brand_html( 'chips' );
				}
				if ( 'account_btn' === $k && function_exists( 'kymeet_wc_account_btn' ) ) {
					return kymeet_wc_account_btn();
				}
				if ( 'account_link' === $k && function_exists( 'kymeet_wc_account_link' ) ) {
					return kymeet_wc_account_link();
				}
				if ( 'hero_banners' === $k && function_exists( 'kymeet_store_hero_banners' ) ) {
					return kymeet_store_hero_banners();
				}
				if ( 'brand_cards' === $k && function_exists( 'kymeet_store_brand_cards' ) ) {
					return kymeet_store_brand_cards();
				}
				if ( 'hero_slider' === $k && function_exists( 'kymeet_store_hero_slider' ) ) {
					return kymeet_store_hero_slider();
				}
				if ( ! isset( $map[ $k ] ) ) {
					return '';
				}
				$t = $map[ $k ];
				$v = kymeet_store_val( $t, $vars );
				if ( 'img' === $t['kind'] ) {
					return kymeet_store_img_url( $v );
				}
				if ( 'link' === $t['kind'] ) {
					return esc_url( $v ? $v : '#' );
				}
				if ( 'text' === $t['kind'] ) {
					return esc_html( $v );
				}
				return esc_attr( $v );
			},
			$html
		);
		echo $html; // phpcs:ignore -- القالب موثوق، والقيم مُهرَّبة أعلاه
	}

	function kymeet_store_render_defaults() {
		foreach ( kymeet_store_sections() as $id ) {
			kymeet_store_tpl( substr( $id, 7 ) );
		}
	}

	# إعدادات التواصل + بيانات المنتجات للسكربت
	function kymeet_store_cfg_script() {
		$wa  = preg_replace( '/\D+/', '', (string) get_option( 'whatsapp_number' ) );
		$tel = preg_replace( '/\D+/', '', (string) get_option( 'phonenumber' ) );
		$shop = function_exists( 'kymeet_app_shop_url' ) ? kymeet_app_shop_url() : home_url( '/shop/' );
		echo '<script>window.KYMEET_CFG=' . wp_json_encode( array(
			'WA'   => $wa,
			'TEL'  => $tel,
			'HOME' => home_url( '/' ),
			'SHOP' => $shop,
			'WC'   => kymeet_wc_active(),
			'AJAX' => kymeet_wc_active() ? add_query_arg( 'wc-ajax', 'kymeet_cart', home_url( '/' ) ) : '',
		) ) . ';</script>';
		if ( function_exists( 'kymeet_store_data_script' ) ) {
			kymeet_store_data_script();
		}
	}
}

# ═══ ترحيل مرة واحدة: ربط الرئيسية بودجات KYMEET القابلة للتعديل ═══
# - تنظيف قوائم الودجات من ودجات الخدمات المحذوفة (كانت تعطّل فتح إعدادات الأقسام)
# - إن لم تكن ودجات kymeet محفوظة في الرئيسية: تُنشأ تلقائيًا (والقديم يُحفظ نسخة احتياطية)
if ( ! function_exists( 'kymeet_store_clean_widget_list' ) ) {
	function kymeet_store_clean_widget_list( $list ) {
		$clean = array();
		if ( ! is_array( $list ) ) {
			return $clean;
		}
		foreach ( $list as $w ) {
			if ( is_array( $w ) && ! empty( $w['widget_id'] ) && class_exists( $w['widget_id'] ) ) {
				$clean[] = $w;
			}
		}
		return $clean;
	}
	function kymeet_store_migrate_home() {
		if ( get_option( 'kymeet_home_migrated_v1439' ) || ! function_exists( 'kayan_seed_home_defaults' ) ) {
			return;
		}
		foreach ( array( 'widgets_home__meta', 'widgets_single__meta', 'widgets_pages__meta', 'widgets_contactus__meta' ) as $opt ) {
			$cur = get_option( $opt );
			if ( is_array( $cur ) && $cur ) {
				update_option( $opt, kymeet_store_clean_widget_list( $cur ) );
			}
		}
		$home = get_option( 'widgets_home__meta' );
		$has  = false;
		if ( is_array( $home ) ) {
			foreach ( $home as $w ) {
				if ( isset( $w['widget_id'] ) && 0 === strpos( $w['widget_id'], 'kymeet_' ) ) {
					$has = true;
					break;
				}
			}
		}
		if ( ! $has ) {
			if ( is_array( $home ) && $home ) {
				update_option( 'kymeet_home_backup', $home, false );
			}
			if ( ! kayan_seed_home_defaults( true ) ) {
				return;
			}
		}
		update_option( 'kymeet_home_migrated_v1439', '1' );
	}
	add_action( 'init', 'kymeet_store_migrate_home', 100 );
}

require_once dirname( __FILE__ ) . '/includes/fixes.php';
require_once dirname( __FILE__ ) . '/includes/content-settings.php';
require_once dirname( __FILE__ ) . '/includes/shell-v3.php';
require_once dirname( __FILE__ ) . '/includes/home-app.php';
require_once dirname( __FILE__ ) . '/admin.php';

# شريط التنقل: kymeet_store_appnav() في includes/shell-v3.php

# شعار الموقع (من إعدادات الهيدر أو الافتراضي)
function kymeet_store_logo_url() {
	$m   = kymeet_store_manifest();
	$opt = get_option( 'kymeet_hf', array() );
	$opt = is_array( $opt ) ? $opt : array();
	if ( ! empty( $m['header'] ) ) {
		foreach ( $m['header'] as $t ) {
			if ( 'img' === $t['kind'] && 'header_img4' === $t['k'] ) {
				$url = kymeet_store_img_url( kymeet_store_val( $t, $opt ) );
				if ( $url && 0 !== strpos( $url, 'data:image/' ) ) {
					return $url;
				}
			}
		}
	}
	$logo__data = get_option( 'logo__data' );
	if ( is_array( $logo__data ) && isset( $logo__data['logo__mode'] ) && 'Image' === $logo__data['logo__mode'] && ! empty( $logo__data['Image'] ) ) {
		$box = $logo__data['Image'];
		if ( ! empty( $box['image_logo_id'] ) ) {
			$u = wp_get_attachment_url( (int) $box['image_logo_id'] );
			if ( $u ) {
				return esc_url( $u );
			}
		}
		if ( ! empty( $box['image_logo']['url'] ) && is_string( $box['image_logo']['url'] ) ) {
			return esc_url( $box['image_logo']['url'] );
		}
		if ( ! empty( $box['image_logo'] ) && is_string( $box['image_logo'] ) && preg_match( '#^https?://#', $box['image_logo'] ) ) {
			return esc_url( $box['image_logo'] );
		}
	}
	$theme_logo = get_template_directory_uri() . '/assets/branding/kymeet-logo.png';
	if ( file_exists( get_template_directory() . '/assets/branding/kymeet-logo.png' ) ) {
		return esc_url( $theme_logo );
	}
	return '';
}

# ═══ PWA: تجربة تطبيق على الموبايل ═══
add_action( 'wp_head', 'kymeet_store_pwa_head', 1 );
function kymeet_store_pwa_head() {
	if ( ! kymeet_store_active() ) {
		return;
	}
	echo '<meta name="theme-color" content="#FFFFFF"><meta name="mobile-web-app-capable" content="yes"><meta name="apple-mobile-web-app-capable" content="yes"><meta name="apple-mobile-web-app-status-bar-style" content="default">';
	echo '<link rel="manifest" href="' . esc_url( add_query_arg( 'kymeet-manifest', '1', home_url( '/' ) ) ) . '">';
}
add_action( 'template_redirect', 'kymeet_store_manifest_json' );
function kymeet_store_manifest_json() {
	// phpcs:ignore WordPress.Security.NonceVerification
	if ( ! isset( $_GET['kymeet-manifest'] ) ) {
		return;
	}
	$icons = array();
	foreach ( array( 192, 512 ) as $s ) {
		$u = get_site_icon_url( $s );
		if ( $u ) {
			$icons[] = array( 'src' => $u, 'sizes' => $s . 'x' . $s, 'type' => 'image/png', 'purpose' => 'any' );
		}
	}
	nocache_headers();
	header( 'Content-Type: application/manifest+json; charset=utf-8' );
	echo wp_json_encode(
		array(
			'name'             => get_bloginfo( 'name' ),
			'short_name'       => 'KYMEET',
			'start_url'        => home_url( '/' ),
			'scope'            => home_url( '/' ),
			'display'          => 'standalone',
			'orientation'      => 'portrait',
			'background_color' => '#F4F7FD',
			'theme_color'      => '#0A1F5C',
			'lang'             => kymeet_store_is_en() ? 'en' : 'ar',
			'dir'              => kymeet_store_is_en() ? 'ltr' : 'rtl',
			'icons'            => $icons,
		),
		JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
	);
	exit;
}

require_once dirname( __FILE__ ) . '/products.php';
require_once dirname( __FILE__ ) . '/woo.php';
