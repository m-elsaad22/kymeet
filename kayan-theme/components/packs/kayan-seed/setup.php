<?php
/**
 * kayan-seed — تلقيم المحتوى الافتراضي الفعلي في إعدادات الصفحة الرئيسية (v1.4.1)
 * ════════════════════════════════════════════════════════════════════════
 * يكتب محتوى تصميم KYMEET الكامل داخل حقول الإعدادات فعلياً (وليس كـ fallback
 * في الكود)، فتفتح لوحة التحكم وتجد كل النصوص مكتوبة أمامك جاهزة للتعديل.
 *
 * البنية: كل ودجت = بوست من نوع widgets__posts، قيمه في widget_post_meta،
 * والترتيب في الخيار widgets_home__meta.
 *
 * لا يعمل تلقائياً عند التفعيل أو التحميل — حتى لا يُفرض محتوى دولة/خدمات
 * على مواقع أخرى. التلقيم اختياري يدوياً من لوحة التحكم فقط، ولا يمس
 * جداول HomeIntro / widgets_home__meta إلا إذا ضغط المدير الزر صراحة.
 * ════════════════════════════════════════════════════════════════════════
 */

if ( ! function_exists( 'kayan_seed_home_defaults' ) ) {

	function kayan_seed_home_content_map() {
		# KYMEET: ودجات متجر العطور (المحتوى الافتراضي داخل القوالب)
		return array_fill_keys( kymeet_store_sections(), array() );
	}

	/**
	 * ترتيب ودجات الرئيسية (تصميم KYMEET)
	 */
	function kayan_seed_home_order() {
		return array_keys( kayan_seed_home_content_map() );
	}

	/**
	 * ينشئ بوست ودجت واحداً ويكتب قيمه الفعلية في widget_post_meta
	 */
	function kayan_seed_create_widget( $widget_id, $values, $index ) {
		$post_id = wp_insert_post( array(
			'post_title'  => $widget_id . '_home_' . $index,
			'post_type'   => 'widgets__posts',
			'post_status' => 'publish',
		) );

		if ( is_wp_error( $post_id ) || ! $post_id ) {
			return false;
		}

		update_post_meta( $post_id, 'widget_type', $widget_id );
		# ═══ هنا يُكتب المحتوى فعلياً في الحقول (لا fallback) ═══
		update_post_meta( $post_id, 'widget_post_meta', $values );

		return $post_id;
	}

	/**
	 * التلقيم الرئيسي — يعمل مرة واحدة ولا يمس إعداداً موجوداً
	 */
	function kayan_seed_home_defaults( $force = false ) {

		# لا تفعل شيئاً إن كانت الرئيسية مُعدّة مسبقاً (حماية بيانات المستخدم)
		$existing = get_option( 'widgets_home__meta' );
		if ( ! $force && is_array( $existing ) && ! empty( $existing ) ) {
			return false;
		}

		$map   = kayan_seed_home_content_map();
		$order = kayan_seed_home_order();
		$list  = array();
		$i     = 0;

		foreach ( $order as $widget_id ) {
			if ( ! isset( $map[ $widget_id ] ) ) {
				continue;
			}
			# تخطّي الودجات غير المسجّلة (إن حُذف ملفها)
			if ( ! class_exists( $widget_id ) ) {
				continue;
			}

			$i++;
			$post_id = kayan_seed_create_widget( $widget_id, $map[ $widget_id ], $i );
			if ( ! $post_id ) {
				continue;
			}

			$list[] = array(
				'widget_id'       => $widget_id,
				'widget_post__id' => $post_id,
			);
		}

		if ( empty( $list ) ) {
			return false;
		}

		update_option( 'widgets_home__meta', $list );
		return count( $list );
	}

	/**
	 * التلقيم التلقائي معطّل عمداً (v1.4.10):
	 * القالب يقرأ المحتوى الموجود في HomeIntro و widgets_home__meta
	 * ولا يكتب مدناً أو خدمات أو نصوص دولة عند التفعيل.
	 */
	function kayan_seed_maybe_run() {
		return;
	}

	/**
	 * زر إعادة التلقيم اليدوي في لوحة التحكم
	 * (المظهر ← إعادة تلقيم محتوى الرئيسية)
	 */
	function kayan_seed_admin_page() {
		add_theme_page(
			'محتوى الرئيسية الافتراضي',
			'محتوى الرئيسية الافتراضي',
			'manage_options',
			'kayan-seed-home',
			'kayan_seed_admin_render'
		);
	}
	add_action( 'admin_menu', 'kayan_seed_admin_page' );

	function kayan_seed_admin_render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$done = false;
		if ( isset( $_POST['kayan_seed_run'] ) && check_admin_referer( 'kayan_seed_action' ) ) {
			$count = kayan_seed_home_defaults( true );
			$done  = $count;
		}

		$current = get_option( 'widgets_home__meta' );
		$has     = is_array( $current ) && ! empty( $current );

		echo '<div class="wrap" dir="rtl" style="max-width:820px">';
			echo '<h1>محتوى الصفحة الرئيسية الافتراضي (اختياري)</h1>';
			echo '<div class="notice notice-info"><p>القالب <strong>لا يكتب أي محتوى تلقائياً</strong>. الرئيسية تستخدم الجداول الموجودة: <code>HomeIntro</code> و <code>widgets_home__meta</code>. هذا الزر اختياري فقط، ومحتواه متجر واحد — لا تستخدمه على موقع في دولة أخرى.</p></div>';

			# لوحة تشخيص الإصدار — تكشف فوراً إن كان الرفع ناقصاً أو النسخة قديمة
			if ( function_exists( 'kayan_version_render_panel' ) ) {
				kayan_version_render_panel();
			}

			if ( false !== $done && $done ) {
				echo '<div class="notice notice-success"><p>تم كتابة محتوى ' . intval( $done ) . ' قسماً في إعدادات الرئيسية. افتح <strong>إعدادات القالب ← الرئيسية</strong> لتعديل النصوص.</p></div>';
			}

			echo '<div class="card" style="padding:16px;max-width:100%">';
				echo '<p>هذه الأداة تكتب محتوى تصميم KYMEET <strong>فعلياً داخل حقول الإعدادات</strong> — فتفتح إعدادات الرئيسية وتجد كل النصوص مكتوبة أمامك جاهزة للتعديل، بدلاً من حقول فارغة.</p>';

				if ( $has ) {
					echo '<p style="color:#b32d2e"><strong>تنبيه:</strong> الصفحة الرئيسية معدّة حالياً بـ ' . count( $current ) . ' قسماً. الضغط على الزر سيستبدلها بالمحتوى الافتراضي، وستفقد تعديلاتك الحالية.</p>';
				} else {
					echo '<p>الصفحة الرئيسية فارغة حالياً — اضغط الزر لتعبئتها بمحتوى التصميم الكامل.</p>';
				}

				echo '<form method="post">';
					wp_nonce_field( 'kayan_seed_action' );
					echo '<p><button type="submit" name="kayan_seed_run" value="1" class="button button-primary"' . ( $has ? ' onclick="return confirm(\'سيتم استبدال محتوى الرئيسية الحالي بالكامل. هل أنت متأكد؟\')"' : '' ) . '>كتابة المحتوى الافتراضي في الإعدادات</button></p>';
				echo '</form>';

				echo '<h3>الأقسام التي ستُكتب (' . count( kayan_seed_home_order() ) . ')</h3>';
				echo '<ol style="columns:2">';
					foreach ( kayan_seed_home_content_map() as $wid => $vals ) {
						echo '<li><code>' . esc_html( $wid ) . '</code> — ' . count( $vals ) . ' حقل</li>';
					}
				echo '</ol>';
			echo '</div>';
		echo '</div>';
	}
}
