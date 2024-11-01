<?php

/** Permban overview and management. */
namespace SuperadminHelper\PermbanUI {
	
	use \SuperadminHelper\Log;
	use \SuperadminHelper\Settings;
	use \SuperadminHelper\z;
	use \SuperadminHelper\Permban as pb;
	
	const PAGE = "suh-permban";
	
	/* Menu for admin and network admin area */
	add_action( "network_admin_menu", '\SuperadminHelper\PermbanUI\network_permban_menu' );
	
	function network_permban_menu() {
		add_submenu_page(
				'users.php',
				__( 'Permban', SUH_TXD ),
				__( 'Permban', SUH_TXD ),
				'manage_network_users',
				PAGE,
				"\SuperadminHelper\PermbanUI\page_handler" );
	}
	
	add_action( "admin_menu", '\SuperadminHelper\PermbanUI\permban_menu' );
	
	function permban_menu() {
		if( !is_multisite() ) {
			add_submenu_page(
					'users.php',
					__( 'Permban', SUH_TXD ),
					__( 'Permban', SUH_TXD ),
					'manage_options',
					PAGE,
					"\SuperadminHelper\PermbanUI\page_handler" );
		}
	}
	
	
	/** Page handler */
	function page_handler() {
		
		$action = isset( $_REQUEST["action"] ) ? $_REQUEST["action"] : "default";
		$settings = Settings::getInstance();
		
		?>
			<div class="wrap">
		<?php
	
		switch( $action ) {

			case "add-permban":
				pb\add_permban( $_GET["ip"] );
				show_default_page();
				break;
				
			case 'remove_permban':
				pb\remove_permban( $_GET['id'] );
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
	
	
	/* Add CSS style for permban table */
	add_action( "admin_head", '\SuperadminHelper\PermbanUI\admin_head' );
	
	
	function admin_head() {
		$page = ( isset($_REQUEST['page'] ) ) ? esc_attr( $_REQUEST['page'] ) : false;
		if( PAGE != $page ) {
			return;
		}
	
		?>
		<style type="text/css">
			.wp-list-table .column-cb { width: 5%; }
			.wp-list-table .column-ip { width: 15%; }
			.wp-list-table .column-attempts { width: 10%; }
			.wp-list-table .column-last_attempt { width: 10%; }
			.wp-list-table .column-notes { width: 60%; }
		</style>
		<?php
	}
	
	
	function show_default_page() {
	
		$permban_table = new PermbanTable();
		$permban_table->prepare_items();
	
		?>
		<h2><?php _e( 'Permban', SUH_TXD ); ?></h2>
		<form method="get">
			<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
			<input type="hidden" name="action" value="add-permban" />
			<label><?php _e( "Add IP", SUH_TXD ); ?>: </label>
			<input type="text" name="ip" />
			<input type="submit" class="button-primary" value="<?php _e( 'Add', SUH_TXD ); ?>" />
		</form>
		<form method="get">
			<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
			<?php $permban_table->search_box( __( "Search", SUH_TXD ), 'standard' ); ?>
			<?php $permban_table->display(); ?>
		</form>
		<?php
	}

	
}

?>