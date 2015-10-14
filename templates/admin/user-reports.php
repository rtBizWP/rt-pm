<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
screen_icon();
?>
<div class="rtpm-container wrap">
    <h2><?php _e( 'User Reports' ); ?></h2>
	<form class="rtpm-user-report-query" method="post">
		<input type="hidden" name="rtpm_user_query" value="1" />
		<div class="row collapse">
			<div class="large-6 columns">
				<div class="row">
					<div class="large-6 columns">
						<label class="left inline" for="rtpm_user"><?php _e('User : '); ?></label>
					</div>
					<div class="large-6 columns">
						<?php $users = rt_biz_get_employees(); ?>
						<select id="rtpm_user" name="rtpm_user">
							<option value=""><?php _e( 'Any' ); ?></option>
							<?php foreach ( $users as $u ) {

								$employee_wp_user_id = rt_biz_get_wp_user_for_person( $u->ID );

								if( empty( $employee_wp_user_id ) ) {
									continue;
								}
								?>
							<option value="<?php echo $employee_wp_user_id; ?>"><?php echo rtbiz_get_user_displayname( $employee_wp_user_id ); ?></option>
							<?php } ?>
						</select>
					</div>
				</div>
				<div class="row">
					<div class="large-6 columns">
						<label class="left inline" for="rtpm_time_entry_type"><?php _e( 'Time Entry Type : ' ); ?></label>
					</div>
					<div class="large-6 columns">
						<?php $terms = get_terms( Rt_PM_Time_Entry_Type::$time_entry_type_tax, array( 'hide_empty' => false, 'order' => 'asc' ) ); ?>
						<select id="rtpm_time_entry_type" name="rtpm_time_entry_type">
							<option value=""><?php _e( 'Any' ); ?></option>
							<?php foreach ( $terms as $term ) { ?>
							<option value="<?php echo $term->slug; ?>" ><?php echo $term->name; ?></option>
							<?php } ?>
						</select>
					</div>
				</div>
				<div class="row">
					<div class="large-6 columns">
						<label for="rtpm_project" class="left inline"><?php _e( 'Project : ' ); ?></label>
					</div>
					<div class="large-6 columns">
						<?php
							global $rt_pm_project;
							$args = array( 'post_type' => $rt_pm_project->post_type );
							$projects_query = new WP_Query( $args );
						?>
						<select id="rtpm_project" name="rtpm_project">
							<option value=""><?php _e( 'Any' ); ?></option>
							<?php foreach ( $projects_query->posts as $p ) { ?>
							<option value="<?php echo $p->ID; ?>"><?php echo $p->post_title; ?></option>
							<?php } ?>
						</select>
					</div>
				</div>
				<input class="button-primary" type="submit" value="<?php _e( 'Run' ); ?>">
			</div>
		</div>
	</form>
	<div id="wp-custom-list-table" class="rtpm-user-report-list">
	<?php
		if ( ! empty( $_POST['rtpm_user_query'] ) ) {
			$rtpm_time_entry_list= new Rt_PM_Time_Entry_List_View( true );
			$rtpm_time_entry_list->prepare_global_items();
			$rtpm_time_entry_list->display();
		}
	?>
	</div>
</div>
