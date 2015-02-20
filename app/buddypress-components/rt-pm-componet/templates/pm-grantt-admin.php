<?php
/**
 * Created by PhpStorm.
 * User: rtcamp
 * Date: 20/2/15
 * Time: 7:58 PM
 */
?>

<?php
$project_id = $_GET['rt_project_id'];
$project_working_hours = get_post_meta( $project_id, 'working_hours' , true);
?>
<form method="post">

<?php wp_nonce_field('rt_pm_edit_work_hours', 'rt_pm_edit_work_hours_nonce'); ?>
    <input type="hidden" value="<?php echo $project_id ?>" name="post[project_id]">
<div class="list-heading">
    <div class="large-12 columns list-title">
        <h4><?php _e( 'Working Hours', RT_PM_TEXT_DOMAIN ) ?></h4>
    </div>
</div>

<div class="row">
    <div class="small-12 medium-6 columns">
        <select name="post[working_hours]">
            <?php for( $i = 1; $i < 25; $i+=0.25 ){ ?>
                <option <?php selected( $project_working_hours, $i ); ?> ><?php _e( $i ) ?></option>
            <?php } ?>
        </select>
    </div>
    <div class="small-12 medium-6 columns">
        <input type="submit" value="Update" />
    </div>
</div>
</form>