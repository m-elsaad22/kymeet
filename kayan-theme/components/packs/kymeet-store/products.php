<?php
/**
 * KYMEET — منتجات العطور (نوع المحتوى perfumes)
 * يُنشأ المنتج من: العطور → إضافة جديد، وتظهر المنتجات تلقائيًا في الرئيسية.
 */
defined( 'ABSPATH' ) || exit;

function kymeet_seed_data() {
	static $d = null;
	if ( null === $d ) {
		$f = kymeet_store_dir() . 'seed/products.json';
		$d = is_readable( $f ) ? json_decode( file_get_contents( $f ), true ) : array();
		if ( ! is_array( $d ) ) {
			$d = array();
		}
		$d = array_merge( array( 'products' => array(), 'brands' => array(), 'best' => array(), 'new' => array() ), $d );
	}
	return $d;
}

function kymeet_product_fields( $pt = 'perfumes' ) {
	$f = array(
		'_kymeet_tag'       => 'الشارة (مثل: الأكثر مبيعًا / جديد)',
		'_kymeet_name_en'   => 'الاسم بالإنجليزية (اختياري)',
		'_kymeet_image_url' => 'رابط صورة خارجي (إن لم تُحدَّد صورة بارزة)',
	);
	if ( 'product' === $pt ) {
		$f = array_merge(
			$f,
			array(
				'_kymeet_notes_top'   => 'نوتات القمة (Top)',
				'_kymeet_notes_heart' => 'نوتات القلب (Heart)',
				'_kymeet_notes_base'  => 'نوتات القاعدة (Base)',
			)
		);
	}
	if ( 'perfumes' === $pt ) {
		$f = array_merge(
			array(
				'_kymeet_price'     => 'السعر الحالي (بالأرقام)',
				'_kymeet_old_price' => 'السعر قبل الخصم (اختياري)',
			),
			$f
		);
	}
	return $f;
}

add_action( 'add_meta_boxes', 'kymeet_product_add_box' );
function kymeet_product_add_box() {
	add_meta_box( 'kymeet_product', 'بيانات العطر (KYMEET)', 'kymeet_product_box', array( 'perfumes', 'product' ), 'normal', 'high' );
}

function kymeet_product_box( $post ) {
	wp_nonce_field( 'kymeet_product', 'kymeet_product_nonce' );
	echo '<table class="form-table">';
	foreach ( kymeet_product_fields( $post->post_type ) as $k => $label ) {
		$v = get_post_meta( $post->ID, $k, true );
		echo '<tr><th>' . esc_html( $label ) . '</th><td><input type="text" class="widefat" name="' . esc_attr( $k ) . '" value="' . esc_attr( $v ) . '"></td></tr>';
	}
	$flags = array( '_kymeet_new' => 'يظهر في «أحدث الإصدارات»' );
	if ( 'perfumes' === $post->post_type ) {
		$flags = array_merge( array( '_kymeet_best' => 'يظهر في «الأكثر طلبًا»' ), $flags );
	}
	foreach ( $flags as $k => $label ) {
		echo '<tr><th>' . esc_html( $label ) . '</th><td><label><input type="checkbox" name="' . esc_attr( $k ) . '" value="1" ' . checked( get_post_meta( $post->ID, $k, true ), '1', false ) . '> نعم</label></td></tr>';
	}
	echo '</table>';
	if ( 'product' === $post->post_type ) {
		echo '<p>السعر والمخزون والمتغيرات من لوحة بيانات المنتج (WooCommerce). «الأكثر طلبًا» = ضع نجمة على المنتج (مميّز). العلامة التجارية من مربع «العلامات التجارية».</p>';
	}
}

