<?php
/**
 * Created by PhpStorm.
 * User: paresh
 * Date: 11/12/14
 * Time: 3:13 PM
 */

function bp_save_project(){

global $rt_pm_project,$rt_pm_bp_pm, $rt_pm_project_type, $rt_pm_task, $rt_pm_time_entries_model, $bp;

$allowed_component = array( BP_PM_SLUG, BP_ACTIVITY_SLUG );

if( !in_array( bp_current_component(), $allowed_component ) )
    return;


if ( !isset( $_POST['post'] ) )
    return;


$newProject = $_POST['post'];

    if(  !isset( $newProject['post_type'] ) )
        return;

    if( $newProject['post_type'] != $rt_pm_project->post_type )
        return;

    if( isset( $newProject['rt_voxxi_blog_id'] ) )
        switch_to_blog( $newProject['rt_voxxi_blog_id'] );


    $post_type = $rt_pm_project->post_type;

    $creationdate = $newProject['post_date'];
        if ( isset( $creationdate ) && $creationdate != '' ) {
            try {
                $dr = date_create_from_format( 'M d, Y H:i A', $creationdate );
                $UTC = new DateTimeZone('UTC');
                $dr->setTimezone($UTC);
                $timeStamp = $dr->getTimestamp();
                $newProject['post_date'] = gmdate('Y-m-d H:i:s', (intval($timeStamp) + ( get_option('gmt_offset') * 3600 )));
                $newProject['post_date_gmt'] = gmdate('Y-m-d H:i:s', (intval($timeStamp)));
            } catch ( Exception $e ) {
                $newProject['post_date'] = current_time( 'mysql' );
                $newProject['post_date_gmt'] = gmdate('Y-m-d H:i:s');
            }
        } else {
            $newProject['post_date'] = current_time( 'mysql' );
            $newProject['post_date_gmt'] = gmdate('Y-m-d H:i:s');
        }

        // Change format for post_duedate
        $postduedate = $newProject['post_duedate'];
        if ( isset( $postduedate ) && $postduedate != '' ) {
            $dr = date_create_from_format( 'M d, Y H:i A', $postduedate );
            $UTC = new DateTimeZone('UTC');
            $dr->setTimezone($UTC);
            $timeStamp = $dr->getTimestamp();
            $newProject['post_duedate'] = gmdate('Y-m-d H:i:s', (intval($timeStamp) + ( get_option('gmt_offset') * 3600 )));
        }

        // Post Data to be saved.
        $post = array(
            'post_author' => $newProject['project_manager'],
            'post_content' => $newProject['post_content'],
            'post_status' => $newProject['post_status'],
            'post_title' => $newProject['post_title'],
            'post_date' => $newProject['post_date'],
            'post_date_gmt' => $newProject['post_date_gmt'],
            'post_type' => $post_type
        );

        $updateFlag = false;
        //check post request is for Update or insert
        if ( isset($newProject['post_id'] ) ) {
            $updateFlag = true;
            $post = array_merge( $post, array( 'ID' => $newProject['post_id'] ) );
            $data = array(
                'project_manager' => $newProject['project_manager'],
                'post_completiondate' => $newProject['post_completiondate'],
                'post_duedate' => $newProject['post_duedate'],
                'project_estimated_time' => $newProject['project_estimated_time'],
                'project_client' => $newProject['project_client'],
                'project_organization' => $newProject['project_organization'],
                'project_member' => isset($newProject['project_member'])? $newProject['project_member'] : '',
                //'business_manager' => $newProject['business_manager'],
                //'_rtpm_status_detail' => $newProject['status_detail'],
                '_rtpm_project_budget' => $newProject['project_budget'],
                'date_update' => current_time( 'mysql' ),
                'date_update_gmt' => gmdate('Y-m-d H:i:s'),
                'user_updated_by' => get_current_user_id(),
            );
            $post_id = @wp_update_post( $post );
            $rt_pm_project_type->save_project_type($post_id,$newProject);
            $data = apply_filters( 'rt_pm_project_detail_meta', $data);
            foreach ( $data as $key=>$value ) {
                update_post_meta( $post_id, $key, $value );
            }
            do_action( 'save_project', $post_id, 'update' );
        }else{
            $data = array(
                'project_manager' => $newProject['project_manager'],
                'post_completiondate' => $newProject['post_completiondate'],
                'post_duedate' => $newProject['post_duedate'],
                'project_estimated_time' => $newProject['project_estimated_time'],
                'project_client' => $newProject['project_client'],
                'project_organization' => $newProject['project_organization'],
                'project_member' => $newProject['project_member'],
                //'business_manager' => $newProject['business_manager'],
                //'_rtpm_status_detail' => $newProject['status_detail'],
                '_rtpm_project_budget' => $newProject['project_budget'],
                'date_update' => current_time( 'mysql' ),
                'date_update_gmt' => gmdate('Y-m-d H:i:s'),
                'user_updated_by' => get_current_user_id(),
                'user_created_by' => get_current_user_id(),
            );
            $post_id = @wp_insert_post($post);
            $rt_pm_project_type->save_project_type($post_id,$newProject);
            $data = apply_filters( 'rt_pm_project_detail_meta', $data);
            foreach ( $data as $key=>$value ) {
                update_post_meta( $post_id, $key, $value );
            }
            do_action( 'save_project', $post_id, 'insert' );
        }

        // External File Links

        if ( isset( $_POST['project_ex_files'] ) ) {
            $new_ex_files = $_POST['project_ex_files'];

            foreach ( $new_ex_files as $ex_file ) {
                if ( empty( $ex_file['link'] ) ) {
                    continue;
                }
                if( empty( $ex_file['title'] ) ) {
                    $ex_file['title'] = $ex_file['link'];
                }


                $args = array(
                    'guid' => $ex_file["link"],
                    'post_title' => $ex_file['title'],
                    'post_content' => $ex_file['title'],
                    'post_parent' => $post_id,
                    'post_author' => get_current_user_id(),
                );
                $post_attachment_hashes = get_post_meta( $post_id, '_rt_wp_pm_external_link' );

                $attachment_id = wp_insert_attachment( $args, $ex_file["link"], $post_id );
                add_post_meta( $post_id, '_rt_wp_pm_external_link', $ex_file["link"] );
                //convert string array to int array

                //Update flag for external link
                update_post_meta( $attachment_id, '_wp_attached_external_file', '1');
                /*update_post_meta($attachment_id, '_flagExternalLink', "true");*/
            }

        }

    $_REQUEST[$post_type."_id"] = $post_id;


         bp_core_add_message( 'Project updated successfully', 'success' );
        if( isset( $newProject['rt_voxxi_blog_id'] ) ){

            restore_current_blog();

            rt_voxxi_set_sidepanel_cookies();

            if( bp_current_action() == 'mentions' ){

                $link = bp_loggedin_user_domain().BP_ACTIVITY_SLUG;
                bp_core_redirect( $link );
            }
        }else{
            $link = $rt_pm_bp_pm->get_component_root_url().bp_current_action();
            $link .= "?post_type={$post_type}&page=rtpm-add-{$post_type}&{$post_type}_id={$_REQUEST["{$post_type}_id"]}&tab={$post_type}-details";
            bp_core_redirect( $link );
        }


}
add_action( 'bp_actions', 'bp_save_project' );