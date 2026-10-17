<?
status_header( 404 );
nocache_headers();

$Styles = array();
$this->Part( 'header', array( 'Styles' => $Styles ) );

echo '<div class="-primary-body">';

	echo '<div class="--primary--intro--pages">';
		echo '<div class="container">';
			echo '<div class="container-pages-head">';
				echo '<div class="--container--category--info">';
					echo '<div class="YC-BreadCrumb">';
						if ( function_exists( 'Breadcrumb' ) ) {
							Breadcrumb();
						}
					echo '</div>';
					echo '<h1>404 — الصفحة غير موجودة</h1>';
					echo '<p>عذراً، الرابط الذي طلبته غير متوفر أو تم نقله. يمكنك العودة للصفحة الرئيسية أو البحث عن خدمة.</p>';
				echo '</div>';
			echo '</div>';
		echo '</div>';
	echo '</div>';

	echo '<div class="-YC-Widgets-Inner-Row">';
		echo '<div class="container">';
			echo '<div class="-page--container-sidebars">';
				echo '<div class="-YourColor-SingleWidget-Section">';
					echo '<p><a class="btn btn-primary" href="' . esc_url( home_url( '/' ) ) . '">العودة للرئيسية</a></p>';
					if ( function_exists( 'get_search_form' ) ) {
						get_search_form();
					}
				echo '</div>';
			echo '</div>';
		echo '</div>';
	echo '</div>';

echo '</div>';

$this->Part( 'footer' );
