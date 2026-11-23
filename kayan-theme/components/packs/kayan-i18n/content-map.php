<?php
/**
 * Arabic → English map for visible homepage / chrome copy.
 * Generic store phrases only.
 * Person names are intentionally omitted. Titles containing «للإيجار» are skipped at runtime.
 */
if ( ! function_exists( 'kayan_i18n_content_pairs' ) ) {
	function kayan_i18n_content_pairs() {
		return array(
			'تواصل عبر واتساب' => 'Chat on WhatsApp',
			'تسوق العطور' => 'Shop perfumes',
			'الأكثر مبيعًا' => 'Best sellers',
			'أحدث الإصدارات' => 'New arrivals',
			'العلامات التجارية' => 'Brands',
		);
	}
}
