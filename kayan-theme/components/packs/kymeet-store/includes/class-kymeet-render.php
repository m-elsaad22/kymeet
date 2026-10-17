<?php
/**
 * Markup for KYMEET homepage widgets (WooCommerce-driven).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Kymeet_Render {

	public static function hero(): void {
		$data  = kymeet_get_data_payload();
		$slides = array();
		foreach ( $data['NEW'] as $slug ) {
			foreach ( $data['products'] as $p ) {
				if ( $p['id'] === $slug ) {
					$slides[] = $p;
					break;
				}
			}
			if ( count( $slides ) >= 5 ) {
				break;
			}
		}
		if ( empty( $slides ) ) {
			$slides = array_slice( $data['products'], 0, 5 );
		}
		echo '<section class="hero hero-c hero-slider-only hero-luxury" data-hc>';
		echo '<div class="hero-grid"></div><div class="hc-track">';
		foreach ( $slides as $p ) {
			$price = kymeet_money( $p['price'] );
			echo '<div class="hc-slide hc-ban"><div class="wrap hcb">';
			echo '<div class="hcb-txt"><span class="tag en">KYMEET</span><small>' . esc_html( $p['brand'] ) . '</small>';
			echo '<h2>' . esc_html( $p['name'] ) . '</h2>';
			echo '<div class="hcb-price"><b>' . esc_html( $price ) . '</b> ج.م</div>';
			echo '<div class="hcb-act">';
			echo '<button type="button" class="btn btn-gold" onclick="addToCart(\'' . esc_js( $p['id'] ) . '\',event,' . (int) $p['pid'] . ')"><i class="fas fa-bag-shopping"></i> أضف للسلة</button>';
			echo '<a class="btn btn-ghost-w" href="' . esc_url( $p['url'] ) . '">التفاصيل</a>';
			echo '</div></div>';
			echo '<a class="hcb-img" href="' . esc_url( $p['url'] ) . '"><img src="' . esc_url( $p['image'] ) . '" alt="' . esc_attr( $p['name'] ) . '" data-brand="' . esc_attr( $p['brand'] ) . '" loading="lazy" onerror="imgErr(this)"></a>';
			echo '</div></div>';
		}
		echo '</div><div class="hc-dots"></div></section>';
	}

	public static function discover(): void {
		$data = kymeet_get_data_payload();
		echo '<section class="sec" id="discover"><div class="wrap">';
		echo '<div class="shead rv"><span class="tag en">DISCOVER</span><h2>اكتشف عالَم <em>KYMEET</em></h2><p>تشكيلة من بيوت عطور مختلفة؛ اختر العلامة التي تستهويك.</p></div>';
		echo '<div class="kslider" data-auto="3800"><div class="kslider-track kbrands">';
		foreach ( $data['BR'] as $brand ) {
			$img = '';
			foreach ( $data['products'] as $p ) {
				if ( $p['brand'] === $brand ) {
					$img = $p['image'];
					break;
				}
			}
			$count = count( array_filter( $data['products'], static fn( $x ) => $x['brand'] === $brand ) );
			echo '<a class="kbrand rv" href="' . esc_url( wc_get_page_permalink( 'shop' ) ) . '" data-b="' . esc_attr( $brand ) . '" onclick="pickBrand(this.dataset.b);return false">';
			echo '<span class="bk-img fit">';
			if ( $img ) {
				echo '<img src="' . esc_url( $img ) . '" alt="' . esc_attr( $brand ) . '" loading="lazy" onerror="this.outerHTML=\'<i class=&quot;fas fa-gem&quot;></i>\'">';
			} else {
				echo '<i class="fas fa-gem"></i>';
			}
			echo '</span><span class="bk-name"><b>' . esc_html( $brand ) . '</b><small>' . esc_html( $count ) . ' عطر</small></span></a>';
		}
		echo '</div></div></div></section>';
	}

	public static function product_slider( string $id, string $tag_en, string $title, string $subtitle, string $grid_id ): void {
		echo '<section class="sec" id="' . esc_attr( $id ) . '"><div class="wrap">';
		echo '<div class="shead rv"><span class="tag en">' . esc_html( $tag_en ) . '</span><h2>' . wp_kses_post( $title ) . '</h2><p>' . esc_html( $subtitle ) . '</p></div>';
		echo '<div class="kslider" data-auto="4200"><div class="kslider-track" id="' . esc_attr( $grid_id ) . '"></div></div>';
		if ( 'bestsellers' === $id ) {
			echo '<div class="more-row"><a href="#catalog" class="btn btn-outline-royal">اكتشف كل العطور <i class="fas fa-arrow-left"></i></a></div>';
		}
		echo '</div></section>';
	}

	public static function brands_cloud(): void {
		$data = kymeet_get_data_payload();
		echo '<section class="sec" id="brands"><div class="wrap"><div class="shead rv"><span class="tag en">HOUSES</span><h2>علاماتٌ لها <em>حضورها</em></h2><p>اختر علامة لعرض المنتجات المتاحة.</p></div></div>';
		echo '<div class="brand-cloud"><div class="brand-track">';
		foreach ( $data['BR'] as $brand ) {
			echo '<span class="brand-chip-x en" onclick="pickBrand(this.textContent.trim())"><i class="fas fa-gem"></i> ' . esc_html( $brand ) . '</span>';
		}
		echo '</div></div></section>';
	}

	public static function story(): void {
		$count = count( kymeet_get_product_rows() );
		$brands = count( kymeet_get_data_payload()['BR'] );
		echo '<section class="sec story" id="story"><div class="wrap"><div class="rv-l">';
		echo '<span class="tag en" style="color:var(--gold-400);letter-spacing:.55em;font-size:12.5px;display:inline-block;margin-bottom:14px">OUR STORY</span>';
		echo '<h2 style="color:#fff">من أرض الحكايات: KYMEET — عطورٌ <em>تترك أثرًا</em></h2>';
		echo '<p style="margin-top:18px">اسمٌ يستحضر أرضًا عرفت أسرار العطر وخلود الأثر. في <strong>KYMEET</strong> نلتقي بهذا الإرث بروح عصرية، ونقدم تشكيلة عطور أصلية من بيوت مختلفة؛ لتختار منها ما يشبهك.</p>';
		echo '<div class="stats-row">';
		echo '<div class="stat-box"><b>' . (int) $count . '</b><small>عطرًا في التشكيلة</small></div>';
		echo '<div class="stat-box"><b>' . (int) $brands . '</b><small>علامة تجارية</small></div>';
		echo '</div>';
		echo '<a href="#catalog" class="btn btn-outline-light">تصفح التشكيلة <i class="fas fa-arrow-left"></i></a>';
		echo '</div></div></section>';
	}

	public static function catalog(): void {
		$max = kymeet_get_data_payload()['maxPrice'];
		echo '<section class="sec" id="catalog"><div class="wrap">';
		echo '<div class="shead rv"><span class="tag en">COLLECTION</span><h2>اعثر على <em>عطرك</em></h2><p>ابحث بالاسم أو العلامة، رتّب النتائج، وحدد نطاق السعر.</p></div>';
		echo '<div class="toolbar rv"><div class="tsearch"><i class="fas fa-magnifying-glass"></i><input id="q" type="search" placeholder="ابحث بالاسم أو العلامة" aria-label="ابحث"></div>';
		echo '<select id="fb" aria-label="العلامة"><option value="">كل العلامات</option></select>';
		echo '<select id="so" aria-label="ترتيب"><option value="">ترتيب مميز</option><option value="a">السعر: من الأقل</option><option value="d">السعر: من الأعلى</option><option value="n">الاسم: أ إلى ي</option></select>';
		echo '<label class="trng"><span id="mv">حتى ' . esc_html( kymeet_money( $max ) ) . ' ج.م</span><input id="mx" type="range" min="800" max="' . (int) $max . '" step="100" value="' . (int) $max . '" aria-label="حتى"></label></div>';
		echo '<div class="cnt" id="cnt"></div><div class="p-grid" id="catGrid"></div></div></section>';
	}

	public static function faq(): void {
		$items = array(
			array( 'هل الأسعار محدثة؟', 'الأسعار المعروضة هي الأسعار وقت التصفح، وقد تتغير. نؤكد السعر النهائي عند تأكيد طلبك.' ),
			array( 'كيف أطلب منتجًا؟', 'أضف العطر إلى السلة ثم اضغط إتمام الشراء، واكتب بياناتك لإتمام الطلب.' ),
			array( 'هل المنتجات أصلية؟', 'نعرض منتجات من تشكيلة المتجر الحالية ببيانات وصور من WooCommerce.' ),
		);
		echo '<section class="sec" id="faq"><div class="wrap"><div class="shead rv"><span class="tag en">FAQ</span><h2>أسئلة <em>شائعة</em></h2></div><div class="faq-wrap">';
		foreach ( $items as $item ) {
			echo '<div class="faq-item rv"><div class="faq-q" onclick="faqT(this)"><span>' . esc_html( $item[0] ) . '</span><i class="fas fa-chevron-down"></i></div>';
			echo '<div class="faq-a"><p>' . esc_html( $item[1] ) . '</p></div></div>';
		}
		echo '</div></div></section>';
	}

	public static function cta(): void {
		echo '<section class="sec fcta" id="contact"><div class="wrap">';
		echo '<h2 class="rv">جاهز لاقتناء <em>عطرك المميز؟</em></h2>';
		echo '<p class="rv">تسوّق المجموعة الكاملة، أو تواصل معنا ليساعدك فريقنا في اختيار العطر الذي يشبهك.</p>';
		echo '<div class="fcta-btns rv">';
		echo '<a href="#" class="btn btn-wa cfg-wa" data-cfg="wa"><i class="fab fa-whatsapp"></i> تواصل واتساب</a>';
		echo '<a href="#" class="btn btn-gold cfg-tel" data-cfg="tel"><i class="fas fa-phone"></i> اتصل الآن</a>';
		echo '<a href="#catalog" class="btn btn-outline-light"><i class="fas fa-bag-shopping"></i> تسوق الآن</a>';
		echo '</div></div></section>';
	}
}
