<?php
/**
 * KYMEET — الأسئلة الشائعة
 * الحقول تُولَّد من kymeet-store/manifest.json (نص/صورة/أيقونة/رابط + English).
 */
class kymeet_faq extends YC__WidgetsMachine {

	public function __construct() {
		parent::__construct();
		$this->widget__name = 'kymeet_faq';
		$this->folder__name = basename( __DIR__ );
	}

	public function widget__ui( $vars ) {
		kymeet_store_tpl( 'faq', is_array( $vars ) ? $vars : array() );
	}

	public function widget__setup() {
		global $yc__widgets__center;
		$yc__widgets__center[ $this->folder__name ]['Packs'][ $this->widget__name ] = array(
			'id'           => $this->widget__name,
			'title'        => 'KYMEET — الأسئلة الشائعة',
			'description'  => 'قسم من تصميم KYMEET لمتجر العطور',
			'screen-shoot' => 'test_URL',
			'fields'       => kymeet_store_fields( 'faq' ),
		);
	}

	public function Setup() {
		add_action( 'yc__widgets__center', array( $this, 'widget__setup' ) );
	}
}
( new kymeet_faq )->Setup();
