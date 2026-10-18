<?php
defined( 'ABSPATH' ) || exit;
header( 'Content-Type: application/json; charset=utf-8' );

$json = array();

$field_arguments = array();
if ( ! empty( $Ajax__data['field_arguments'] ) ) {
	if ( is_string( $Ajax__data['field_arguments'] ) ) {
		$decoded = json_decode( base64_decode( $Ajax__data['field_arguments'] ), true );
		$field_arguments = is_array( $decoded ) ? $decoded : array();
	} elseif ( is_array( $Ajax__data['field_arguments'] ) ) {
		$field_arguments = $Ajax__data['field_arguments'];
	}
}

$field_id     = isset( $field_arguments['id'] ) ? $field_arguments['id'] : '';
$update_type  = isset( $field_arguments['update__type'] ) ? $field_arguments['update__type'] : 'option';
$widget_post  = isset( $Ajax__data['widget__post'] ) ? (int) $Ajax__data['widget__post'] : 0;
$widget_uniq  = isset( $Ajax__data['widgetUniq'] ) ? $Ajax__data['widgetUniq'] : null;

# قراءة القائمة الحالية
$value = array();
if ( $field_id && 'option' === $update_type ) {
	$cur = get_option( $field_id );
	if ( is_array( $cur ) ) {
		$value = $cur;
	}
} elseif ( $field_id && 'term' === $update_type && ! empty( $Ajax__data['tag_ID'] ) ) {
	$cur = get_term_meta( (int) $Ajax__data['tag_ID'], $field_id, true );
	if ( is_array( $cur ) ) {
		$value = $cur;
	}
}

# إن وصلت قيمة من النموذج نفضّلها إن كانت مصفوفة
if ( $field_id && isset( $Ajax__data[ $field_id ] ) && is_array( $Ajax__data[ $field_id ] ) ) {
	$value = $Ajax__data[ $field_id ];
}

$removed = false;

# 1) حذف بالمفتاح uniq
if ( null !== $widget_uniq && '' !== $widget_uniq && is_array( $value ) ) {
	if ( array_key_exists( $widget_uniq, $value ) ) {
		unset( $value[ $widget_uniq ] );
		$removed = true;
	}
	# مفاتيح رقمية قادمة كنص
	if ( ! $removed && is_numeric( $widget_uniq ) && array_key_exists( (int) $widget_uniq, $value ) ) {
		unset( $value[ (int) $widget_uniq ] );
		$removed = true;
	}
}

# 2) حذف بمعرّف البوست
if ( ! $removed && $widget_post && is_array( $value ) ) {
	foreach ( $value as $k => $v ) {
		if ( is_array( $v ) && isset( $v['widget_post__id'] ) && (int) $v['widget_post__id'] === $widget_post ) {
			unset( $value[ $k ] );
			$removed = true;
			break;
		}
	}
}

if ( $removed ) {
	$value = array_values( $value ); // إعادة فهرسة حتى لا تبقى فجوات
	if ( $field_id && 'option' === $update_type ) {
		update_option( $field_id, $value );
	} elseif ( $field_id && 'term' === $update_type && ! empty( $Ajax__data['tag_ID'] ) ) {
		update_term_meta( (int) $Ajax__data['tag_ID'], $field_id, $value );
	}
	if ( $widget_post ) {
		wp_delete_post( $widget_post, true );
	}
	$json['alert'] = 'sucsses';
	$json['ok']    = 1;
} else {
	# حتى لو لم تُوجد في القائمة: احذف البوست إن وُجد
	if ( $widget_post ) {
		wp_delete_post( $widget_post, true );
		$json['alert'] = 'sucsses';
		$json['ok']    = 1;
	} else {
		$json['alert'] = 'error';
		$json['ok']    = 0;
		$json['msg']   = 'تعذّر تحديد القسم للحذف';
	}
}

echo wp_json_encode( $json );
