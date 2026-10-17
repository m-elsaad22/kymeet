<?
global $post;

$Styles = array(
	'city-archive' => 'singular/city-archive.css',
	'city__widget' => 'YourColor__Widgets/city__widget.css',
);

$page_title = get_the_title( $post );
$custom_meta = function_exists( 'kayan_seo_get_post_seo_description' )
	? kayan_seo_get_post_seo_description( $post->ID )
	: get_post_meta( $post->ID, 'kayan_meta_description', true );
$post_content = $post->post_content;
$post_content = str_replace( '<br/>', PHP_EOL, $post_content );
$post_content = str_replace( '&nbsp;', ' ', $post_content );
$post_content = strip_tags( $post_content );

if ( ! empty( $custom_meta ) ) {
	$intro_content = $custom_meta;
} elseif ( ! empty( $post_content ) ) {
	$intro_content = $post_content;
} else {
	$intro_content = 'نغطي أهم المدن بخدمات منزلية احترافية — اختر مدينتك واطلب الخدمة الآن.';
}

if ( strlen( $intro_content ) > 400 ) {
	$intro_content = mb_substr( $intro_content, 0, 400, 'utf-8' ) . '... <a href="javascript:void(0);" data-button="readmore-objects" data-object-type="post_type" data-object-name="' . esc_attr( $post->post_type ) . '" data-object-id="' . (int) $post->ID . '" class="readmore--category-item">قراءة المزيد</a>';
}

$page_background = get_post_meta( $post->ID, 'page_back_image', true );
if ( empty( $page_background ) ) {
	$page_background = get_option( 'background_image' );
}

$cities = get_terms(
	array(
		'taxonomy' => 'city',
		'number' => 60,
		'hide_empty' => false,
		'orderby' => 'name',
		'order' => 'ASC',
	)
);
$cities = is_array( $cities ) ? $cities : array();
$city_count = count( $cities );
$site_name = function_exists( 'kayan_seo_get_site_name' ) ? kayan_seo_get_site_name() : get_bloginfo( 'name' );

$this->Part( 'header', array( 'Styles' => $Styles ) );

echo '<div class="-primary-body kayan-city-archive kayan-cities-index">';

	echo '<div class="kayan-city-hero' . ( ! empty( $page_background ) ? ' has-image' : '' ) . '">';
		if ( ! empty( $page_background ) ) {
			echo '<div class="kayan-city-hero__media" aria-hidden="true">';
				echo '<img src="' . esc_url( $page_background ) . '" alt="' . esc_attr( $page_title ) . '" width="1200" height="480" loading="eager" fetchpriority="high" decoding="async" />';
			echo '</div>';
		}
		echo '<div class="container">';
			echo '<div class="kayan-city-hero__inner">';
				echo '<div class="kayan-city-hero__content">';
					echo '<p class="kayan-city-hero__eyebrow">' . esc_html( $site_name ) . '</p>';
					echo '<h1 class="kayan-city-hero__title">' . esc_html( $page_title ) . '</h1>';
					echo '<div class="kayan-city-hero__desc --archive--be-content">' . $intro_content . '</div>';
					if ( $city_count > 0 ) {
						echo '<p class="kayan-city-hero__stat"><i class="fa-solid fa-location-dot" aria-hidden="true"></i> ' . (int) $city_count . ' مدينة نخدمها</p>';
					}
				echo '</div>';
			echo '</div>';
		echo '</div>';
	echo '</div>';

	echo '<div class="-Yc-breadcrumb-">';
		echo '<div class="container">';
			echo '<div class="YC-BreadCrumb -BreadCrumb-PT-page">';
				Breadcrumb();
			echo '</div>';
		echo '</div>';
	echo '</div>';

	echo '<div class="-page--container-sidebars">';
		echo '<div class="-YC-Widgets-Inner-Row">';
			echo '<div class="container">';
				echo '<div class="kayan-city-section-head">';
					echo '<h2 class="kayan-city-section-head__title">اختر مدينتك</h2>';
					echo '<p class="kayan-city-section-head__desc">تصفّح الخدمات المنزلية المتوفرة في كل مدينة.</p>';
				echo '</div>';

				if ( ! empty( $cities ) ) {
					echo '<div class="-cityBox-widgets-items-s1 -page--cites--boxes">';
						$uniq = uniqid();
						foreach ( $cities as $city ) {
							$city_url = get_term_link( $city );
							if ( is_wp_error( $city_url ) ) {
								continue;
							}
							$icon = get_term_meta( $city->term_id, 'icon', true );
							$city_image = function_exists( 'kayan_seo_get_term_image_url' ) ? kayan_seo_get_term_image_url( $city->term_id ) : '';
							$description = strip_tags( $city->description );
							$words = preg_split( '/\s+/u', trim( $description ) );
							$snippet = implode( ' ', array_slice( $words, 0, 12 ) );
							if ( empty( $snippet ) ) {
								$snippet = 'خدمات منزلية في ' . $city->name;
							}

							echo '<div class="--single--city--boxitem kayan-cities-index__item" data-trigger-action="' . esc_attr( $uniq ) . '">';
								echo '<div class="--cites-single-box-">';
									if ( ! empty( $city_image ) ) {
										echo '<div class="kayan-cities-index__thumb"><img src="' . esc_url( $city_image ) . '" alt="' . esc_attr( $city->name ) . '" loading="lazy" decoding="async" width="320" height="180" /></div>';
									}
									echo '<div class="-city-wrap-">';
										echo '<div class="--city--logoIcon">';
											if ( ! empty( $icon ) ) {
												echo '<div class="--citeyes-icon-in">' . $icon . '</div>';
											} else {
												echo '<div class="--citeyes-icon-in"><i class="fa-solid fa-house"></i></div>';
											}
										echo '</div>';
										echo '<div class="--city--info-boxitem">';
											echo '<a href="' . esc_url( $city_url ) . '" title="' . esc_attr( $city->name ) . '" data-trigger-url="' . esc_attr( $uniq ) . '"><h3 class="--city-name--">' . esc_html( $city->name ) . '</h3></a>';
											echo '<p class="--city-content--">' . esc_html( $snippet ) . '</p>';
											if ( (int) $city->count > 0 ) {
												echo '<span class="kayan-cities-index__count">' . (int) $city->count . ' خدمة</span>';
											}
										echo '</div>';
									echo '</div>';
								echo '</div>';
							echo '</div>';
						}
					echo '</div>';
				} else {
					echo '<p class="kayan-cities-index__empty">لم تُضف مدن بعد. أضف مدناً من لوحة التحكم → المقالات → المدن.</p>';
				}
			echo '</div>';
		echo '</div>';
	echo '</div>';

echo '</div>';

$this->Part( 'footer', array( 'Styles' => $Styles ) );
