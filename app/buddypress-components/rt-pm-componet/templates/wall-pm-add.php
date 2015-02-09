<?php
/**
 * Created by PhpStorm.
 * User: paresh
 * Date: 10/12/14
 * Time: 8:11 PM
 */

global $rt_pm_project,$rt_pm_bp_pm, $rt_pm_project_type, $rt_pm_task, $rt_pm_time_entries_model, $bp;


$post_id = $_GET["id"];

$post = get_post( $post_id );

$user_edit = true;
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

if (isset($post->ID)) {

    $create = rt_convert_strdate_to_usertimestamp($post->post_date_gmt);

    $modify = rt_convert_strdate_to_usertimestamp($post->post_modified_gmt);
    $createdate = $create->format("M d, Y h:i A");
    $modifydate = $modify->format("M d, Y h:i A");

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
        $email = rt_biz_get_entity_meta( $organization->ID, $rt_pm_project->organization_email_key, true );
        $company_url = add_query_arg( array( 'id'=>$organization->ID, 'action'=>'edit' ), get_people_root_url().Rt_Bp_People_Loader::$companies_slug) ;
        if (isset($project_organization) && $project_organization && !empty($project_organization) && in_array($organization->ID, $project_organization)) {
            $subProjectOrganizationsHTML .= "<li id='project-org-auth-" . $organization->ID
                . "' class='contact-list'><div class='row'><div class='column small-2'>" . $logo . '</div><div class="column small-9 vertical-center"><a target="_blank" class="" title="'.$organization->post_title.'" href="'.$company_url.'">'.$organization->post_title.'</a></div>'
                . "<div class='column small-1 vertical-center'><a class='right' href='#removeProjectOrganization'><i class='foundicon-remove'></i></a>
                            <input type='hidden' name='post[project_organization][]' value='" . $organization->ID . "' /></div></div></li>";
        }
        $arrProjectOrganizations[] = array("id" => $organization->ID, "label" => $organization->post_title, "imghtml" => $logo, 'user_edit_link'=> $company_url );
    }
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
             	<a target="_blank" class="" title="'.$author->display_name.'" href="'.bp_core_get_userlink($author->ID, false, true).'">'.rt_get_user_displayname( $author->ID ).'</a>
             </div>'
                . "<div class='columns small-1 vertical-center'><a class='right' href='#removeProjectMember'><i class='foundicon-remove'></i></a>
                            <input type='hidden' name='post[project_member][]' value='" . $author->ID . "' /> </div>

                </div>
            </li>";
        }
        //$arrProjectMember[] = array("id" => $author->ID, "label" => $author->display_name, "imghtml" => get_avatar($author->user_email, 32), 'user_edit_link'=>  bp_core_get_userlink($author->ID, false,true));
    }
}

$arrProjectMember = get_employee_array( $results_member );

