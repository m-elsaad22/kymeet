<?php
/**
 * KYMEET — تكامل WooCommerce الكامل
 * المتجر (المنتجات/المخزون/المتغيرات/الكوبونات/الشحن/الضرائب/الدفع/الطلبات/الحساب) كله من WooCommerce،
 * والقالب يتولّى التصميم: الرئيسية، السلة المنسدلة، المتجر، صفحة المنتج، السلة، الدفع، حسابي.
 */
defined( 'ABSPATH' ) || exit;

# ═══ دعم القالب ═══
add_action( 'after_setup_theme', 'kymeet_wc_theme_support' );
function kymeet_wc_theme_support() {
	add_theme_support(
		'woocommerce',
		array(
			'thumbnail_image_width' => 600,
			'single_image_width'    => 900,
		)
	);
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );
}
# ستايل WooCommerce الافتراضي معطّل: التصميم كله في kymeet.css
add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );
add_filter( 'woocommerce_show_page_title', '__return_false' );
add_action( 'init', 'kymeet_wc_hooks', 20 );
function kymeet_wc_hooks() {
	# العنوان يُطبع في رأس الصفحة (phero) فلا نكرره
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );
}

# صورة المنتج الخارجية كبديل لمّا لا توجد صورة في مكتبة الوسائط
add_filter( 'woocommerce_placeholder_img_src', 'kymeet_wc_placeholder' );
function kymeet_wc_placeholder( $src ) {
	global $product;
	if ( $product instanceof WC_Product ) {
		$u = get_post_meta( $product->get_id(), '_kymeet_image_url', true );
		if ( $u ) {
			return esc_url( $u );
		}
	}
	return $src;
}

# ═══ تنبيه لو WooCommerce غير مفعّلة ═══
add_action( 'admin_notices', 'kymeet_wc_notice' );
function kymeet_wc_notice() {
	if ( kymeet_wc_active() || ! current_user_can( 'install_plugins' ) ) {
		return;
	}
	echo '<div class="notice notice-warning"><p><strong>قالب KYMEET:</strong> فعّل إضافة WooCommerce ليعمل المتجر كاملًا (السلة والدفع والطلبات والمخزون). حتى ذلك الحين تعمل الرئيسية بوضع العرض فقط. <a href="' . esc_url( admin_url( 'plugins.php' ) ) . '">الإضافات</a></p></div>';
}

# ═══ أزرار الهيدر ═══
function kymeet_wc_account_btn() {
	if ( ! kymeet_wc_active() ) {
		return '';
	}
	$url = wc_get_page_permalink( 'myaccount' );
	return '<a class="btn btn-icon hide-m" href="' . esc_url( $url ) . '" aria-label="' . esc_attr( kymeet_store_is_en() ? 'My account' : 'حسابي' ) . '"><i class="far fa-user"></i></a>';
}
function kymeet_wc_account_link() {
	if ( ! kymeet_wc_active() ) {
		return '';
	}
	return '<a class="mlink" href="' . esc_url( wc_get_page_permalink( 'myaccount' ) ) . '">' . esc_html( kymeet_store_is_en() ? 'My account' : 'حسابي' ) . '</a>';
}

# ═══ السلة (AJAX) — تستخدم سلة WooCommerce الحقيقية ═══
add_action( 'wc_ajax_kymeet_cart', 'kymeet_wc_ajax' );

function kymeet_wc_plain( $html ) {
	return html_entity_decode( wp_strip_all_tags( $html ), ENT_QUOTES, 'UTF-8' );
}

