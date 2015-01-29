<?php
global $rt_pm_project,$rt_pm_bp_pm, $rt_pm_project_type, $rt_pm_task, $rt_pm_time_entries_model, $bp;

if( ! isset( $_REQUEST['post_type'] ) || $_REQUEST['post_type'] != $rt_pm_project->post_type ) {
    wp_die("Opsss!! You are in restricted area");
}

$post_type=$_REQUEST['post_type'];

//Trash action
if( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'trash' && isset( $_REQUEST[$post_type.'_id'] ) ) {
    wp_delete_post( $_REQUEST[$post_type.'_id'] );
	$args = array(
		'post_type' =>  $rt_pm_task->post_type,
		'post_status' => 'any',
		'meta_query' => array(
			'key' => Rt_PM_Task_List_View::$project_id_key,
			'value' => array( $_REQUEST[$post_type.'_id'] ),
		),
	);
	$tasks = get_posts( $args );
	foreach ( $tasks as $t ) {
		wp_delete_post( $t );
	}
	$rt_pm_time_entries_model->delete_timeentry( array( 'project_id' => $_REQUEST[$post_type.'_id'] ) );
	echo '<script> window.location="' . $rt_pm_bp_pm->get_component_root_url().bp_current_action() . '"; </script> ';
    die();
}

//Archive action
if( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'archive' && isset( $_REQUEST[$post_type.'_id'] ) ) {
    wp_trash_post( $_REQUEST[$post_type.'_id'] );
	$args = array(
		'post_type' =>  $rt_pm_task->post_type,
		'post_status' => 'any',
		'meta_query' => array(
			'key' => Rt_PM_Task_List_View::$project_id_key,
			'value' => array( $_REQUEST[$post_type.'_id'] ),
		),
	);
	$tasks = get_posts( $args );
	foreach ( $tasks as $t ) {
		// wp_trash_post( $t );
	}
	// $rt_pm_time_entries_model->delete_timeentry( array( 'project_id' => $_REQUEST[$post_type.'_id'] ) );
	echo '<script> window.location="' . $rt_pm_bp_pm->get_component_root_url().bp_current_action() . '"; </script> ';
    die();
}

//UnArchive action
if( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'unarchive' && isset( $_REQUEST[$post_type.'_id'] ) ) {
	$unarchive_post = array(
		'ID'           => $_REQUEST[$post_type.'_id'],
		'post_status' => 'active'
	);
    wp_update_post( $unarchive_post );
	$args = array(
		'post_type' =>  $rt_pm_task->post_type,
		'post_status' => 'any',
		'meta_query' => array(
			'key' => Rt_PM_Task_List_View::$project_id_key,
			'value' => array( $_REQUEST[$post_type.'_id'] ),
		),
	);
	$tasks = get_posts( $args );
	foreach ( $tasks as $t ) {
		//wp_delete_post( $t );
	}
	// $rt_pm_time_entries_model->delete_timeentry( array( 'project_id' => $_REQUEST[$post_type.'_id'] ) );
	echo '<script> window.location="' . $rt_pm_bp_pm->get_component_root_url().bp_current_action() . '"; </script> ';
    die();
}

//Check for wp error
if ( isset($post_id) && is_wp_error( $post_id ) ) {
    wp_die( 'Error while creating new '. ucfirst( $rt_pm_project->labels['name'] ) );
}

$form_ulr = add_query_arg( array( 'post_type' => $post_type ), $rt_pm_bp_pm->get_component_root_url().bp_current_action() );

