<?php
global $yc__widgets__center;

$widget__post = isset( $widget__post ) ? (int) $widget__post : 0;
$widget_type  = $widget__post ? get_post_meta( $widget__post, 'widget_type', true ) : '';
$widget_post_meta = ( is_array( get_post_meta( $widget__post, 'widget_post_meta', true ) ) ) ? get_post_meta( $widget__post, 'widget_post_meta', true ) : array();

# إن لم تصل قائمة القيمة من الـ AJAX: اقرأها من الخيار/الترم مباشرة
if ( empty( $value ) || ! is_array( $value ) ) {
	$value = array();
	if ( ! empty( $id ) && ! empty( $update__type ) && 'option' === $update__type ) {
		$opt = get_option( $id );
		if ( is_array( $opt ) ) {
			$value = $opt;
		}
	}
}

# إيجاد مفتاح الودجت في القائمة (يدعم المفتاح 0)
$Widget__Key = null;
foreach ( $value as $k => $v ) {
	if ( ! is_array( $v ) ) {
		continue;
	}
	$pid = isset( $v['widget_post__id'] ) ? (int) $v['widget_post__id'] : 0;
	if ( $pid && $pid === $widget__post ) {
		$Widget__Key = $k;
		break;
	}
}

# إن لم نجد المفتاح لكن النوع مسجّل: نفتح التعديل بأي حال
$packs = ( isset( $ModelCenter ) && isset( $yc__widgets__center[ $ModelCenter ]['Packs'] ) ) ? $yc__widgets__center[ $ModelCenter ]['Packs'] : array();
$has_pack = ( $widget_type && isset( $packs[ $widget_type ] ) );

if ( $has_pack && null === $Widget__Key ) {
	$Widget__Key = 'p' . $widget__post;
}

if ( $has_pack && null !== $Widget__Key && false !== $Widget__Key ) {
	echo '<div class="widget-fields-InnerElemnt">';
	echo '<div class="widget-fields-area">';
	if ( isset( $packs[ $widget_type ]['fields'] ) && is_array( $packs[ $widget_type ]['fields'] ) ) {
		if ( function_exists( 'kayan_hydrate_widget_meta_for_admin' ) ) {
			$widget_post_meta = kayan_hydrate_widget_meta_for_admin( $widget_type, $widget_post_meta );
		}
		foreach ( $packs[ $widget_type ]['fields'] as $k => $single_field ) {
			if ( ! is_array( $single_field ) || empty( $single_field['type'] ) ) {
				continue;
			}
			$single_field['parent_id'] = 'widget_post_meta_' . $single_widget__uniq . '_' . $id;
			if ( isset( $single_field['id'] ) && isset( $widget_post_meta[ $single_field['id'] ] ) && '' !== $widget_post_meta[ $single_field['id'] ] && null !== $widget_post_meta[ $single_field['id'] ] ) {
				if ( isset( $widget_post_meta[ $single_field['id'] . '_id' ] ) && ! empty( $widget_post_meta[ $single_field['id'] . '_id' ] ) ) {
					$single_field['value']['url'] = $widget_post_meta[ $single_field['id'] ];
					$single_field['value']['id']  = $widget_post_meta[ $single_field['id'] . '_id' ];
				} else {
					$single_field['value'] = $widget_post_meta[ $single_field['id'] ];
				}
				if ( isset( $single_field['create_hide_fields'] ) && is_array( $single_field['create_hide_fields'] ) ) {
					foreach ( $single_field['create_hide_fields'] as $ek => $innerField ) {
						if ( isset( $single_field['value'] ) && $ek == $single_field['value'] && ! empty( $innerField['fields'] ) ) {
							foreach ( $innerField['fields'] as $e => $newfld ) {
								if ( isset( $widget_post_meta[ $newfld['id'] ] ) && ! empty( $widget_post_meta[ $newfld['id'] ] ) ) {
									if ( isset( $widget_post_meta[ $newfld['id'] . '_id' ] ) && ! empty( $widget_post_meta[ $newfld['id'] . '_id' ] ) ) {
										$single_field['create_hide_fields'][ $ek ]['fields'][ $e ]['value']['url'] = $widget_post_meta[ $newfld['id'] ];
										$single_field['create_hide_fields'][ $ek ]['fields'][ $e ]['value']['id']  = $widget_post_meta[ $newfld['id'] . '_id' ];
									} else {
										$single_field['create_hide_fields'][ $ek ]['fields'][ $e ]['value'] = $widget_post_meta[ $newfld['id'] ];
									}
								}
							}
						}
					}
				}
			}
			$this->Fields__Part( $single_field['type'], $single_field );
		}
	} else {
		echo '<p style="padding:16px;color:#666;">لا توجد حقول قابلة للتعديل لهذا القسم.</p>';
	}
	echo '</div>';
	echo '<div class="-YC-widget-save-area">';
	echo '<div class="-YC-widget-action-button -YC-widget-undo-button activable disable" data-un-key="' . esc_attr( $single_widget__uniq ) . '"><span>تراجع </span><i class="fa-solid fa-rotate-left"></i></div>';
	echo '<div class="-YC-widget-action-button -YC-widget-save-button activable disable" data-uniq="' . esc_attr( (string) $Widget__Key ) . '" data-un-key="' . esc_attr( $single_widget__uniq ) . '" data-save-widget="widget_post_meta_' . esc_attr( $single_widget__uniq ) . '_' . esc_attr( $id ) . '"><span>حفظ</span><i class="fa-solid fa-arrow-left"></i></div>';
	echo '</div>';
	echo '</div>';
} else {
	$hint = '';
	if ( ! $widget__post ) {
		$hint = 'معرّف الودجت غير صالح.';
	} elseif ( ! $widget_type ) {
		$hint = 'نوع القسم غير مسجّل على هذا الودجت.';
	} elseif ( ! $has_pack ) {
		$hint = 'القسم «' . esc_html( (string) $widget_type ) . '» غير متاح في مركز الودجات.';
	} else {
		$hint = 'تعذّر فتح إعدادات القسم.';
	}
	echo '<div class="widget-fields-InnerElemnt"><div class="widget-fields-area"><p style="padding:16px;color:#b91c1c;">' . $hint . '</p></div></div>';
}
