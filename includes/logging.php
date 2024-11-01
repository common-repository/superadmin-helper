<?php

/** Logging of events in WordPress into WLS */
namespace SuperadminHelper\Logging {
	
	use \SuperadminHelper\Log;
	use \SuperadminHelper\Settings;
		
	// sending e-mail
		
	add_filter( "wp_mail", '\SuperadminHelper\Logging\email' );
	
	function email( $mail_info ) {
		$settings = Settings::getInstance();
		if( $settings->log_wp_mail && defined( 'WLS' ) && wls_is_registered( \SuperadminHelper\LOGNAME_MAIL ) ) {
			wls_simple_log(
					\SuperadminHelper\LOGNAME_MAIL,
					sprintf(
							"Sending an e-mail with subject \"%s\"\nTo: %s\nHeaders: %s\nMessage: %s\nAttachments: %s",
							$mail_info['subject'],
							print_r( $mail_info['to'], true ),
							print_r( $mail_info['headers'], true ),
							$mail_info['message'],
							$mail_info['attachments'] ),
					1 );
		}
		return $mail_info;
	}

	// user profile update
	
	add_action( "profile_update", '\SuperadminHelper\Logging\user_profile_update' );
	
	function user_profile_update( $user_id ) {
		$userdata = get_userdata( $user_id );
		$message = 'User profile updated: '.print_r( $userdata, true );
		Log::log( $message, 2 );
	}
	
	

	
	
	// logging in
	
	add_action( "wp_login", '\SuperadminHelper\Logging\login' );
	
	function login( $username ) {
		$settings = Settings::getInstance();
		if( $settings->log_wp_login ) {
			Log::debug( 'User '.$username.' logging in (IP '.suh_get_ip().').' );
		}
	}
	
	
	// password resetting
	
	add_action( "lostpassword_post", '\SuperadminHelper\Logging\lostpassword_post' );
	
	function lostpassword_post() {
		Log::debug( 'A password reset was requested.' );
		return;
	}
	
	add_action( "password_reset", '\SuperadminHelper\Logging\password_reset' );
	
	function password_reset( $user ) {
		Log::info( 'User '.$user->user_login.' has reset it\'s password.' );
	}
	
	
	// deleting an user
	
	add_action( "delete_user", '\SuperadminHelper\Logging\delete_user' );
	
	function delete_user( $user_id ) {
		Log::log( 'User ID='.$user_id.' was deleted.', 2 );
	}
	
	
	// uploading a file
	
	add_action( "add_attachment", '\SuperadminHelper\Logging\add_attachment' );
	
	function add_attachment( $id ) {
		$file = get_attached_file( $id );
		Log::log( 'File uploaded: "' . $file . '"', 2 );
	}
	
	
	// user logout
	
	add_action( "wp_logout", '\SuperadminHelper\Logging\wp_logout' );
	
	function wp_logout( $user_login ) {
		$settings = Settings::getInstance();
		if( $settings->log_wp_logout ) {
			Log::debug( 'User '.$user_login.' has logged out.' );
		}
	}
	
	
	// user registered
	
	add_action( "user_register", '\SuperadminHelper\Logging\user_register' );
	
	function user_register( $user_id ) {
		$userdata = get_userdata( $user_id );
		Log::log( 'User '.$userdata->user_login.' registered.', 2 );
	}
	
	
	// theme switch
	
	add_action( "switch_theme", '\SuperadminHelper\Logging\switch_theme' );
	
	function switch_theme( $theme ) {
		Log::log( 'Theme switched to '.$theme.'.', 2 );
	}
	
	
	// activate plugin
	
	add_action( "activated_plugin", '\SuperadminHelper\Logging\activate_plugin', 10, 2 );
	
	function activate_plugin( $plugin, $network_wide ) {
		if( $network_wide ) {
			Log::log( 'Plugin activated: "'.$plugin.'" network-wide.', 2 );
		} else {
			Log::log( 'Plugin activated: "'.$plugin.'".', 2 );
		}
	}
	
	// deactivate plugin
	
	add_action( "deactivated_plugin", '\SuperadminHelper\Logging\deactivate_plugin', 10, 2 );
	
	function deactivate_plugin( $plugin, $network_wide ) {
		if( $network_wide ) {
			Log::log( 'Plugin deactivated: "'.$plugin.'" network-wide.', 2 );
		} else {
			Log::log( 'Plugin deactivated: "'.$plugin.'".', 2 );
		}
	}
	
	
	// deactivate blog
	
	add_action( "deactivate_blog", '\SuperadminHelper\Logging\deactivate_blog' );
	
	function deactivate_blog( $blog_id ) {
		Log::log( 'Blog with id=='.$blog_id.' deactivated.', 2 );
	}
	
	
	// activate blog
	
	add_action( "activate_blog", '\SuperadminHelper\Logging\activate_blog' );
	
	function activate_blog( $blog_id ) {
		Log::log( 'Blog with id=='.$blog_id.' activated.', 2 );
	}
	
	
	// archive_blog
	
	add_action( "archive_blog", '\SuperadminHelper\Logging\archive_blog' );
	