///alert Notification
if (isset($_REQUEST["{$post_type}_id"])) {
    $form_ulr .= "&{$post_type}_id=" . $_REQUEST["{$post_type}_id"];
    if (isset($_REQUEST["new"])) {
        ?>
        <div class="alert-box success">
            <?php _e('New '.  ucfirst($labels['name']).' Inserted Sucessfully.'); ?>
            <a href="#" class="close">&times;</a>
        </div>
    <?php
    }
    if(isset($updateFlag) && $updateFlag){ ?>
        <div class="alert-box success">
            <?php _e(ucfirst($labels['name'])." Updated Sucessfully."); ?>
            <a href="#" class="close">&times;</a>
        </div>
    <?php }
    $post = get_post($_REQUEST["{$post_type}_id"]);
    if (!$post) {
        ?>
        <div class="alert-box alert">
            Invalid Post ID
            <a href="" class="close">&times;</a>
        </div>
        <?php
        $post = false;
    }
    if ( $post->post_type != $rt_pm_project->post_type ) {
        ?>
        <div class="alert-box alert">
            Invalid Post Type
            <a href="" class="close">&times;</a>
        </div>
        <?php
        $post = false;
    }

    $create = rt_convert_strdate_to_usertimestamp($post->post_date_gmt);

    $modify = rt_convert_strdate_to_usertimestamp($post->post_modified_gmt);
    $createdate = $create->format("M d, Y h:i A");
    $modifydate = $modify->format("M d, Y h:i A");

}

// get project meta
if (isset($post->ID)) {
    $post_author = $post->post_author;
	$project_manager = get_post_meta( $post->ID, "project_manager", true );
    $project_member = get_post_meta($post->ID, "project_member", true);
    $project_client = get_post_meta($post->ID, "project_client", true);
	$project_organization = get_post_meta($post->ID, "project_organization", true);
    $completiondate= get_post_meta($post->ID, 'post_completiondate', true);
    $duedate= get_post_meta($post->ID, 'post_duedate', true);
	if ( ! empty( $duedate ) ){
        $duedate = rt_convert_strdate_to_usertimestamp( $duedate );
		$duedate = $duedate->format("M d, Y h:i A"); // date formating hack
	}
    if ( ! empty( $completiondate ) ){

        $completiondate = rt_convert_strdate_to_usertimestamp($completiondate); // date formating hack
        $completiondate = $completiondate->format("M d, Y h:i A"); // date formating hack
    }
	$business_manager = get_post_meta( $post->ID, 'business_manager', true );
} else {
    $post_author = get_current_user_id();
}

//project manager & project members
$results_member = Rt_PM_Utils::get_pm_rtcamp_user();
$arrProjectMember = array();
$subProjectMemberHTML = "";
if( !empty( $results_member ) ) {
    foreach ( $results_member as $author ) {
        if (isset($project_member) && $project_member && !empty($project_member) && in_array($author->ID, $project_member)) {
            $subProjectMemberHTML .= "<li id='project-member-auth-" . $author->ID . "' class='contact-list'>"
            . "<div class='row'>
            <div class='column small-2'>"
             . get_avatar($author->user_email, 32) . ' </div>
             <div  class="columns small-9 vertical-center">
             	<a target="_blank" class="" title="'.$author->display_name.'" href="'.bp_core_get_userlink( $author->ID, false, true).'">'.$author->display_name.'</a>
             </div>'
                . "<div class='columns small-1 vertical-center'><a class='right' href='#removeProjectMember'><i class='foundicon-remove'></i></a>
                            <input type='hidden' name='post[project_member][]' value='" . $author->ID . "' /> </div>
                            
                </div>
            </li>";
        }
        //$arrProjectMember[] = array("id" => $author->ID, "label" => $author->display_name, "imghtml" => get_avatar($author->user_email, 32), 'user_edit_link'=>  bp_core_get_userlink($author->ID, false, true));
    }
}

$arrProjectMember = get_employee_array( $results_member );

