<?php
/**
 * Global chrome: header override hints, cart drawer, dialogs, bottom nav.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function kymeet_render_splash(): void {
	if ( ! kymeet_store_active() || ! is_front_page() ) {
		return;
	}
	$bg = KYMEET_STORE_URL . 'assets/splash-pyramids.jpg';
	$shop = wc_get_page_permalink( 'shop' );
	?>
<div id="kymeet-splash" class="kymeet-splash" style="background-image:url('<?php echo esc_url( $bg ); ?>')">
  <div class="kymeet-splash__veil" aria-hidden="true"></div>
  <div class="kymeet-splash__logo">
    <div class="kymeet-splash__mark"><i class="fas fa-crown" aria-hidden="true"></i></div>
    <b>KYMEET</b>
    <small>Egypt I.E.A</small>
  </div>
  <div class="kymeet-splash__cta">
    <a class="btn btn-gold" href="<?php echo esc_url( $shop ); ?>" id="kymeet-splash-go">تسوق الآن <i class="fas fa-arrow-left"></i></a>
  </div>
</div>
	<?php
}

function kymeet_render_header(): void {
	if ( ! kymeet_store_active() ) {
		return;
	}
	$home   = home_url( '/' );
	$shop   = wc_get_page_permalink( 'shop' );
	$acct   = wc_get_page_permalink( 'myaccount' );
	$logo   = get_theme_mod( 'custom_logo' );
	$logo_u = $logo ? wp_get_attachment_image_url( $logo, 'medium' ) : '';
	?>
<header id="kymeet-hdr" class="kymeet-hdr">
  <div class="wrap nav">
    <a href="<?php echo esc_url( $home ); ?>" class="logo">
      <?php if ( $logo_u ) : ?>
        <img class="logo-img" src="<?php echo esc_url( $logo_u ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
      <?php endif; ?>
      <span class="logo-text">KYMEET<b>.</b></span>
    </a>
    <nav class="menu" aria-label="القائمة الرئيسية">
      <a href="<?php echo esc_url( $shop ); ?>">تسوق العطور</a>
      <a href="<?php echo esc_url( $home ); ?>#bestsellers">الأكثر مبيعًا</a>
      <a href="<?php echo esc_url( $home ); ?>#new">أحدث الإصدارات</a>
      <a href="<?php echo esc_url( $home ); ?>#brands">العلامات التجارية</a>
      <a href="<?php echo esc_url( $home ); ?>#story">حكاية كيميت</a>
      <a href="<?php echo esc_url( $home ); ?>#contact">اتصل بنا</a>
    </nav>
    <div class="nav-actions">
      <button type="button" class="btn btn-icon kymeet-search" aria-label="بحث" onclick="goSearch()"><i class="fas fa-magnifying-glass"></i></button>
      <a class="btn btn-icon hide-m" href="<?php echo esc_url( $acct ); ?>" aria-label="حسابي"><i class="far fa-user"></i></a>
      <button class="btn btn-icon cart-btn" aria-label="السلة" onclick="openCart()">
        <i class="fas fa-bag-shopping"></i>
        <span class="badge" id="cartCount">0</span>
      </button>
      <button class="ham" onclick="toggleMob(true)" aria-label="القائمة"><span></span><span></span><span></span></button>
    </div>
  </div>
</header>
<div class="mob kymeet-mob" id="kymeet-mob">
  <button class="mob-close" onclick="toggleMob(false)" aria-label="إغلاق"><i class="fas fa-xmark"></i></button>
  <a class="mlink" href="<?php echo esc_url( $shop ); ?>" onclick="toggleMob(false)">تسوق العطور</a>
  <a class="mlink" href="<?php echo esc_url( $home ); ?>#catalog" onclick="toggleMob(false)">كل العطور</a>
  <a class="mlink" href="<?php echo esc_url( $home ); ?>#kymeet-wish" onclick="toggleMob(false)">المفضلة</a>
  <a class="mlink" href="<?php echo esc_url( $acct ); ?>">حسابي</a>
</div>
	<?php
}

function kymeet_render_footer_shell(): void {
	if ( ! kymeet_store_active() ) {
		return;
	}
	$home = home_url( '/' );
	$shop = wc_get_page_permalink( 'shop' );
	$acct = wc_get_page_permalink( 'myaccount' );
	?>
<div class="cart-overlay" id="cartOverlay" onclick="closeCart()"></div>
<aside class="cart-drawer" id="cartDrawer">
  <div class="cart-head"><h3>سلة التسوق</h3><button type="button" onclick="closeCart()" aria-label="إغلاق"><i class="fas fa-xmark"></i></button></div>
  <div class="cart-body" id="cartBody"></div>
  <div class="cart-foot">
    <div class="cart-total"><span>الإجمالي</span><b id="cartTotal">0</b></div>
    <button class="btn btn-gold" onclick="checkout(event)">إتمام الشراء</button>
  </div>
</aside>
<dialog id="pd"><div class="pd-in"><button class="pd-x" onclick="this.closest('dialog').close()" aria-label="إغلاق"><i class="fas fa-xmark"></i></button><div id="pdImg"></div><div id="pdTxt"></div></div></dialog>
<div class="toast" id="toast"><b id="toastTitle"></b><small id="toastMsg"></small></div>
<nav class="appnav kymeet-appnav" aria-label="التنقل الرئيسي">
  <a class="an-i" data-nav="home" href="<?php echo esc_url( $home ); ?>"><i class="fas fa-house"></i><span>الرئيسية</span></a>
  <a class="an-i" data-nav="shop" href="<?php echo esc_url( $shop ); ?>"><i class="fas fa-store"></i><span>المتجر</span></a>
  <a class="an-i" data-nav="brands" href="<?php echo esc_url( $home ); ?>#brands"><i class="fas fa-gem"></i><span>البراندات</span></a>
  <button type="button" class="an-i" data-nav="cart" onclick="openCart()"><i class="fas fa-bag-shopping"></i><span>العربة</span><b class="an-badge" id="anBadge">0</b></button>
  <a class="an-i" data-nav="wish" href="<?php echo esc_url( $home ); ?>#kymeet-wish"><i class="far fa-heart"></i><span>المفضلة</span></a>
  <a class="an-i" data-nav="account" href="<?php echo esc_url( $acct ); ?>"><i class="far fa-user"></i><span>حسابي</span></a>
</nav>
	<?php
	kymeet_print_config_scripts();
}

function kymeet_single_sticky_buy(): void {
	if ( ! kymeet_store_active() || ! function_exists( 'is_product' ) || ! is_product() ) {
		return;
	}
	global $product;
	if ( ! $product instanceof WC_Product ) {
		return;
	}
	?>
<div class="kymeet-sticky-buy" aria-hidden="false">
  <div class="kymeet-sticky-buy__price"><?php echo wp_kses_post( $product->get_price_html() ); ?></div>
  <a class="btn btn-gold kymeet-sticky-buy__btn" href="<?php echo esc_url( $product->add_to_cart_url() ); ?>">
    <?php echo esc_html( $product->single_add_to_cart_text() ); ?>
  </a>
</div>
	<?php
}

function kymeet_sr_h1(): void {
	if ( ! kymeet_store_active() || ! is_front_page() ) {
		return;
	}
	echo '<h1 class="kymeet-sr-h1">KYMEET Perfumes — عطور فاخرة مستوحاة من أصالة كيمت</h1>';
}
