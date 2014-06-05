<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
    global $rt_pm_project;
    $projectTable = new Rt_PM_Project_List_View();
    if (!isset( $_REQUEST['post_type'] )){
        $_REQUEST['post_type'] = 'rt_project';
    }
    $post_type = $_REQUEST['post_type'];

    $user_edit = false;
    if ( current_user_can( "edit_{$post_type}" ) ) {
        $user_edit = 'true';
    } else if ( current_user_can( "read_{$post_type}" ) ) {
        $user_edit = 'false';
    } else {
        wp_die("Opsss!! You are in restricted area");
    }

    $error=$projectTable->page_action();
    $form_ulr = admin_url("edit.php?post_type={$post_type}&page=rtpm-all-{$post_type}");
?>
<?php screen_icon(); ?>
<div class="rtpm-container wrap">
    <h2>
        <?php echo $labels['all_items']; ?>
        <button id="btn-add-new-post" class="add-new-h2"><?php _e( 'Add new' ); ?></button>
    </h2>
    <?php $form_container_class = 'large-12 small-12 columns rtpm-project-form-container ';
    if ( isset( $error ) && ! empty( $error )   ){
        $form_container_class.= 'collapse';
    }else{
        $form_container_class.= 'closed';
    }
    if(isset( $error ) && ! empty( $error )){
        if( $error == 1  ){
            ?><div style="padding:10px;" class="success"><?php
            echo 'Project Successfully Added';
            ?> </div><?php
        }else{
            ?><div style="padding:10px;" class="error"><?php
            echo $error;
            ?> </div><?php
        }
    } ?>

    <div id="add-new-post" class="<?php echo $form_container_class ?>">
        <div class="inside">
            <h4 class="hndle"><span><i class="general foundicon-tools"></i> Start a New Project</span></h4>
            <form method="post" id="form-add-project" action="<?php echo $form_ulr; ?>">
                <?php $projectTable->ui_create_project($user_edit); ?>
            </form>
        </div>
    </div>
    <div style="padding:0" class="large-12 columns rtpm-projects">
            <?php $projectTable->table_view(); ?>
    </div>
</div>

<script>
    jQuery(document).ready(function($) {
        // Quick Add project on List Project
        $('#btn-add-new-post').click(function(){
            if ( $('#add-new-post').hasClass('closed')){
                $('#add-new-post').removeClass('closed');
                $('#add-new-post').addClass('collapse');
            }else{
                $('#add-new-post').removeClass('collapse');
                $('#add-new-post').addClass('closed');
            }
        });
    });
</script>