//Project client
$results_client = Rt_PM_Utils::get_pm_client_user();
$arrProjectClient = array();
$subProjectClientHTML = "";
if( !empty( $results_client ) ) {
    foreach ( $results_client as $client ) {
		$email = rt_biz_get_entity_meta( $client->ID, $this->contact_email_key, true );
        $client_url = add_query_arg( array( 'id'=>$client->ID, 'action'=>'edit' ), get_people_root_url().Rt_User_Category::$clients_category_slug ) ;
        if (isset($project_client) && $project_client && !empty($project_client) && in_array($client->ID, $project_client)) {
            $subProjectClientHTML .= "<li id='project-client-auth-" . $client->ID
                . "' class='contact-list'><div class='row'><div class='large-2 column'>" . get_avatar($email, 32) . '</div>
                <div class="column small-9 vertical-center"><a target="_blank" class="" title="'.$client->post_title.'" href="'. $client_url .'">'.$client->post_title.'</a></div>'
                . "<div class='column small-1 vertical-center'><a class='right' href='#removeProjectClient'><i class='foundicon-remove'></i></a>
                            <input type='hidden' name='post[project_client][]' value='" . $client->ID . "' />
                        </div>
                    </div>
                </li>";
        }
		$connection = rt_biz_get_organization_to_person_connection( $client->ID );
		$org = array();
		foreach ( $connection as $c ) {
			$org[] = $c->ID;
		}
        $arrProjectClient[] = array("id" => $client->ID, "label" => $client->post_title, "imghtml" => get_avatar($email, 32), 'user_edit_link'=>  $client_url, 'organization' => $org);
    }
}

//Project organization
$results_organization = Rt_PM_Utils::get_pm_organizations();
$arrProjectOrganizations = array();
$subProjectOrganizationsHTML = "";
if( !empty( $results_organization ) ) {
    foreach ( $results_organization as $organization ) {
    	if ( has_post_thumbnail($organization->ID) ){

            $logo = get_the_post_thumbnail( $organization->ID, array( 32, 32 ) );
        } else{

            $logo = '<img src="'.RT_PM_URL.'app/buddypress-components/rt-pm-componet/assets/img/logo-default.png" width="32" height="32" class="attachment-32x32 wp-post-image"/>';
        }
		$email = rt_biz_get_entity_meta( $organization->ID, $this->organization_email_key, true );
        $company_url = add_query_arg( array( 'id'=>$organization->ID, 'action'=>'edit' ), get_people_root_url().Rt_Bp_People_Loader::$companies_slug) ;
        if (isset($project_organization) && $project_organization && !empty($project_organization) && in_array($organization->ID, $project_organization)) {
            $subProjectOrganizationsHTML .= "<li id='project-org-auth-" . $organization->ID
                . "' class='contact-list'><div class='row'><div class='column small-2'>" . $logo . '</div><div class="column small-9 vertical-center"><a target="_blank" class="" title="'.$organization->post_title.'" href="'. $company_url .'">'.$organization->post_title.'</a></div>'
                . "<div class='column small-1 vertical-center'><a class='right' href='#removeProjectOrganization'><i class='foundicon-remove'></i></a>
                            <input type='hidden' name='post[project_organization][]' value='" . $organization->ID . "' /></div></div></li>";
        }
        $arrProjectOrganizations[] = array("id" => $organization->ID, "label" => $organization->post_title, "imghtml" => $logo, 'user_edit_link'=> $company_url);
    }
}

