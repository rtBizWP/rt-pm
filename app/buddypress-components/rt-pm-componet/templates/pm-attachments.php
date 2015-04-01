<?php
/**
 * Created by PhpStorm.
 * User: paresh
 * Date: 1/4/15
 * Time: 1:31 PM
 */

global $rt_pm_bp_pm;
$post_type=$_REQUEST['post_type'];
$projectid = $_REQUEST["{$post_type}_id"];

$author_cap = rt_biz_get_access_role_cap( RT_PM_TEXT_DOMAIN, 'author' );
if( current_user_can( $author_cap ) ){

    $user_edit = true;
}else {

    $user_edit = false;
}

$attachments = array();
$arg= array(
    'posts_per_page' => -1,
    'post_parent' => $projectid,
    'post_type' => 'attachment',
);
if ( isset($_REQUEST['attachment_tag']) && $_REQUEST['attachment_tag']!= -1 ){
    $arg=array_merge($arg, array('tax_query' => array(
            array(
                'taxonomy' => 'attachment_tag',
                'field' => 'term_id',
                'terms' => $_REQUEST['attachment_tag'])
        ))
    );
}

if (isset($_POST['post'])){
    $new_attachment = $_POST['post'];
    $projectid = $new_attachment["post_project_id"];
    $args = array(
        'guid' => $new_attachment["post_link"],
        'post_title' => $new_attachment["post_title"],
        'post_content' => $new_attachment["post_title"],
        'post_parent' => $projectid,
        'post_author' => get_current_user_id(),
    );
    $post_attachment_hashes = get_post_meta( $projectid, '_rt_wp_pm_external_link' );
    if ( empty( $post_attachment_hashes ) || $post_attachment_hashes != $new_attachment->post_link  ) {
        $attachment_id = wp_insert_attachment( $args, $new_attachment["post_link"], $projectid );
        add_post_meta( $projectid, '_rt_wp_pm_external_link', $new_attachment["post_link"] );
        //convert string array to int array
        //Set term to external link

        if( isset( $new_attachment["term"] ) )
            wp_set_object_terms( $attachment_id, $new_attachment["term"], 'attachment_tag');
        //Update flag for external link
        update_post_meta( $attachment_id, '_wp_attached_external_file', '1');
        /*update_post_meta($attachment_id, '_flagExternalLink', "true");*/
    }
}
delete_post_meta( $projectid, '_rt_wp_pm_external_link' );
if ( isset( $projectid ) ) {
    $attachments = get_posts($arg );
}

