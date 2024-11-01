<?php

/** Remember users' last login time and show it in user table */
namespace SuperadminHelper\RecordLastLoginTime {
	
	
	const LAST_LOGIN_META_KEY = "suh_last_login";
	
	
	add_action( "wp_login", '\SuperadminHelper\RecordLastLoginTime\save_last_login' );
	
	/** Save (current) login time in user's meta */
	function save_last_login( $username ) {
		$userdata = get_userdatabylogin( $username );
		update_user_meta( $userdata->ID, LAST_LOGIN_META_KEY, date( "Y-m-d H:i" ) );
	}
	
	
	add_filter( "wpmu_users_columns", '\SuperadminHelper\RecordLastLoginTime\add_last_login_column' );
	add_filter( "manage_users_columns", '\SuperadminHelper\RecordLastLoginTime\add_last_login_column' );
	
	/** Show column with last login times in user table */
	function add_last_login_column( $columns ) {
		if( is_super_admin() or ( is_network_admin() && is_multisite() ) or ( !is_network_admin() && !is_multisite() ) ) {
			$columns['suh_last_login'] = __( 'Last login', SUH_TXD );
		}
		add_action( "manage_users_custom_column",  '\SuperadminHelper\RecordLastLoginTime\add_last_login_column_value', 20, 3);
		return $columns;
	}
	
	
	/** Populate column with login values */
	function add_last_login_column_value( $value, $column_name, $user_id ) {
		if( $column_name == 'suh_last_login' ) {
			$last_login = get_user_meta( $user_id, LAST_LOGIN_META_KEY, true );
			if( $last_login == '' ) {
				return "N/A";
			} else {
				return $last_login;
			}
		}
	}
	
	
	/*
	TODO
	
	add_filter( 'manage_users_sortable_columns', 'suh_make_last_login_column_sortable' );
	function suh_make_last_login_column_sortable( $columns ) {
	if( is_super_admin() or ( !is_network_admin() && !is_multisite() ) ) {
	$columns['suh_last_login'] = 'suh_last_login';
	}
	return $columns;
	}
	
	
	add_filter( 'request', 'suh_last_login_orderby' );
	function suh_last_login_orderby( $vars ) {
	if( ( is_super_admin() or ( !is_network_admin() && !is_multisite() ) )
			&& isset( $vars['orderby'] ) && $vars['orderby'] == 'suh_last_login' ) {
	$vars = array_merge( $vars, array(
			'meta_key' => 'suh_last_login',
			'orderby' => 'meta_value'
	) );
	}
	return $vars;
	}*/
	
}

?>