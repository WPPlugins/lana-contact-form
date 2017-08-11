<?php
/**
 * Plugin Name: Lana Contact Form
 * Plugin URI: http://wp.lanaprojekt.hu/blog/wordpress-plugins/lana-contact-form/
 * Description: Easy to use contact form with captcha.
 * Version: 1.0.5
 * Author: Lana Design
 * Author URI: http://wp.lanaprojekt.hu/blog/
 */

defined( 'ABSPATH' ) or die();
define( 'LANA_CONTACT_FORM_VERSION', '1.0.5' );
define( 'LANA_CONTACT_FORM_DIR_URL', plugin_dir_url( __FILE__ ) );

/**
 * Language
 * load
 */
load_plugin_textdomain( 'lana-contact-form', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

/**
 * Lana Contact Form
 * session start
 */
function lana_contact_form_register_session() {

	global $post;

	if ( is_admin() ) {
		return;
	}

	if ( ! is_a( $post, 'WP_Post' ) || ! has_shortcode( $post->post_content, 'lana_contact_form' ) ) {
		return;
	}

	if ( ! session_id() ) {
		session_start();
	}
}

add_action( 'wp', 'lana_contact_form_register_session' );

/**
 * Styles
 * load in plugin
 */
function lana_contact_form_bootstrap_styles() {

	if ( ! wp_style_is( 'bootstrap' ) ) {

		wp_register_style( 'lana-contact-form', plugin_dir_url( __FILE__ ) . '/assets/css/lana-contact-form.css', array(), LANA_CONTACT_FORM_VERSION );
		wp_enqueue_style( 'lana-contact-form' );
	}
}

add_action( 'wp_enqueue_scripts', 'lana_contact_form_bootstrap_styles', 1002 );

/**
 * Lana Contact Form
 * form post handle
 */
function lana_contact_form_post_handle() {

	global $lana_contact_form;

	if ( ! isset( $_POST['lana_contact_submit'] ) ) {
		return;
	}

	$lana_contact_form = array(
		'has_error'  => false,
		'errors'     => array(),
		'fields'     => array(
			'name'    => '',
			'email'   => '',
			'message' => ''
		),
		'infos'      => array(),
		'email_sent' => false
	);

	/**
	 * Validate
	 * Nonce field
	 */
	if ( empty( $_POST['lana_contact_form_nonce_field'] ) ) {
		$lana_contact_form['errors']['nonce_field'] = __( 'That nonce field was incorrect.', 'lana-contact-form' );
		$lana_contact_form['has_error']             = true;

		return;
	}

	if ( ! wp_verify_nonce( $_POST['lana_contact_form_nonce_field'], 'send' ) ) {
		$lana_contact_form['errors']['nonce_field'] = __( 'That nonce field was incorrect.', 'lana-contact-form' );
		$lana_contact_form['has_error']             = true;

		return;
	}

	/**
	 * Fields
	 */
	$lana_contact_form['fields']['name']    = sanitize_text_field( $_POST['lana_contact']['name'] );
	$lana_contact_form['fields']['email']   = sanitize_email( $_POST['lana_contact']['email'] );
	$lana_contact_form['fields']['message'] = implode( "\n", array_map( 'sanitize_text_field', explode( "\n", $_POST['lana_contact']['message'] ) ) );
	$lana_contact_form['fields']['captcha'] = intval( $_POST['lana_contact']['captcha'] );

	/**
	 * Validate
	 * Name
	 */
	if ( empty( $lana_contact_form['fields']['name'] ) ) {
		$lana_contact_form['errors']['name'] = __( 'Please enter your name.', 'lana-contact-form' );
		$lana_contact_form['has_error']      = true;
	}

	/**
	 * Validate
	 * Email
	 */
	if ( empty( $lana_contact_form['fields']['email'] ) ) {
		$lana_contact_form['errors']['email'] = __( 'Please enter your email address.', 'lana-contact-form' );
		$lana_contact_form['has_error']       = true;
	}

	if ( filter_var( $lana_contact_form['fields']['email'], FILTER_VALIDATE_EMAIL ) == false ) {
		$lana_contact_form['errors']['email'] = __( 'Please enter valid email address.', 'lana-contact-form' );
		$lana_contact_form['has_error']       = true;
	}

	/**
	 * Validate
	 * Message
	 */
	if ( empty( $lana_contact_form['fields']['message'] ) ) {
		$lana_contact_form['errors']['message'] = __( 'Please enter a message.', 'lana-contact-form' );
		$lana_contact_form['has_error']         = true;
	}

	/**
	 * Validate
	 * captcha
	 */
	if ( empty( $lana_contact_form['fields']['captcha'] ) ) {
		$lana_contact_form['errors']['captcha'] = __( 'Please enter a captcha.', 'lana-contact-form' );
		$lana_contact_form['has_error']         = true;
	}

	if ( $lana_contact_form['fields']['captcha'] != $_SESSION['lana_contact_form']['captcha'] ) {
		$lana_contact_form['errors']['captcha'] = __( 'That captcha was incorrect. Try again.', 'lana-contact-form' );
		$lana_contact_form['has_error']         = true;
	}

	/**
	 * Validate
	 * errors
	 */
	if ( ! empty( $lana_contact_form['errors'] ) ) {
		return;
	}

	if ( $lana_contact_form['has_error'] == true ) {
		return;
	}

	/**
	 * Send
	 * Contact email
	 */
	$email_to = get_option( 'admin_email' );

	$subject = '[' . get_bloginfo( 'name' ) . '] ' . sprintf( __( 'Contact from %s', 'lana-contact-form' ), $lana_contact_form['fields']['name'] );

	$body = __( 'You\'ve got a new message.', 'lana-contact-form' );
	$body .= "\n\n";
	$body .= sprintf( __( 'Name: %s', 'lana-contact-form' ), $lana_contact_form['fields']['name'] );
	$body .= "\n";
	$body .= sprintf( __( 'Email: %s', 'lana-contact-form' ), $lana_contact_form['fields']['email'] );
	$body .= "\n\n";
	$body .= __( 'Message: ', 'lana-contact-form' );
	$body .= $lana_contact_form['fields']['message'];

	$headers = 'From: ' . $lana_contact_form['fields']['name'] . ' <' . $lana_contact_form['fields']['email'] . '>' . "\r\n";

	wp_mail( $email_to, $subject, $body, $headers );

	$lana_contact_form = array(
		'fields'     => array(
			'name'    => '',
			'email'   => '',
			'message' => ''
		),
		'infos'      => array( __( 'Your email was sent.', 'lana-contact-form' ) ),
		'email_sent' => true
	);
}

add_action( 'wp_head', 'lana_contact_form_post_handle' );

/**
 * Lana Contact Form
 * get info from lana_contact infos
 */
function lana_contact_form_get_infos() {

	global $lana_contact_form;

	if ( ! empty( $lana_contact_form['infos'] ) ) :
		foreach ( $lana_contact_form['infos'] as $info ) :
			?>
            <div class="alert alert-info alert-dismissible" role="alert">
                <button type="button" class="close" data-dismiss="alert">
                    <span aria-hidden="true">&times;</span>
                </button>
                <strong><?php _e( 'Info', 'lana-contact-form' ); ?>!</strong>
				<?php echo $info; ?>
            </div>
			<?php
		endforeach;
	endif;
}

/**
 * Lana Contact Form
 * get error from lana_contact errors
 */
function lana_contact_form_get_errors() {

	global $lana_contact_form;

	if ( ! isset( $lana_contact_form['has_error'] ) || ! $lana_contact_form['has_error'] ) {
		return;
	}

	if ( ! empty( $lana_contact_form['errors'] ) ) :
		foreach ( $lana_contact_form['errors'] as $error ) :
			?>
            <div class="alert alert-danger alert-dismissible" role="alert">
                <button type="button" class="close" data-dismiss="alert">
                    <span aria-hidden="true">&times;</span>
                </button>
                <strong><?php _e( 'Error', 'lana-contact-form' ); ?>!</strong>
				<?php echo $error; ?>
            </div>
			<?php
		endforeach;
	endif;
}

/**
 * Lana Contact Form
 * get captcha
 * @return string
 */
function lana_contact_form_get_captcha() {

	error_reporting( 0 );

	$image = imagecreatetruecolor( 70, 30 );
	$white = imagecolorallocate( $image, 255, 255, 255 );
	$black = imagecolorallocate( $image, 0, 0, 0 );
	$num1  = rand( 1, 9 );
	$num2  = rand( 1, 9 );
	$str   = $num1 . ' + ' . $num2 . ' = ';
	$font  = dirname( __FILE__ ) . '/assets/fonts/bebas.ttf';

	imagefill( $image, 0, 0, $white );
	imagettftext( $image, 18, 0, 0, 24, $black, $font, $str );

	ob_start();
	imagepng( $image );
	$image_data = ob_get_clean();
	imagedestroy( $image );

	$_SESSION['lana_contact_form']['captcha'] = $num1 + $num2;

	return $image_data;
}

/**
 * Lana Contact Form
 * get base64 encoded captcha
 * @return string
 */
function lana_contact_form_get_base64_captcha() {
	return base64_encode( lana_contact_form_get_captcha() );
}

/**
 * Lana Contact Form
 * include html
 * @return string
 */
function lana_contact_form() {

	ob_start();
	include_once 'views/lana-contact-form.php';
	$output = ob_get_clean();

	return $output;
}

/**
 * Lana Contact Form Shortcode
 * @return string
 */
function lana_contact_form_shortcode() {
	return lana_contact_form();
}

add_shortcode( 'lana_contact_form', 'lana_contact_form_shortcode' );

/**
 * Init Widget
 */
add_action( 'widgets_init', function () {
	include 'includes/class-lana-contact-form-widget.php';
	register_widget( 'Lana_Contact_Form_Widget' );
} );