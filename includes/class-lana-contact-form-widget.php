<?php

/**
 * Class Lana_Contact_Form_Widget
 */
class Lana_Contact_Form_Widget extends WP_Widget{

	/**
	 * Lana Conact Form Widget
	 * constructor
	 */
	public function __construct() {

		$widget_title       = __( 'Lana - Contact Form', 'lana-contact-form' );
		$widget_description = __( 'Contact form with captcha.', 'lana-contact-form' );
		$widget_options     = array( 'description' => $widget_description );

		parent::__construct( 'lana_contact_form', $widget_title, $widget_options );
	}

	/**
	 * Output Widget HTML
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		echo lana_contact_form();
	}
} 