//Project client
$results_client = Rt_PM_Utils::get_pm_client_user();
$arrProjectClient = array();
$subProjectClientHTML = "";
if( !empty( $results_client ) ) {
    foreach ( $results_client as $client ) {
        $email = rt_biz_get_entity_meta( $client->ID, $rt_pm_project->contact_email_key, true );
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

?>
<form method="post" action="" >
       <input type="hidden" name="post[action]" value="<?php echo $_GET['action'] ?>" />
       <input type="hidden" name="post[template]" value="<?php echo $_GET['template'] ?>" />
       <input type="hidden" name="post[actvity_element_id]" value="<?php echo $_GET['actvity_element_id'] ?>" />
       <input type="hidden" name="post[rt_voxxi_blog_id]" value="<?php echo $_GET['rt_voxxi_blog_id'] ?>" />

       <?php if (isset($post->ID) && $user_edit ) { ?>
           <input type="hidden" name="post[post_id]" id='project_id' value="<?php echo $post->ID; ?>" />
           <input type="hidden" name="post[post_type]"  value="<?php echo $post->post_type; ?>" />
       <?php } ?>

        <div class="row">
            <div class="small-10 columns">
                <h2><?php echo ( isset($post->ID) ) ? $post->post_title : "Project"; ?></h2>
            </div>
            <div class="small-2 columns">
                <a title="Close" class="right close-sidepanel"><i class="fa fa-caret-square-o-right fa-2x"></i></a>
            </div>
        </div>


    <div class="row column-title">
        <!-- Post title START -->
        <div class="small-12 columns">
            <?php if( $user_edit ) { ?>
                <input name="post[post_title]"  type="text" placeholder="Project title" value="<?php echo ( isset($post->ID) ) ? $post->post_title : ""; ?>" />
            <?php } else { ?>
                <span><?php echo ( isset($post->ID) ) ? $post->post_title : ""; ?></span><br /><br />
            <?php } ?>
        </div>
        <!-- Post title END -->
    </div>

    <div class="row">
        <div class="small-12 columns">
            <?php if( $user_edit ) { ?>
            <textarea placeholder="Project content" rows="5" name="post[post_content]"><?php echo isset( $post->ID )  ? $post->post_content : "" ?></textarea>
            <?php } else {
                echo ucfirst($labels['name']).' Content : <br /><br /><span>'.(( isset($post->ID) ) ? $post->post_content : '').'</span><br /><br />';
            } ?>
        </div>
    </div>

    <div class="row">

        <h6> <?php _e("Clients"); ?></h6>
        <hr/>
        <div class="row">
            <script>
                var arr_project_organization =<?php echo json_encode($arrProjectOrganizations); ?>;
            </script>
            <?php if ( $user_edit ) { ?>
                <div class="small-12 column">
                    <input style="margin-bottom:10px" type="text" placeholder="Type Name to select" id="project_org_search_account" />
                </div>
            <?php } ?>
        </div>
        <ul id="divProjectAccountsList">
            <?php echo $subProjectOrganizationsHTML; ?>
        </ul>
    </div>


    <div class="row">
        <div class="columns medium-6 small-12"/>
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

    <div class="row">
        <h6> <?php _e( 'Project Manager' ); ?></h6>
        <hr>
        <div class="columns small-12">
            <?php if( $user_edit ) {
                rtpm_render_manager_selectbox( $project_manager );
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

    <div class="columns small-12">
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

       <div class="row">
           <h6> <?php _e("BDM"); ?></h6>

           <div class="small-12 columns">
               <?php if( $user_edit ) {
                   rt_pm_render_bdm_selectbox( $business_manager );
                } ?>
           </div>
       </div>

       <h3 class="row-title"> <?php _e("Project Information"); ?></h3>
       <hr/>
       <div class="row">
           <div class="column  small-12">
               <div class="small-4 columns <?php echo ( ! $user_edit ) ? 'rtpm_attr_border' : ''; ?>">
                   <label for="post[post_status]"><?php _e("Status"); ?></label>
               </div>
               <div class="small-8 columns">
                   <?php
                   if (isset($post->ID))
                       $pstatus = $post->post_status;
                   else
                       $pstatus = "";
                   $post_status = $rt_pm_project->get_custom_statuses();
                   $custom_status_flag = true;
                   ?>

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

               </div>
           </div>

           <div class="column small-12">
               <div class="large-4 small-4 columns">
                   <label>Create Date</label>
               </div>
               <div class="small-8 columns <?php echo ( ! $user_edit ) ? 'rtpm_attr_border' : ''; ?>">
                       <input  type="text"  name="post[post_date]" readonly="" placeholder="Select Create Date"
                              value="<?php echo ( isset($createdate) ) ? $createdate : ''; ?>"
                              title="<?php echo ( isset($createdate) ) ? $createdate : ''; ?>"/>
               </div>
           </div>
           <div class="column  small-12">
               <div class="small-4 columns">
                   <label><?php _e('Closing Date'); ?></label>
               </div>
               <div class="small-8 columns <?php echo ( ! $user_edit ) ? 'rtpm_attr_border' : ''; ?>">
                   <?php if( $user_edit ) { ?>
                       <input class="datetimepicker moment-from-now" type="text" name="post[post_completiondate]" placeholder="Select Completion Date"
                              value="<?php echo ( isset($completiondate) ) ? $completiondate : ''; ?>"
                              title="<?php echo ( isset($completiondate) ) ? $completiondate : ''; ?>">
                       <input  type="hidden" value="<?php echo ( isset($completiondate) ) ? $completiondate : ''; ?>" />
                   <?php } else { ?>
                       <span class="rtpm_view_mode moment-from-now"><?php echo $completiondate ?></span>
                   <?php } ?>
               </div>
           </div>
           <div class="column  small-12">
               <div class="small-4 columns">
                   <label><?php _e('Due Date'); ?></label>
               </div>
               <div class="small-8 columns <?php echo ( ! $user_edit ) ? 'rtpm_attr_border' : ''; ?>">
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
           <div id="rtpm_project_type_wrapper" class="column  small-12 <?php echo ( ! $user_edit ) ? 'rtpm_attr_border' : ''; ?>">
               <div class="large-4 small-4 columns">
                   <label><?php _e('Project Type'); ?></label>
               </div>
               <div class="small-8 columns">
                   <?php $rt_pm_project_type->get_project_types_dropdown( ( isset( $post->ID ) ) ? $post->ID : '', $user_edit ); ?>
               </div>
           </div>
           <?php if (isset($post->ID)) { ?>
               <div class="column small-12">
                   <div class="small-4 columns">
                       <label>Modify Date</label>
                   </div>
                   <div class="small-8 columns <?php echo ( ! $user_edit ) ? 'rtpm_attr_border' : ''; ?>">
                       <?php if( $user_edit ) { ?>
                           <input class="moment-from-now"  type="text" placeholder="Modified on Date"  value="<?php echo $modifydate; ?>" id="modification_date"
                                  title="<?php echo $modifydate; ?>" readonly="readonly">
                       <?php } else { ?>
                           <span class="rtpm_view_mode moment-from-now"><?php echo $modifydate; ?></span>
                       <?php } ?>
                   </div>
               </div>
           <?php } ?>
           <div class="column small-12">
               <div class="small-4 columns">
                   <label>Estimated Time</label>
               </div>
               <div class="small-8 columns <?php echo ( ! $user_edit ) ? 'rtpm_attr_border' : ''; ?>">
                   <?php if( $user_edit ) { ?>
                       <input name="post[project_estimated_time]" type="text" value="<?php echo ( isset($post->ID) ) ? get_post_meta( $post->ID, 'project_estimated_time', true ) : ''; ?>" />
                   <?php } else { ?>
                       <span class="rtpm_view_mode moment-from-now"><?php echo ( isset($post->ID) ) ? get_post_meta( $post->ID, 'project_estimated_time', true ) : ''; ?></span>
                   <?php } ?>
               </div>
           </div>
           <div class="column small-12">
               <div class="small-4 columns">
                   <label for="project_budget">Budget</label>
               </div>

               <div class="small-8 columns <?php echo ( ! $user_edit ) ? 'rtpm_attr_border' : ''; ?>">

                   <?php if( $user_edit ) { ?>
                       <input type="text" name="post[project_budget]" id="project_budget" value="<?php echo ( isset( $post->ID ) ) ? get_post_meta( $post->ID, '_rtpm_project_budget', true ) : ''; ?>" />
                   <?php } else { ?>
                       <span class="rtpm_view_mode"><?php echo ( isset( $post->ID ) ) ? get_post_meta( $post->ID, '_rtpm_project_budget', true ) : ''; ?></span>
                   <?php } ?>
               </div>
           </div>
       </div>

       <?php if ( isset( $post->ID ) ) { do_action( 'rt_pm_bp_wall_other_details', $user_edit, $post ); } ?>

       <h3><b><?php _e('External'); ?></b></h3>
       <hr/>
       <div class="row">

           <div class="large-12 columns">

               <?php if( $user_edit ) {
                   ?>
                   <div class="row collapse" id="external-files-container">

                       <div class="small-3 columns">
                           <input type="text" id='add_ex_file_title' placeholder="Title"/>
                       </div>
                       <div class="small-8 columns">
                           <input type="text" id='add_ex_file_link' placeholder="Link"/>
                       </div>
                       <div class="small-1 columns">
                           <a class="button add-button add-external-link"  id="add_new_ex_file" ><i class="fa fa-plus"></i></a>
                       </div>
                   </div>
               <?php } ?>
           </div>

           <div class="scroll-height">
               <?php
               $attachments = get_posts( array(
                   'post_type' => 'attachment',
                   'posts_per_page' => -1,
                   'post_parent' => $post_id,
                   'exclude'     => get_post_thumbnail_id()

               ) );
               if ( $attachments ){ ?>
                   <?php foreach ($attachments as $attachment) { ?>
                       <?php $extn_array = explode('.', $attachment->guid); $extn = $extn_array[count($extn_array) - 1];
                       if ( get_post_meta( $attachment->ID, '_wp_attached_external_file', true) != 1 ){
                           continue;
                       }
                       $file_type_dir = RT_PM_PATH_APP ."assets/file-type/";
                       $file_types = scandir( $file_type_dir );
                       $ext_file = $extn . ".png";
                       if ( ! in_array($ext_file,  $file_types)) {
                           $extn ='unknown';
                       }

                       ?>

                       <div class="small-12 columns">
                           <div class="small-11 columns">
                               <a target="_blank" href="<?php echo wp_get_attachment_url($attachment->ID); ?>">
                                   <img height="20px" width="20px" src="<?php echo RT_PM_URL . "app/assets/file-type/" . $extn . ".png"; ?>" />
                                   <?php echo $attachment->post_title; ?>
                               </a>
                               <input type="hidden" name="attachment[]" value="<?php echo $attachment->ID; ?>" />
                           </div>
                           <div class="small-1 columns">
                               <a data-attachmentid="<?php echo $attachment->ID; ?>" class="rtpm_delete_project_attachment button add-button removeMeta"><i class="fa fa-times"></i></a>
                           </div>
                       </div>
                   <?php } ?>
               <?php }else{ ?>
                   <?php if (  isset($_POST['attachment_tag']) && $_POST['attachment_tag']!= -1 ){ ?>
                       <div class="small-12 mobile-large-3 columns no-attachment-item">
                           <?php $term = get_term( $_POST['attachment_tag'], 'attachment_tag' ); ?>
                           Not Found Attachment<?php echo isset( $term )? ' of ' . $term->name . ' Term!' :'!' ?>
                       </div>
                   <?php }else{ ?>
                       <div class="small-12 mobile-large-3 columns no-attachment-item">
                           <?php delete_post_meta($projectid, '_rt_wp_pm_attachment_hash'); ?>
                           Attachment Not found!
                       </div>
                   <?php } ?>
               <?php } ?>
           </div>
       </div>

       <h3><?php _e('Attachments'); ?></h3>
       <hr/>

       <?php render_rt_bp_wall_documents_section( $post_id ) ?>

       <div class="row">
           <div class="samll-12 columns right">
               <input class="right" type="submit" value="Save" />
           </div>
       </div>




</form>