<?php
/**
 * Created by PhpStorm.
 * User: paresh
 * Date: 1/4/15
 * Time: 1:33 PM
 */
global $rt_biz_notification_rules_model, $rt_pm_notification, $rt_pm_bp_pm, $rt_pm_project;
$project_id = $_REQUEST["{$rt_pm_project->post_type}_id"];
$users = Rt_PM_Utils::get_pm_rtcamp_user();
$operators = $rt_pm_notification->get_operators();
$post_type = $_REQUEST['post_type'];

$author_cap = rt_biz_get_access_role_cap( RT_PM_TEXT_DOMAIN, 'author' );
if( current_user_can( $author_cap ) ){

    $user_edit = true;
}else {

    $user_edit = false;
}

if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'delete' ) {
    if ( isset( $_REQUEST['rule_id'] ) ) {
        $rule = $rt_biz_notification_rules_model->get( array( 'id' => $_REQUEST['rule_id'] ) );
        $rule = $rule[0];
        if ( $rule->rule_type == 'periodic' ) {
            wp_clear_scheduled_hook( 'rt_pm_timely_notification_'.$rule->id, array( $rule ) );
        }
        $rt_biz_notification_rules_model->delete( array( 'id' => $_REQUEST['rule_id'] ) );
    }
}

if ( isset( $_POST['rtpm_add_notification_rule'] ) ) {

    $error = array();
    $rule_type = '';
    $schedule = ( ! empty( $_POST['rtpm_nr_schedule'] ) ) ? $_POST['rtpm_nr_schedule'] : NULL;
    $period = ( ! empty( $_POST['rtpm_nr_period'] ) ) ? $_POST['rtpm_nr_period'] : NULL;
    $period_type = ( ! empty( $_POST['rtpm_nr_period_type'] ) ) ? $_POST['rtpm_nr_period_type'] : NULL;
    $context = '';
    $operator = '';
    $value = $_POST['rtpm_nr_value'];
    $value_type = '';
    $user = '';

    if ( empty( $_POST['rtpm_nr_context'] ) ) {
        $error[] = '<div class="error"><p>'.__('Context is required').'</p></div>';
    } else {
        $context = $_POST['rtpm_nr_context'];
    }
    if ( empty( $_POST['rtpm_nr_operator'] ) ) {
        $error[] = '<div class="error"><p>'.__('Operator is required').'</p></div>';
    } else {
        $operator = $_POST['rtpm_nr_operator'];
    }
    if ( empty( $_POST['rtpm_nr_value_type'] ) ) {
        $error[] = '<div class="error"><p>'.__('Value Type is required').'</p></div>';
    } else {
        $value_type = $_POST['rtpm_nr_value_type'];
    }
    if ( empty( $_POST['rtpm_nr_user'] ) ) {
        $error[] = '<div class="error"><p>'.__('User is required').'</p></div>';
    } else {
        $user = $_POST['rtpm_nr_user'];
    }
    if ( empty( $_POST['rtpm_nr_type'] ) || ! in_array( $_POST['rtpm_nr_type'], array( 'triggered', 'periodic' ) ) ) {
        $error[] = '<div class="error"><p>'.__('User is required').'</p></div>';
    } else {
        $rule_type = $_POST['rtpm_nr_type'];
    }

    if ( $rule_type == 'periodic' ) {
        if ( empty( $period_type ) ) {
            $error[] = '<div class="error"><p>'.__('Period Type is required').'</p></div>';
        }
        if ( empty( $schedule ) ) {
            $error[] = '<div class="error"><p>'.__('Schedule is required').'</p></div>';
        }
    }

    if ( empty( $error ) ) {
        $data = array(
            'rule_type' => $rule_type,
            'schedule' => $schedule,
            'period' => $period,
            'period_type' => $period_type,
            'module' => RT_PM_TEXT_DOMAIN,
            'entity' => $this->post_type,
            'entity_id' => $project_id,
            'context' => $context,
            'operator' => $operator,
            'value' => $value,
            'value_type' => $value_type,
            'subject' => $operators[$operator]['subject'],
            'message' => $operators[$operator]['message'],
            'user' => $user,
        );
        $rt_biz_notification_rules_model->insert( $data );
    } else {
        $_SESSION['rtpm_errors'] = $error;
    }
    $bp_bp_nav_link = $rt_pm_bp_pm->get_component_root_url().bp_current_action();
    $bp_bp_nav_link .= "?post_type={$post_type}&{$post_type}_id={$_REQUEST[ "{$post_type}_id" ]}&tab={$post_type}-notification";
    echo '<script>window.location="' . $bp_bp_nav_link . '";</script> ';
    die();
}
?>
<div class="wrap rtpm-notification-rules">
    <?php
    if (isset($_SESSION['rtpm_errors'])){
        $error = $_SESSION['rtpm_errors'];
        unset( $_SESSION['rtpm_errors'] );
    }

    if( ! empty( $error ) ) {
        foreach ( $error as $e ) {
            echo $e;
        }
    }
    ?>
    <div class="list-heading">
        <div class="large-10 columns list-title">
            <h2><?php _e( '#'.get_post_meta(  $project_id, 'rtpm_job_no', true ).' '. get_post_field( 'post_title', $project_id ), RT_PM_TEXT_DOMAIN );?></h2>
        </div>
        <div class="large-2 columns">

        </div>
    </div>
    <div class="list-heading notification-top">
        <div class="large-10 columns list-title">
            <h4><?php _e( 'Triggered Notifications', RT_PM_TEXT_DOMAIN ) ?></h4>
        </div>
        <div class="large-2 columns">

        </div>
    </div>
    <?php if ( $user_edit ) { ?>
        <div class="add-notification-rule">
            <form method="post">
                <input type="hidden" name="rtpm_add_notification_rule" value="1" />
                <input type="hidden" name="rtpm_nr_type" value="triggered" />
                <div class="">
                    <div class="large-3 columns">
                        <select name="rtpm_nr_context" required="required">
                            <option value=""><?php _e( 'Contexts' ) ?></option>
                            <?php foreach ( $rt_pm_notification->get_contexts() as $key => $label ) { ?>
                                <option value="<?php echo $key; ?>"><?php echo $label; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="large-2 columns">
                        <select name="rtpm_nr_operator" required="required">
                            <option value=""><?php _e( 'Operators' ); ?></option>
                            <?php foreach ( $operators as $key => $operator ) { ?>
                                <option value="<?php echo $key; ?>"><?php echo $key; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="large-1 columns">
                        <input type="text" name="rtpm_nr_value" />
                    </div>
                    <div class="large-2 columns">
                        <select name="rtpm_nr_value_type" required="required">
                            <option value=""><?php _e( 'Value Type' ); ?></option>
                            <option value="absolute"><?php _e('Absolute'); ?></option>
                            <option value="percentage"><?php _e('Percentage'); ?></option>
                        </select>
                    </div>
                    <div class="large-3 columns">
                        <select name="rtpm_nr_user" required="required">
                            <option value=""><?php _e( 'Select User to Notify' ); ?></option>
                            <option value="{{project_manager}}"><?php _e( '{{project_manager}}' ); ?></option>
                            <option value="{{business_manager}}"><?php _e( '{{business_manager}}' ); ?></option>
                            <?php foreach ( $users as $u ) { ?>
                                <option value="<?php echo $u->ID; ?>"><?php echo $u->display_name; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="large-1 columns">
                        <input type="submit" class="button-primary" value="<?php _e( 'Add' ); ?>">
                    </div>
                </div>
            </form>
        </div>
    <?php } ?>
    <table class="responsive" cellspacing="0">
        <thead>
        <tr>
            <th scope='col' id='rtpm_schedule' class='manage-column column-rtpm_schedule'  style=""><label>Schedule</label></th>
            <th scope='col' id='rtpm_context' class='manage-column column-rtpm_context'  style=""><label>Context</label></th>
            <th scope='col' id='rtpm_operator' class='manage-column column-rtpm_operator'  style=""><label>Operator</label></th>
            <th scope='col' id='rtpm_value' class='manage-column column-rtpm_value'  style=""><label>Value</label></th>
            <th scope='col' id='rtpm_value_type' class='manage-column column-rtpm_value_type' style=""><label>Value Type</label></th>
            <th scope='col' id='rtpm_period' class='manage-column column-rtpm_period'  style=""><label>Period</label></th>
            <th scope='col' id='rtpm_period_type' class='manage-column column-rtpm_period_type' style=""><label>Period Type</label></th>
            <th scope='col' id='rtpm_user' class='manage-column column-rtpm_user'  style=""><label>User to Notify</label></th>
            <th scope='col' id='rtpm_delete_rule' class='manage-column column-rtpm_delete_rule'  style=""><span>&nbsp;</span></th>
        </tr>
        </thead>

        <tfoot>
        <tr>
            <th scope='col' id='rtpm_schedule' class='manage-column column-rtpm_schedule'  style=""><label>Schedule</label></th>
            <th scope='col' id='rtpm_context' class='manage-column column-rtpm_context'  style=""><label>Context</label></th>
            <th scope='col' id='rtpm_operator' class='manage-column column-rtpm_operator'  style=""><label>Operator</label></th>
            <th scope='col' id='rtpm_value' class='manage-column column-rtpm_value'  style=""><label>Value</label></th>
            <th scope='col' id='rtpm_value_type' class='manage-column column-rtpm_value_type' style=""><label>Value Type</label></th>
            <th scope='col' id='rtpm_period' class='manage-column column-rtpm_period'  style=""><label>Period</label></th>
            <th scope='col' id='rtpm_period_type' class='manage-column column-rtpm_period_type' style=""><label>Period Type</label></th>
            <th scope='col' id='rtpm_user' class='manage-column column-rtpm_user'  style=""><label>User to Notify</label></th>
            <th scope='col' id='rtpm_rule_actions' class='manage-column column-rtpm_rule_actions'  style=""><label>&nbsp;</label></th>
        </tr>
        </tfoot>

        <tbody>
        <?php
        $rules = $rt_biz_notification_rules_model->get( array( 'entity_id' => $project_id, 'rule_type' => 'triggered' ) );
        ?>
        <?php if ( ! empty( $rules ) ) { ?>
            <?php foreach ( $rules as $r ) { ?>
                <tr>
                    <td class="rtpm_schedule column-rtpm_schedule">
                        <?php
                        $schedules = wp_get_schedules();
                        echo ( ! empty( $schedules[$r->schedule]['display'] ) ) ? $schedules[$r->schedule]['display'] : __( 'NA' );
                        ?>
                    </td>
                    <td class='rtpm_context column-rtpm_context'>
                        <?php
                        $context = $rt_pm_notification->get_context_label( $r->context, $r->rule_type );
                        echo $context;
                        ?>
                    </td>
                    <td class='rtpm_operator column-rtpm_operator'><?php echo $r->operator; ?></td>
                    <td class='rtpm_value column-rtpm_value'>
                        <?php
                        echo ( ! empty( $r->value ) ) ? $r->value : __( 'Nill' );
                        ?>
                    </td>
                    <td class='rtpm_value_type column-rtpm_value_type'>
                        <?php
                        switch( $r->value_type ) {
                            case 'percentage' :
                                _e( 'Percentage' );
                                break;
                            case 'absolute' :
                                _e( 'Absolute' );
                                break;
                        }
                        ?>
                    </td>
                    <td class="rtpm_period column-rtpm_period">
                        <?php
                        if ( $r->rule_type == 'triggered' ) {
                            _e('NA');
                        } else {
                            echo ( ! empty( $r->period ) ) ? $r->period : __( 'Nill' );
                        }
                        ?>
                    </td>
                    <td class="rtpm_period_type column-rtpm_period_type">
                        <?php
                        if ( $r->rule_type == 'triggered' ) {
                            _e('NA');
                        } else {
                            echo ucfirst( $r->period_type );
                        }
                        ?>
                    </td>
                    <td class='rtpm_user column-rtpm_user'>
                        <?php
                        switch( $r->user ) {
                            case '{{project_manager}}':
                                $project_manager = get_user_by( 'id', get_post_meta( $project_id, 'project_manager', true ) );
                                echo $project_manager->display_name;
                                break;
                            case '{{business_manager}}':
                                $business_manager = get_user_by( 'id', get_post_meta( $project_id, 'business_manager', true ) );
                                echo $business_manager->display_name;
                                break;
                            default:
                                $user = get_user_by( 'id', $r->user );
                                echo $user->display_name;
                                break;
                        }
                        ?>
                    </td>
                    <td class='rtpm_rule_actions'>
                        <a class="rtpm-delete" href="<?php echo add_query_arg( array( 'action' => 'delete', 'rule_id' => $r->id ) ); ?>"><?php _e( 'Delete' ) ?></a>
                    </td>
                </tr>
            <?php } ?>
        <?php } else { ?>
            <tr><td colspan="6"><?php echo _e( 'No Rules Found !' ); ?></td></tr>
        <?php } ?>
        </tbody>
    </table>
    <br />
    <hr />
    <br />
    <div class="list-heading notification-bottom">
        <div class="large-10 columns list-title">
            <h4><?php _e( 'Periodic Notifications', RT_PM_TEXT_DOMAIN ) ?></h4>
        </div>
        <div class="large-2 columns">

        </div>
    </div>
    <?php if ( $user_edit ) { ?>
        <div class="add-notification-rule notification-bottom">
            <form method="post">
                <input type="hidden" name="rtpm_add_notification_rule" value="1" />
                <input type="hidden" name="rtpm_nr_type" value="periodic" />
                <div class="">
                    <div class="large-3 columns">
                        <select name="rtpm_nr_schedule" required="required">
                            <option value=""><?php _e( 'Schedule' ); ?></option>
                            <?php foreach ( wp_get_schedules() as $key => $schedule ) { ?>
                                <option value="<?php echo $key; ?>"><?php echo $schedule['display']; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="large-3 columns">
                        <select name="rtpm_nr_context" required="required">
                            <option value=""><?php _e( 'Contexts' ) ?></option>
                            <?php foreach ( $rt_pm_notification->get_contexts( 'periodic' ) as $key => $label ) { ?>
                                <option value="<?php echo $key; ?>"><?php echo $label; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="large-2 columns">
                        <select name="rtpm_nr_operator" required="required">
                            <option value=""><?php _e( 'Operators' ); ?></option>
                            <?php foreach ( $operators as $key => $operator ) { ?>
                                <option value="<?php echo $key; ?>"><?php echo $key; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="large-2 columns">
                        <input type="text" name="rtpm_nr_value" placeholder="<?php _e( 'Value' ); ?>" />
                    </div>
                    <div class="large-2 columns">
                        <select name="rtpm_nr_value_type" required="required">
                            <option value=""><?php _e( 'Value Type' ); ?></option>
                            <option value="absolute"><?php _e('Absolute'); ?></option>
                            <option value="percentage"><?php _e('Percentage'); ?></option>
                        </select>
                    </div>
                </div>
                <div class="">
                    <div class="large-3 columns">
                        <input type="text" name="rtpm_nr_period" placeholder="<?php _e( 'Period' ); ?>" />
                    </div>
                    <div class="large-4 columns">
                        <select name="rtpm_nr_period_type" required="required">
                            <option value=""><?php _e( 'Period Type' ); ?></option>
                            <option value="before"><?php _e( 'Before' ); ?></option>
                            <option value="after"><?php _e( 'After' ); ?></option>
                        </select>
                    </div>
                    <div class="large-4 columns">
                        <select name="rtpm_nr_user" required="required">
                            <option value=""><?php _e( 'Select User to Notify' ); ?></option>
                            <option value="{{project_manager}}"><?php _e( '{{project_manager}}' ); ?></option>
                            <option value="{{business_manager}}"><?php _e( '{{business_manager}}' ); ?></option>
                            <?php foreach ( $users as $u ) { ?>
                                <option value="<?php echo $u->ID; ?>"><?php echo $u->display_name; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="large-1 columns">
                        <input type="submit" class="button-primary" value="<?php _e( 'Add' ); ?>">
                    </div>
                </div>
            </form>
        </div>
    <?php } ?>
    <table class="responsive" cellspacing="0">
        <thead>
        <tr>
            <th scope='col' id='rtpm_schedule' class='manage-column column-rtpm_schedule'  style=""><label>Schedule</label></th>
            <th scope='col' id='rtpm_context' class='manage-column column-rtpm_context'  style=""><label>Context</label></th>
            <th scope='col' id='rtpm_operator' class='manage-column column-rtpm_operator'  style=""><label>Operator</label></th>
            <th scope='col' id='rtpm_value' class='manage-column column-rtpm_value'  style=""><label>Value</label></th>
            <th scope='col' id='rtpm_value_type' class='manage-column column-rtpm_value_type' style=""><label>Value Type</label></th>
            <th scope='col' id='rtpm_period' class='manage-column column-rtpm_period'  style=""><label>Period</label></th>
            <th scope='col' id='rtpm_period_type' class='manage-column column-rtpm_period_type' style=""><label>Period Type</label></th>
            <th scope='col' id='rtpm_user' class='manage-column column-rtpm_user'  style=""><label>User to Notify</label></th>
            <th scope='col' id='rtpm_delete_rule' class='manage-column column-rtpm_delete_rule'  style=""><label>&nbsp;</label></th>
        </tr>
        </thead>

        <tfoot>
        <tr>
            <th scope='col' id='rtpm_schedule' class='manage-column column-rtpm_schedule'  style=""><label>Schedule</label></th>
            <th scope='col' id='rtpm_context' class='manage-column column-rtpm_context'  style=""><label>Context</label></th>
            <th scope='col' id='rtpm_operator' class='manage-column column-rtpm_operator'  style=""><label>Operator</label></th>
            <th scope='col' id='rtpm_value' class='manage-column column-rtpm_value'  style=""><label>Value</label></th>
            <th scope='col' id='rtpm_value_type' class='manage-column column-rtpm_value_type' style=""><label>Value Type</label></th>
            <th scope='col' id='rtpm_period' class='manage-column column-rtpm_period'  style=""><label>Period</label></th>
            <th scope='col' id='rtpm_period_type' class='manage-column column-rtpm_period_type' style=""><label>Period Type</label></th>
            <th scope='col' id='rtpm_user' class='manage-column column-rtpm_user'  style=""><label>User to Notify</label></th>
            <th scope='col' id='rtpm_rule_actions' class='manage-column column-rtpm_rule_actions'  style=""><label>&nbsp;</label></th>
        </tr>
        </tfoot>

        <tbody>
        <?php
        $rules = $rt_biz_notification_rules_model->get( array( 'entity_id' => $project_id, 'rule_type' => 'periodic' ) );
        ?>
        <?php if ( ! empty( $rules ) ) { ?>
            <?php foreach ( $rules as $r ) { ?>
                <tr>
                    <td class="rtpm_schedule column-rtpm_schedule">
                        <?php
                        $schedules = wp_get_schedules();
                        echo ( ! empty( $schedules[$r->schedule]['display'] ) ) ? $schedules[$r->schedule]['display'] : __( 'NA' );
                        ?>
                    </td>
                    <td class='rtpm_context column-rtpm_context'>
                        <?php
                        $context = $rt_pm_notification->get_context_label( $r->context, $r->rule_type );
                        echo $context;
                        ?>
                    </td>
                    <td class='rtpm_operator column-rtpm_operator'><?php echo $r->operator; ?></td>
                    <td class='rtpm_value column-rtpm_value'>
                        <?php
                        echo ( ! empty( $r->value ) ) ? $r->value : __( 'Nill' );
                        ?>
                    </td>
                    <td class='rtpm_value_type column-rtpm_value_type'>
                        <?php
                        switch( $r->value_type ) {
                            case 'percentage' :
                                _e( 'Percentage' );
                                break;
                            case 'absolute' :
                                _e( 'Absolute' );
                                break;
                        }
                        ?>
                    </td>
                    <td class="rtpm_period column-rtpm_period">
                        <?php
                        if ( $r->rule_type == 'triggered' ) {
                            _e('NA');
                        } else {
                            echo ( ! empty( $r->period ) ) ? $r->period : __( 'Nill' );
                        }
                        ?>
                    </td>
                    <td class="rtpm_period_type column-rtpm_period_type">
                        <?php
                        if ( $r->rule_type == 'triggered' ) {
                            _e('NA');
                        } else {
                            echo ucfirst( $r->period_type );
                        }
                        ?>
                    </td>
                    <td class='rtpm_user column-rtpm_user'>
                        <?php
                        switch( $r->user ) {
                            case '{{project_manager}}':
                                $project_manager = get_user_by( 'id', get_post_meta( $project_id, 'project_manager', true ) );
                                echo $project_manager->display_name;
                                break;
                            case '{{business_manager}}':
                                $business_manager = get_user_by( 'id', get_post_meta( $project_id, 'business_manager', true ) );
                                echo $business_manager->display_name;
                                break;
                            default:
                                $user = get_user_by( 'id', $r->user );
                                echo $user->display_name;
                                break;
                        }
                        ?>
                    </td>
                    <td class='rtpm_rule_actions'>
                        <a class="rtpm-delete" href="<?php echo add_query_arg( array( 'action' => 'delete', 'rule_id' => $r->id ) ); ?>"><?php _e( 'Delete' ) ?></a>
                    </td>
                </tr>
            <?php } ?>
        <?php } else { ?>
            <tr><td colspan="6"><?php echo _e( 'No Rules Found !' ); ?></td></tr>
        <?php } ?>
        </tbody>
    </table>
</div>