function kymeet_wc_cart_state( $msg = '', $redirect = '' ) {
	$cart  = WC()->cart;
	$items = array();
	$cart->calculate_totals();
	foreach ( $cart->get_cart() as $key => $ci ) {
		$p = $ci['data'];
		if ( ! $p instanceof WC_Product ) {
			continue;
		}
		$parent = $p->get_parent_id() ? $p->get_parent_id() : $p->get_id();
		$img    = wp_get_attachment_image_url( $p->get_image_id(), 'thumbnail' );
		if ( ! $img ) {
			$img = get_post_meta( $parent, '_kymeet_image_url', true );
		}
		$brand = '';
		$t     = get_the_terms( $parent, 'product_brand' );
		if ( is_array( $t ) && $t ) {
			$brand = $t[0]->name;
		}
		$items[] = array(
			'key'   => $key,
			'pid'   => (int) $ci['product_id'],
			'name'  => $p->get_name(),
			'brand' => $brand,
			'qty'   => (int) $ci['quantity'],
			'line'  => kymeet_wc_plain( $cart->get_product_subtotal( $p, $ci['quantity'] ) ),
			'img'   => $img ? $img : '',
			'url'   => $p->get_permalink(),
		);
	}
	return array(
		'count'    => (int) $cart->get_cart_contents_count(),
		'subtotal' => kymeet_wc_plain( $cart->get_cart_subtotal() ),
		'items'    => $items,
		'checkout' => wc_get_checkout_url(),
		'cart'     => wc_get_cart_url(),
		'msg'      => $msg,
		'redirect' => $redirect,
	);
}

function kymeet_wc_ajax() {
	if ( ! kymeet_wc_active() || ! WC()->cart ) {
		wp_send_json( array( 'error' => 1 ) );
	}
	// phpcs:disable WordPress.Security.NonceVerification
	$op       = isset( $_REQUEST['op'] ) ? sanitize_key( wp_unslash( $_REQUEST['op'] ) ) : 'get';
	$msg      = '';
	$redirect = '';
	if ( 'add' === $op ) {
		$pid = isset( $_POST['pid'] ) ? absint( $_POST['pid'] ) : 0;
		$qty = isset( $_POST['qty'] ) ? max( 1, absint( $_POST['qty'] ) ) : 1;
		$pr  = $pid ? wc_get_product( $pid ) : false;
		if ( ! $pr || 'publish' !== get_post_status( $pid ) ) {
			$msg = 'unavailable';
		} elseif ( ! $pr->is_type( 'simple' ) || ! $pr->is_purchasable() || ! $pr->is_in_stock() ) {
			$redirect = get_permalink( $pid );
		} elseif ( ! WC()->cart->add_to_cart( $pid, $qty ) ) {
			$errs = wc_get_notices( 'error' );
			$msg  = ( $errs && isset( $errs[0]['notice'] ) ) ? kymeet_wc_plain( $errs[0]['notice'] ) : 'error';
		}
		wc_clear_notices();
	} elseif ( 'set' === $op ) {
		$key = isset( $_POST['key'] ) ? wc_clean( wp_unslash( $_POST['key'] ) ) : '';
		$qty = isset( $_POST['qty'] ) ? absint( $_POST['qty'] ) : 0;
		if ( $key && WC()->cart->get_cart_item( $key ) ) {
			if ( $qty > 0 ) {
				WC()->cart->set_quantity( $key, $qty, true );
			} else {
				WC()->cart->remove_cart_item( $key );
			}
		}
		wc_clear_notices();
	} elseif ( 'remove' === $op ) {
		$key = isset( $_POST['key'] ) ? wc_clean( wp_unslash( $_POST['key'] ) ) : '';
		if ( $key ) {
			WC()->cart->remove_cart_item( $key );
		}
		wc_clear_notices();
	}
	// phpcs:enable
	wp_send_json( kymeet_wc_cart_state( $msg, $redirect ) );
}

# ═══ الصفحات: السلة/الدفع بالشورتكود الكلاسيكي ليتحكم التصميم بها كاملة ═══
function kymeet_wc_use_shortcode_pages() {
	$map = array(
		'cart'     => array( 'wp:woocommerce/cart', '[woocommerce_cart]' ),
		'checkout' => array( 'wp:woocommerce/checkout', '[woocommerce_checkout]' ),
	);
	foreach ( $map as $page => $m ) {
		$id = wc_get_page_id( $page );
		if ( $id > 0 ) {
			$post = get_post( $id );
			if ( $post && false !== strpos( $post->post_content, $m[0] ) ) {
				wp_update_post( array( 'ID' => $id, 'post_content' => $m[1] ) );
			}
		}
	}
}

