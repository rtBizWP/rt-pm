<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

    global $rt_pm_project,$rt_pm_bp_pm, $rt_pm_bp_pm_project;

    if (!isset( $_REQUEST['post_type'] )){
        $_REQUEST['post_type'] = 'rt_project';
    }
	if (!isset( $_REQUEST['action'] )){
        $_REQUEST['action'] = '';
    }
    $post_type = $_REQUEST['post_type'];

    $user_edit = false;
	if ( $_REQUEST['action'] == 'view' && current_user_can( "read_{$post_type}" ) ){
		 $user_edit = false;
	} else  if ( current_user_can( "edit_{$post_type}" ) ) {
        $user_edit = true;
    } else if ( current_user_can( "read_{$post_type}" ) ) {
        $user_edit = false;
    } else {
        wp_die("Opsss!! You are in restricted area");
    }?>
    <!--<div class="rtpm-container">
        <?php
        $is_new_project_page = isset($_REQUEST["{$post_type}_id"])? false : true;
		$bp_bp_nav_link = $rt_pm_bp_pm->get_component_root_url().bp_current_action();
        if (!$is_new_project_page) {
            $ref_link = "?post_type={$post_type}&{$post_type}_id={$_REQUEST["{$post_type}_id"]}&";
        }

		?>
        <!--<dl class="tabs five-up">
			<dd <?php echo ( ! isset( $_REQUEST['tab'] ) || ( isset($_REQUEST['tab']) && $_REQUEST['tab']==$post_type.'-details' ) ) ? 'class="active"':'';  ?> ><a href="<?php echo isset($ref_link)?$bp_bp_nav_link . $ref_link . "tab={$post_type}-details":""; ?>">Details</a></dd>
            <?php if ( !$is_new_project_page) { ?>
            <dd <?php echo isset($_REQUEST['tab']) && $_REQUEST['tab']==$post_type.'-files' ? 'class="active"':'';  ?> ><a href="<?php echo $bp_bp_nav_link . $ref_link . "tab={$post_type}-files"; ?>">Attachments</a></dd>
			<dd <?php echo isset($_REQUEST['tab']) && $_REQUEST['tab']==$post_type.'-timeentry' ? 'class="active"':'';  ?> ><a href="<?php echo $bp_bp_nav_link . $ref_link . "tab={$post_type}-timeentry"; ?>">Time Entries</a></dd>
            <dd <?php echo isset($_REQUEST['tab']) && $_REQUEST['tab']==$post_type.'-task' ? 'class="active"':'';  ?> ><a href="<?php echo $bp_bp_nav_link . $ref_link . "tab={$post_type}-task"; ?>">Tasks</a></dd>
            <dd <?php echo isset($_REQUEST['tab']) && $_REQUEST['tab']==$post_type.'-notification' ? 'class="active"':''; ?>><a href="<?php echo $bp_bp_nav_link . $ref_link . "tab={$post_type}-notification"; ?>">Notification</a></dd>
            <?php } ?>            
        </dl> 

        <div class="tabs-content">
            <div class="content active" >-->
                <?php
                    if ( isset($_REQUEST['tab']) && ( $_REQUEST['tab']==$post_type.'-task/' || $_REQUEST['tab']==$post_type.'-task' ) ){
                        $rt_pm_bp_pm_project->get_project_task_tab($labels,$user_edit);
                    }
                    if ( isset($_REQUEST['tab']) && ( $_REQUEST['tab']==$post_type.'-timeentry/' || $_REQUEST['tab']==$post_type.'-timeentry') ){
                        $rt_pm_bp_pm_project->get_project_timeentry_tab($labels,$user_edit);
                    }
                    if ( isset($_REQUEST['tab']) && ( $_REQUEST['tab']==$post_type.'-files/' || $_REQUEST['tab']==$post_type.'-files') ){
                        $rt_pm_bp_pm_project->get_project_file_tab($labels,$user_edit);
                    }
                    if ( isset($_REQUEST['tab']) && ( $_REQUEST['tab']==$post_type.'-notification/' || $_REQUEST['tab']==$post_type.'-notification') ){
                        $rt_pm_bp_pm_project->get_project_notification_tab($labels,$user_edit);
                    }
					if ( $is_new_project_page || !isset($_REQUEST['tab']) || ( isset($_REQUEST['tab']) && ( $_REQUEST['tab']==$post_type.'-details/' || $_REQUEST['tab']==$post_type.'-details') )) {
                        $rt_pm_bp_pm_project->get_project_description_tab($labels,$user_edit);
                    }

                ?>
        <!--    </div>
        </div>
    </div>-->
