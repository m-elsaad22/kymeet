<?php
defined( 'ABSPATH' ) || exit;
/**
 * kayan-cpt — أنواع المحتوى لمتجر العطور (KYMEET)
 * العطور (perfumes) + العلامات التجارية (perfume_brand) + التصنيفات (perfume_category)
 * + التقييمات (reviews) + الأسئلة الشائعة (faqs)
 */
if ( ! function_exists( 'kayan_cpt_register' ) ) {

	function kayan_cpt_labels( $plural, $singular ) {
		return array(
			'name'               => $plural,
			'singular_name'      => $singular,
			'menu_name'          => $plural,
			'name_admin_bar'     => $singular,
			'add_new'            => 'إضافة جديد',
			'add_new_item'       => 'إضافة ' . $singular,
			'new_item'           => $singular . ' جديد',
			'edit_item'          => 'تعديل ' . $singular,
			'view_item'          => 'عرض ' . $singular,
			'all_items'          => 'كل ' . $plural,
			'search_items'       => 'بحث في ' . $plural,
			'not_found'          => 'لا توجد عناصر.',
			'not_found_in_trash' => 'لا توجد عناصر في سلة المهملات.',
		);
	}

	function kayan_cpt_register() {
		register_post_type( 'perfumes', array(
			'labels'        => kayan_cpt_labels( 'العطور', 'عطر' ),
			'public'        => ! class_exists( 'WooCommerce' ),
			'show_ui'       => ! class_exists( 'WooCommerce' ),
			'has_archive'   => 'perfumes',
			'menu_position' => 5,
			'menu_icon'     => 'dashicons-products',
			'rewrite'       => array( 'slug' => 'perfume', 'with_front' => false ),
			'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes' ),
			'show_in_rest'  => true,
			'rest_base'     => 'perfumes',
		) );
		register_post_type( 'reviews', array(
			'labels'        => kayan_cpt_labels( 'تقييمات العملاء', 'تقييم' ),
			'public'        => true,
			'has_archive'   => 'reviews',
			'menu_position' => 6,
			'menu_icon'     => 'dashicons-star-filled',
			'rewrite'       => array( 'slug' => 'reviews', 'with_front' => false ),
			'supports'      => array( 'title', 'editor', 'thumbnail', 'custom-fields' ),
			'show_in_rest'  => true,
			'rest_base'     => 'reviews',
		) );
		register_post_type( 'faqs', array(
			'labels'        => kayan_cpt_labels( 'الأسئلة الشائعة', 'سؤال' ),
			'public'        => true,
			'has_archive'   => 'faqs',
			'menu_position' => 7,
			'menu_icon'     => 'dashicons-editor-help',
			'rewrite'       => array( 'slug' => 'faqs', 'with_front' => false ),
			'supports'      => array( 'title', 'editor', 'custom-fields' ),
			'show_in_rest'  => true,
			'rest_base'     => 'faqs',
		) );

		register_taxonomy( 'perfume_brand', array( 'perfumes' ), array(
			'labels'            => array(
				'name'          => 'العلامات التجارية',
				'singular_name' => 'علامة تجارية',
				'menu_name'     => 'العلامات التجارية',
				'all_items'     => 'كل العلامات',
				'edit_item'     => 'تعديل العلامة',
				'add_new_item'  => 'إضافة علامة',
				'search_items'  => 'بحث في العلامات',
			),
			'hierarchical'      => false,
			'public'            => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'brand', 'with_front' => false ),
		) );
		register_taxonomy( 'perfume_category', array( 'perfumes' ), array(
			'labels'            => array(
				'name'          => 'تصنيفات العطور',
				'singular_name' => 'تصنيف',
				'menu_name'     => 'تصنيفات العطور',
				'all_items'     => 'كل التصنيفات',
				'edit_item'     => 'تعديل التصنيف',
				'add_new_item'  => 'إضافة تصنيف',
				'search_items'  => 'بحث في التصنيفات',
			),
			'hierarchical'      => true,
			'public'            => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'perfume-category', 'with_front' => false, 'hierarchical' => true ),
		) );
	}
	add_action( 'init', 'kayan_cpt_register', 5 );

	function kayan_cpt_flush() {
		kayan_cpt_register();
		flush_rewrite_rules();
	}
	add_action( 'after_switch_theme', 'kayan_cpt_flush' );

	function kayan_cpt_maybe_flush() {
		if ( get_option( 'kayan_cpt_flushed_v1436' ) !== 'yes' ) {
			flush_rewrite_rules();
			update_option( 'kayan_cpt_flushed_v1436', 'yes' );
		}
	}
	add_action( 'init', 'kayan_cpt_maybe_flush', 99 );
}