# ═══ ترحيل: العطور (perfumes) ← منتجات WooCommerce ═══
function kymeet_wc_collect_source() {
	$src    = array();
	$legacy = post_type_exists( 'perfumes' ) ? get_posts(
		array(
			'post_type'   => 'perfumes',
			'post_status' => 'any',
			'numberposts' => -1,
			'orderby'     => array( 'menu_order' => 'ASC', 'ID' => 'ASC' ),
		)
	) : array();
	foreach ( $legacy as $p ) {
		$brand = '';
		$t     = get_the_terms( $p->ID, 'perfume_brand' );
		if ( is_array( $t ) && $t ) {
			$brand = $t[0]->name;
		}
		$img = get_the_post_thumbnail_url( $p->ID, 'full' );
		if ( ! $img ) {
			$img = get_post_meta( $p->ID, '_kymeet_image_url', true );
		}
		$src[] = array(
			'slug'    => $p->post_name,
			'name'    => $p->post_title,
			'brand'   => $brand,
			'price'   => (float) get_post_meta( $p->ID, '_kymeet_price', true ),
			'old'     => (float) get_post_meta( $p->ID, '_kymeet_old_price', true ),
			'image'   => (string) $img,
			'thumb'   => (int) get_post_thumbnail_id( $p->ID ),
			'tag'     => (string) get_post_meta( $p->ID, '_kymeet_tag', true ),
			'name_en' => (string) get_post_meta( $p->ID, '_kymeet_name_en', true ),
			'best'    => (bool) get_post_meta( $p->ID, '_kymeet_best', true ),
			'new'     => (bool) get_post_meta( $p->ID, '_kymeet_new', true ),
			'content' => $p->post_content,
			'excerpt' => $p->post_excerpt,
		);
	}
	if ( ! $src ) {
		$d = kymeet_seed_data();
		foreach ( $d['products'] as $p ) {
			$src[] = array(
				'slug'    => $p['id'],
				'name'    => $p['name'],
				'brand'   => isset( $p['brand'] ) ? $p['brand'] : '',
				'price'   => (float) $p['price'],
				'old'     => 0,
				'image'   => isset( $p['image'] ) ? $p['image'] : '',
				'thumb'   => 0,
				'tag'     => isset( $p['tag'] ) ? $p['tag'] : '',
				'name_en' => '',
				'best'    => in_array( $p['id'], $d['best'], true ),
				'new'     => in_array( $p['id'], $d['new'], true ),
				'content' => '',
				'excerpt' => '',
			);
		}
	}
	return $src;
}

function kymeet_wc_migrate() {
	if ( ! kymeet_wc_active() || ! post_type_exists( 'product' ) || ! class_exists( 'WC_Product_Simple' ) ) {
		return 0;
	}
	$d = kymeet_seed_data();
	if ( taxonomy_exists( 'product_brand' ) ) {
		foreach ( $d['brands'] as $b ) {
			if ( ! term_exists( $b, 'product_brand' ) ) {
				wp_insert_term( $b, 'product_brand' );
			}
		}
	}
	$n = 0;
	foreach ( kymeet_wc_collect_source() as $i => $r ) {
		if ( get_page_by_path( $r['slug'], OBJECT, 'product' ) ) {
			continue;
		}
		$pr = new WC_Product_Simple();
		$pr->set_name( $r['name'] );
		$pr->set_slug( $r['slug'] );
		$pr->set_status( 'publish' );
		$pr->set_catalog_visibility( 'visible' );
		if ( $r['old'] > $r['price'] && $r['price'] > 0 ) {
			$pr->set_regular_price( (string) $r['old'] );
			$pr->set_sale_price( (string) $r['price'] );
		} else {
			$pr->set_regular_price( (string) $r['price'] );
		}
		$pr->set_description( $r['content'] );
		$pr->set_short_description( $r['excerpt'] );
		$pr->set_menu_order( $i );
		$pr->set_manage_stock( false );
		$pr->set_stock_status( 'instock' );
		$pr->set_featured( ! empty( $r['best'] ) );
		if ( $r['thumb'] ) {
			$pr->set_image_id( $r['thumb'] );
		}
		$id = $pr->save();
		if ( ! $id ) {
			continue;
		}
		if ( $r['image'] ) {
			update_post_meta( $id, '_kymeet_image_url', esc_url_raw( $r['image'] ) );
		}
		if ( $r['tag'] ) {
			update_post_meta( $id, '_kymeet_tag', $r['tag'] );
		}
		if ( $r['name_en'] ) {
			update_post_meta( $id, '_kymeet_name_en', $r['name_en'] );
		}
		if ( $r['new'] ) {
			update_post_meta( $id, '_kymeet_new', '1' );
		}
		if ( $r['brand'] && taxonomy_exists( 'product_brand' ) ) {
			wp_set_object_terms( $id, $r['brand'], 'product_brand' );
		}
		$n++;
	}
	kymeet_wc_use_shortcode_pages();
	kymeet_wc_create_attributes();
	return $n;
}

