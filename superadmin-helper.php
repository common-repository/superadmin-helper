<?php
/*
Plugin Name: Superadmin helper
Description: Set of utilities for managing multisite Wordpress installations. Logging, simple permban, etc.
Version: 2.0.5
Author: Zaantar
Author URI: http://zaantar.eu
Donate Link: http://zaantar.eu/financni-prispevek
Plugin URI: http://wordpress.org/plugins/superadmin-helper
License: GPL2
*/

/*
    Copyright 2010-2014 Zaantar (email: zaantar@zaantar.eu)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

namespace SuperadminHelper {
	
	
	/* Assure that 'class-wp-list-table.php' is available. */
	if( !class_exists( 'WP_List_Table' ) ) {
		require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
	}
	
	
	/* Include all neccessary plugin files */
	$includes = array(
			"compatibility.php",
			"legacy.php",
			"logging.php",
			"permban-table.php",
			"permban-ui.php",
			"permban.php",
			"primary-blog-setting.php",
			"record-last-login-time.php",
			"settings-ui.php",
			"settings.php",
			"zan.php" );

	
	/* Include the files defined above */
	foreach( $includes as $include ) {
		require_once plugin_dir_path( __FILE__ ) . "includes/$include";
	}
	
	
	/* Installation */
	register_activation_hook( __FILE__, '\SuperadminHelper\install' );
	
	function install() {
		Permban\create_tables();
	}
	
	
	/** Slug of the main plugin page in admin area */
	const PAGE = "superadmin-helper";
	
	
	/** Text domain name for i18n */
	define( "SUH_TXD", "superadmin-helper" );
	
	
	/* I18N */
	add_action( 'init', "\SuperadminHelper\load_textdomain" );
	
	function load_textdomain() {
		$plugin_dir = basename(dirname(__FILE__));
		load_plugin_textdomain( SUH_TXD, false, $plugin_dir.'/languages' );
	}
	
	
	/* Logging */
	const LOGNAME = "suh-log";
	const LOGNAME_MAIL = "suh-mail";
	
	class Log {
	
		/* This is a static class */
		private function __construct() { }
	
		static function dberror( $action ) {
			global $wpdb;
			self::log( "Database error while performing action '$action': QUERY '{$wpdb->last_query}'; RESULT '".print_r( $wpdb->last_result, true )."'; ERROR '{$wpdb->last_error}'.", 4 );
		}
	
	
		static function log( $message, $severity ) {
			if( defined( 'WLS' ) && wls_is_registered( LOGNAME ) ) {
				wls_simple_log( LOGNAME, $message, $severity );
			}
		}
	
	
		static function debug( $message ) {
			self::log( $message, 1 );
		}
	
		static function info( $message ) {
			self::log( $message, 2 );
		}
	
		static function warning( $message ) {
			self::log( $message, 3 );
		}
	
		static function error( $message ) {
			self::log( $message, 4 );
		}
	
		static function fatal( $message ) {
			self::log( $message, 5 );
		}
	
	
		static function register() {
			if( defined( "WLS" ) ) {
				$ok1 = wls_register( LOGNAME, __( 'Basic information about events in multisite network.', SUH_TXD ) );
				$ok2 = wls_register( LOGNAME_MAIL, __( 'List of e-mails sent by WordPress', SUH_TXD ) );
				return $ok1 && $ok2;
			} else {
				return false;
			}
		}
	
	
		static function unregister() {
			if( defined( "WLS" ) ) {
				$ok1 = wls_unregister( LOGNAME );
				$ok2 = wls_unregister( LOGNAME_MAIL );
				return $ok1 && $ok2;
			} else {
				return false;
			}
		}
	
	}
	
	
	/** Get HTTP client's IP address */
	function get_ip() {
		if(!empty($_SERVER['HTTP_CLIENT_IP'])) {
			$ip_address = $_SERVER['HTTP_CLIENT_IP'];
		} else if(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else if(!empty($_SERVER['REMOTE_ADDR'])) {
			$ip_address = $_SERVER['REMOTE_ADDR'];
		} else {
			$ip_address = '';
		}
		if(strpos($ip_address, ',') !== false) {
			$ip_address = explode(',', $ip_address);
			$ip_address = $ip_address[0];
		}
		return esc_attr($ip_address);
	}
	
	
}

?>
