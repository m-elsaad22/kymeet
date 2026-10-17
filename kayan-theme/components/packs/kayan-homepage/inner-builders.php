<?
/**
 * Inner page layout markup — uses kayan-inner.css + kayan-home design tokens.
 */

if ( ! function_exists( 'kayan_homepage_uses_inner_layout' ) ) {
	function kayan_homepage_uses_inner_layout() {
		return function_exists( 'kayan_homepage_inner_page_request' ) && kayan_homepage_inner_page_request();
	}
}

if ( ! function_exists( 'kayan_homepage_render_inner_hero' ) ) {
	/**
	 * @param array $args title, subtitle, image_url, image_alt, category_term, total_rate.
	 */
	function kayan_homepage_render_inner_hero( $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'title'          => '',
				'subtitle'       => '',
				'image_url'      => '',
				'image_alt'      => '',
				'category_term'  => null,
				'total_rate'     => 0,
			)
		);

		$title     = trim( (string) $args['title'] );
		$subtitle  = trim( (string) $args['subtitle'] );
		$image_url = trim( (string) $args['image_url'] );
		$image_alt = trim( (string) $args['image_alt'] );
		if ( $image_alt === '' ) {
			$image_alt = $title;
		}

		echo '<section class="kayan-inner-hero">';
		if ( $image_url !== '' ) {
			echo '<div class="kayan-inner-hero__media">';
			echo '<img src="' . esc_url( $image_url ) . '" alt="' . esc_attr( $image_alt ) . '" loading="eager" decoding="async" />';
			echo '</div>';
			echo '<div class="kayan-inner-hero__overlay" aria-hidden="true"></div>';
		}
		echo '<div class="kayan-inner-hero__content">';
		if ( $title !== '' ) {
			echo '<h1 class="kayan-inner-hero__title">' . esc_html( $title ) . '</h1>';
		}
		if ( $subtitle !== '' ) {
			echo '<p class="kayan-inner-hero__sub">' . esc_html( $subtitle ) . '</p>';
		}

		$has_meta = ( is_object( $args['category_term'] ) && ! empty( $args['category_term']->name ) )
			|| ( (float) $args['total_rate'] > 0 );
		if ( $has_meta ) {
			echo '<div class="kayan-inner-hero__meta">';
			if ( is_object( $args['category_term'] ) && ! empty( $args['category_term']->name ) ) {
				$term      = $args['category_term'];
				$term_link = isset( $term->term_link ) ? $term->term_link : get_term_link( $term );
				$term_link = is_wp_error( $term_link ) ? '#' : $term_link;
				$color     = ! empty( $term->uicolor ) ? $term->uicolor : 'var(--turq)';
				echo '<a class="kayan-inner-hero__badge" href="' . esc_url( $term_link ) . '" style="--badge-color:' . esc_attr( $color ) . '">';
				if ( ! empty( $term->icon ) ) {
					echo '<i class="' . esc_attr( $term->icon ) . '"></i> ';
				}
				echo esc_html( $term->name );
				echo '</a>';
			}
			if ( (float) $args['total_rate'] > 0 ) {
				$rate = round( (float) $args['total_rate'], 1 );
				echo '<div class="kayan-inner-hero__rating" aria-label="' . esc_attr( sprintf( 'التقييم %.1f من 5', $rate ) ) . '">';
				for ( $i = 1; $i <= 5; $i++ ) {
					if ( $rate >= $i ) {
						echo '<i class="fas fa-star"></i>';
					} elseif ( $rate >= ( $i - 0.5 ) ) {
						echo '<i class="fas fa-star-half-stroke"></i>';
					} else {
						echo '<i class="far fa-star"></i>';
					}
				}
				echo '<span class="kayan-inner-hero__rating-value">' . esc_html( number_format_i18n( $rate, 1 ) ) . '</span>';
				echo '</div>';
			}
			echo '</div>';
		}

		echo '</div>';
		echo '</section>';
	}
}

if ( ! function_exists( 'kayan_homepage_render_inner_breadcrumb' ) ) {
	function kayan_homepage_render_inner_breadcrumb() {
		echo '<nav class="kayan-inner-breadcrumb" aria-label="Breadcrumb">';
		echo '<div class="kayan-inner-breadcrumb__inner">';
		if ( function_exists( 'Breadcrumb' ) ) {
			ob_start();
			Breadcrumb();
			$html = ob_get_clean();
			if ( $html !== '' ) {
				$html = str_replace( 'class="BreadcrumbsFilters"', 'class="BreadcrumbsFilters kayan-inner-breadcrumb__trail"', $html );
				echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Breadcrumb() markup.
			}
		}
		echo '</div>';
		echo '</nav>';
	}
}