add_action( 'admin_init', 'kymeet_wc_migrate_once' );
function kymeet_wc_migrate_once() {
	if ( ! current_user_can( 'manage_options' ) || get_option( 'kymeet_wc_migrated_v150' ) || ! kymeet_wc_active() ) {
		return;
	}
	if ( post_type_exists( 'product' ) ) {
		kymeet_wc_migrate();
		update_option( 'kymeet_wc_migrated_v150', '1' );
	}
}

# تحميل صور المنتجات الخارجية إلى مكتبة الوسائط (دفعات صغيرة)
function kymeet_wc_sideload_images( $limit = 4 ) {
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
	$ids = get_posts(
		array(
			'post_type'   => 'product',
			'post_status' => 'any',
			'numberposts' => (int) $limit,
			'fields'      => 'ids',
			'meta_query'  => array( // phpcs:ignore
				'relation' => 'AND',
				array( 'key' => '_kymeet_image_url', 'value' => '', 'compare' => '!=' ),
				array( 'key' => '_thumbnail_id', 'compare' => 'NOT EXISTS' ),
				array( 'key' => '_kymeet_sideload_failed', 'compare' => 'NOT EXISTS' ),
			),
		)
	);
	$ok = 0;
	foreach ( $ids as $id ) {
		$att = media_sideload_image( get_post_meta( $id, '_kymeet_image_url', true ), $id, get_the_title( $id ), 'id' );
		if ( is_wp_error( $att ) ) {
			update_post_meta( $id, '_kymeet_sideload_failed', '1' );
		} else {
			set_post_thumbnail( $id, $att );
			$ok++;
		}
	}
	return array( count( $ids ), $ok );
}

function kymeet_wc_pending_images() {
	$q = get_posts(
		array(
			'post_type'   => 'product',
			'post_status' => 'any',
			'numberposts' => -1,
			'fields'      => 'ids',
			'meta_query'  => array( // phpcs:ignore
				'relation' => 'AND',
				array( 'key' => '_kymeet_image_url', 'value' => '', 'compare' => '!=' ),
				array( 'key' => '_thumbnail_id', 'compare' => 'NOT EXISTS' ),
				array( 'key' => '_kymeet_sideload_failed', 'compare' => 'NOT EXISTS' ),
			),
		)
	);
	return count( $q );
}

