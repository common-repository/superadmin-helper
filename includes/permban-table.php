<?php

namespace SuperadminHelper\PermbanUI {
	
	use \SuperadminHelper\Settings;
	use \SuperadminHelper\Permban as pb;

	/** Table showing banned IPs. */
	class PermbanTable extends \WP_List_Table {

		function __construct() {
			parent::__construct( array(
				"singular" => __( "permban", SUH_TXD ),
				"plural" => __( "permbans", SUH_TXD ),
				"ajax" => false
			) );
		}
		
		
		function get_columns() {
			$columns = array(
				'cb' => '<input type="checkbox" />',
				"ip" => __( "IP", SUH_TXD ),
				"attempts" => __( "Attempts", SUH_TXD ),
				"last_attempt" => __( "Last attempt", SUH_TXD ),
				"notes" => __( "Notes", SUH_TXD )
			);
			return $columns;
		}
		
		
		function get_sortable_columns() {
			return array(
				"ip" => array( "ip", false ),
				"attempts" => array( "attempt_count", false ),
				"last_attempt" => array( "last_attempt", false )
			);
		}
		
		
		function get_bulk_actions() {
			$actions = array(
				"remove_permbans" => __( "Remove permbans", SUH_TXD )
			);
			return $actions;
		}
		
		
		function prepare_items() {
			
			$per_page = 50;
			
			$columns = $this->get_columns();
			$hidden = array();
			$sortable = $this->get_sortable_columns();
			
			$this->_column_headers = array( $columns, $hidden, $sortable );
			
			$this->process_bulk_action();
			
			$search = isset( $_REQUEST["s"] ) ? $_REQUEST["s"] : "";
			$orderby = isset( $_REQUEST["orderby"] ) ? $_REQUEST["orderby"] : "last_attempt";
			$order = isset( $_REQUEST["order"] ) ? $_REQUEST["order"] : "ASC";
			
			$current_page = $this->get_pagenum();
			
			$this->items = pb\get_permbans( $search, $orderby, $order, $per_page, ( $current_page - 1 ) * $per_page );
			
			$total_items = pb\get_permban_count( $search );
			
			$this->set_pagination_args( array(
	            'total_items' => $total_items,
	            'per_page'    => $per_page,
	            'total_pages' => ceil( $total_items / $per_page )
	        ) );
		}
		
		
		function column_cb( $item ){
	        return sprintf(
	            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
	            "permban",
	            $item->id
	        );
	    }
		
		
		function column_ip( $item ) {
			$actions = array(
					"remove_permban" => sprintf(
								"<a href=\"?page=%s&action=%s&id=%s\">%s</a>",
								$_REQUEST["page"], "remove_permban", $item->id, __( "Remove permban", SUH_TXD )
								) );
			
			return "<code><strong>{$item->ip}</strong></code>{$this->row_actions($actions)}";
		}
		
		
		function column_attempts( $item ) {
			return $item->attempt_count;
		}
		
		
		function column_last_attempt( $item ) {
			if( $item->attempt_count > 0 ) {
				return $item->last_attempt;
			} else {
				return "";
			}
		}
		
		
		function column_notes( $item ) {
			// attempt count
			/*$ac = sprintf( __( "There have been %s attempts to access site from this IP since it was banned.", SUH_TEXTDOMAIN ),
				$item->attempt_count
			);
			if( $item->attempt_count > 0 ) {
				$ac .= " ".sprintf( __( "Last one occured at %s.", SUH_TEXTDOMAIN ), $item->last_attempt );
			}*/
			
			// attack origins
			
			$origins = array();
			
			// comment authors
			$comments = pb\get_possible_comment_authors( $item->ip );
			if( !empty( $comments ) ) {
				_e( 'Possible origins of attack / affected visitors', SUH_TXD );
				$authors = array();
				foreach( $comments as $comment ) {
					$author = $comment->comment_author;
					if( !empty( $comment->comment_author_url ) ) {
						$author = '<a href="'.$comment->comment_author_url.'">'.$author.'</a>';
					}
					$author.= ' (<a href="mailto:'.$comment->comment_author_email.'">'.$comment->comment_author_email.'</a>)';
					$authors[] = $author;
				}
				$origins[] = implode( ', ', $authors );
			}
			
			// WLS entries
			if( defined( 'WLS' ) && function_exists( 'wls_entries_table' ) ) {
				global $wpdb;
				$log_entries = $wpdb->get_results( $wpdb->prepare(
					'SELECT id, text FROM '.wls_entries_table().' WHERE (
						text LIKE %s
						AND text NOT LIKE %s
						AND text NOT LIKE %s
					)',
					'%'.$item->ip.'%',
					'%Attempt to access from banned IP%',
					'%Permban for % successfully created%'
				) );
				$entries = array();
				foreach( $log_entries as $log_entry ) {
					$log_text = strlen( $log_entry->text ) > 150 ? substr( $log_entry->text, 0, 150 )."..." : $log_entry->text;
					$entries[] = sprintf(
						'<tr style="border:none;">
							<td style="border:none;"><small><code>%d</code>: %s</small></td>
							<td style="border:none;">
								<form
									method="post" action="index.php?page=wls-superadmin-overview" target="_blank"
								>
									<input type="hidden" name="entry_id" value="%s">
									<input type="submit" value="&raquo;">
								</form>
							</td>
						</tr>',
						$log_entry->id, $log_text, $log_entry->id
					);
				}
				if( !empty( $entries ) ) {
					$origins[] = 'WLS log entries concerning this IP address:<table style="border:none;">'.implode( '', $entries ).'</table>';
				}
			}
			if( !empty( $origins ) ) {
				$or = '<p>'.implode( '</p><p>', $origins ).'</p>';
			} else {
				$or = __( 'Unknown attack origin.', SUH_TXD );
			}
		
			return "$or";
		}
		
		
		function process_bulk_action() {
			if( $this->current_action() == "remove_permbans" ) {
				foreach( $_GET["permban"] as $permban_id ) {
					pb\remove_permban( $permban_id );
				}
			}
		}
		
	}

}

?>
