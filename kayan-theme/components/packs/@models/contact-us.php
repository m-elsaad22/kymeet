<?
$Styles = array();
$UniqId = uniqid();

$YC__WidgetsMachine = new YC__WidgetsMachine;

$widgets_contactus__meta = ( is_array( get_option( 'widgets_contactus__meta' ) ) ) ? get_option( 'widgets_contactus__meta' ) : array();
$widgets_contactus__meta = ( is_array( $widgets_contactus__meta ) ) ? $widgets_contactus__meta : array();

if( !empty( $widgets_contactus__meta ) ){
	$widgets__Enqueues = $YC__WidgetsMachine->widgets__Enqueues($widgets_contactus__meta);
	$Styles = array_merge($Styles,$widgets__Enqueues);
}				

$post_content = $post->post_content;
$post_content = str_replace('<br/>', PHP_EOL, $post_content);
$post_content = str_replace('&nbsp;', ' ', $post_content);
$post_content = strip_tags($post_content);
$MoreClass 		= '';
if( strlen($post_content) > 350 ) {
	$post_content = mb_substr($post_content, 0, 350, 'utf-8').'... <a href="javascript:void(0);" data-button="readmore-objects" data-object-type="post_type" data-object-name="'.$post->post_type.'" data-object-id="'.$post->ID.'" class="readmore--category-item">قراءة المزيد</a>';
}else{
	$post_content = $post_content;
}
$Styles['contact__form'] = 'YourColor__Widgets/contact__form.css';
$Styles['kayan-home']    = 'kayan-home.css';
$Styles['kayan-inner']   = 'kayan-inner.css';

$contactus_page__data = get_option('contactus_page__data');
$contactus_page__data = ( ( is_array( $contactus_page__data ) ) ) ? $contactus_page__data : array();
$page_background = get_post_meta($post->ID, 'page_back_image', true);

if (empty($page_background)) {
    $page_background = get_option('background_image');
}

$whatsapp_number = get_post_meta( $post->ID,'whatsapp_number',true );
if( empty( $whatsapp_number ) ) $whatsapp_number = get_option('whatsapp_number');

$phonenumber = get_post_meta( $post->ID,'phone_number',true );
if( empty( $phonenumber ) ) $phonenumber = get_option('phonenumber');

$this->Part('header',array('Styles'=>$Styles));

echo '<div class="kayan-inner-archive-shell kayan-contact-page">';

if ( function_exists( 'kayan_homepage_inner_hero' ) ) {
	kayan_homepage_inner_hero( $post->post_title, $post_content, $page_background, $post->ID );
} elseif ( function_exists( 'kayan_homepage_render_inner_hero' ) ) {
	kayan_homepage_render_inner_hero(
		array(
			'title'     => $post->post_title,
			'subtitle'  => wp_strip_all_tags( $post_content ),
			'image_url' => is_numeric( $page_background ) ? (string) wp_get_attachment_image_url( (int) $page_background, 'full' ) : (string) $page_background,
		)
	);
}

if ( function_exists( 'kayan_homepage_render_inner_breadcrumb' ) ) {
	kayan_homepage_render_inner_breadcrumb();
} else {
	echo '<div class="kayan-inner-breadcrumb"><div class="kayan-inner-breadcrumb__inner"><div class="YC-BreadCrumb -BreadCrumb-PT-' . esc_attr( $post->post_type ) . '">';
	Breadcrumb();
	echo '</div></div></div>';
}

echo '<div class="kayan-inner-body">';
echo '<div class="kayan-inner-layout kayan-inner-layout--wide-main">';

echo '<main class="kayan-inner-body__content kayan-inner-section kayan-contact-page__widgets">';
if( empty( $hide__sidebar__single ) && !empty( $widgets_contactus__meta ) ) {
	$YC__WidgetsMachine->widgets___UI(
		array(
			'Widgets_data'=>$widgets_contactus__meta,
			'WidgetID'=>'widgets_contactus__meta',
		)
	);
}
echo '</main>';

echo '<aside class="kayan-inner-sidebar">';
if ( function_exists( 'kayan_homepage_render_contact_box' ) ) {
	kayan_homepage_render_contact_box( $post->ID, $phonenumber, $whatsapp_number );
}
echo '</aside>';

echo '</div>';
echo '</div>';
echo '</div>';

$this->Part('footer',array('Styles'=>$Styles));
