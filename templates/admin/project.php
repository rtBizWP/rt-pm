<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

    $AddProject = new Rt_PM_Add_Project();

    $post_type=$_REQUEST['post_type'];

    $user_edit = false;
    if ( current_user_can( "edit_{$post_type}" ) ) {
        $user_edit = 'true';
    } else if ( current_user_can( "read_{$post_type}" ) ) {
        $user_edit = 'false';
    } else {
        wp_die("Opsss!! You are in restricted area");
    }?>
    <div class="rtpm-container">
        <div style="max-width:none;" class="row">
            <div style="padding:0" class="large-6 columns">
                <?php
                if (isset($post->ID)) {
                    $post_icon = "foundicon-".( ( $user_edit ) ? 'edit' : 'view-mode' );
                    $page_title = ucfirst($labels['name']);
                } else {
                    $post_icon = "foundicon-add-doc";
                    $page_title = "Add ".ucfirst($labels['name']);
                }
                ?>
                <h4><i class="gen-enclosed <?php echo $post_icon; ?>"></i> <?php _e($page_title); ?></h4>
            </div>
            <div style="padding:0;" class="large-6 columns rtcrm-sticky">
                <?php if(isset($post->ID) && current_user_can( "delete_{$post_type}s" ) ){ ?>
                    <button id="button-trash" type="button" class="right mybutton alert" ><?php _e("Trash"); ?></button>
                <?php } ?>
            </div>
        </div>
        <?php
        $is_new_project_page = isset($_REQUEST["{$post_type}_id"])? false : true;
        if (!$is_new_project_page) {
            $ref_link = "edit.php?post_type={$post_type}&page=rtpm-add-{$post_type}&{$post_type}_id={$_REQUEST["{$post_type}_id"]}&";
        }else{
            $ref_link = "edit.php?post_type={$post_type}&page=rtpm-add-{$post_type}&";
        }
        ?>
        <dl class="tabs five-up">
            <dd <?php echo !isset($_REQUEST['tab']) || ( isset($_REQUEST['tab']) && $_REQUEST['tab']==$post_type.'-details' ) ? 'class="active"':'';  ?> ><a href="<?php echo admin_url($ref_link . "tab={$post_type}-details"); ?>">Project Details</a></dd>
            <?php if ( !$is_new_project_page) { ?>
            <dd <?php echo isset($_REQUEST['tab']) && $_REQUEST['tab']==$post_type.'-task' ? 'class="active"':'';  ?> ><a href="<?php echo admin_url($ref_link . "tab={$post_type}-task"); ?>">Task</a></dd>
            <dd <?php echo isset($_REQUEST['tab']) && $_REQUEST['tab']==$post_type.'-timeentry' ? 'class="active"':'';  ?> ><a href="<?php echo admin_url($ref_link . "tab={$post_type}-timeentry"); ?>">Time Entry</a></dd>
            <dd <?php echo isset($_REQUEST['tab']) && $_REQUEST['tab']==$post_type.'-files' ? 'class="active"':'';  ?> ><a href="<?php echo admin_url($ref_link . "tab={$post_type}-files"); ?>">Project File</a></dd>
            <dd <?php echo isset($_REQUEST['tab']) && $_REQUEST['tab']==$post_type.'-reports' ? 'class="active"':'';  ?>><a href="<?php echo admin_url($ref_link . "tab={$post_type}-reports"); ?>">Project Reports</a></dd>
            <?php } ?>
        </dl>

        <div class="tabs-content">
            <div class="content active" >
                <?php
                    if ( isset($_REQUEST['tab']) && $_REQUEST['tab']==$post_type.'-task' ){
                        $AddProject->get_project_task_tab($labels,$user_edit);
                    }
                    if ( isset($_REQUEST['tab']) && $_REQUEST['tab']==$post_type.'-timeentry' ){
                        $AddProject->get_project_timeentry_tab($labels,$user_edit);
                    }
                    if ( isset($_REQUEST['tab']) && $_REQUEST['tab']==$post_type.'-files' ){
                        $AddProject->get_project_file_tab($labels,$user_edit);
                    }
                    if ( $is_new_project_page || !isset($_REQUEST['tab']) || ( isset($_REQUEST['tab']) && $_REQUEST['tab']==$post_type.'-details' )) {
                        $AddProject->get_project_description_tab($labels,$user_edit);
                    }

                ?>
            </div>
        </div>
    </div>
