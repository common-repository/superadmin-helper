<?php

/* Legacy function that may be used by other plugins */

function suh_get_ip() {
	return \SuperadminHelper\get_ip();
}


function suh_maybe_permban( $username ) {

	$settings = \SuperadminHelper\Settings::getInstance();
	if( $settings->is_permban_active ) {
		$user = get_userdatabylogin( $username );
		$ip = suh_get_ip();
		if ( !$user || ( $user->user_login != $username ) ) {
			$forbidden_logins = explode( ',', $settings->forbidden_logins );
			if( in_array( $username, $forbidden_logins ) ) {
				\SuperadminHelper\add_permban( $ip );
			}
		}
	}
}

?>