<?php
defined( 'ABSPATH' ) or die();

global $lana_contact_form;
?>
<div class="lana-contact-form-feedback">
	<?php
	lana_contact_form_get_infos();
	lana_contact_form_get_errors();
	?>
</div>
<form id="lana-contact-form" method="post" role="form" class="form-horizontal">
	<?php wp_nonce_field( 'send', 'lana_contact_form_nonce_field' ); ?>

	<div class="form-group">
		<label for="lana_contact_name" class="col-sm-3 control-label">
			<?php _e( 'Name', 'lana-contact-form' ); ?>
		</label>

		<div class="col-sm-6">
			<input type="text" class="form-control" name="lana_contact[name]" id="lana_contact_name"
			       value="<?php echo esc_attr( $lana_contact_form['fields']['name'] ); ?>" required>
		</div>
	</div>

	<div class="form-group">
		<label for="lana_contact_email" class="col-sm-3 control-label">
			<?php _e( 'Email', 'lana-contact-form' ); ?>
		</label>

		<div class="col-sm-6">
			<input type="email" class="form-control" name="lana_contact[email]" id="lana_contact_email"
			       value="<?php echo esc_attr( $lana_contact_form['fields']['email'] ); ?>" required>
		</div>
	</div>

	<div class="form-group">
		<label for="lana_contact_message" class="col-sm-3 control-label">
			<?php _e( 'Message', 'lana-contact-form' ); ?>
		</label>

		<div class="col-sm-6">
			<textarea class="form-control" rows="10" name="lana_contact[message]" id="lana_contact_message"
			          required><?php echo esc_textarea( $lana_contact_form['fields']['message'] ); ?></textarea>
		</div>
	</div>

	<div class="form-group">
		<label for="lana_contact_captcha" class="col-sm-3 text-right">
			<img src="data:image/png;base64,<?php echo esc_attr( lana_contact_form_get_base64_captcha() ); ?>" class="pull-right">
		</label>

		<div class="col-sm-6">
			<input type="number" class="form-control" name="lana_contact[captcha]" id="lana_contact_captcha"
			       required size="2" min="0" max="20">
		</div>
	</div>

	<div class="form-group">
		<div class="col-sm-offset-3 col-sm-6">
			<button type="submit" class="btn btn-primary" name="lana_contact_submit">
				<?php _e( 'Submit', 'lana-contact-form' ); ?>
			</button>
		</div>
	</div>

</form>