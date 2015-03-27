<?php
/**
 * Created by PhpStorm.
 * User: paresh
 * Date: 11/12/14
 * Time: 3:13 PM
 */

/**
 * Save project working hours
 * @return bool|void
 */
function bp_save_working_hours(){

    if( !bp_is_current_component( 'pm' ) )
        return false;


    if ( ! isset( $_POST['rt_pm_edit_work_hours_nonce'] ) || ! wp_verify_nonce( $_POST['rt_pm_edit_work_hours_nonce'], 'rt_pm_edit_work_hours' ) )
        return;

    $data = $_POST['post'];

    update_post_meta( $data['project_id'], 'working_hours', $data['working_hours'] );

}
add_action('bp_actions', 'bp_save_working_hours');

/**
 * Save project working days
 * @return bool|void
 */
function bp_save_working_days(){

    if( !bp_is_current_component( 'pm' ) )
        return false;

    if ( ! isset( $_POST['rt_pm_edit_work_days_nonce'] ) || ! wp_verify_nonce( $_POST['rt_pm_edit_work_days_nonce'], 'rt_pm_edit_work_days' ) )
        return;


    $data = $_POST['post'];

    if( !isset( $data ) )
        return false;

    $working_days = array();

    if( isset( $data['days'] ) )
        $working_days['days'] = $data['days'];


    if( isset( $data['occasion_name'] ) ){

        foreach($data['occasion_name'] as $index => $occasion_name) {

            if ( empty( $occasion_name ) || empty( $data['occasion_date'][ $index ] ) ) continue;

            $working_days['occasions'][] = array(
                'name'  => $occasion_name,
                'date' =>$data['occasion_date'][ $index ]
            );
        }
    }

    update_post_meta( $data['project_id'], 'working_days', $working_days );

}
add_action('bp_actions', 'bp_save_working_days');