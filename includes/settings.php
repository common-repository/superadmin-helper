<?php

namespace SuperadminHelper {
	
	/** Singleton providing access to SuperadminHelper settings. */
	class Settings {
	
		/** Name of the WordPress option where all settings are stored. */
		const SETTINGS_KEY = "suh_settings";
	
	
		/** Cached settings. We usually only read them and they don't change from
		 * the outside during execution. */
		private static $cache = false;
	
	
		/** Default values for settings. */
		private static $defaults = array(
				'is_permban_active' => false,
				'forbidden_logins' => 'admin',
				'banned_message' => '',
				'hide_donation_button' => false,
				'permban_ready' => false,
				'db_version' => 1,
				'log_blog_redirect_404' => true,
				'log_wp_mail' => true,
				'log_wp_login' => true,
				'log_wp_logout' => true,
				'log_permbans' => true );
	
	
		private static $instance = false;
	
	
		static function getInstance() {
			if( self::$instance === false ) {
				self::$instance = new Settings();
			}
			return self::$instance;
		}
	
	
		/** Load settings into the cache. */
		private function __construct() {
			self::$cache = wp_parse_args( get_site_option( self::SETTINGS_KEY, array(), false ), self::$defaults );
			//Log::debug( "SUH loaded settings: " . print_r( self::$cache, true ) );
		}
	
	
		/** Dynamic property getter - allows to access settings like $instance->setting_name.
		 *
		 * @return value of the setting or NULL if such setting doesn't exist.
		 */
		function __get( $property ) {
			return array_key_exists( $property, self::$cache ) ? self::$cache[$property] : NULL;
		}
	
	
		/** Dynamic property setter - allows to assign a value to a setting like
		 * $instance->setting_name = new_value. Note that settings must be saved to
		 * the database by save() to make them persistent.
		 */
		function __set( $property, $value ) {
			//Log::debug( "SUH setting $property := $value." );
			self::$cache[$property] = $value;
		}
	
	
		/** Save settings from cache to database. */
		function save() {
			//Log::debug( "SUH saving settings: " . print_r( self::$cache, true ) );
			update_site_option( self::SETTINGS_KEY, self::$cache );
		}
	
	
		/** Overwrite the cache. */
		function set( $data ) {
			self::$cache = $data;
		}
	
	}
	
}

?>