$form_ulr = $rt_pm_bp_pm->get_component_root_url().bp_current_action();
$form_ulr .= "?post_type={$post_type}&page=rtpm-add-{$post_type}&{$post_type}_id={$_REQUEST["{$post_type}_id"]}&tab={$post_type}-files";
?>
<h4><?php _e('Attachments'); ?></h4>
<div id="wp-custom-list-table">
    <div id="attachment-error" class="row"></div>
    <h3><b><?php _e('External'); ?></b></h3>
    <hr/>
    <div class="row">

        <div class="large-12 columns">

            <?php if( $user_edit ) {
                ?>
                <div class="row collapse" id="external-files-container">

                    <div class="small-3 columns">
                        <input type="text" id='add_ex_file_title' placeholder="Title"/>
                    </div>
                    <div class="small-8 columns">
                        <input type="text" id='add_ex_file_link' placeholder="Link"/>
                    </div>
                    <div class="small-1 columns">
                        <a class="button add-button add-external-link"  id="add_new_ex_file" ><i class="fa fa-plus"></i></a>
                    </div>
                </div>
            <?php } ?>

            <?php
            if ( isset( $post->ID ) ) {
                $post_ex_files = get_post_meta( $post->ID, 'lead_external_file' );
                $count = 1;
                foreach ( $post_ex_files as $ex_file ) {
                    $ex_file = (array)json_decode( $ex_file );
                    ?>
                    <div class="row collapse">

                        <div class="small-3 columns">
                            <?php if( $user_edit ) { ?>
                                <input type="text" name="lead_ex_files[<?php echo $count; ?>'][title]" value="<?php echo $ex_file['title']; ?>" />
                            <?php } else { ?>
                                <span><?php echo $key.': '; ?></span>
                            <?php } ?>
                        </div>
                        <?php if( $user_edit ) { ?>
                            <div class="small-8 columns">
                                <input type="text" name="lead_ex_files[<?php echo $count; ?>'][link]" value="<?php echo $ex_file['link']; ?>" />
                            </div>
                            <div class="large-1 columns">
                                <a data-attachmentid="<?php echo $attachment->ID; ?>" class="rtpm_delete_project_attachment right button add-button removeMeta"><i class="fa fa-times"></i></a>
                            </div>
                        <?php } else { ?>
                            <div class="small-9 columns">
                                <span><?php echo $pmeta; ?></span>
                            </div>
                        <?php } ?>

                    </div>
                    <?php
                    $count++;
                }
            } ?>


        </div>
    </div>

    <div id="attachment-search-row" class="row collapse postbox">
        <!--<form id ="attachment-search" method="post" action="<?php echo $form_ulr; ?>">
					   		<div class="row list-heading">
					   			<div class="large-4 columns">
                    				<?php _e('Attachments'); ?>
                				</div>
                				<div class="large-8 columns">
                    				<button class="right mybutton" type="submit" ><?php _e('Search'); ?></button>
                    				<?php
        if ( isset( $_REQUEST['attachment_tag'] ) ) {
            wp_dropdown_categories('taxonomy=attachment_tag&hide_empty=0&orderby=name&name=attachment_tag&show_option_none=Select Media tag&selected='.$_REQUEST['attachment_tag']);
        }
        ?>
                				</div>
                    		</div>
					   </form></h6>-->
        <input type='hidden' id='post-id' name='post[rt_project_id]' value=<?php echo $projectid; ?>>
        <?php pm_add_documents_section( $projectid ); ?>
        <!--<div class="inside">
					   <div class="row collapse" id="attachment-container">

						   <div class="scroll-height">
							   <?php if ( $attachments ){ ?>
								   <?php foreach ($attachments as $attachment) { ?>
									   <?php $extn_array = explode('.', $attachment->guid); $extn = $extn_array[count($extn_array) - 1];
            if ( get_post_meta( $attachment->ID, '_wp_attached_external_file', true) == 1){
                $extn ='unknown';
            }
            ?>
									   <div class="large-12 mobile-large-3 columns attachment-item" data-attachment-id="<?php echo $attachment->ID; ?>">
										   <a target="_blank" href="<?php echo wp_get_attachment_url($attachment->ID); ?>">
											   <img height="20px" width="20px" src="<?php echo RT_PM_URL . "app/assets/file-type/" . $extn . ".png"; ?>" />
											   <?php echo $attachment->post_title; ?>
										   </a>
										   <?php $taxonomies=get_attachment_taxonomies($attachment);
            $taxonomies=get_the_terms($attachment,$taxonomies[0]);
            $term_html = '';
            if ( isset( $taxonomies ) && !empty( $taxonomies ) ){?>
											   <div style="display:inline-flex;margin-left: 20px;" class="attachment-meta">[&nbsp;
												   <?php foreach( $taxonomies as $taxonomy){
                if ( !empty($term_html) ){
                    $term_html.=',&nbsp;';
                }
                $term_html .= '<a href="'.$rt_pm_bp_pm->get_component_root_url().bp_current_action() .'?post_type='.$post_type.'&'.$post_type.'_id='.$_REQUEST["{$post_type}_id"].'&tab='.$post_type.'-files&attachment_tag='.$taxonomy->term_id.'"'.'" title="'. $taxonomy->name .'" >'.$taxonomy->name.'</a>';
            }
                echo $term_html;?>&nbsp;]

											   </div>
										   <?php } ?>
										   <?php if( $user_edit ) { ?>
											   <a href="#" data-attachmentid="<?php echo $attachment->ID; ?>"  class="rtpm_delete_project_attachment right">x</a>
										   <?php } ?>
										   <input type="hidden" name="attachment[]" value="<?php echo $attachment->ID; ?>" />
										  <?php /*var_dump( get_post_meta($attachment, '_flagExternalLink') ) */?>
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
				   </div>-->
    </div>
</div>

<!--reveal-modal-add-external file -->
<div id="div-add-external-link" class="reveal-modal large">
    <fieldset>
        <legend><h4>Add External Link</h4></legend>
        <form method="post" id="form-external-link" data-posttype="projec-attachment" action="<?php echo $form_ulr; ?>">
            <input type="hidden" name="post[post_project_id]" id='project_id' value="<?php echo $_REQUEST["{$post_type}_id"]; ?>" />
            <div class="row collapse">
                <div class="large-3 small-4 columns">
                    <label>Title</label>
                </div>
                <div class="large-9 mobile-large-1 columns <?php echo ( ! $user_edit ) ? 'rtpm_attr_border' : ''; ?>">
                    <input id="add_modal_title" placeholder="Title" name="post[post_title]" type="text" value="" />
                </div>
            </div>
            <div class="row collapse">
                <div class="large-3 small-4 columns">
                    <label>External Link</label>
                </div>
                <div class="large-9 mobile-large-1 columns <?php echo ( ! $user_edit ) ? 'rtpm_attr_border' : ''; ?>">
                    <input id="add_modal_link" placeholder="External Link" name="post[post_link]" type="text" value="" />
                </div>
            </div>
            <!--
                        <div class="row collapse">
                            <div class="large-3 small-4 columns">
                                <label>Categories</label>
                            </div>
                            <div class="large-9 mobile-large-1 columns <?php echo ( ! $user_edit ) ? 'rtpm_attr_border' : ''; ?>">
                                <?php $media_terms= get_categories("taxonomy=attachment_tag&hide_empty=0&orderby=name");?>
                                <ul class="media-term-list">
                                    <?php foreach ( $media_terms as $term ){?>
                                    <li><label><input type="checkbox" name="post[term][]" value="<?php echo $term->term_id; ?>"><span><?php echo $term->name; ?></span></label></li>
                                    <?php } ?>
                                </ul>
                            </div>
                        </div>
                        -->
            <div class="row collapse">

            </div>
            <button class="mybutton right" type="submit" id="save-external-link">Save</button>
        </form>
    </fieldset>
    <a class="close-reveal-modal">×</a>
</div>