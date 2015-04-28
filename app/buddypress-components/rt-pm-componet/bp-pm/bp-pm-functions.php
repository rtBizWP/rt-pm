<?php

/********************************************************************************
 * Screen Functions
 *
 * Screen functions are the controllers of BuddyPress. They will execute when their
 * specific URL is caught. They will first save or manipulate data using business
 * functions, then pass on the user to a template file.
 */
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Description of BP HRM functions.
 *
 * @author kishore
 */


function pm_pagination( $totalPage, $page ){
    global $rt_pm_bp_pm;
   
    if( $totalPage > 1 ){
                                            
        $base = $rt_pm_bp_pm->get_component_root_url().bp_current_action().'/%_%';
        $formate = 'page/%#%';
        if( isset( $_GET['orderby'] ) ) {

                $arr_params = array( 'orderby' => $_GET['orderby'], 'order' => $_GET['order'] );
                $base = add_query_arg( $arr_params, $rt_pm_bp_pm->get_component_root_url().bp_current_action() ) .'%_%' ; 
                $formate = '&paged=%#%';
        }

        $customPagHTML     =  '<div class="projects-lists pagination" role="menubar" aria-label="Pagination"><span class="current">Page '.$page.' of '.$totalPage.'</span>'.paginate_links( array(
        'base' => $base,
        'format' => $formate,
        'total' => $totalPage,
        'current' => $page
        )).'</div>';
        echo $customPagHTML;
    }
}

function render_project_summary_buttons( $post_id ){
	global $rt_pm_bp_pm;
	if (isset($post_id)) {
				$save_button = __( 'Update' );
			} else {
				$save_button = __( 'Add Project' );
			}
	?>
	<button class="mybutton" type="submit" ><?php _e($save_button); ?></button>
				<?php 
				if(isset($post_id)) { 
					$get_post_status = get_post_status( $post_id );
					if ( isset( $get_post_status ) && $get_post_status == 'trash' ){
						$archive_action = 'unarchive';
						$archive_button = __( 'Unarchive' );
						$button_archive_id = 'button-unarchive';
						$redirect = $rt_pm_bp_pm->get_component_root_url(). 'archives';
					} else {
						$archive_action = 'archive';
						$archive_button = __( 'Archive' );
						$button_archive_id = 'button-archive';
						$redirect = $rt_pm_bp_pm->get_component_root_url();
					}
					
				?>
				<button id="top-<?php echo $button_archive_id; ?>" class="mybutton" data-href="<?php echo add_query_arg( array( 'action' => $archive_action, 'rt_project_id' => $post_id ), $redirect ); ?>" class=""><?php _e($archive_button); ?></button>
				<button id="top-button-trash" class="mybutton" data-href="<?php echo add_query_arg( array( 'action' => 'trash', 'rt_project_id' => $post_id ), $redirect ); ?>" class=""><?php _e( 'Delete' ); ?></button>
				<?php  }
}

function pm_get_attachment_data(){
  
    $meta = get_post( $_POST['attachment_id'] );
   
    echo json_encode( $meta );
    
    die(0);
}

add_action( 'wp_ajax_rtpmattachment_metadata', 'pm_get_attachment_data' );

function pm_save_attachment_data(){
  
  
    $args = array(
        'ID' => $_POST['ID'],
        'post_title' => $_POST['post_title'],
        'post_excerpt' => $_POST['post_excerpt'],
        'post_content' => $_POST['post_content'],
    );
   $post_id = wp_update_post( $args );
   echo $post_id;
   die();
  
}

add_action( 'wp_ajax_rtpmattachment_save_data', 'pm_save_attachment_data' );


function pm_add_new_documents(){

    
    $parent_post_id = $_POST['post_id'];
    $filename = $_POST['filename'];
    //var_dump($filenames);
    //foreach ( $filenames as $filename ) {
        
    
    // $filename should be the path to a file in the upload directory.
   

    // The ID of the post this attachment is for.
    //$parent_post_id = 37;

    // Check the type of tile. We'll use this as the 'post_mime_type'.
    $filetype = wp_check_filetype( basename( $filename ), null );

    // Get the path to the upload directory.
    $wp_upload_dir = wp_upload_dir();

    // Prepare an array of post data for the attachment.
    $attachment = array(
            'guid'           => $wp_upload_dir['url'] . '/' . basename( $filename ), 
            'post_mime_type' => $filetype['type'],
            'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
            'post_content'   => '',
            'post_status'    => 'inherit'
    );

    // Insert the attachment.
    $attach_id = wp_insert_attachment( $attachment, $filename, $parent_post_id );

   $data = array(
           'attachment_id'=>$attach_id
    );
    echo json_encode( $data );
    
    die();
}
add_action( 'wp_ajax_rtpm_add_new_documents', 'pm_add_new_documents'  );


function pm_remove_document(){
    
    $attachment_id = $_POST[ 'attachment_id' ];
    wp_delete_attachment( $attachment_id );
}
add_action( 'wp_ajax_rtpm_remove_document', 'pm_remove_document'  );


/**
 * Render BDM selectbox
 * @param $business_manager
 */
function rt_pm_render_bdm_selectbox( $business_manager ){ ?>

    <select name="post[business_manager]" >
        <option value=""><?php _e( 'Select BM' ); ?></option>
        <?php

        $employees = rt_biz_get_employees();

        if (!empty( $employees )) {
            foreach ($employees as $bm) {

                $employee_wp_user_id = rt_biz_get_wp_user_for_person( $bm->ID );

                if ( $employee_wp_user_id == $business_manager) {
                    $selected = " selected";
                } else {
                    $selected = " ";
                }
                echo '<option value="' . $employee_wp_user_id . '"' . $selected . '>' . rt_get_user_displayname( $employee_wp_user_id ) . '</option>';
            }
        }
        ?>
    </select>

<?php }

/**
 * Render assignee selectbox in Add new Task
 * @param $task_assignee
 */
function rt_pm_render_task_assignee_selectbox( $task_assignee ){ ?>

    <select name="post[post_assignee]" >
        <option value=""><?php _e( 'Select Assignee' ); ?></option>
        <?php
        $employees = rt_biz_get_employees();
        if (!empty( $employees )) {
            foreach ( $employees as $author) {

                $employee_wp_user_id = rt_biz_get_wp_user_for_person( $author->ID );

                if ( $employee_wp_user_id == $task_assignee ) {
                    $selected = " selected";
                } else {
                    $selected = " ";
                }
                echo '<option value="' . $employee_wp_user_id . '"' . $selected . '>' . rt_get_user_displayname( $employee_wp_user_id ) . '</option>';
            }
        }
        ?>
    </select>
<?php }



/**
 * Render BDM selectbox
 * @param $business_manager
 */
function rtpm_render_manager_selectbox( $project_manager ){ ?>
    <select name="post[project_manager]" >
        <option value=""><?php _e( 'Select PM' ); ?></option>
        <?php
        $employees = rt_biz_get_employees();

        if (!empty( $employees )) {
            foreach ($employees as $bm) {

                $employee_wp_user_id = rt_biz_get_wp_user_for_person( $bm->ID );

                if ( $employee_wp_user_id == $project_manager ) {
                    $selected = " selected";
                } else {
                    $selected = " ";
                }
                echo '<option value="' . $employee_wp_user_id . '"' . $selected . '>' . rt_get_user_displayname( $employee_wp_user_id ) . '</option>';
            }
        }
        ?>
    </select>
<?php }