# ═══ صفحة أدوات KYMEET ← WooCommerce ═══
add_action( 'admin_menu', 'kymeet_wc_tools_menu', 20 );
function kymeet_wc_tools_menu() {
	add_submenu_page( 'kymeet-hf', 'KYMEET — WooCommerce', 'أدوات WooCommerce', 'manage_options', 'kymeet-wc', 'kymeet_wc_tools_page' );
}
function kymeet_wc_tools_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	echo '<div class="wrap" dir="rtl"><h1>KYMEET — أدوات WooCommerce</h1>';
	if ( ! kymeet_wc_active() ) {
		echo '<div class="notice notice-warning"><p>WooCommerce غير مفعّلة.</p></div></div>';
		return;
	}
	if ( isset( $_POST['kymeet_wc_action'] ) && check_admin_referer( 'kymeet_wc_tools' ) ) {
		$act = sanitize_key( wp_unslash( $_POST['kymeet_wc_action'] ) );
		if ( 'migrate' === $act ) {
			echo '<div class="notice notice-success"><p>أُنشئ ' . (int) kymeet_wc_migrate() . ' منتجًا جديدًا (الموجود لا يتكرر).</p></div>';
		} elseif ( 'images' === $act ) {
			$r = kymeet_wc_sideload_images( 4 );
			echo '<div class="notice notice-success"><p>عولجت ' . (int) $r[0] . ' صورة، نجح تحميل ' . (int) $r[1] . '.</p></div>';
		} elseif ( 'attrs' === $act ) {
			echo '<div class="notice notice-success"><p>أُنشئت ' . (int) kymeet_wc_create_attributes() . ' خاصية (الجنس، التركيز، الموسم، الحجم). أضف قيمها من المنتجات ← الخصائص.</p></div>';
		} elseif ( 'pages' === $act ) {
			kymeet_wc_use_shortcode_pages();
			echo '<div class="notice notice-success"><p>صفحتا السلة والدفع تعملان بالشورتكود الكلاسيكي.</p></div>';
		}
	}
	echo '<p>المنتجات والمخزون والمتغيرات والكوبونات والشحن والضرائب وبوابات الدفع والطلبات كلها من قائمة WooCommerce. القالب يتكفل بالتصميم.</p>';
	echo '<form method="post">';
	wp_nonce_field( 'kymeet_wc_tools' );
	echo '<p><button class="button button-primary" name="kymeet_wc_action" value="migrate">استيراد عطور KYMEET كمنتجات WooCommerce</button> <span class="description">يعمل تلقائيًا مرة واحدة؛ الزر لإعادة التشغيل.</span></p>';
	echo '<p><button class="button" name="kymeet_wc_action" value="images">تحميل الصور الخارجية للمكتبة (4 في كل ضغطة)</button> <span class="description">متبقٍ: ' . (int) kymeet_wc_pending_images() . '</span></p>';
	echo '<p><button class="button" name="kymeet_wc_action" value="attrs">إنشاء خصائص العطور (الجنس / التركيز / الموسم / الحجم)</button></p>';
	echo '<p><button class="button" name="kymeet_wc_action" value="pages">تحويل صفحتي السلة والدفع للشورتكود الكلاسيكي</button></p>';
	echo '</form></div>';
}

# أرشيف المتجر / التصنيف / العلامة / الوسم بتصميم KYMEET (المحتوى من WooCommerce)
function kymeet_wc_render_archive() {
	$en    = kymeet_store_is_en();
	$title = function_exists( 'woocommerce_page_title' ) ? woocommerce_page_title( false ) : '';
	// phpcs:ignore WordPress.Security.NonceVerification
	if ( isset( $_GET['kx_offers'] ) ) {
		$title = $en ? 'Offers' : 'العروض';
	}
	echo '<section class="kapp-shop-head"><div class="wrap">';
	echo '<h1 class="kapp-shop-head__title">' . esc_html( $title ) . '</h1>';
	echo '<p class="kapp-shop-head__crumb"><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html( $en ? 'Home' : 'الرئيسية' ) . '</a><span aria-hidden="true"> / </span><span>' . esc_html( $title ) . '</span></p>';
	echo '</div></section>';
	echo '<section class="sec kapp-shop-body"><div class="wrap"><div class="woocommerce kx-wc">';
	echo kymeet_wc_filter_bar(); // phpcs:ignore
	woocommerce_content();
	echo '</div></div></section>';
}