?>
    <?php if( $user_edit ) { ?>
    <div class="rtpm-container">
    <form method="post" id="form-add-post" data-posttype="<?php echo $post_type ?>" action="<?php echo $form_ulr; ?>">
    <?php } ?>
        <?php if (isset($post->ID) && $user_edit ) { ?>
            <input type="hidden" name="post[post_id]" id='project_id' value="<?php echo $post->ID; ?>" />
            <input type="hidden" name="post[post_type]"  value="<?php echo $post->post_type; ?>" />
        <?php } ?>
        <div>
        	<div class="large-3 columns">
        		<h2><?php _e('Details', RT_PM_TEXT_DOMAIN);?></h2>
        	</div>
			<?php
			if (isset($post->ID)) {
				$save_button = __( 'Update' );
			} else {
				$save_button = __( 'Add Project' );
			}
			?>
            
                
			<?php if( $user_edit ) { ?>
			<div class="large-9 columns action-bar">
				<button class="mybutton" type="submit" ><?php _e($save_button); ?></button>
				<?php 
				if(isset($post->ID)) { 
					$get_post_status = get_post_status( $post->ID );
					if ( isset( $get_post_status ) && $get_post_status == 'trash' ){
						$archive_action = 'unarchive';
						$archive_button = __( 'Unarchive' );
						$button_archive_id = 'button-unarchive';
						$redirect = $rt_pm_bp_pm->get_component_root_url(). 'archives';
					} else {
						$archive_action = 'archive';
						$archive_button = __( 'Archive' );
						$button_archive_id = 'button-archive';
						$redirect = $rt_pm_bp_pm->get_component_root_url();
					}
					
				?>
				<button id="top-<?php echo $button_archive_id; ?>" class="mybutton" data-href="<?php echo add_query_arg( array( 'action' => $archive_action, 'rt_project_id' => $post->ID ), $redirect ); ?>" class=""><?php _e($archive_button); ?></button>
				<button id="top-button-trash" class="mybutton" data-href="<?php echo add_query_arg( array( 'action' => 'trash', 'rt_project_id' => $post->ID ), $redirect ); ?>" class=""><?php _e( 'Delete' ); ?></button>
				<?php } ?>
			</div>
			<?php } ?>
            
        </div>
        <div class="row">
            <!-- Post title START -->
            <div class="small-12 columns">
                <?php if( $user_edit ) { ?>
                    <input name="post[post_title]" id="new_<?php echo $post_type ?>_title" type="text" placeholder="<?php _e(ucfirst($labels['name'])." Name"); ?>" value="<?php echo ( isset($post->ID) ) ? $post->post_title : ""; ?>" />
                <?php } else { ?>
                    <span><?php echo ( isset($post->ID) ) ? $post->post_title : ""; ?></span><br /><br />
                <?php } ?>
            </div>
             <!-- Post title END -->
        </div>
        <div class="row column-title">
            <div class="large-12 columns">
                <?php
                if( $user_edit ) {
                    wp_editor( ( isset( $post->ID ) ) ? $post->post_content : "", "post_content", array( 'textarea_name' => 'post[post_content]', 'media_buttons' => false, 'tinymce' => false, 'quicktags' => false, 'textarea_rows' => 5 ) );
                } else {
                    echo ucfirst($labels['name']).' Content : <br /><br /><span>'.(( isset($post->ID) ) ? $post->post_content : '').'</span><br /><br />';
                }
                ?>
            </div>
		</div>
		<br>
		<div class="row row-title">
			<div class="columns medium-6 small-12"/>
					<h6> <?php _e("Clients"); ?></h6>
                    <hr/>
					<div class="row collapse">
						<script>
                            var arr_project_organization =<?php echo json_encode($arrProjectOrganizations); ?>;
                        </script>
					<?php if ( $user_edit ) { ?>
						<div class="small-12 columns">
							<input style="margin-bottom:10px" type="text" placeholder="Type Name to select" id="project_org_search_account" />
						</div>
					<?php } ?>
					</div>
					<ul id="divProjectAccountsList">
						<?php echo $subProjectOrganizationsHTML; ?>
					</ul>
            </div>
            <div class="columns medium-6 small-12">
                <h6><?php _e( 'Manager' ); ?></h6>
				<hr>
                <div class="row collapse">
                    <?php if( $user_edit ) {
                        rtpm_render_manager_selectbox

        /**
         * Render BDM selectbox
         * @param $business_manager
         */
        function rt_render_manager_selectbox( $project_manager ){ ?>
            <select name="post[project_manager]" >
                <option value=""><?php _e( 'Select PM' ); ?></option>
                <?php
                $employees = rt_biz_get_employees();

                if (!empty( $employees )) {
                    foreach ($employees as $bm) {

                        $employee_wp_user_id = rt_biz_get_wp_user_for_person( $bm->ID );

                        if ( $employee_wp_user_id == $project_manager ) {
                            $selected = " selected";
                        } else {
                            $selected = " ";
                        }
                        echo '<option value="' . $employee_wp_user_id . '"' . $selected . '>' . rt_get_user_displayname( $employee_wp_user_id ) . '</option>';
                    }
                }
                ?>
            </select>
        <?php }
( $project_manager );
						} else {
							if (!empty($results_member)) {
                                foreach ($results_member as $author) {
                                    if ($author->ID == $project_manager) {
                                        $selected = " selected";
										echo '<div style="margin-bottom:10px" class="small-8 large-8 columns rtpm_attr_border">' .
										'<span class="rtpm_view_mode">' . $author->display_name . '</span>' .
										'</div>';
                                    }
                                    
                                                                        
                                }
                            }
						} 
					?>
                </div>
            </div>
            
        </div>
        <div class="row">
			<div class="columns medium-6 small-12">
				<h6> <?php _e("Contacts"); ?></h6>
            	<hr/>
                <script>
                    var arr_project_client_user =<?php echo json_encode($arrProjectClient); ?>;
                </script>
                <?php if ( $user_edit ) { ?>
                    <input style="margin-bottom:10px" type="text" placeholder="Type User Name to select" id="project_client_user_ac" />
                <?php } ?>
                <ul id="divProjectClientList">
                    <?php echo $subProjectClientHTML; ?>
                </ul>
			</div>
			<div class="columns medium-6 small-12">
				<h6> <?php _e("Team"); ?></h6>
        		<hr/>
				<script>
					var arr_project_member_user =<?php echo json_encode($arrProjectMember); ?>;
				</script>
				<?php if ( $user_edit ) { ?>
				<input style="margin-bottom:10px" type="text" placeholder="Type User Name to select" id="project_member_user_ac" />
				<?php } ?>
				<ul id="divProjectMemberList">
					<?php echo $subProjectMemberHTML; ?>
				</ul>
			</div>
		</div>
        <div class="row">
            <div class="medium-6 columns right">
                <h6> <?php _e("BDM"); ?></h6>
                <?php if( $user_edit ) {
                    rt_pm_render_bdm_selectbox( $business_manager );
                 } ?>
            </div>
        </div>
        <h3 class="row-title"> <?php _e("Project Information"); ?></h3>
        <hr/>
        <div class="row meta-box">
            <div class="column medium-6 small-12">
                <div class="small-12 large-4 columns">
                    <label for="post[post_status]"><?php _e("Status"); ?></label>
                </div>
                <div class="small-12 large-8 columns <?php echo ( ! $user_edit ) ? 'rtpm_attr_border' : ''; ?>">
                    <?php
                    if (isset($post->ID))
                        $pstatus = $post->post_status;
                    else
                        $pstatus = "";
                    $post_status = $rt_pm_project->get_custom_statuses();
                    $custom_status_flag = true;
                    ?>
                    <?php if( $user_edit ) { ?>
                        <select id="rtpm_post_status" class="right" name="post[post_status]">
                            <?php foreach ($post_status as $status) {
                                if ($status['slug'] == $pstatus) {
                                    $selected = 'selected="selected"';
                                    $custom_status_flag = false;
                                } else {
                                    $selected = '';
                                }
                                printf('<option value="%s" %s >%s</option>', $status['slug'], $selected, $status['name']);
                            } ?>
                            <?php if ( $custom_status_flag && isset( $post->ID ) ) { echo '<option selected="selected" value="'.$pstatus.'">'.$pstatus.'</option>'; } ?>
                        </select>
                    <?php } else {
                        $status_html='';
                        foreach ( $post_status as $status ) {
                            if($status['slug'] == $pstatus) {
                                $status_html = '<span class="rtpm_view_mode">'.$status['name'].'</span>';
                                break;
                            }
                        }
                        if ( !isset( $status_html ) || empty( $status_html ) && ( isset( $pstatus ) && !empty( $pstatus ) ) ){
                            $status_html = '<span class="rtpm_view_mode">'.$pstatus.'</span>';
                        }
                        echo $status_html;
                    } ?>
                </div>
            </div>
            <!--<div id="rtpm_closing_reason_wrapper" class="column medium-6 small-12 ">
                <div class="small-4 columns">
                        <label>Close Reason</label>
                </div>
                <div class="small-8 columns">
                    <select id="rtpm_closing_reason" name="post[closing_reason][]" class="rt-form-select">
                        <option value="" selected="selected">Select a Reason</option>
                    </select>
                </div>
            </div>-->

            <div class="column medium-6 small-12">
                <div class="large-4 small-4 columns">
                    <label>Create Date</label>
                </div>
                <div class="large-8 mobile-large-1 columns <?php echo ( ! $user_edit ) ? 'rtpm_attr_border' : ''; ?>">
                    <?php if( $user_edit ) { ?>
                        <input class="datetimepicker moment-from-now" type="text"  name="post[post_date]"  placeholder="Select Create Date"
                               value="<?php echo ( isset($createdate) ) ? $createdate : ''; ?>"
                               title="<?php echo ( isset($createdate) ) ? $createdate : ''; ?>">

                    <?php } else { ?>
                        <span class="rtpm_view_mode moment-from-now"><?php echo $createdate ?></span>
                    <?php } ?>
                </div>
            </div>
            <div class="column medium-6 small-12">
                <div class="large-4 small-4 columns">
                     <label><?php _e('Closing Date'); ?></label>
                </div>
                <div class="large-8 mobile-large-1 columns <?php echo ( ! $user_edit ) ? 'rtpm_attr_border' : ''; ?>">
                    <?php if( $user_edit ) { ?>
                        <input class="datetimepicker moment-from-now" type="text" name="post[post_completiondate]" placeholder="Select Completion Date"
                               value="<?php echo ( isset($completiondate) ) ? $completiondate : ''; ?>"
                               title="<?php echo ( isset($completiondate) ) ? $completiondate : ''; ?>">

                    <?php } else { ?>
                        <span class="rtpm_view_mode moment-from-now"><?php echo $completiondate ?></span>
                    <?php } ?>
                </div>
            </div>
            <div class="column medium-6 small-12">
                <div class="large-4 small-4 columns">
                    <label><?php _e('Due Date'); ?></label>
                </div>
                <div class="large-8 mobile-large-1 columns <?php echo ( ! $user_edit ) ? 'rtpm_attr_border' : ''; ?>">
                    <?php if( $user_edit ) { ?>
                        <input class="datetimepicker moment-from-now" type="text" name="post[post_duedate]" placeholder="Select Due Date"
                               value="<?php echo ( isset($duedate) ) ? $duedate : ''; ?>"
                               title="<?php echo ( isset($duedate) ) ? $duedate : ''; ?>">

                    <?php } else { ?>
                        <span class="rtpm_view_mode moment-from-now"><?php echo $duedate ?></span>
                    <?php } ?>
                </div>
            </div>
    	</div>
    	<div class="row meta-box">
    				<div id="rtpm_project_type_wrapper" class="column medium-6 small-12 <?php echo ( ! $user_edit ) ? 'rtpm_attr_border' : ''; ?>">
                        <div class="large-4 small-12 columns">
                            <label><?php _e('Project Type'); ?></label>
                        </div>
                        <div class="large-8 small-12 columns">
                            <?php $rt_pm_project_type->get_project_types_dropdown( ( isset( $post->ID ) ) ? $post->ID : '', $user_edit ); ?>
                        </div>
                    </div>
                    <?php if (isset($post->ID)) { ?>
                        <div class="column medium-6 small-12">
                            <div class="large-4 mobile-large-1 columns">
                                <label>Modify Date</label>
                            </div>
                            <div class="large-8 mobile-large-1 columns <?php echo ( ! $user_edit ) ? 'rtpm_attr_border' : ''; ?>">
                                <?php if( $user_edit ) { ?>
                                    <input class="moment-from-now"  type="text" placeholder="Modified on Date"  value="<?php echo $modifydate; ?>" id="modification_date"
                                           title="<?php echo $modifydate; ?>" readonly="readonly">
                                <?php } else { ?>
                                    <span class="rtpm_view_mode moment-from-now"><?php echo $modifydate; ?></span>
                                <?php } ?>
                            </div>
                        </div>
                    <?php } ?>
                    <div class="column medium-6 small-12">
                        <div class="large-4 small-4 columns">
                            <label>Estimated Time</label>
                        </div>
                        <div class="large-8 mobile-large-2 columns <?php echo ( ! $user_edit ) ? 'rtpm_attr_border' : ''; ?>">
                            <?php if( $user_edit ) { ?>
                                <input name="post[project_estimated_time]" type="text" value="<?php echo ( isset($post->ID) ) ? get_post_meta( $post->ID, 'project_estimated_time', true ) : ''; ?>" />
                            <?php } else { ?>
                                <span class="rtpm_view_mode moment-from-now"><?php echo ( isset($post->ID) ) ? get_post_meta( $post->ID, 'project_estimated_time', true ) : ''; ?></span>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="column medium-6 small-12">
						<div class="small-4 columns">
							<label for="project_budget">Budget</label>
						</div>

						<div class="large-8 mobile-large-2 columns <?php echo ( ! $user_edit ) ? 'rtpm_attr_border' : ''; ?>">
							
							<?php if( $user_edit ) { ?>
							<input type="text" name="post[project_budget]" id="project_budget" value="<?php echo ( isset( $post->ID ) ) ? get_post_meta( $post->ID, '_rtpm_project_budget', true ) : ''; ?>" />
							<?php } else { ?>
							<span class="rtpm_view_mode"><?php echo ( isset( $post->ID ) ) ? get_post_meta( $post->ID, '_rtpm_project_budget', true ) : ''; ?></span>
							<?php } ?>
						</div>
					</div>
    	</div>
        <?php 
		if ( isset( $post->ID ) ) { do_action( 'rt_pm_bp_other_details', $user_edit, $post ); }


        if( $post->post_parent != 0 ){

            Rt_Crm_Lead_Info_Metabox::render_bp_lead_info_metabox( $post->post_parent, false );
        }

        ?>

        <div class="row column-title">
			<?php
			if (isset($post->ID)) {
				$save_button = __( 'Update' );
			} else {
				$save_button = __( 'Add Project' );
			}
			?>
            
                
			<?php if( $user_edit ) { ?>
			<div class="large-12 columns action-bar">
				<button class="mybutton" type="submit" ><?php _e($save_button); ?></button>
				<?php 
				if(isset($post->ID)) { 
					$get_post_status = get_post_status( $post->ID );
					if ( isset( $get_post_status ) && $get_post_status == 'trash' ){
						$archive_action = 'unarchive';
						$archive_button = __( 'Unarchive' );
						$button_archive_id = 'button-unarchive';
					} else {
						$archive_action = 'archive';
						$archive_button = __( 'Archive' );
						$button_archive_id = 'button-archive';
					}
				?>
				<button id="<?php echo $button_archive_id; ?>" class="mybutton" data-href="<?php echo add_query_arg( array( 'action' => $archive_action, 'rt_project_id' => $post->ID ), $redirect ); ?>" class=""><?php _e($archive_button); ?></button>
				<button id="button-trash" class="mybutton" data-href="<?php echo add_query_arg( array( 'action' => 'trash', 'rt_project_id' => $post->ID ), $redirect ); ?>" class=""><?php _e( 'Delete' ); ?></button>
				<?php } ?>
			</div>
			<?php } ?>
            
        </div>
    <?php if( $user_edit ) { ?>
    </form>
    </div>
    <?php } ?>