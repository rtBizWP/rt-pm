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

function pm_add_documents_section( $post_id ){ ?>
 <div class="row">

                         <div class="large-7 columns">
                             <ul class="attachments" id="attachment-document">
                            <?php

                                $attachments = get_posts( array(
                                        'post_type' => 'attachment',
                                        'posts_per_page' => -1,
                                        'post_parent' => $post_id,
                                        'exclude'     => get_post_thumbnail_id()

                                ) );

                                foreach ($attachments as $key => $attachment) {

                                      $thumbnail_url = wp_get_attachment_thumb_url( $attachment->ID );
                                      if ( empty( $thumbnail_url ) ) {
                                          $thumbnail_url = wp_mime_type_icon( $attachment->post_mime_type );
                                      }
                                ?>

                               <li tabindex="0" role="checkbox" aria-label="6_webp" aria-checked="false" class="attachment save-ready document-attachment">
                                   <div class="attachment-preview js--select-attachment type-image subtype-png landscape">
                                        <div class="thumbnail">
                                            <div class="centered">
                                                <img src="<?php echo $thumbnail_url;  ?>" draggable="false" alt="">
                                            </div>
                                        </div>
                                       <div class="filename">
                                           <div class="filetitle"><?php echo $attachment->post_title; ?></div>
                                       </div>
                                    </div>
                                   <a class="check document-check"  title="Deselect" tabindex="-1" data-document-id="<?php echo $attachment->ID; ?>"><div class="media-modal-icon"></div></a>
                                </li>
                                <?php } ?>
                             </ul>
                         </div>
                        <div class="uploader large-5 columns">
                           
                        <input class="left mybutton" type="button"  id="upload_image" value="Choose Documents"  />
                        <button class="left mybutton add-external-link" type="button" ><?php _e("Add External link"); ?></button>
                        
                        <div class="attachment-info panel left">
                              <h5><?php _e( 'Attachment Details', RT_BIZ_TEXT_DOMAIN ) ?></h5>

                        <div class="details">

                            <strong>File type: </strong><div class="filetype"> </div>
                            <strong>Uploaded on: </strong><div class="uploaded"> </div>

                        </div>
                  <div class="settings">
                               
                            <label class="setting" data-setting="url">
                                    <span class="name">URL</span>
                                    <input type="text" id="fileurl" readonly="">
                            </label>

                            <label class="setting" data-setting="title">
                                    <span class="name">Title</span>
                                    <input type="text" id="filetitle" >
                            </label>

                            <label class="setting" data-setting="caption">
                                    <span class="name">Caption</span>
                                    <textarea id="filecaption"></textarea>
                            </label>

                            <label class="setting" data-setting="description">
                                    <span class="name">Description</span>
                                    <textarea id="filedescription"></textarea>
                            </label>


                        
                    </div>

                    <div class="actions alignright" data-attachment-id="">
                        <input type="button" value="Update" id="save-attachment">
                        <input type="button" value="Delete" id="delete-attachment">

                    </div>

		</div>
    </div>
</div>

<?php }




?>