# ═══ نوتات العطر (Top / Heart / Base) كتبويب في صفحة المنتج ═══
add_filter( 'woocommerce_product_tabs', 'kymeet_wc_tabs' );
function kymeet_wc_tabs( $tabs ) {
	global $product;
	if ( ! $product instanceof WC_Product ) {
		return $tabs;
	}
	$id = $product->get_id();
	if ( get_post_meta( $id, '_kymeet_notes_top', true ) || get_post_meta( $id, '_kymeet_notes_heart', true ) || get_post_meta( $id, '_kymeet_notes_base', true ) ) {
		$tabs['kymeet_notes'] = array(
			'title'    => kymeet_store_is_en() ? 'Fragrance notes' : 'نوتات العطر',
			'priority' => 15,
			'callback' => 'kymeet_wc_notes_panel',
		);
	}
	return $tabs;
}
function kymeet_wc_notes_panel() {
	global $product;
	$en   = kymeet_store_is_en();
	$cols = array(
		'_kymeet_notes_top'   => $en ? 'Top notes' : 'نوتات القمة',
		'_kymeet_notes_heart' => $en ? 'Heart notes' : 'نوتات القلب',
		'_kymeet_notes_base'  => $en ? 'Base notes' : 'نوتات القاعدة',
	);
	echo '<div class="kx-notes">';
	foreach ( $cols as $k => $label ) {
		$v = get_post_meta( $product->get_id(), $k, true );
		if ( $v ) {
			echo '<div><h4>' . esc_html( $label ) . '</h4><p>' . esc_html( $v ) . '</p></div>';
		}
	}
	echo '</div>';
}

# ═══ عطور مشابهة: من نفس العلامة التجارية أولًا ═══
add_filter( 'woocommerce_related_products', 'kymeet_wc_related', 10, 3 );
function kymeet_wc_related( $related, $product_id, $args ) {
	$t = get_the_terms( $product_id, 'product_brand' );
	if ( ! is_array( $t ) || ! $t ) {
		return $related;
	}
	$ids   = get_posts(
		array(
			'post_type'    => 'product',
			'post_status'  => 'publish',
			'numberposts'  => 8,
			'fields'       => 'ids',
			'post__not_in' => array( $product_id ),
			'orderby'      => 'rand',
			'tax_query'    => array( array( 'taxonomy' => 'product_brand', 'field' => 'term_id', 'terms' => wp_list_pluck( $t, 'term_id' ) ) ), // phpcs:ignore
		)
	);
	$limit = ( is_array( $args ) && isset( $args['limit'] ) ) ? (int) $args['limit'] : 4;
	return array_slice( array_values( array_unique( array_merge( $ids, (array) $related ) ) ), 0, max( 1, $limit ) );
}

# ═══ خصائص العطور (الجنس / التركيز / الموسم / الحجم) لفلترة المتجر ═══
function kymeet_wc_create_attributes() {
	$made = 0;
	$defs = array(
		'gender'        => 'الجنس',
		'concentration' => 'التركيز',
		'season'        => 'الموسم',
		'size'          => 'الحجم',
	);
	foreach ( $defs as $slug => $label ) {
		if ( ! wc_attribute_taxonomy_id_by_name( $slug ) ) {
			$r = wc_create_attribute(
				array(
					'name'         => $label,
					'slug'         => $slug,
					'type'         => 'select',
					'order_by'     => 'menu_order',
					'has_archives' => false,
				)
			);
			if ( ! is_wp_error( $r ) ) {
				$made++;
			}
		}
	}
	return $made;
}

