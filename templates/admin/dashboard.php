<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
    global $rt_pm_project;
	$leadsTable = new Rt_PM_Project_List_View();

    $user_edit = false;
    if ( current_user_can( "edit_{$post_type}" ) ) {
        $user_edit = 'true';
    } else if ( current_user_can( "read_{$post_type}" ) ) {
        $user_edit = 'false';
    } else {
        wp_die("Opsss!! You are in restricted area");
    }

    $error=$leadsTable->page_action();

    $form_ulr = admin_url("edit.php?post_type={$rt_pm_project->post_type}&page=rtpm-all-{$rt_pm_project->post_type}");

?>
<?php screen_icon(); ?>
<div class="wrap">
    <h2>
        <?php echo $labels['all_items']; ?>
    </h2>

    <div style="margin-top:20px;" id="add-new-post" class="large-12 small-12 columns">
        <?php
            if ( isset( $error ) && ! empty( $error )   ){
                ?><div style="padding:10px;" class="error"><?php
                    echo $error;
                ?> </div><?php
            }
        ?>
        <form method="post" id="form-add-post" class="columns rtpm-project-form-container" action="<?php echo $form_ulr; ?>">
            <div class="large-7 columns">
                <h4><i class="gen-enclosed foundicon-add-doc"></i> Start a New Project</h4>
            </div>
            <?php $leadsTable->ui_create_project($user_edit); ?>
        </form>
    </div>
    <div style="padding:0" class="large-12 columns rtpm-projects">
            <?php $leadsTable->table_view(); ?>
    </div>

    <style>
        .rtpm-projects{
            margin-top: 20px;
        }


        .hndle{
            border-bottom: 1px solid #eee;
            padding: 5px;
            margin-bottom: 0;
            font-weight: bold;
            color: #444;
            font-size: 12px;
        }

        .rtpm-project-form-container{
            background: #fff;
            padding-top: 20px;
            -webkit-border-radius: 3px;
            -moz-border-radius: 3px;
            border-radius: 3px;
            -webkit-box-shadow: 0 0 6px rgba(0, 0, 0, 0.3);
            -moz-box-shadow: 0 0 6px rgba(0, 0, 0, 0.3);
            box-shadow: 0 0 6px rgba(0, 0, 0, 0.3);
        }

        .rtpm-project-form-container h3{
            margin: 0 0 20px 0;
        }

        .rtpm-project-form-container .data-input{
            margin-bottom:10px;
        }

        article{
            min-height: 150px;
            padding: 15px;
            float: left;
            margin-bottom:15px;
            background: #fff;
            -webkit-border-radius: 3px;
            -moz-border-radius: 3px;
            border-radius: 3px;
            -webkit-box-shadow: 0 0 6px rgba(0, 0, 0, 0.3);
            -moz-box-shadow: 0 0 6px rgba(0, 0, 0, 0.3);
            box-shadow: 0 0 6px rgba(0, 0, 0, 0.3);
        }

        .rtpm-projects [class*="columns"] + [class*="columns"]:last-child{
            float: left;
        }

        article h2{
            color: #2ea2cc;
            padding: 0;
            margin: 0;
            text-transform: uppercase;
        }
        article h4{
            color: #222222;
            padding: 0;
            margin: 0;
        }
        article h4 p,.rtpm-project-detail p{
            margin: 0;
        }
        article a{
            text-decoration:none;
        }
        .rtpm-project-detail{
            color: #B6B6b4;
            margin: 10px 0;
        }

    </style>
</div>