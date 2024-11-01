<?php

/** Banning IPs from which is someone trying to use a banned account. */
namespace SuperadminHelper\Permban {
	
	use \SuperadminHelper\Log;
	use \SuperadminHelper\Settings;
	use \SuperadminHelper\z;
	
	
	/* Table names */
	const PERMBAN = "permban";
	
	
	/** Returns a table name for provided constant (must be one of the above). */
	function tn( $table_name ) {
		global $wpdb;
		return $wpdb->base_prefix . "suh_" . $table_name;
	}
	
	
	/** Create table for permban */
	function create_tables() {
		global $wpdb;
		
		$ok = $wpdb->query(
				"CREATE TABLE IF NOT EXISTS " . tn( PERMBAN ) . " (
		            id INT NOT NULL AUTO_INCREMENT,
		            ip VARCHAR( 63 ) UNIQUE,
		            attempt_count INT DEFAULT 0,
		            last_attempt DATETIME,
		            PRIMARY KEY ( id )
		        )" );
		
		if( $ok ) {
			Log::log( 'Successfully created permban table.', 2 );
		} else {
			Log::log( 'Error while creating permban table.', 5 );
		}
	}
	
	
	/* Block banned IP address. */
	add_action( "init", '\SuperadminHelper\Permban\ban_check');
	
	function ban_check() {
		
		$settings = Settings::getInstance();
		
		if( $settings->is_permban_active ) {
			
			$ip = \SuperadminHelper\get_ip();
			
			if( !is_user_logged_in() && is_ip_banned( $ip ) ) {

				if( $settings->log_banned_attempt ) {
					Log::log( 'Attempt to access from banned IP '.$ip.'.', 1 );
				}
				increment_attempt_count( $ip );
	
				wp_die( $settings->banned_message, __( "Permban", SUH_TXD ) );
				exit();
			}
		}
	}
	
	
	/** Check if IP is banned. */
	function is_ip_banned( $ip ) {
		global $wpdb;
		$ban_count = $wpdb->get_var(
				$wpdb->prepare( "SELECT COUNT(1) FROM " . tn( PERMBAN ) . " WHERE ip = %s", $ip ) );
		return ( $ban_count > 0 );
	}
	
	
	/** Increment access attempt count for given IP */
	function increment_attempt_count( $ip ) {
		global $wpdb;
		
		/* Lock permban table */
		$lock_name = "suh_increment_attempt_count($ip)";
		$locked = $wpdb->get_var( "SELECT GET_LOCK('$lock_name', 30)" );
		if( $locked != 1 ) {
			/* fail. */
			return;
		}
		
		/* Update attempt count */
		$wpdb->query(
				$wpdb->prepare(
						"UPDATE " . tn( PERMBAN ) . " SET attempt_count = attempt_count + 1, last_attempt = NOW() WHERE ip LIKE %s",
						$ip ) );
		
		/* Unlock */
		$wpdb->query( "SELECT RELEASE_LOCK('$lock_name')" );
	}
	
	
	/* Process failed login */
	add_action( "wp_login_failed", '\SuperadminHelper\Permban\process_failed_login' );
	
	function process_failed_login( $username ) {
	
		$settings = Settings::getInstance();
		
		/* If permban should not be active, abort */
		if( !$settings->is_permban_active ) {
			return;
		}
		
		$user = get_userdatabylogin( $username );
		$ip = \SuperadminHelper\get_ip();
		
		/* Check whether user exists. */
		if ( !$user || ($user->user_login != $username) ) {
			
			/* User doesn't exist. Check if ban should be created. */
			$forbidden_logins = explode( ',', $settings->forbidden_logins );
			if( in_array( $username, $forbidden_logins ) ) {
				if( $settings->log_permbans ) {
					Log::log( "Login failed: unknown user '$username' (IP $ip). Creating a permban for this IP address.", 3 );
				}
				add_permban( $ip );
			} else {
				Log::log( "Login failed: unknown user '$username' (IP $ip)", 4 );
			}
			
		} else {
			/* User exists. */
			Log::log( 'Login failed: incorrect password for '.$username.' (IP '.$ip.').', 4 );
		}
	}
	
	
	/** Add IP to permban table */
	function add_permban( $ip ) {
		
		$settings = Settings::getInstance();

		global $wpdb;
		
		$inserted = $wpdb->query(
				$wpdb->prepare(
						"INSERT INTO " . tn( PERMBAN ) . " (ip, attempt_count) VALUE (%s, 1) ON DUPLICATE KEY UPDATE attempt_count = attempt_count + 1",
						$ip ) );
		
		if( $settings->log_permbans ) {
			if( $inserted == false ) {
				Log::dberror( "creating permban for IP $ip." );
			}
		}
		
	}
	
	
	function remove_permban( $id ) {
		global $wpdb;
		return !( $wpdb->query( $wpdb->prepare( 'DELETE FROM '. tn( PERMBAN ).' WHERE id = %d', $id ) ) === FALSE );
	}
	
	
	/** Get permban list */
	function get_permbans( $search = "", $orderby = "last_attempt", $order = "DESC", $limit = NULL, $offset = 0 ) {
		global $wpdb;
		$limit = ( $limit == NULL ) ? "" : $wpdb->prepare( "LIMIT %d OFFSET %d", $limit, $offset );
		if( !empty( $search ) ) {
			$where = $wpdb->prepare( " WHERE ip LIKE %s", "%$search%" );
		} else {
			$where = "";
		}
		
		$query = "SELECT * FROM ". tn( PERMBAN ) . " $where ORDER BY `$orderby` $order $limit";
		
		$results = $wpdb->get_results( $query );
		return $results;
	}
	
	
	function get_permban_count( $search = "" ) {
		global $wpdb;
		
		if( !empty( $search ) ) {
			$where = $wpdb->prepare( " WHERE ip LIKE %s", "%$search%" );
		} else {
			$where = "";
		}
		
		return $wpdb->get_var( "SELECT COUNT(1) FROM " . tn( PERMBAN ) . $where );
	}
	
	
	
	function get_possible_comment_authors( $ip ) {
		global $wpdb;
		$blog_query = 'SELECT blog_id FROM '.$wpdb->base_prefix.'blogs';
		$blog_list = $wpdb->get_col( $blog_query );
		$comment_subqueries = array();
		foreach( $blog_list as $blog_id ) {
			$comment_subqueries[] = $wpdb->prepare(
					'SELECT comment_author, comment_author_email, comment_author_url, comment_ID, comment_post_ID
					FROM '.$wpdb->base_prefix.$blog_id.'_comments
					WHERE comment_author_IP = %s',
					$ip	);
		}
		$comment_query = '( '.implode( ' ) UNION ( ', $comment_subqueries ).' )';
		$comments = $wpdb->get_results( $comment_query );
		return $comments;
	}
	
}

?>