<?php
/**
 * KYMEET — كتالوج العطور والبحث
 * الحقول تُولَّد من kymeet-store/manifest.json (نص/صورة/أيقونة/رابط + English).
 */
class kymeet_catalog extends YC__WidgetsMachine {

	public function __construct() {
		parent::__construct();
		$this->widget__name = 'kymeet_catalog';
		$this->folder__name = basename( __DIR__ );
	}

	public function widget__ui( $vars ) {
		kymeet_store_tpl( 'catalog', is_array( $vars ) ? $vars : array() );
	}

	public function widget__setup() {
		global $yc__widgets__center;
		$yc__widgets__center[ $this->folder__name ]['Packs'][ $this->widget__name ] = array(
			'id'           => $this->widget__name,
			'title'        => 'KYMEET — كتالوج العطور والبحث',
			'description'  => 'قسم من تصميم KYMEET لمتجر العطور',
			'screen-shoot' => 'test_URL',
			'fields'       => kymeet_store_fields( 'catalog' ),
		);
	}

	public function Setup() {
		add_action( 'yc__widgets__center', array( $this, 'widget__setup' ) );
	}
}
( new kymeet_catalog )->Setup();
