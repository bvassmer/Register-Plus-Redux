<?php
if ( function_exists( 'wp_new_user_notification' ) ) {
	add_action( 'admin_notices', array( $register_plus_redux, 'ConflictWarning' ), 10, 1 );
}

// Called after user completes registration from wp-login.php
// Called after admin creates user from wp-admin/user-new.php
// Called after admin creates new site, which also creates new user from wp-admin/network/edit.php (MS)
// Called after admin creates user from wp-admin/network/edit.php (MS)
if ( !function_exists( 'wp_new_user_notification' ) ) {
	function wp_new_user_notification( $user_id, $plaintext_pass = '' ) {
		global $pagenow;
		global $register_plus_redux;

		trigger_error( sprintf( __( 'Register Plus Redux DEBUG: wp_new_user_notification($user_id=%s, $plaintext_pass=%s) from %s', 'register-plus-redux' ), $user_id, $plaintext_pass, $pagenow ) ); 
		if ( $register_plus_redux->GetReduxOption( 'user_set_password' ) == TRUE && !empty( $_POST['pass1'] ) )
			$plaintext_pass = get_magic_quotes_gpc() ? stripslashes( $_POST['pass1'] ) : $_POST['pass1'];
		if ( $pagenow == 'user-new.php' && !empty( $_POST['pass1'] ) )
			$plaintext_pass = get_magic_quotes_gpc() ? stripslashes( $_POST['pass1'] ) : $_POST['pass1'];
		//TODO: Code now only forces users registering to verify email, may want to add settings to have admin created users verify email too
		if ( $pagenow == 'wp-login.php' && $register_plus_redux->GetReduxOption( 'verify_user_email' ) == TRUE ) {
			$verification_code = wp_generate_password( 20, FALSE );
			update_user_meta( $user_id, 'email_verification_code', $verification_code );
			update_user_meta( $user_id, 'email_verification_sent', gmdate( 'Y-m-d H:i:s' ) );
			$register_plus_redux->sendVerificationMessage( $user_id, $verification_code );
		}
		if ( ( $pagenow == 'wp-login.php' && $register_plus_redux->GetReduxOption( 'disable_user_message_registered' ) == FALSE ) || 
			( $pagenow != 'wp-login.php' && $register_plus_redux->GetReduxOption( 'disable_user_message_created' ) == FALSE ) ) {
			if ( $register_plus_redux->GetReduxOption( 'verify_user_email' ) == FALSE && $register_plus_redux->GetReduxOption( 'verify_user_admin' ) == FALSE ) {
				$register_plus_redux->sendUserMessage( $user_id, $plaintext_pass );
			}
		}
		if ( ( $pagenow == 'wp-login.php' && $register_plus_redux->GetReduxOption( 'disable_admin_message_registered' ) == FALSE ) || 
			( $pagenow != 'wp-login.php' && $register_plus_redux->GetReduxOption( 'disable_admin_message_created' ) == FALSE ) ) {
			$register_plus_redux->sendAdminMessage( $user_id, $plaintext_pass, isset( $verification_code ) ? $verification_code : '' );
		}
	}
}
?>