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
$project_working_days = get_post_meta( $project_id, 'working_days' , true);

$days = array();
$occasions = array();

if( isset( $project_working_days['days'] ) )
    $days = $project_working_days['days'];

if( isset( $project_working_days['occasions'] )  )
     $occasions = $project_working_days['occasions'];
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
            <?php for( $i = 1; $i <= 24 ; $i+=0.25 ){ ?>
                <option <?php selected( $project_working_hours, $i ); ?> ><?php _e( $i ) ?></option>
            <?php } ?>
        </select>
    </div>
    <div class="small-12 medium-6 columns">
        <input type="submit" value="Update" />
    </div>
</div>
</form>

<form method="post">
    <?php wp_nonce_field('rt_pm_edit_work_days', 'rt_pm_edit_work_days_nonce'); ?>
    <input type="hidden" value="<?php echo $project_id ?>" name="post[project_id]">
    <div class="list-heading">
        <div class="large-12 columns list-title">
            <h4><?php _e( 'Working Days', RT_PM_TEXT_DOMAIN ) ?></h4>
        </div>
    </div>

     <div class="list-heading">
            <div class="large-12 columns">
                <hr/><h6><?php _e( 'Non working days', RT_PM_TEXT_DOMAIN ) ?></h6><hr/>
            </div>
     </div>

    <div class="row">
        <div class="small-12 medium-6 columns">

            <input type="checkbox" id="saturday" name="post[days][]" value="6" <?php echo in_array( '6', $days ) ?  'checked="checked"' : ''; ?>>
            <label for="saturday">Saturday</label>

            <input type="checkbox" id="sunday" name="post[days][]" value="0" <?php echo in_array( '0', $days ) ?  'checked="checked"' : ''; ?>>
            <label for="sunday">Sunday</label>

        </div>
    </div>

    <div class="row">
        <div class="small-12">
            <?php _e( 'Add date and reason (both are required) and block out that day', RT_PM_TEXT_DOMAIN ) ?>
        </div>
    </div>

    <div class="row rt-parent-row">
        <div class="row collapse">
            <div class="small-8 medium-4 columns">
                <label><?php _e( 'Occasion', RT_PM_TEXT_DOMAIN ) ?> <span class="required">*</span></label>
            </div>

            <div class="small-4 medium-3 columns left">
                <label><?php _e( 'Date', RT_PM_TEXT_DOMAIN ) ?> <span class="required">*</span></label>
            </div>
        </div>
        <div class="row rt-row">
            <div class="small-6 medium-4 columns">

                <input type="text" name="post[occasion_name][]">
            </div>

            <div class="small-4 medium-3 columns">

                <input type="text" placeholder="DD/MM/YYYY" class="datepicker" name="post[occasion_date][]">
            </div>

            <div class="small-2 columns left">
                <a class="add-button add-multiple button"><i class="fa fa-plus"></i></a>
            </div>
        </div>

        <?php
            foreach( $occasions as  $occasion ){
                ?>

                <div class="row rt-row">
                    <div class="small-6 medium-4 columns">

                        <input type="text" name="post[occasion_name][]" value=<?php _e( $occasion['name'], RT_PM_TEXT_DOMAIN ) ?>>
                    </div>

                    <div class="small-4 medium-3 columns">

                        <input type="text" placeholder="DD/MM/YYYY" class="datepicker" name="post[occasion_date][]" value=<?php _e( $occasion['date'] ) ?>>
                    </div>

                    <div class="small-2 columns left">
                        <a class="add-button delete-multiple button"><i class="fa fa-times"></i></a>
                    </div>
                </div>

            <?php }
        ?>

    </div>


    <div class="row">
        <div class="small-12 medium-6 columns right">
            <input type="submit" value="Update" />
        </div>
    </div>

</form>