	function archive_blog( $blog_id ) {
		Log::log( 'Blog with id=='.$blog_id.' archived.', 2 );
	}
	
	
	// unarchive_blog
	
	add_action( "unarchive_blog", '\SuperadminHelper\Logging\unarchive_blog' );
	
	function unarchive_blog( $blog_id ) {
		Log::log( 'Blog with id=='.$blog_id.' unarchived.', 2 );
	}
	
	
	// make_spam_blog
	
	add_action( "make_spam_blog", '\SuperadminHelper\Logging\make_spam_blog');
	
	function make_spam_blog( $blog_id ) {
		Log::log( 'Blog with id=='.$blog_id.' marked as spam.', 2 );
	}
	
	
	// make_ham_blog
	
	add_action( "make_ham_blog", '\SuperadminHelper\Logging\make_ham_blog' );
	
	function make_ham_blog( $blog_id ) {
		Log::log( 'Blog with id=='.$blog_id.' marked as ham.', 2 );
	}
	
	
	// mature_blog
	
	add_action( "mature_blog", '\SuperadminHelper\Logging\mature_blog' );
	
	function mature_blog( $blog_id ) {
		Log::log( 'Blog with id=='.$blog_id.' marked as mature.', 2 );
	}
	
	// unmature_blog
	
	add_action( "unmature_blog", '\SuperadminHelper\Logging\unmature_blog' );
	
	function unmature_blog( $blog_id ) {
		Log::log( 'Removed "mature" mark from blog with id=='.$blog_id.'.', 2 );
	}
	
	
	// delete blog
	
	add_action( "delete_blog", '\SuperadminHelper\Logging\delete_blog', 10, 2 );
	
	function delete_blog( $blog_id, $drop ) {
		$msg = 'Deleting blog with id=='.$blog_id;
		if( $drop ) {
			$msg.= ' (dropping from database)';
		}
		$msg.= '.';
		Log::log( $msg, 3 );
	}
	
	
	// add user to blog
	
	add_action( "add_user_to_blog", '\SuperadminHelper\Logging\add_user_to_blog', 10, 3 );
	
	function add_user_to_blog( $user_id, $role, $blog_id ) {
		$userdata = get_userdata( $user_id );
		$blog_details = get_blog_details( $blog_id, true );
		Log::log( 'User '.$userdata->user_login.' (ID=='.$user_id.') added to blog '.$blog_details->blogname.' ('.$blog_details->domain.', ID=='.$blog_details->blog_id.') with role '.$role.'.', 2 );
	}
	
	
	// remove user from blog
	
	add_action( "remove_user_from_blog", '\SuperadminHelper\Logging\remove_user_from_blog', 10, 2 );
	
	function remove_user_from_blog( $user_id, $blog_id ) {
		$userdata = get_userdata( $user_id );
		$blog_details = get_blog_details( $blog_id, true );
		Log::log( 'User '.$userdata->user_login.' (ID=='.$user_id.') removed from blog '.$blog_details->blogname.' ('.$blog_details->domain.', ID=='.$blog_details->blog_id.').', 3 );
	}
	
	
	// update_plugin_complete_actions
	
	add_filter( "update_plugin_complete_actions", '\SuperadminHelper\Logging\update_plugin_complete_actions', 10, 2 );
	
	function update_plugin_complete_actions( $update_actions, $plugin ) {
		Log::log( 'Upgrading plugin "'.$plugin.'".', 3 );
		return $update_actions;
	}
	
	
	// install_plugin_complete_actions
	
	add_filter( "install_plugin_complete_actions", '\SuperadminHelper\Logging\install_plugin_complete_actions', 10, 3 );
	
	function install_plugin_complete_actions( $install_actions, $api, $plugin_file ) {
		Log::log( 'Installing plugin "'.$plugin_file.'".', 3 );
		return $install_actions;
	}
	
	
	// update_theme_complete_actions
	
	add_filter( "update_theme_complete_actions", '\SuperadminHelper\Logging\update_theme_complete_actions', 10, 2 );
	
	function update_theme_complete_actions( $update_actions, $theme ) {
		Log::log( 'Upgrading theme "'.$theme.'".', 3 );
		return $update_actions;
	}
	
	
	// install_theme_complete_actions
	
	add_filter( "install_theme_complete_actions", '\SuperadminHelper\Logging\install_theme_complete_actions', 10, 4 );
	
	function install_theme_complete_actions( $install_actions, $api, $stylesheet, $theme_file ) {
		Log::log( 'Installing theme "'.$theme_file.'".', 3 );
		return $install_actions;
	}
	
	
	// blog_redirect_404
	
	add_filter( "blog_redirect_404", '\SuperadminHelper\Logging\blog_redirect_404' );
	
	function blog_redirect_404( $redirect ) {
		$settings = Settings::getInstance();
		if( $settings->log_blog_redirect_404 ) {
			Log::log( 'Requested a non-available page "'.$_SERVER['REQUEST_URI'].'", redirecting to "'.$redirect.'".', 1 );
		}
		return $redirect;
	}
	
}

?>
