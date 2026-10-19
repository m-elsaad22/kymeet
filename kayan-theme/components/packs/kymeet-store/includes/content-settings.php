<?php
/**
 * KYMEET app content — optional blocks (hidden until filled).
 */
defined( 'ABSPATH' ) || exit;

function kymeet_app_content_option() {
	$opt = get_option( 'kymeet_app_content', array() );
	return is_array( $opt ) ? $opt : array();
}

function kymeet_app_content_get( $key_ar, $key_en = '' ) {
	$opt = kymeet_app_content_option();
	$en  = function_exists( 'kymeet_app_en' ) && kymeet_app_en();
	$key = $en && $key_en ? $key_en : $key_ar;
	if ( isset( $opt[ $key ] ) && is_string( $opt[ $key ] ) && '' !== trim( $opt[ $key ] ) ) {
		return trim( $opt[ $key ] );
	}
	if ( $en && isset( $opt[ $key_ar ] ) && is_string( $opt[ $key_ar ] ) && '' !== trim( $opt[ $key_ar ] ) ) {
		return trim( $opt[ $key_ar ] );
	}
	return '';
}

function kymeet_app_content_has( $key_ar, $key_en = '' ) {
	return '' !== kymeet_app_content_get( $key_ar, $key_en );
}

function kymeet_app_content_lines( $key_ar, $key_en = '' ) {
	$raw = kymeet_app_content_get( $key_ar, $key_en );
	if ( '' === $raw ) {
		return array();
	}
	$lines = preg_split( '/\r\n|\r|\n/', $raw );
	$out   = array();
	foreach ( $lines as $line ) {
		$line = trim( $line );
		if ( '' !== $line ) {
			$out[] = $line;
		}
	}
	return $out;
}

add_action( 'admin_menu', 'kymeet_app_content_menu' );
function kymeet_app_content_menu() {
	add_submenu_page(
		'kymeet-hf',
		'KYMEET — المحتوى',
		'المحتوى (App)',
		'manage_options',
		'kymeet-app-content',
		'kymeet_app_content_page'
	);
}

add_action( 'customize_register', 'kymeet_app_customize_register' );
function kymeet_app_customize_register( $wp_customize ) {
	$wp_customize->add_section(
		'kymeet_app_content',
		array(
			'title'    => 'KYMEET — المحتوى',
			'priority' => 160,
		)
	);
	$fields = kymeet_app_content_field_defs();
	foreach ( $fields as $id => $def ) {
		$wp_customize->add_setting(
			'kymeet_app_content[' . $id . ']',
			array(
				'type'              => 'option',
				'capability'        => 'edit_theme_options',
				'sanitize_callback' => 'sanitize_textarea_field',
			)
		);
		$wp_customize->add_control(
			'kymeet_app_content_' . $id,
			array(
				'label'   => $def['label'],
				'section' => 'kymeet_app_content',
				'type'    => 'textarea',
				'settings'=> 'kymeet_app_content[' . $id . ']',
			)
		);
	}
}

function kymeet_app_content_field_defs() {
	return array(
		'announce_ar'       => array( 'label' => 'شريط الإعلان — عربي (فارغ = مخفي)' ),
		'announce_en'       => array( 'label' => 'Announcement — English (empty = hidden)' ),
		'hero_line_ar'      => array( 'label' => 'سطر الهيرو — عربي' ),
		'hero_line_en'      => array( 'label' => 'Hero line — English' ),
		'story_ar'          => array( 'label' => 'قصة العلامة — عربي' ),
		'story_en'          => array( 'label' => 'Brand story — English' ),
		'trust_ar'          => array( 'label' => 'عناصر الثقة — عربي (سطر لكل عنصر)' ),
		'trust_en'          => array( 'label' => 'Trust bullets — English (one per line)' ),
		'newsletter_ar'     => array( 'label' => 'مقدمة النشرة — عربي' ),
		'newsletter_en'     => array( 'label' => 'Newsletter intro — English' ),
		'footer_blurb_ar'   => array( 'label' => 'تعريف الفوتر — عربي' ),
		'footer_blurb_en'   => array( 'label' => 'Footer blurb — English' ),
	);
}

function kymeet_app_content_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$fields = kymeet_app_content_field_defs();
	$opt    = kymeet_app_content_option();
	if ( isset( $_POST['kymeet_app_content_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['kymeet_app_content_nonce'] ) ), 'kymeet_app_content' ) ) {
		$new = array();
		foreach ( array_keys( $fields ) as $id ) {
			if ( isset( $_POST[ $id ] ) ) {
				$new[ $id ] = sanitize_textarea_field( wp_unslash( $_POST[ $id ] ) );
			}
		}
		update_option( 'kymeet_app_content', $new );
		$opt = $new;
		echo '<div class="notice notice-success"><p>تم الحفظ.</p></div>';
	}
	echo '<div class="wrap" dir="rtl"><h1>KYMEET — محتوى الواجهة</h1>';
	echo '<p>اترك الحقل فارغًا لإخفاء القسم عن الزوار. نفس الحقول متاحة في <strong>المظهر ← تخصيص ← KYMEET — المحتوى</strong>.</p>';
	echo '<form method="post">';
	wp_nonce_field( 'kymeet_app_content', 'kymeet_app_content_nonce' );
	echo '<table class="form-table">';
	foreach ( $fields as $id => $def ) {
		$val = isset( $opt[ $id ] ) ? $opt[ $id ] : '';
		echo '<tr><th><label for="' . esc_attr( $id ) . '">' . esc_html( $def['label'] ) . '</label></th>';
		echo '<td><textarea class="large-text" rows="3" id="' . esc_attr( $id ) . '" name="' . esc_attr( $id ) . '">' . esc_textarea( $val ) . '</textarea></td></tr>';
	}
	echo '</table>';
	submit_button( 'حفظ' );
	echo '</form></div>';
}
