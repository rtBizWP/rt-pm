jQuery(document).ready(function($) {
	$(".deletepostlink").click(function(){
		var r = confirm("Are you sure you want to delete this?");
		if (r != true) {
			return false;
		}
		//window.location = $(this).data('href');
		//return false;
    });
	
	var custom_uploader;

	$('#upload_image').on('click',function(e) {

		e.preventDefault();

		//If the uploader object has already been created, reopen the dialog
		if (custom_uploader) {
			custom_uploader.open();
			return;
		}

		//Extend the wp.media object
		custom_uploader = wp.media.frames.file_frame = wp.media({
			title: 'Choose Documents',
			button: {
				text: 'Choose Documents'
			},
			multiple: true
		});
		//When a file is selected, grab the URL and set it as the text field's value
		custom_uploader.on('select', function() {

			attachments = custom_uploader.state().get('selection').toJSON();
			filenames = [];

			$.each(attachments,function( index, value ){
				
				var data = {
					action: "rtpm_add_new_documents",
					post_id: $("#post-id").val(),
					filename: value.url,

				}
				$.post(ajaxurl,data,function( res ) {
					 $('#attachment-document').prepend( generate_document_ui( value, res ) );
				}, 'json' );
			   
			   // filenames.push(value.url);
			});
		  
			 

		});

		//Open the uploader dialog
		custom_uploader.open();

	});
	
	$('li.document-attachment').live('click', function(){
		
		var is_checked = $(this).attr('aria-checked');
		
		if ( is_checked == 'false' ) {
			
			$("#attachment-document>li.document-attachment").attr( 'aria-checked', 'false' );
			$("#attachment-document>li.document-attachment").removeClass(' details selected');
			
			$(this).attr( 'aria-checked', 'true' );
			$(this).addClass(' details selected');
			
			var attachment_id = $(this).find('a').data('document-id') ;

			$(".actions").data( 'attachment-id', attachment_id );

			var data = {
				action: 'rtpmattachment_metadata',
				attachment_id: attachment_id
			};

			$.post(ajaxurl,data,function( res ) {
				 $('.filetype').html( res.post_mime_type );
				 $('.uploaded').html( res.post_modified );
				 $('#fileurl').val( res.guid );
				 $('#filetitle').val( res.post_title );
				 $('#filecaption').val( res.post_excerpt );
				 $('#filedescription').val( res.post_content );

			}, 'json' );

			
		}else{
			$('.filetype').html('');
			$('.uploaded').html('');
			$('#fileurl').val('');
			$('#filetitle').val('');
			$('#filecaption').val('');
			$('#filedescription').val('');
			$(this).attr( 'aria-checked', 'false' );
			$(this).removeClass(' details selected');
		}            
	 
	});
	
	$("#save-attachment").on('click', function(){
	   var attachment_id = $(".actions").data( 'attachment-id' );
	  
		
		var data = {
			action:'rtpmattachment_save_data',
			ID:attachment_id,
			post_title: $('#filetitle').val(),
			post_excerpt: $('#filecaption').val(),
			post_content :$('#filedescription').val()
		};
	 
		 $.post(ajaxurl,data,function( res ) {
			 
			 if ( res > 0) {
				 
			   $element = $("#attachment-document>li.document-attachment.details.selected");

			   $element.find('.filetitle').html( $('#filetitle').val() );
			   
			   
			  }
		
		 
		});
	});
	
	$("#delete-attachment").live('click', function(){
		
		 var attachment_id = $(".actions").data( 'attachment-id' );
		 
			var data = {
				action: "rtpm_remove_document",
				attachment_id: attachment_id,
			}
			$.post(ajaxurl,data,function( res ) {
			   $element = $("#attachment-document>li.document-attachment.details.selected");
			   $element.remove();
				 $('.filetype').html('');
				$('.uploaded').html('');
				$('#fileurl').val('');
				$('#filetitle').val('');
				$('#filecaption').val('');
				$('#filedescription').val('');
			} );
		
	});
	
   
	$('a.document-check').live('click', function(e){
		e.preventDefault();
		$element =  $(this).parent();
		 var data = {
			action: "rtpm_remove_document",
			attachment_id: $(this).data('document-id'),
		}
		$.post(ajaxurl,data,function( res ) {
		   $element.remove();
		} );
	  
	});
	
	function generate_document_ui( attachment, res ){
        
        var img_src = ( attachment.type == 'image' ) ? attachment.url : attachment.icon ;
        
        return  "<li tabindex='0' role='checkbox' aria-label='6_webp' aria-checked='false' class='attachment save-ready document-attachment'>"
                   +"<div class='attachment-preview js--select-attachment type-image subtype-png landscape'>"
                        +"<div class='thumbnail'>"
                            +"<div class='centered'>"
                                +"<img src='"+ img_src +"' draggable='false' alt=''>"
                             +"</div>"
                        +"</div>"
                        +"<div class='filename'>"
                            +"<div>"+attachment.title+"</div>"
                          +"</div>"
                   +"</div>"
                   +"<a class='check document-check'  title='Deselect' tabindex='-1' data-document-id='"+res.attachment_id+"'><div class='media-modal-icon'></div></a>"
                +"</li>";
    }
});