add_action( 'save_post_perfumes', 'kymeet_product_save' );
add_action( 'save_post_product', 'kymeet_product_save' );
function kymeet_product_save( $post_id ) {
	if ( ! isset( $_POST['kymeet_product_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['kymeet_product_nonce'] ) ), 'kymeet_product' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$pt = get_post_type( $post_id );
	foreach ( kymeet_product_fields( $pt ) as $k => $label ) {
		$v = isset( $_POST[ $k ] ) ? sanitize_text_field( wp_unslash( $_POST[ $k ] ) ) : '';
		if ( '_kymeet_image_url' === $k ) {
			$v = esc_url_raw( $v );
		}
		update_post_meta( $post_id, $k, $v );
	}
	$flags = ( 'perfumes' === $pt ) ? array( '_kymeet_best', '_kymeet_new' ) : array( '_kymeet_new' );
	foreach ( $flags as $k ) {
		update_post_meta( $post_id, $k, empty( $_POST[ $k ] ) ? '' : '1' );
	}
}

# صف بيانات منتج واحد
function kymeet_store_row( $p ) {
	$en    = function_exists( 'kymeet_store_is_en' ) && kymeet_store_is_en();
	$brand = '';
	$terms = get_the_terms( $p->ID, kymeet_store_brand_tax() );
	if ( is_array( $terms ) && $terms ) {
		$brand = $terms[0]->name;
	}
	$img = get_the_post_thumbnail_url( $p->ID, 'large' );
	if ( ! $img ) {
		$img = get_post_meta( $p->ID, '_kymeet_image_url', true );
	}
	$name = $p->post_title;
	if ( $en ) {
		$n = get_post_meta( $p->ID, '_kymeet_name_en', true );
		if ( $n ) {
			$name = $n;
		}
	}
	$row = array(
		'id'    => $p->post_name,
		'pid'   => (int) $p->ID,
		'name'  => $name,
		'brand' => $brand,
		'price' => 0,
		'image' => $img ? $img : '',
		'url'   => get_permalink( $p ),
		'buy'   => true,
	);
	$tag = get_post_meta( $p->ID, '_kymeet_tag', true );
	if ( 'product' === $p->post_type && function_exists( 'wc_get_product' ) ) {
		$pr = wc_get_product( $p->ID );
		if ( $pr ) {
			$row['price'] = (float) $pr->get_price();
			if ( $pr->is_on_sale() && $pr->get_regular_price() ) {
				$row['old'] = (float) $pr->get_regular_price();
			}
			$row['buy']   = $pr->is_type( 'simple' ) && $pr->is_purchasable() && $pr->is_in_stock();
			$row['stock'] = $pr->is_in_stock();
			if ( ! $pr->is_in_stock() && ! $tag ) {
				$tag = $en ? 'Out of stock' : 'نفدت الكمية';
			}
		}
	} else {
		$row['price'] = (float) get_post_meta( $p->ID, '_kymeet_price', true );
		$old          = get_post_meta( $p->ID, '_kymeet_old_price', true );
		if ( $old ) {
			$row['old'] = (float) $old;
		}
	}
	if ( $tag ) {
		$row['tag'] = $tag;
	}
	return $row;
}

# بيانات المنتجات للواجهة (من الموقع، وإلا النسخة الأولية)
function kymeet_store_data() {
	$products = array();
	$best     = array();
	$new      = array();
	$pt       = kymeet_store_pt();
	$posts    = post_type_exists( $pt ) ? get_posts(
		array(
			'post_type'   => $pt,
			'post_status' => 'publish',
			'numberposts' => 300,
			'orderby'     => array( 'menu_order' => 'ASC', 'ID' => 'ASC' ),
		)
	) : array();
	foreach ( $posts as $p ) {
		$products[] = kymeet_store_row( $p );
		$is_best    = false;
		if ( 'product' === $pt && function_exists( 'wc_get_product' ) ) {
			$pr      = wc_get_product( $p->ID );
			$is_best = $pr && $pr->is_featured();
		} else {
			$is_best = (bool) get_post_meta( $p->ID, '_kymeet_best', true );
		}
		if ( $is_best ) {
			$best[] = $p->post_name;
		}
		if ( get_post_meta( $p->ID, '_kymeet_new', true ) ) {
			$new[] = $p->post_name;
		}
	}
	if ( ! $products ) {
		$d = kymeet_seed_data();
		return array( 'products' => $d['products'], 'BR' => $d['brands'], 'BEST' => $d['best'], 'NEW' => $d['new'] );
	}
	return array( 'products' => $products, 'BR' => kymeet_store_brand_names(), 'BEST' => $best, 'NEW' => $new );
}

function kymeet_store_data_script() {
	echo '<script>window.KYMEET_DATA=' . wp_json_encode( kymeet_store_data() ) . ';</script>';
}

# استيراد المنتجات الحالية كمنتجات حقيقية (مرة واحدة)
function kymeet_seed_products() {
	if ( get_option( 'kymeet_products_seeded' ) || ! post_type_exists( 'perfumes' ) || ( function_exists( 'kymeet_wc_active' ) && kymeet_wc_active() ) ) {
		return;
	}
	$d = kymeet_seed_data();
	foreach ( $d['brands'] as $b ) {
		if ( ! term_exists( $b, 'perfume_brand' ) ) {
			wp_insert_term( $b, 'perfume_brand' );
		}
	}
	$i = 0;
	foreach ( $d['products'] as $p ) {
		$i++;
		if ( get_page_by_path( $p['id'], OBJECT, 'perfumes' ) ) {
			continue;
		}
		$id = wp_insert_post(
			array(
				'post_type'   => 'perfumes',
				'post_status' => 'publish',
				'post_title'  => $p['name'],
				'post_name'   => $p['id'],
				'menu_order'  => $i,
			)
		);
		if ( ! $id || is_wp_error( $id ) ) {
			continue;
		}
		update_post_meta( $id, '_kymeet_price', $p['price'] );
		update_post_meta( $id, '_kymeet_image_url', $p['image'] );
		if ( ! empty( $p['tag'] ) ) {
			update_post_meta( $id, '_kymeet_tag', $p['tag'] );
		}
		if ( in_array( $p['id'], $d['best'], true ) ) {
			update_post_meta( $id, '_kymeet_best', '1' );
		}
		if ( in_array( $p['id'], $d['new'], true ) ) {
			update_post_meta( $id, '_kymeet_new', '1' );
		}
		if ( ! empty( $p['brand'] ) ) {
			wp_set_object_terms( $id, $p['brand'], 'perfume_brand' );
		}
	}
	update_option( 'kymeet_products_seeded', '1' );
}
add_action( 'admin_init', 'kymeet_seed_products_maybe' );
function kymeet_seed_products_maybe() {
	if ( current_user_can( 'manage_options' ) ) {
		kymeet_seed_products();
	}
}

# بطاقة منتج (نفس شكل بطاقات الرئيسية)
function kymeet_store_money( $v ) {
	return number_format( (float) $v, 0, '.', ',' );
}
function kymeet_store_card( $p ) {
	$r   = kymeet_store_row( $p );
	$id  = esc_attr( $r['id'] );
	$url = esc_url( $r['url'] );
	$img = $r['image'] ? '<img src="' . esc_url( $r['image'] ) . '" alt="' . esc_attr( $r['name'] ) . '" data-brand="' . esc_attr( $r['brand'] ) . '" loading="lazy" onerror="imgErr(this)">' : '';
	$tag = '';
	if ( isset( $r['tag'] ) ) {
		$tag = '<span class="badge' . ( false !== strpos( $r['tag'], 'جديد' ) ? ' new' : '' ) . '">' . esc_html( $r['tag'] ) . '</span>';
	}
	$old = isset( $r['old'] ) ? ' <s>' . esc_html( kymeet_store_money( $r['old'] ) ) . '</s>' : '';
	$en  = function_exists( 'kymeet_store_is_en' ) && kymeet_store_is_en();
	if ( ! empty( $r['buy'] ) ) {
		$btn = '<button class="add" type="button" onclick="addToCart(\'' . $id . '\',event,' . (int) $r['pid'] . ')"><i class="fas fa-bag-shopping"></i> ' . esc_html( $en ? 'Add to cart' : 'أضف للسلة' ) . '</button>';
	} else {
		$lbl = ( isset( $r['stock'] ) && ! $r['stock'] ) ? ( $en ? 'Details' : 'التفاصيل' ) : ( $en ? 'Choose options' : 'اختر الخيارات' );
		$btn = '<a class="add" href="' . $url . '"><i class="fas fa-sliders"></i> ' . esc_html( $lbl ) . '</a>';
	}
	$out  = '<article class="prod"><a class="prod-img" href="' . $url . '">' . $tag . $img . '</a>';
	$out .= '<div class="prod-info"><span class="prod-brand">' . esc_html( $r['brand'] ) . '</span>';
	$out .= '<h3 class="prod-name"><a href="' . $url . '">' . esc_html( $r['name'] ) . '</a></h3>';
	$out .= '<div class="prod-price"><span class="now">' . esc_html( kymeet_store_money( $r['price'] ) ) . '<small>' . esc_html( $en ? 'EGP' : 'ج.م' ) . '</small></span>' . $old . '</div>';
	$out .= '<div class="prod-actions">' . $btn;
	$out .= '<a class="view" href="' . $url . '" aria-label="' . esc_attr( $r['name'] ) . '"><i class="far fa-eye"></i></a></div></div></article>';
	return $out;
}

# أرشيف العطور / العلامة / التصنيف
add_action( 'pre_get_posts', 'kymeet_store_archive_query' );
function kymeet_store_archive_query( $q ) {
	if ( is_admin() || ! $q->is_main_query() ) {
		return;
	}
	if ( $q->is_post_type_archive( 'perfumes' ) || $q->is_tax( array( 'perfume_brand', 'perfume_category' ) ) ) {
		$q->set( 'posts_per_page', 24 );
		$q->set( 'orderby', array( 'menu_order' => 'ASC', 'ID' => 'ASC' ) );
	}
}
function kymeet_store_render_archive( $title, $sub = '' ) {
	echo '<section class="phero compact"><div class="wrap"><h1>' . esc_html( $title ) . '</h1>';
	if ( $sub ) {
		echo '<p class="psub">' . esc_html( wp_strip_all_tags( $sub ) ) . '</p>';
	}
	echo '</div></section><section class="sec"><div class="wrap">';
	$terms = get_terms( array( 'taxonomy' => 'perfume_brand', 'hide_empty' => true, 'orderby' => 'term_id' ) );
	if ( ! is_wp_error( $terms ) && $terms ) {
		echo '<div class="kx-brands"><a class="chip" href="' . esc_url( get_post_type_archive_link( 'perfumes' ) ) . '">' . esc_html( kymeet_store_is_en() ? 'All' : 'الكل' ) . '</a>';
		foreach ( $terms as $t ) {
			$l = get_term_link( $t );
			if ( ! is_wp_error( $l ) ) {
				echo '<a class="chip" href="' . esc_url( $l ) . '">' . esc_html( $t->name ) . '</a>';
			}
		}
		echo '</div>';
	}
	if ( have_posts() ) {
		echo '<div class="kx-grid">';
		while ( have_posts() ) {
			the_post();
			echo kymeet_store_card( get_post() ); // phpcs:ignore
		}
		echo '</div><div class="kx-pager">' . wp_kses_post( paginate_links( array( 'prev_text' => '‹', 'next_text' => '›' ) ) ) . '</div>';
	} elseif ( function_exists( 'kayan_kit_empty' ) ) {
		kayan_kit_empty();
	}
	echo '</div></section>';
}

# سلايدر الهيرو: أحدث المنتجات (الأحدث إضافةً أولًا)
function kymeet_store_hero_slider() {
	$rows  = array();
	$posts = post_type_exists( kymeet_store_pt() ) ? get_posts(
		array(
			'post_type'   => kymeet_store_pt(),
			'post_status' => 'publish',
			'numberposts' => 6,
			'orderby'     => array( 'date' => 'DESC', 'ID' => 'DESC' ),
		)
	) : array();
	foreach ( $posts as $p ) {
		$rows[] = kymeet_store_row( $p );
	}
	if ( ! $rows ) {
		$d    = kymeet_seed_data();
		$rows = array_slice( array_reverse( $d['products'] ), 0, 6 );
	}
	if ( ! $rows ) {
		return '';
	}
	$en  = function_exists( 'kymeet_store_is_en' ) && kymeet_store_is_en();
	$out = '<div class="hv-slider" data-hv>';
	foreach ( $rows as $i => $r ) {
		$img = ! empty( $r['image'] ) ? '<img src="' . esc_url( $r['image'] ) . '" alt="' . esc_attr( $r['name'] ) . '" data-brand="' . esc_attr( $r['brand'] ) . '" onerror="imgErr(this)">' : '';
		$url = ! empty( $r['url'] ) ? $r['url'] : '#catalog';
		$out .= '<div class="hv-slide' . ( 0 === $i ? ' on' : '' ) . '">';
		$out .= '<span class="hv-new"><i class="fas fa-star"></i> ' . esc_html( $en ? 'Just arrived' : 'وصل حديثًا' ) . '</span>';
		$out .= '<a class="hv-disc" href="' . esc_url( $url ) . '">' . $img . '</a>';
		$out .= '<div class="hv-cap"><div><small>' . esc_html( $r['brand'] ) . '</small><b>' . esc_html( $r['name'] ) . '</b><span class="pr">' . esc_html( kymeet_store_money( $r['price'] ) ) . ' ' . esc_html( $en ? 'EGP' : 'ج.م' ) . '</span></div>';
		$out .= '<button type="button" class="btn btn-gold" onclick="addToCart(\'' . esc_attr( $r['id'] ) . '\',event' . ( ! empty( $r['pid'] ) ? ',' . (int) $r['pid'] : '' ) . ')"><i class="fas fa-bag-shopping"></i> ' . esc_html( $en ? 'Add' : 'أضف' ) . '</button></div></div>';
	}
	if ( count( $rows ) > 1 ) {
		$prev = $en ? 'left' : 'right';
		$next = $en ? 'right' : 'left';
		$out .= '<button type="button" class="hv-nav prev" aria-label="Previous"><i class="fas fa-chevron-' . $prev . '"></i></button>';
		$out .= '<button type="button" class="hv-nav next" aria-label="Next"><i class="fas fa-chevron-' . $next . '"></i></button>';
		$out .= '<div class="hv-dots">';
		foreach ( $rows as $i => $r ) {
			$out .= '<button type="button" class="' . ( 0 === $i ? 'on' : '' ) . '" aria-label="' . (int) ( $i + 1 ) . '"></button>';
		}
		$out .= '</div>';
	}
	return $out . '</div>';
}

# ═══ العلامات التجارية: صورة لكل علامة + كروت الرئيسية ═══
add_action( 'perfume_brand_add_form_fields', 'kymeet_brand_field_add' );
add_action( 'perfume_brand_edit_form_fields', 'kymeet_brand_field_edit' );
add_action( 'created_perfume_brand', 'kymeet_brand_field_save' );
add_action( 'edited_perfume_brand', 'kymeet_brand_field_save' );
add_action( 'admin_enqueue_scripts', 'kymeet_brand_assets' );

function kymeet_brand_assets( $hook ) {
	if ( in_array( $hook, array( 'edit-tags.php', 'term.php' ), true ) ) {
		$s = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( $s && 'perfume_brand' === $s->taxonomy ) {
			wp_enqueue_media();
		}
	}
}

function kymeet_brand_field_html( $val ) {
	$h  = '<input type="text" id="kymeet_brand_image" name="kymeet_brand_image" value="' . esc_attr( $val ) . '" class="regular-text" placeholder="رابط الصورة أو رقمها"> ';
	$h .= '<button type="button" class="button" id="kymeet_brand_pick">اختيار صورة</button>';
	$h .= '<p class="description">تظهر داخل كارت العلامة في الصفحة الرئيسية. إن تُركت فارغة تُستخدم صورة أول عطر من العلامة.</p>';
	$h .= '<script>document.getElementById("kymeet_brand_pick").addEventListener("click",function(){var f=wp.media({multiple:false});f.on("select",function(){document.getElementById("kymeet_brand_image").value=f.state().get("selection").first().toJSON().url});f.open()});</script>';
	return $h;
}

function kymeet_brand_field_add() {
	wp_nonce_field( 'kymeet_brand', 'kymeet_brand_nonce' );
	echo '<div class="form-field"><label for="kymeet_brand_image">صورة العلامة</label>' . kymeet_brand_field_html( '' ) . '</div>'; // phpcs:ignore
}

function kymeet_brand_field_edit( $term ) {
	wp_nonce_field( 'kymeet_brand', 'kymeet_brand_nonce' );
	$v = get_term_meta( $term->term_id, 'kymeet_brand_image', true );
	echo '<tr class="form-field"><th scope="row"><label for="kymeet_brand_image">صورة العلامة</label></th><td>' . kymeet_brand_field_html( $v ) . '</td></tr>'; // phpcs:ignore
}

function kymeet_brand_field_save( $term_id ) {
	if ( ! isset( $_POST['kymeet_brand_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['kymeet_brand_nonce'] ) ), 'kymeet_brand' ) ) {
		return;
	}
	if ( ! current_user_can( 'manage_categories' ) ) {
		return;
	}
	$v = isset( $_POST['kymeet_brand_image'] ) ? trim( wp_unslash( $_POST['kymeet_brand_image'] ) ) : ''; // phpcs:ignore
	$v = is_numeric( $v ) ? absint( $v ) : esc_url_raw( $v );
	update_term_meta( $term_id, 'kymeet_brand_image', $v );
}

# كروت العلامات (من العلامات المضافة في الموقع)
function kymeet_store_brand_cards() {
	$en    = function_exists( 'kymeet_store_is_en' ) && kymeet_store_is_en();
	$items = array();
	$terms = get_terms( array( 'taxonomy' => kymeet_store_brand_tax(), 'hide_empty' => false, 'orderby' => 'term_id' ) );
	if ( ! is_wp_error( $terms ) && $terms ) {
		foreach ( $terms as $t ) {
			$img = '';
			$fit = false;
			$m   = get_term_meta( $t->term_id, 'kymeet_brand_image', true );
			if ( ! $m ) {
				$m = get_term_meta( $t->term_id, 'thumbnail_id', true );
			}
			if ( $m ) {
				$img = is_numeric( $m ) ? (string) wp_get_attachment_url( (int) $m ) : (string) $m;
			}
			if ( ! $img ) {
				$fit = true;
				$ps  = get_posts(
					array(
						'post_type'   => kymeet_store_pt(),
						'numberposts' => 1,
						'orderby'     => array( 'menu_order' => 'ASC', 'ID' => 'ASC' ),
						'tax_query'   => array( array( 'taxonomy' => kymeet_store_brand_tax(), 'field' => 'term_id', 'terms' => $t->term_id ) ), // phpcs:ignore
					)
				);
				if ( $ps ) {
					$img = (string) get_the_post_thumbnail_url( $ps[0]->ID, 'large' );
					if ( ! $img ) {
						$img = (string) get_post_meta( $ps[0]->ID, '_kymeet_image_url', true );
					}
				}
			}
			$items[] = array( 'name' => $t->name, 'img' => $img, 'fit' => $fit, 'count' => (int) $t->count );
		}
	}
	if ( ! $items ) {
		$d = kymeet_seed_data();
		foreach ( $d['brands'] as $b ) {
			$img = '';
			foreach ( $d['products'] as $p ) {
				if ( isset( $p['brand'] ) && $p['brand'] === $b ) {
					$img = $p['image'];
					break;
				}
			}
			$items[] = array( 'name' => $b, 'img' => $img, 'fit' => true, 'count' => 0 );
		}
	}
	$out = '';
	foreach ( $items as $it ) {
		$pic = $it['img'] ? '<img src="' . esc_url( $it['img'] ) . '" alt="' . esc_attr( $it['name'] ) . '" loading="lazy" onerror="this.outerHTML=\'&lt;i class=&quot;fas fa-gem&quot;&gt;&lt;/i&gt;\'">' : '<i class="fas fa-gem"></i>';
		$cnt = $it['count'] ? '<small>' . (int) $it['count'] . ' ' . esc_html( $en ? 'perfumes' : 'عطر' ) . '</small>' : '';
		$out .= '<a class="kbrand rv" href="https://kymeet-perfumes.com/shop/" data-b="' . esc_attr( $it['name'] ) . '" onclick="pickBrand(this.dataset.b);return false">';
		$out .= '<span class="bk-img' . ( $it['fit'] ? ' fit' : '' ) . '">' . $pic . '</span>';
		$out .= '<span class="bk-name"><b>' . esc_html( $it['name'] ) . '</b>' . $cnt . '</span></a>';
	}
	return $out;
}

# بانرات الهيرو: شرائح كاملة لأحدث المنتجات (بجانب الشريحة الرئيسية)
function kymeet_store_hero_banners() {
	$rows  = array();
	$posts = post_type_exists( kymeet_store_pt() ) ? get_posts(
		array(
			'post_type'   => kymeet_store_pt(),
			'post_status' => 'publish',
			'numberposts' => 5,
			'orderby'     => array( 'date' => 'DESC', 'ID' => 'DESC' ),
		)
	) : array();
	foreach ( $posts as $p ) {
		$rows[] = kymeet_store_row( $p );
	}
	if ( ! $rows ) {
		$d    = kymeet_seed_data();
		$rows = array_slice( array_reverse( $d['products'] ), 0, 5 );
	}
	$en  = function_exists( 'kymeet_store_is_en' ) && kymeet_store_is_en();
	$out = '';
	foreach ( $rows as $i => $r ) {
		$url = ! empty( $r['url'] ) ? $r['url'] : '#catalog';
		$img = ! empty( $r['image'] ) ? '<img src="' . esc_url( $r['image'] ) . '" alt="' . esc_attr( $r['name'] ) . '" data-brand="' . esc_attr( $r['brand'] ) . '" loading="' . ( 0 === $i ? 'eager' : 'lazy' ) . '" onerror="imgErr(this)">' : '<i class="fas fa-gem"></i>';
		$old = isset( $r['old'] ) ? ' <s>' . esc_html( kymeet_store_money( $r['old'] ) ) . '</s>' : '';
		$add = ! empty( $r['pid'] ) ? ',' . (int) $r['pid'] : '';
		$out .= '<div class="hc-slide hc-ban"><div class="wrap hcb"><div class="hcb-txt"><span class="tag en">' . esc_html( isset( $r['old'] ) ? ( $en ? 'Special offer' : 'عرض خاص' ) : ( $en ? 'Just arrived' : 'وصل حديثًا' ) ) . '</span>';
		$out .= '<small>' . esc_html( $r['brand'] ) . '</small><h2>' . esc_html( $r['name'] ) . '</h2>';
		$out .= '<div class="hcb-price"><b>' . esc_html( kymeet_store_money( $r['price'] ) ) . '</b> ' . esc_html( $en ? 'EGP' : 'ج.م' ) . $old . '</div>';
		$out .= '<div class="hcb-act"><button type="button" class="btn btn-gold" onclick="addToCart(\'' . esc_attr( $r['id'] ) . '\',event' . $add . ')"><i class="fas fa-bag-shopping"></i> ' . esc_html( $en ? 'Add to cart' : 'أضف للسلة' ) . '</button>';
		$out .= '<a class="btn btn-ghost-w" href="' . esc_url( $url ) . '">' . esc_html( $en ? 'Details' : 'التفاصيل' ) . '</a></div></div>';
		$out .= '<a class="hcb-img" href="' . esc_url( $url ) . '">' . $img . '</a></div></div>';
	}
	return $out;
}
