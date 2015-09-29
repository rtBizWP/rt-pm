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
    if ( current_user_can( "projects_edit_projects" ) ) {
        $user_edit = 'true';
    } else if ( current_user_can( "projects_read_projects" ) ) {
        $user_edit = 'false';
    } else {
        wp_die("Opsss!! You are in restricted area");
    }

   // $error=$projectTable->page_action();
    $form_ulr = admin_url("edit.php?post_type={$post_type}&page=rtpm-all-{$post_type}");
?>
<?php screen_icon(); ?>
<div class="rtpm-container wrap">
    <h2>
        <?php echo $labels['all_items']; ?>

    </h2>

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