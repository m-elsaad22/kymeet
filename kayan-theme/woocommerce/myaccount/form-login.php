<?php
/**
 * صفحة تسجيل الدخول / إنشاء حساب بتصميم KYMEET.
 * تحافظ على كل خطافات WooCommerce وأسماء الحقول (login / register / nonce).
 *
 * @package WooCommerce\Templates
 * @version 9.9.0
 */

defined( 'ABSPATH' ) || exit;

$kx_en   = function_exists( 'kymeet_store_is_en' ) && kymeet_store_is_en();
$kx_reg  = 'yes' === get_option( 'woocommerce_enable_myaccount_registration' );
$kx_logo = function_exists( 'kymeet_store_logo_url' ) ? kymeet_store_logo_url() : '';
// phpcs:disable WordPress.Security.NonceVerification
$kx_first_reg = $kx_reg && ! empty( $_POST['register'] );
$kx_user      = ( ! empty( $_POST['username'] ) && is_string( $_POST['username'] ) ) ? wp_unslash( $_POST['username'] ) : '';
$kx_email     = ( ! empty( $_POST['email'] ) && is_string( $_POST['email'] ) ) ? wp_unslash( $_POST['email'] ) : '';
// phpcs:enable

do_action( 'woocommerce_before_customer_login_form' );
?>
<div class="kx-auth-card" id="customer_login">
	<div class="ka-brand">
		<?php if ( $kx_logo ) : ?>
			<img class="ka-logo" src="<?php echo $kx_logo; // phpcs:ignore ?>" alt="KYMEET">
		<?php endif; ?>
		<b class="lat">KYMEET</b>
		<small><?php echo esc_html( $kx_en ? 'Perfumes that leave a trace' : 'عطور تترك أثرًا' ); ?></small>
	</div>

	<?php if ( $kx_reg ) : ?>
	<div class="ka-tabs" role="tablist">
		<button type="button" role="tab" class="<?php echo $kx_first_reg ? '' : 'on'; ?>" data-ka="login"><?php echo esc_html( $kx_en ? 'Sign in' : 'تسجيل الدخول' ); ?></button>
		<button type="button" role="tab" class="<?php echo $kx_first_reg ? 'on' : ''; ?>" data-ka="register"><?php echo esc_html( $kx_en ? 'Create account' : 'حساب جديد' ); ?></button>
	</div>
	<?php endif; ?>

	<div class="ka-pane<?php echo $kx_first_reg ? '' : ' on'; ?>" data-pane="login">
		<h2><?php echo esc_html( $kx_en ? 'Welcome back' : 'أهلًا بعودتك' ); ?></h2>
		<p class="ka-sub"><?php echo esc_html( $kx_en ? 'Sign in to track your orders and check out faster.' : 'سجّل دخولك لتتبع طلباتك وإتمام الشراء بسرعة.' ); ?></p>

		<form class="woocommerce-form woocommerce-form-login login" method="post" novalidate>
			<?php do_action( 'woocommerce_login_form_start' ); ?>

			<div class="ka-field">
				<label for="username"><?php echo esc_html( $kx_en ? 'Email or username' : 'البريد الإلكتروني أو اسم المستخدم' ); ?></label>
				<span class="ka-in"><i class="far fa-envelope"></i>
				<input type="text" class="woocommerce-Input input-text" name="username" id="username" autocomplete="username" value="<?php echo esc_attr( $kx_user ); ?>" required aria-required="true"></span>
			</div>
			<div class="ka-field">
				<label for="password"><?php echo esc_html( $kx_en ? 'Password' : 'كلمة المرور' ); ?></label>
				<span class="ka-in"><i class="fas fa-lock"></i>
				<input class="woocommerce-Input input-text" type="password" name="password" id="password" autocomplete="current-password" required aria-required="true">
				<button type="button" class="ka-eye" aria-label="<?php echo esc_attr( $kx_en ? 'Show password' : 'إظهار كلمة المرور' ); ?>"><i class="far fa-eye"></i></button></span>
			</div>

			<?php do_action( 'woocommerce_login_form' ); ?>

			<div class="ka-row">
				<label class="ka-check"><input name="rememberme" type="checkbox" id="rememberme" value="forever"> <span><?php echo esc_html( $kx_en ? 'Remember me' : 'تذكرني' ); ?></span></label>
				<a class="ka-link" href="<?php echo esc_url( wp_lostpassword_url() ); ?>"><?php echo esc_html( $kx_en ? 'Forgot password?' : 'نسيت كلمة المرور؟' ); ?></a>
			</div>

			<?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>
			<button type="submit" class="ka-btn woocommerce-form-login__submit" name="login" value="<?php esc_attr_e( 'Log in', 'woocommerce' ); ?>"><?php echo esc_html( $kx_en ? 'Sign in' : 'دخول' ); ?></button>

			<?php do_action( 'woocommerce_login_form_end' ); ?>
		</form>
	</div>

	<?php if ( $kx_reg ) : ?>
	<div class="ka-pane<?php echo $kx_first_reg ? ' on' : ''; ?>" data-pane="register">
		<h2><?php echo esc_html( $kx_en ? 'Create your account' : 'أنشئ حسابك' ); ?></h2>
		<p class="ka-sub"><?php echo esc_html( $kx_en ? 'Join KYMEET for faster checkout and order tracking.' : 'انضم إلى كيميت لشراء أسرع وتتبّع لطلباتك.' ); ?></p>

		<form method="post" class="woocommerce-form woocommerce-form-register register" <?php do_action( 'woocommerce_register_form_tag' ); ?> >
			<?php do_action( 'woocommerce_register_form_start' ); ?>

			<?php if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) : ?>
			<div class="ka-field">
				<label for="reg_username"><?php echo esc_html( $kx_en ? 'Username' : 'اسم المستخدم' ); ?></label>
				<span class="ka-in"><i class="far fa-user"></i>
				<input type="text" class="woocommerce-Input input-text" name="username" id="reg_username" autocomplete="username" value="<?php echo esc_attr( $kx_first_reg ? $kx_user : '' ); ?>" required aria-required="true"></span>
			</div>
			<?php endif; ?>

			<div class="ka-field">
				<label for="reg_email"><?php echo esc_html( $kx_en ? 'Email address' : 'البريد الإلكتروني' ); ?></label>
				<span class="ka-in"><i class="far fa-envelope"></i>
				<input type="email" class="woocommerce-Input input-text" name="email" id="reg_email" autocomplete="email" value="<?php echo esc_attr( $kx_email ); ?>" required aria-required="true"></span>
			</div>

			<?php if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) : ?>
			<div class="ka-field">
				<label for="reg_password"><?php echo esc_html( $kx_en ? 'Password' : 'كلمة المرور' ); ?></label>
				<span class="ka-in"><i class="fas fa-lock"></i>
				<input type="password" class="woocommerce-Input input-text" name="password" id="reg_password" autocomplete="new-password" required aria-required="true">
				<button type="button" class="ka-eye" aria-label="<?php echo esc_attr( $kx_en ? 'Show password' : 'إظهار كلمة المرور' ); ?>"><i class="far fa-eye"></i></button></span>
			</div>
			<?php else : ?>
			<p class="ka-sub"><?php echo esc_html( $kx_en ? 'A link to set your password will be sent to your email.' : 'سيصلك رابط لتعيين كلمة المرور على بريدك الإلكتروني.' ); ?></p>
			<?php endif; ?>

			<?php do_action( 'woocommerce_register_form' ); ?>

			<?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>
			<button type="submit" class="ka-btn woocommerce-form-register__submit" name="register" value="<?php esc_attr_e( 'Register', 'woocommerce' ); ?>"><?php echo esc_html( $kx_en ? 'Create account' : 'إنشاء الحساب' ); ?></button>

			<?php do_action( 'woocommerce_register_form_end' ); ?>
		</form>
	</div>
	<?php endif; ?>
</div>
<?php do_action( 'woocommerce_after_customer_login_form' ); ?>
