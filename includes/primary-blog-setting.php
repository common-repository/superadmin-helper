<?php

/** Allow superadmin to change users' primary blog. */
namespace SuperadminHelper\PrimaryBlogSetting {
	
	
	add_action( "show_user_profile", '\SuperadminHelper\PrimaryBlogSetting\show_user_profile' );
	add_action( "edit_user_profile", '\SuperadminHelper\PrimaryBlogSetting\show_user_profile' );
	add_action( "personal_options_update", '\SuperadminHelper\PrimaryBlogSetting\edit_user_profile' );
	add_action( "edit_user_profile_update", '\SuperadminHelper\PrimaryBlogSetting\edit_user_profile' );
	
	
	function show_user_profile( $user ) {
		if( !is_super_admin() || !is_multisite() ) {
			return;
		}
		?>
		<h3><?php _e( 'Superadmin mode', SUH_TXD ); ?><!--Superadmin mód--></h3>
		<p><?php _e( 'Be very careful. Wrong value can do some damage.', SUH_TXD ); ?>
		</p>
		<table class="form-table">
			<tr>
			    <th><label for="primary_blog"><?php _e( 'Primary blog', SUH_TXD ); ?></label></th>
			    <td>
			        <input type="text" name="primary_blog" id="primary_blog" value="<?php echo( get_user_meta( $user->ID, 'primary_blog', true ) ); ?>" class="regular-text">
			    </td>
			</tr>
		</table>
		<?php
	}
	
	
	function edit_user_profile( $user_id ) {
		if( !is_super_admin() ) {
			return;
		}
		$pb = $_POST['primary_blog'];
		update_user_meta( $user_id, 'primary_blog', $pb );
	}
	
	
}

?>