if ( ! function_exists( 'kayan_homepage_render_contact_box' ) ) {
	/**
	 * @param int|null   $post_id             Post/page ID for contact resolver.
	 * @param string|null $phonenumber_fallback Fallback when resolver is unavailable.
	 * @param string|null $whatsapp_fallback    Fallback when resolver is unavailable.
	 */
	function kayan_homepage_render_contact_box( $post_id = null, $phonenumber_fallback = null, $whatsapp_fallback = null ) {
		$post_id = (int) $post_id;
		$show_call = ! function_exists( 'kayan_ui_show_call_button' ) || kayan_ui_show_call_button();

		if ( function_exists( 'kayan_hp_resolve_phone' ) ) {
			$phone = kayan_hp_resolve_phone( $post_id > 0 ? $post_id : null );
		} else {
			$phone = trim( (string) $phonenumber_fallback );
		}

		if ( function_exists( 'kayan_hp_resolve_tel_url' ) ) {
			$tel_url = kayan_hp_resolve_tel_url( $post_id > 0 ? $post_id : null );
		} else {
			$digits  = preg_replace( '/\D+/', '', (string) $phone );
			$tel_url = $digits !== '' ? 'tel:+' . ltrim( $digits, '+' ) : '#';
		}

		if ( function_exists( 'kayan_hp_resolve_whatsapp_url' ) ) {
			$wa_url = kayan_hp_resolve_whatsapp_url( $post_id > 0 ? $post_id : null );
		} else {
			$wa_raw = trim( (string) $whatsapp_fallback );
			if ( function_exists( 'kayan_wa_build_url' ) && $wa_raw !== '' ) {
				$wa_url = kayan_wa_build_url( $wa_raw, null, $post_id > 0 ? get_the_title( $post_id ) : '' );
			} else {
				$digits = preg_replace( '/\D+/', '', $wa_raw );
				$wa_url = $digits !== '' ? 'https://wa.me/' . $digits : '#';
			}
		}

		$btn_call = function_exists( 'kayan_i18n_t' ) ? kayan_i18n_t( 'btn_call', 'اتصل' ) : 'اتصل';
		$btn_wa   = function_exists( 'kayan_i18n_t' ) ? kayan_i18n_t( 'btn_whatsapp_full', 'واتساب' ) : 'واتساب';

		echo '<div class="kayan-contact-box">';
		echo '<h3 class="kayan-contact-box__title">';
		echo esc_html( function_exists( 'kayan_homepage_ui_string' ) ? kayan_homepage_ui_string( 'btn_service', 'تواصل معنا' ) : 'تواصل معنا' );
		echo '</h3>';
		echo '<p class="kayan-contact-box__text">';
		echo esc_html( function_exists( 'kayan_homepage_get_company_name' ) ? kayan_homepage_get_company_name() : get_bloginfo( 'name' ) );
		echo '</p>';
		echo '<div class="kayan-contact-box__actions">';

		if ( $show_call && $tel_url !== '#' && trim( (string) $phone ) !== '' ) {
			echo '<a href="' . esc_url( $tel_url ) . '" class="btn btn-call">';
			echo '<i class="fas fa-phone"></i> ' . esc_html( $btn_call );
			echo '</a>';
		}

		if ( $wa_url !== '#' ) {
			echo '<a href="' . esc_url( $wa_url ) . '" class="btn btn-wa" target="_blank" rel="noopener noreferrer">';
			echo '<i class="fab fa-whatsapp"></i> ' . esc_html( $btn_wa );
			echo '</a>';
		}

		echo '</div>';
		echo '</div>';
	}
}

if ( ! function_exists( 'kayan_homepage_render_sidebar_related_services' ) ) {
	/**
	 * @param int   $post_id        Current post ID.
	 * @param array $category_ids   Category term IDs.
	 * @param int   $limit          Max related posts.
	 */
	function kayan_homepage_render_sidebar_related_services( $post_id, $category_ids = array(), $limit = 6 ) {
		$post_id      = (int) $post_id;
		$category_ids = array_filter( array_map( 'intval', (array) $category_ids ) );
		if ( empty( $category_ids ) ) {
			return;
		}

		$posts = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => max( 1, (int) $limit ),
				'post__not_in'   => array( $post_id ),
				'orderby'        => 'rand',
				'no_found_rows'  => true,
				'tax_query'      => array(
					array(
						'taxonomy' => 'category',
						'field'    => 'term_id',
						'terms'    => $category_ids,
					),
				),
			)
		);

		if ( empty( $posts ) ) {
			return;
		}

		echo '<div class="kayan-inner-sidebar__card kayan-inner-sidebar__related">';
		echo '<h3 class="kayan-inner-sidebar__title">خدمات ذات صلة</h3>';
		echo '<ul class="kayan-inner-related-list">';
		foreach ( $posts as $related_post ) {
			$url = get_permalink( $related_post );
			echo '<li><a href="' . esc_url( $url ) . '"><i class="fas fa-chevron-left"></i> ' . esc_html( get_the_title( $related_post ) ) . '</a></li>';
		}
		echo '</ul>';
		echo '</div>';
	}
}

if ( ! function_exists( 'kayan_homepage_render_inner_header' ) ) {
	function kayan_homepage_render_inner_header( $args = array() ) {
		kayan_homepage_render_inner_hero( $args );
		kayan_homepage_render_inner_breadcrumb();
	}
}

if ( ! function_exists( 'kayan_homepage_render_inner_layout_open' ) ) {
	/**
	 * Opens kayan-inner-body + layout grid. Set has_sidebar false for full-width.
	 */
	function kayan_homepage_render_inner_layout_open( $has_sidebar = true ) {
		$layout_class = $has_sidebar ? 'kayan-inner-layout' : 'kayan-inner-layout kayan-inner-layout--no-sidebar';
		echo '<div class="kayan-inner-body">';
		echo '<div class="' . esc_attr( $layout_class ) . '">';
		echo '<main class="kayan-inner-body__content kayan-inner-section">';
	}
}

if ( ! function_exists( 'kayan_homepage_render_inner_sidebar_open' ) ) {
	function kayan_homepage_render_inner_sidebar_open() {
		echo '</main>';
		echo '<aside class="kayan-inner-sidebar">';
	}
}

if ( ! function_exists( 'kayan_homepage_render_inner_layout_close' ) ) {
	function kayan_homepage_render_inner_layout_close( $has_sidebar = true ) {
		if ( $has_sidebar ) {
			echo '</aside>';
		} else {
			echo '</main>';
		}
		echo '</div>';
		echo '</div>';
	}
}