# شريط الفلترة أعلى المتجر: علامات + خصائص + سعر (كلها فلاتر WooCommerce الأصلية)
function kymeet_wc_filter_bar() {
	$en  = kymeet_store_is_en();
	$out = '';
	// Brand chips rendered via kapp-chips (shell-v3) on app shop — avoid duplicate row.
	if ( ! ( function_exists( 'kymeet_store_active' ) && kymeet_store_active() ) && taxonomy_exists( 'product_brand' ) ) {
		$terms = get_terms( array( 'taxonomy' => 'product_brand', 'hide_empty' => true, 'orderby' => 'term_id' ) );
		if ( ! is_wp_error( $terms ) && $terms ) {
			$out .= '<div class="kx-brands"><a class="chip" href="' . esc_url( wc_get_page_permalink( 'shop' ) ) . '">' . esc_html( $en ? 'All' : 'الكل' ) . '</a>';
			foreach ( $terms as $t ) {
				$l = get_term_link( $t );
				if ( ! is_wp_error( $l ) ) {
					$out .= '<a class="chip' . ( is_tax( 'product_brand', $t->term_id ) ? ' on' : '' ) . '" href="' . esc_url( $l ) . '">' . esc_html( $t->name ) . '</a>';
				}
			}
			$out .= '</div>';
		}
	}
	$attrs = array(
		'gender'        => $en ? 'Gender' : 'الجنس',
		'concentration' => $en ? 'Concentration' : 'التركيز',
		'season'        => $en ? 'Season' : 'الموسم',
		'size'          => $en ? 'Size' : 'الحجم',
	);
	$form = '';
	foreach ( $attrs as $slug => $label ) {
		if ( ! taxonomy_exists( 'pa_' . $slug ) ) {
			continue;
		}
		$terms = get_terms( array( 'taxonomy' => 'pa_' . $slug, 'hide_empty' => true ) );
		if ( is_wp_error( $terms ) || ! $terms ) {
			continue;
		}
		// phpcs:ignore WordPress.Security.NonceVerification
		$cur   = isset( $_GET[ 'filter_' . $slug ] ) ? sanitize_text_field( wp_unslash( $_GET[ 'filter_' . $slug ] ) ) : '';
		$form .= '<select name="filter_' . esc_attr( $slug ) . '" aria-label="' . esc_attr( $label ) . '"><option value="">' . esc_html( $label ) . ': ' . esc_html( $en ? 'All' : 'الكل' ) . '</option>';
		foreach ( $terms as $t ) {
			$form .= '<option value="' . esc_attr( $t->slug ) . '"' . selected( $cur, $t->slug, false ) . '>' . esc_html( $t->name ) . '</option>';
		}
		$form .= '</select>';
	}
	// phpcs:disable WordPress.Security.NonceVerification
	$min = isset( $_GET['min_price'] ) ? absint( $_GET['min_price'] ) : '';
	$max = isset( $_GET['max_price'] ) ? absint( $_GET['max_price'] ) : '';
	$ord = isset( $_GET['orderby'] ) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : '';
	// phpcs:enable
	$form .= '<input type="number" min="0" name="min_price" value="' . esc_attr( $min ) . '" placeholder="' . esc_attr( $en ? 'Min price' : 'أقل سعر' ) . '">';
	$form .= '<input type="number" min="0" name="max_price" value="' . esc_attr( $max ) . '" placeholder="' . esc_attr( $en ? 'Max price' : 'أعلى سعر' ) . '">';
	if ( $ord ) {
		$form .= '<input type="hidden" name="orderby" value="' . esc_attr( $ord ) . '">';
	}
	$form .= '<button class="button" type="submit">' . esc_html( $en ? 'Filter' : 'تصفية' ) . '</button>';
	$out  .= '<form class="kx-filters" method="get" action="' . esc_url( strtok( get_pagenum_link( 1 ), '?' ) ) . '">' . $form . '</form>';
	return $out;
}

# العروض: ?kx_offers=1 يعرض المنتجات المخفّضة فقط
add_action( 'pre_get_posts', 'kymeet_wc_offers_query' );
function kymeet_wc_offers_query( $q ) {
	// phpcs:ignore WordPress.Security.NonceVerification
	if ( is_admin() || ! $q->is_main_query() || ! isset( $_GET['kx_offers'] ) || ! function_exists( 'wc_get_product_ids_on_sale' ) ) {
		return;
	}
	if ( $q->is_post_type_archive( 'product' ) || $q->is_tax( array( 'product_cat', 'product_brand', 'product_tag' ) ) ) {
		$ids = wc_get_product_ids_on_sale();
		$q->set( 'post__in', $ids ? array_map( 'absint', $ids ) : array( 0 ) );
	}
}

# إعدادات الحساب: التسجيل مفعّل وكلمة المرور يكتبها العميل (مرة واحدة)
add_action( 'admin_init', 'kymeet_wc_settings_once' );
function kymeet_wc_settings_once() {
	if ( ! current_user_can( 'manage_options' ) || get_option( 'kymeet_wc_settings_v152' ) || ! kymeet_wc_active() ) {
		return;
	}
	update_option( 'woocommerce_enable_myaccount_registration', 'yes' );
	update_option( 'woocommerce_registration_generate_password', 'no' );
	update_option( 'kymeet_wc_settings_v152', '1' );
}
