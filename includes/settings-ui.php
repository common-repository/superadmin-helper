<?php

/** Admin pages for SUH setup. */
namespace SuperadminHelper\SettingsUI {
	
	use \SuperadminHelper\Log;
	use \SuperadminHelper\Settings;
	use \SuperadminHelper\z;
	
	const PAGE = "suh-options";
	
	function suh_get_pagenow( $page = '' ) {
		global $pagenow;
		$result = $pagenow;
		if( empty( $page ) ) {
			$result.= '?page='.$_GET['page'];
		} else {
			$result.= '?page='.$page;
		}
		return $result;
	}
	
	/* Add admin menus for admin or network admin area */
	
	add_action( "network_admin_menu", '\SuperadminHelper\SettingsUI\network_admin_menu' );
	
	function network_admin_menu() {
		add_submenu_page(
				'settings.php',
				__( 'Superadmin helper settings' , SUH_TXD ),
				__( 'Superadmin helper', SUH_TXD ),
				'manage_network_options',
				PAGE,
				"\SuperadminHelper\SettingsUI\options_page_handler" );
	}
	
	
	add_action( "admin_menu", '\SuperadminHelper\SettingsUI\admin_menu' );
	
	function admin_menu() {
		if( is_multisite() ) {
			return;
		}
		add_submenu_page(
				'options-general.php',
				__( 'Superadmin helper settings', SUH_TXD ),
				__( 'Superadmin helper', SUH_TXD ),
				'manage_options',
				PAGE,
				'\SuperadminHelper\SettingsUI\options_page_handler' );
	}
	
	
	function options_page_handler() {
		
		$action = isset( $_REQUEST["action"] ) ? $_REQUEST["action"] : "default";
		$settings = Settings::getInstance();
		
		?>
			<div class="wrap">
		<?php
		
		switch( $action ) {
			
			case 'update-options':
				$ps = $_POST["settings"];
				$settings->is_permban_active = isset( $ps["is_permban_active"] );
				$settings->forbidden_logins = $ps["forbidden_logins"];
				$settings->banned_message = $ps["banned_message"];
				$settings->hide_donation_button = isset( $ps["hide_donation_button"] );
				$settings->permban_ready = isset( $ps["permban_ready"] );
				$settings->db_version = $ps["db_version"];
				$settings->log_blog_redirect_404 = isset( $ps["log_blog_redirect_404"] );
				$settings->log_wp_mail = isset( $ps["log_wp_mail"] );
				$settings->log_wp_login = isset( $ps["log_wp_login"] );
				$settings->log_wp_logout = isset( $ps["log_wp_logout"] );
				$settings->log_permbans = isset( $ps["log_permbans"] );
				$settings->save();
				z::nag( __( 'Settings saved', SUH_TXD ) );
				show_default_page();
				break;
				
			case 'wls-register':
				$ok = Log::register();
				if( $ok ) {
					z::nag( __( 'Superadmin Helper was successfully registered with WLS.', SUH_TXD ) );
				} else {
					z::nagerr( __( 'Error while trying to register Superadmin Helper with WLS.', SUH_TXD ) );
				}
				show_default_page();
				break;
				
			case 'wls-unregister':
				$ok = Log::unregister();
				if( $ok ) {
					z::nag( __( 'Superadmin Helper successfully unregistered from WLS, log entries deleted.', SUH_TXD ) );
				} else {
					z::nagerr( __( 'Error while trying to unregister Superadmin Helper from WLS', SUH_TXD ) );
				}
				show_default_page();
				break;
				
			default:
				show_default_page();
				break;
		}
		
		?>
			</div>
		<?php
	}
	
	
	function show_default_page() {
		$settings = Settings::getInstance();
		
		?>
		
		<h2><?php _e( 'Superadmin Helper options', SUH_TXD ); ?></h2>
		
		<?php
		
			/* Donation button */
			z::maybe_donation_button( $settings->hide_donation_button, SUH_TXD );

			/* WLS registration */
			if( defined( 'WLS' ) ) {
				?>
				<h3><?php _e( 'Registration with WLS', SUH_TXD ); ?></h3>
		    	<p><?php _e( 'Wordpress Logging Service was detected.', SUH_TXD ); ?></p>
	    		<?php
	    			if( !wls_is_registered( \SuperadminHelper\LOGNAME ) || !wls_is_registered( \SuperadminHelper\LOGNAME_MAIL ) ) {
	    				?>
						<form method="post"">
							<input type="hidden" name="action" value="wls-register" />
							<?php submit_button( __( "Register with WLS", SUH_TXD ), "primary" ); ?>
						</form>
						<?php
					} else {
						?>
						<form method="post">
							<input type="hidden" name="action" value="wls-unregister" />
							<?php submit_button( __( "Unregister from WLS and delete log entries", SUH_TXD ), "primary", "submit", true, array( "style" => "color: yellow; font-weight: bold;" ) ); ?>
						</form>
						<?php
					}
				?>
				<?php
        	}
        ?>
        
        <h3><?php _e( 'Permban', SUH_TXD ); ?></h3>
        <form method="post">
            <input type="hidden" name="action" value="update-options" />
            <input type="hidden" name="settings[db_version]" value="<?php echo $settings->db_version; ?>" />
            <input type="hidden" name="settings[permban_ready]" value="<?php echo $settings->permban_ready; ?>" />
            <table class="form-table">
                <tr valign="top">
                	<th>
                		<label for="settings[is_permban_active]">
                			<?php _e( 'Activate IP permban when someone tries to login with forbidden username', SUH_TXD ); ?>
	                	</label>
	                </th>
                	<td>
                		<input type="checkbox" name="settings[is_permban_active]" <?php checked( $settings->is_permban_active ); ?>" />
                	</td>
                </tr>
                <tr valign="top">
                	<th>
                		<label for="settings[forbidden_logins]">
                			<?php _e( 'Forbiden logins', SUH_TXD ); ?>
                		</label>
                	</th>
                	<td>
                		<input type="text" name="settings[forbidden_logins]" value="<?php echo $settings->forbidden_logins; ?>" />
                	</td>
                	<td>
                		<small><?php printf( __( 'If anyone tries to login with this account names, their IP will be banned. You can separate multiple logins by %s.', SUH_TXD ), '<code>,</code>' ); ?></small>
                	</td>
                </tr>
                <tr valign="top">
                	<th>
                		<label for="settings[banned_message]">
                			<?php _e( 'Message to be shown to blocked viewers', SUH_TXD ); ?>
                		</label>
                	</th>
                	<td>
                		<textarea name="settings[banned_message]" cols="60" rows="15" style="font-family: monospace;"><?php
                			echo stripslashes( $settings->banned_message );
                		?></textarea>
                	</td>
                </tr>
           </table>
           <h3><?php _e( 'Event logging', SUH_TXD ); ?></h3>
           <table class="form-table">
                <tr valign="top">
                	<th>
                		<label>Attempt to access site from banned IP</label><br />
                	</th>
                	<td>
                		<input type="checkbox" name="settings[log_banned_attempt]" <?php checked( $settings->log_banned_attempt ); ?> />
                	</td>
                	<td><small><?php _e( 'If checked, every attempt to access this site from a banned IP will be logged.', SUH_TXD ); ?></small></td>
                </tr>
                <tr valign="top">
                	<th>
                		<label>Attempt to login as banned user and IP banning</label><br />
                	</th>
                	<td>
                		<input type="checkbox" name="settings[log_permbans]" <?php checked( $settings->log_permbans ); ?> />
                	</td>
                	<td><small><?php _e( 'If checked, every attempt to login with banned username and a creation of permban will be logged.', SUH_TXD ); ?></small></td>
                </tr>
                <tr valign="top">
                	<th>
                		<label><code>wp_mail</code></label><br />
                	</th>
                	<td>
                		<input type="checkbox" name="settings[log_wp_mail]" <?php checked( $settings->log_wp_mail ); ?> />
                	</td>
                	<td><small><?php printf( __( 'If checked, every e-mail send by WordPress will be logged in %s category.', SUH_TXD ), '<code>suh-mail</code>' ); ?></small></td>
                </tr>
                <tr valign="top">
                	<th>
                		<label><code>wp_login</code></label><br />
                	</th>
                	<td>
                		<input type="checkbox" name="settings[log_wp_login]" <?php checked( $settings->log_wp_login ); ?> />
                	</td>
                	<td><small><?php _e( 'Successful logins', SUH_TXD ); ?></small></td>
                </tr>
                <tr valign="top">
                	<th>
                		<label><code>wp_logout</code></label><br />
                	</th>
                	<td>
                		<input type="checkbox" name="settings[log_wp_logout]" <?php checked( $settings->log_wp_logout ); ?> />
                	</td>
                </tr>
                <tr valign="top">
                	<th>
                		<label><code>blog_redirect_404</code></label><br />
                	</th>
                	<td>
                		<input type="checkbox" name="settings[log_blog_redirect_404]" <?php checked( $settings->log_blog_redirect_404 ); ?> />
                	</td>
                	<td><small><?php _e( 'Works if NOBLOGREDIRECT is defined.', SUH_TXD ); ?></small></td>
                </tr>
			</table>
			
			<h3><?php _e( 'Other settings', SUH_TXD ); ?></h3>
           	<table class="form-table">
                <tr valign="top">
                	<th>
                		<label><?php _e( 'Hide donation button', SUH_TXD ); ?></label><br />
                	</th>
                	<td>
                		<input type="checkbox" name="settings[hide_donation_button]" <?php checked( $settings->hide_donation_button ); ?> />
                	</td>
                	<td><small><?php _e( 'If you don\'t want to be bothered again...', SUH_TXD ); ?></small></td>
                </tr>
			</table>
			<?php submit_button( __( 'Save', SUH_TXD ), "primary" ); ?>
		</form>
		<?php
	}
	
}


?>