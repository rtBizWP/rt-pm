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
	<div class="inside">
					   <div class="row collapse" id="attachment-container">
						<input type='hidden' class="right mybutton add-project-file" data-projectid="<?php echo $post_id; ?>" id="add_project_attachment">
						   <div class="scroll-height">
							   <?php 
							   $attachments = get_posts( array(
                                                'post_type' => 'attachment',
                                                'posts_per_page' => -1,
                                                'post_parent' => $post_id,
                                                'exclude'     => get_post_thumbnail_id()

                                        ) );
							   if ( $attachments ){ ?>
								   <?php foreach ($attachments as $attachment) { ?>
									   <?php $extn_array = explode('.', $attachment->guid); $extn = $extn_array[count($extn_array) - 1];
									   		if ( get_post_meta( $attachment->ID, '_wp_attached_external_file', true) != 1 ){
											   continue;		 
							   				}
										   if ( get_post_meta( $attachment->ID, '_wp_attached_external_file', true) == 1){
											   //$extn ='unknown';
										   }
									   ?>
									   
									   <div class="large-12 columns">
										<div class="large-11 columns">
											<a target="_blank" href="<?php echo wp_get_attachment_url($attachment->ID); ?>">
											   <img height="20px" width="20px" src="<?php echo RT_PM_URL . "app/assets/file-type/" . $extn . ".png"; ?>" />
											   <?php echo $attachment->post_title; ?>
										   </a>
										   <input type="hidden" name="attachment[]" value="<?php echo $attachment->ID; ?>" />
										</div>
										<div class="large-1 columns">
											<a data-attachmentid="<?php echo $attachment->ID; ?>" class="rtpm_delete_project_attachment button add-button removeMeta"><i class="fa fa-times"></i></a>
										</div>
									</div>
								   <?php } ?>
							   <?php }else{ ?>
								   <?php if (  isset($_POST['attachment_tag']) && $_POST['attachment_tag']!= -1 ){ ?>
									   <div class="large-12 mobile-large-3 columns no-attachment-item">
										   <?php $term = get_term( $_POST['attachment_tag'], 'attachment_tag' ); ?>
										   Not Found Attachment<?php echo isset( $term )? ' of ' . $term->name . ' Term!' :'!' ?>
									   </div>
								   <?php }else{ ?>
									   <div class="large-12 mobile-large-3 columns no-attachment-item">
										   <?php delete_post_meta($projectid, '_rt_wp_pm_attachment_hash'); ?>
										   Attachment Not found!
									   </div>
								   <?php } ?>
							   <?php } ?>
						   </div>
					   </div>
				   </div>
	<h3><b><?php _e('Internal'); ?></b></h3>
    <hr/>
	<div class="row">
        <div class="large-12 columns add-attachment-button">
            <a class="right add-button button"  id="upload_image" ><i class="fa fa-plus"></i></i></a>
        </div>
    </div>


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
	                                      	if ( get_post_meta( $attachment->ID, '_wp_attached_external_file', true) == 1 ){
											   continue;		 
							   				}

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
                                <div class="uploader large-5 column panel">

                                    <h5><?php _e( 'ATTACHMENT DETAILS', RT_BIZ_TEXT_DOMAIN ) ?></h5>

                                    <div class="attachment-info">

                                        <div>

                                            <div class="filetype"> <strong>File type: </strong> </div>
                                            <div class="uploaded">  <strong class="left">Uploaded on: </strong> </div>
                                            <a target="_blank">Download This</a>

                                        </div>

                                        <div class="settings">
                                            <label>
                                                <span>URL</span>
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
                                        <input type="button" value="Delete" id="delete-attachment" >

                                    </div>

		</div>
                                </div>
        </div> 


<?php }




?>