<?php

/** Assure database compatibility through SUH versions */
namespace SuperadminHelper\Compatibility {
	
	use \SuperadminHelper\Log;
	use \SuperadminHelper\Settings;
	use \SuperadminHelper\Permban as pb;
	
	/** Database version required by the plugin. */
	const DATABASE_VERSION = 5;
	
	
	add_action( "plugins_loaded", '\SuperadminHelper\Compatibility\db_version_check' );
	
	
	/** Compare stored database version with required version of the plugin and
	 * gradually upgrade if neccessary.
	 */
	function db_version_check() {
		
		$settings = Settings::getInstance();
		
		//Log::debug( "settings->db_version = {$settings->db_version}" );
		
		if( $settings->db_version == DATABASE_VERSION ) {
			return;
		}
		
		$lock_name = 'DATABASE_VERSION_update';
		
		if( $settings-> db_version < 2 ) {
			// convert from multiple site options to one
			$settings->banned_message = get_site_option( 'suh_banned_message' );
			$settings->is_permban_active = ( get_site_option( 'suh_permban_active', 'false' ) == 'true' );
			$settings->permban_ready = get_site_option( 'suh_permban_ready', false );
			$settings->db_version = 2;

			delete_site_option( 'suh_banned_message' );
			delete_site_option( 'suh_permban_active' );
			delete_site_option( 'suh_permban_ready' );
			
			Log::info( 'SUH_DB_VERSION upgraded from 1 to 2.');
			$settings->db_version = 2;
			
			$settings->save();
		}
		
		/* Now db_version is at least 2. */
		
		if( $settings->db_version < 3 ) {
			Log::debug( 'Upgrading DATABASE_VERSION from 2 to 3.' );
			if( $settings->permban_ready ) {
				// remove duplicate ip entries and alter permban table so that it is unique
				// (only if we have the table already created)
				Log::debug( 'Removing duplicate ip entries in suh_permban_table.' );
				global $wpdb;
				if( $wpdb->get_var( 'SELECT IS_FREE_LOCK(\''.$lock_name.'\')' ) != '1' ) {
					return;
				}
				$locked = $wpdb->get_var( 'SELECT GET_LOCK(\''.$lock_name.'\', 0)' );
				if( $locked != '1' ) {
					Log::debug( 'Cannot get lock because: '.$wpdb->last_error );
					return;
				}
				if( $wpdb->query( "ALTER IGNORE TABLE " . pb\tn( pb\PERMBAN ) . " ADD UNIQUE INDEX(ip)" ) === FALSE ) {
					Log::fatal(
							sprintf (
									'MySQL while upgrading DATABASE_VERSION.<br/>last query: "%s"<br/>result: "%s"<br/>error: %s.',
									$wpdb->last_query,
									print_r( $wpdb->last_result, true ),
									$wpdb->last_error ) );
					/*if( is_admin() ) {
						suh_nagerr( 'Database error when upgrading Superadmin Helper. Please contact plugin developer.' );
					}*/
					$wpdb->query( 'SELECT RELEASE_LOCK(\''.$lock_name.'\')' );
					return;
				}
				$wpdb->query( 'SELECT RELEASE_LOCK(\''.$lock_name.'\')' );
	
			}
			$settings->db_version = 3;
			$settings->save();
			Log::info( 'DATABASE_VERSION upgraded from 2 to 3.');
		}
		
		/* Now db_version is at least 3 */
		
		if( $settings->db_version < 4 ) {
			Log::debug( 'Upgrading DATABASE_VERSION from 3 to 4.' );
			if( $settings->permban_ready ) {
				global $wpdb;
				if( $wpdb->get_var( 'SELECT IS_FREE_LOCK(\''.$lock_name.'\')' ) != '1' ) {
					return;
				}
				$locked = $wpdb->get_var( 'SELECT GET_LOCK(\''.$lock_name.'\', 0)' );
				if( $locked != '1' ) {
					Log::debug( 'Cannot get lock because: '.$wpdb->last_error );
					return;
				}
				if( $wpdb->query( "ALTER TABLE " . pb\tn( pb\PERMBAN ) . " ADD COLUMN (attempt_count INT DEFAULT 0, last_attempt DATETIME)" ) === FALSE ) {
					Log::fatal(
							sprintf (
									'MySQL while upgrading DATABASE_VERSION.<br/>last query: "%s"<br/>result: "%s"<br/>error: %s.',
									$wpdb->last_query,
									print_r( $wpdb->last_result, true ),
									$wpdb->last_error ) );
					/*if( is_admin() ) {
						suh_nagerr( 'Database error when upgrading Superadmin Helper. Please contact plugin developer.' );
					}*/
					$wpdb->query( 'SELECT RELEASE_LOCK(\''.$lock_name.'\')' );
					return;
				}
				$wpdb->query( 'SELECT RELEASE_LOCK(\''.$lock_name.'\')' );
			} else {
				Log::debug( 'No action needed.' );
			}
			$settings->db_version = 4;
			$settings->save();
			Log::info( 'DATABASE_VERSION upgraded from 3 to 4.' );
		}
		
		/* Now db_version is at least 4 */
		
		if( $settings->db_version < 5 ) {
			
			Log::debug( "Upgrading DATABASE_VERSION from 4 to 5." );
			//Log::debug( "before: settings->permban_ready = {$settings->permban_ready}, settings->db_version = {$settings->db_version}" );
			if( !$settings->permban_ready ) {
				Log::debug( "Permban table was not present, creating it now." );
				pb\create_tables();
			}
			$settings->permban_ready = true;
			$settings->db_version = 5;
			$settings->save();
			Log::info( "DATABASE_VERSION upgraded from 4 to 5." );
			//Log::debug( "after: settings->permban_ready = {$settings->permban_ready}, settings->db_version = {$settings->db_version}" );
		}
		
		/* Now db_version is at least 5 */
	}
	
}

?>