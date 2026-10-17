<?php
/**
 * KYMEET widget classes (YourColor widget center).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class Kymeet_Widget_Base {

	public $widget__name;
	public $folder__name = 'KYMEET';

	public function widget__setup(): void {
		global $yc__widgets__center;
		if ( ! isset( $yc__widgets__center[ $this->folder__name ] ) ) {
			$yc__widgets__center[ $this->folder__name ] = array(
				'name'  => $this->folder__name,
				'path'  => KYMEET_STORE_PATH,
				'Packs' => array(),
			);
		}
		$yc__widgets__center[ $this->folder__name ]['Packs'][ $this->widget__name ] = array(
			'id'          => $this->widget__name,
			'title'       => $this->widget__name,
			'description' => 'KYMEET Store',
			'fields'      => array(),
		);
	}
}

class kymeet_hero extends Kymeet_Widget_Base {
	public function __construct() {
		$this->widget__name = 'kymeet_hero';
	}
	public function widget__ui( $vars ) {
		Kymeet_Render::hero();
	}
}

class kymeet_discover extends Kymeet_Widget_Base {
	public function __construct() {
		$this->widget__name = 'kymeet_discover';
	}
	public function widget__ui( $vars ) {
		Kymeet_Render::discover();
	}
}

class kymeet_bestsellers extends Kymeet_Widget_Base {
	public function __construct() {
		$this->widget__name = 'kymeet_bestsellers';
	}
	public function widget__ui( $vars ) {
		Kymeet_Render::product_slider( 'bestsellers', 'BESTSELLERS', 'الأكثر <em>طلبًا</em>', 'منتجات بارزة من تشكيلة KYMEET.', 'bsGrid' );
	}
}

class kymeet_brands extends Kymeet_Widget_Base {
	public function __construct() {
		$this->widget__name = 'kymeet_brands';
	}
	public function widget__ui( $vars ) {
		Kymeet_Render::brands_cloud();
	}
}

class kymeet_story extends Kymeet_Widget_Base {
	public function __construct() {
		$this->widget__name = 'kymeet_story';
	}
	public function widget__ui( $vars ) {
		Kymeet_Render::story();
	}
}

class kymeet_curated extends Kymeet_Widget_Base {
	public function __construct() {
		$this->widget__name = 'kymeet_curated';
	}
	public function widget__ui( $vars ) {
		$data = kymeet_get_data_payload();
		$p    = $data['products'][0] ?? null;
		if ( ! $p ) {
			return;
		}
		echo '<section class="sec" id="curated"><div class="wrap"><div class="edit rv">';
		echo '<div class="edit-img"><div class="edit-ring"></div><img src="' . esc_url( $p['image'] ) . '" alt="' . esc_attr( $p['name'] ) . '" loading="lazy"></div>';
		echo '<div class="edit-txt"><span class="tag en">مختارات كيميت</span><h2>تفاصيل صغيرة.<br><em>أثرٌ يبقى طويلًا.</em></h2>';
		echo '<p>' . esc_html( $p['name'] ) . ' — ' . esc_html( $p['brand'] ) . '</p>';
		echo '<button class="btn btn-gold" onclick="openP(\'' . esc_js( $p['id'] ) . '\')">اكتشف العطر <i class="fas fa-arrow-left"></i></button>';
		echo '</div></div></div></section>';
	}
}

class kymeet_new extends Kymeet_Widget_Base {
	public function __construct() {
		$this->widget__name = 'kymeet_new';
	}
	public function widget__ui( $vars ) {
		Kymeet_Render::product_slider( 'new', 'NEW ARRIVALS', 'إصدارات <em>حديثة</em>', 'وصل حديثًا — أحدث المنتجات في المتجر.', 'newGrid' );
	}
}

class kymeet_faq extends Kymeet_Widget_Base {
	public function __construct() {
		$this->widget__name = 'kymeet_faq';
	}
	public function widget__ui( $vars ) {
		Kymeet_Render::faq();
	}
}

class kymeet_catalog extends Kymeet_Widget_Base {
	public function __construct() {
		$this->widget__name = 'kymeet_catalog';
	}
	public function widget__ui( $vars ) {
		Kymeet_Render::catalog();
	}
}

class kymeet_cta extends Kymeet_Widget_Base {
	public function __construct() {
		$this->widget__name = 'kymeet_cta';
	}
	public function widget__ui( $vars ) {
		Kymeet_Render::cta();
	}
}

$kymeet_widget_classes = array(
	'kymeet_hero',
	'kymeet_discover',
	'kymeet_bestsellers',
	'kymeet_brands',
	'kymeet_story',
	'kymeet_curated',
	'kymeet_new',
	'kymeet_faq',
	'kymeet_catalog',
	'kymeet_cta',
);

add_action(
	'yc__widgets__center',
	static function () use ( $kymeet_widget_classes ) {
		foreach ( $kymeet_widget_classes as $class ) {
			if ( class_exists( $class ) ) {
				( new $class() )->widget__setup();
			}
		}
	},
	20
);

foreach ( $kymeet_widget_classes as $class ) {
	if ( class_exists( $class ) ) {
		new $class();
	}
}
