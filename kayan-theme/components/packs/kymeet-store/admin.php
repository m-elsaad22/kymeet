<?php
/**
 * KYMEET — صفحة تعديل الهيدر والفوتر (النصوص/الصور/الأيقونات + العربية/الإنجليزية)
 */
defined( 'ABSPATH' ) || exit;

add_action( 'admin_menu', 'kymeet_hf_menu' );
function kymeet_hf_menu() {
	add_menu_page( 'KYMEET — الهيدر والفوتر', 'KYMEET هيدر/فوتر', 'manage_options', 'kymeet-hf', 'kymeet_hf_page', 'dashicons-admin-appearance', 4 );
}

add_action( 'admin_enqueue_scripts', 'kymeet_hf_assets' );
function kymeet_hf_assets( $hook ) {
	if ( 'toplevel_page_kymeet-hf' === $hook ) {
		wp_enqueue_media();
	}
}

function kymeet_hf_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$m   = kymeet_store_manifest();
	$opt = get_option( 'kymeet_hf', array() );
	$opt = is_array( $opt ) ? $opt : array();
	if ( isset( $_POST['kymeet_hf_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['kymeet_hf_nonce'] ) ), 'kymeet_hf' ) ) {
		$new = array();
		foreach ( array( 'header', 'footer' ) as $sec ) {
			if ( empty( $m[ $sec ] ) ) {
				continue;
			}
			foreach ( $m[ $sec ] as $t ) {
				foreach ( array( $t['k'], $t['k'] . '_en' ) as $key ) {
					if ( isset( $_POST[ $key ] ) ) {
						$v = trim( wp_unslash( $_POST[ $key ] ) ); // phpcs:ignore
						$new[ $key ] = ( 'img' === $t['kind'] && ! is_numeric( $v ) ) ? esc_url_raw( $v ) : sanitize_text_field( $v );
					}
				}
			}
		}
		update_option( 'kymeet_hf', $new );
		$opt = $new;
		echo '<div class="notice notice-success"><p>تم الحفظ.</p></div>';
	}
	echo '<div class="wrap" dir="rtl"><h1>KYMEET — الهيدر والفوتر</h1><p>اترك الحقل فارغًا لاستخدام النص الافتراضي. حقول English تظهر عند تبديل اللغة.</p>';
	echo '<form method="post">';
	wp_nonce_field( 'kymeet_hf', 'kymeet_hf_nonce' );
	foreach ( array( 'header' => 'الهيدر والقائمة', 'footer' => 'الفوتر والسلة' ) as $sec => $title ) {
		if ( empty( $m[ $sec ] ) ) {
			continue;
		}
		echo '<h2>' . esc_html( $title ) . '</h2><table class="form-table">';
		foreach ( $m[ $sec ] as $t ) {
			$val = isset( $opt[ $t['k'] ] ) ? $opt[ $t['k'] ] : '';
			echo '<tr><th>' . esc_html( $t['label'] ) . '</th><td>';
			if ( 'img' === $t['kind'] ) {
				echo '<input type="text" class="regular-text kymeet-img" id="f_' . esc_attr( $t['k'] ) . '" name="' . esc_attr( $t['k'] ) . '" value="' . esc_attr( $val ) . '" placeholder="رابط الصورة أو رقمها"> <button type="button" class="button kymeet-pick" data-t="f_' . esc_attr( $t['k'] ) . '">اختيار صورة</button>';
			} else {
				echo '<input type="text" class="large-text" name="' . esc_attr( $t['k'] ) . '" value="' . esc_attr( $val ) . '" placeholder="' . esc_attr( mb_substr( $t['ar'], 0, 60 ) ) . '">';
				if ( in_array( $t['kind'], array( 'text', 'attr' ), true ) ) {
					$en = isset( $opt[ $t['k'] . '_en' ] ) ? $opt[ $t['k'] . '_en' ] : '';
					echo '<br><input type="text" class="large-text" dir="ltr" name="' . esc_attr( $t['k'] . '_en' ) . '" value="' . esc_attr( $en ) . '" placeholder="English: ' . esc_attr( mb_substr( $t['en'], 0, 60 ) ) . '">';
				}
			}
			echo '</td></tr>';
		}
		echo '</table>';
	}
	submit_button( 'حفظ' );
	echo '</form></div>';
	echo '<script>document.querySelectorAll(".kymeet-pick").forEach(function(b){b.addEventListener("click",function(){var f=wp.media({multiple:false});f.on("select",function(){var a=f.state().get("selection").first().toJSON();document.getElementById(b.dataset.t).value=a.url});f.open()})});</script>';
}
