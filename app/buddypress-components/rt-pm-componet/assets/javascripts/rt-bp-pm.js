jQuery(document).ready(function($) {

    Foundation.global.namespace = '';

    $(document).foundation();

    exf_count = 12345;
    $("#add_new_ex_file").click(function(e){
        var title = $("#add_ex_file_title").val();
        var link = $("#add_ex_file_link").val();
        if($.trim(link)=="")
            return false;
        $("#add_ex_file_title").val("");
        $("#add_ex_file_link").val("");

        var tmpstr=' <div class="large-12 columns" >';
        tmpstr +='<div class="large-3 columns">';
        tmpstr +='<input type="text" name="project_ex_files[' + exf_count +'][title]" value="' + title +'" />';
        tmpstr +='</div><div class="large-8 columns">';
        tmpstr +='<input type="text" name="project_ex_files[' + exf_count +'][link]" value="' + link +'" />';
        tmpstr +='</div><div class="large-1 columns">';
        tmpstr +='<a class="button add-button removeMeta"><i class="fa fa-times"></i></a>';
        tmpstr +='</div></div>';
        exf_count++;
        $("#external-files-container").append(tmpstr);
    });



    //autocomplete project member
    try {
        if (arr_project_member_user != undefined) {
            jQuery("#project_member_user_ac").autocomplete({
                source: function (request, response) {
                    var term = $.ui.autocomplete.escapeRegex(request.term)
                        , startsWithMatcher = new RegExp("^" + term, "i")
                        , startsWith = $.grep(arr_project_member_user, function (value) {
                            return startsWithMatcher.test(value.label || value.value || value);
                        })
                        , containsMatcher = new RegExp(term, "i")
                        , contains = $.grep(arr_project_member_user, function (value) {
                            return $.inArray(value, startsWith) < 0 &&
                                containsMatcher.test(value.label || value.value || value);
                        });

                    response(startsWith.concat(contains));
                },
                focus: function(event, ui) {

                },
                select: function(event, ui) {
                    if (jQuery("#project-member-auth-" + ui.item.id).length < 1) {
                        jQuery("#divProjectMemberList").append("<li id='project-member-auth-" + ui.item.id + "' class='contact-list' ><div class='row'><div class='small-2 column'>" + ui.item.imghtml + "</div><div class='small-9 columns vertical-center'><a target='_blank' href='"+ui.item.user_edit_link+"'>" + ui.item.label + "</a></div><div class='small-1 columns vertical-center'><a href='#removeProjectMember' class='right'><i class='foundicon-remove'></i></a><input type='hidden' name='post[project_member][]' value='" + ui.item.id + "' /></div></div></li>")
                    }
                    jQuery("#project_member_user_ac").val("");
                    return false;
                }
            }).data("ui-autocomplete")._renderItem = function(ul, item) {
                return $("<li></li>").data("ui-autocomplete-item", item).append("<a class='ac-project-member-selected'>" + item.imghtml + "&nbsp;" + item.label + "</a>").appendTo(ul);
            };

            jQuery(document).on('click', "a[href=#removeProjectMember]", function(e) {
                e.preventDefault();
                $(this).parent().parent().parent().remove();
            });

        }
    } catch (e) {

    }
	
	// get previous or next calender on resources page
	
	jQuery('.rtpm-get-calender').click( function(){
		var date = jQuery(this).data("date");
		var flag = jQuery(this).data("flag");
		jQuery.ajax({
            type: 'POST',
            dataType: 'json',
            url: ajaxurl,
            data: {
                'action': 'rtpm_get_resources_calender',
                'date': date,
                'flag': flag,
            },
            success: function (data) {
                if (data.fetched) {
                   jQuery( "#rtpm-resources-calender" ).empty();
				   jQuery( "#rtpm-resources-calender" ).append( data.html );
				   jQuery('#rtpm-get-next-calender').data("date",data.nextdate);
				   jQuery('#rtpm-get-prev-calender').data("date",data.prevdate);
				   loadhoverelement();
                } else {

                }
            }

        });
		
	});
	
	// show resources tooltip
	loadhoverelement();
	function loadhoverelement(){
		jQuery( ".rtpm-show-tooltip" ).hover(
				function( e ) {
				var parentoffset = jQuery( '.rt-right-container' ).offset();
				if( (e.pageX+250) > (jQuery(window).width()) )
				{
					jQuery( this ).parent().find( '.rtpm-task-info-tooltip' ).css( 'display', 'block' ).css( 'top', e.pageY-parentoffset.top).css( 'left', e.pageX-parentoffset.left-200);
				}else{
					jQuery( this ).parent().find( '.rtpm-task-info-tooltip' ).css( 'display', 'block' ).css( 'top', e.pageY-parentoffset.top).css( 'left', e.pageX-parentoffset.left);
				}
				}, function() {
					//var tooltip = jQuery( this ).parent().find( '.rtpm-task-info-tooltip' );
				  jQuery( this ).parent().find( '.rtpm-task-info-tooltip' ).css( 'display', 'none' );
				}
			);
	}
	
	jQuery(".rtpm-show-user-tooltip").hover(
				function( e ) {
				var parentoffset = jQuery( '.rt-left-container' ).offset();
				jQuery('#item-body').css('overflow','visible');
				jQuery( this ).parent().find( '.rtpm-task-info-tooltip' ).css( 'display', 'block' ).css( 'top', e.pageY-parentoffset.top).css( 'left', e.pageX-parentoffset.left);
				}, function() {
					//var tooltip = jQuery( this ).parent().find( '.rtpm-task-info-tooltip' );
					if( jQuery(window).width( ) < 680 ){
						jQuery('#item-body').css('overflow','hidden');
				}
				  jQuery( this ).parent().find( '.rtpm-task-info-tooltip' ).css( 'display', 'none' );
				}
			);
	
    //autocomplete project organization
    try {
        if (arr_project_organization != undefined) {
            jQuery("#project_org_search_account").autocomplete({
                source: function (request, response) {
                    var term = $.ui.autocomplete.escapeRegex(request.term)
                        , startsWithMatcher = new RegExp("^" + term, "i")
                        , startsWith = $.grep(arr_project_organization, function (value) {
                            return startsWithMatcher.test(value.label || value.value || value);
                        })
                        , containsMatcher = new RegExp(term, "i")
                        , contains = $.grep(arr_project_organization, function (value) {
                            return $.inArray(value, startsWith) < 0 &&
                                containsMatcher.test(value.label || value.value || value);
                        });

                    response(startsWith.concat(contains));
                },
                focus: function(event, ui) {

                },
                select: function(event, ui) {
                    if (jQuery("#project-org-auth-" + ui.item.id).length < 1) {
                        jQuery("#divProjectAccountsList").append("<li id='project-org-auth-" + ui.item.id + "' class='contact-list' ><div class='row'><div class='column small-2'>" + ui.item.imghtml + "</div><div class='column small-9 vertical-center'><a target='_blank' href='"+ui.item.user_edit_link+"'>" + ui.item.label + "</a></div><div class='column small-1 vertical-center'><a href='#removeProjectOrganization' class='right'><i class='foundicon-remove'></i></a><input type='hidden' name='post[project_organization][]' value='" + ui.item.id + "' /></div></div></li>")
                    }
                    jQuery("#project_org_search_account").val("");
                    return false;
                }
            }).data("ui-autocomplete")._renderItem = function(ul, item) {
                return $("<li></li>").data("ui-autocomplete-item", item).append("<a class='ac-project-client =-selected'>" + item.imghtml + "&nbsp;" + item.label + "</a>").appendTo(ul);
            };

            jQuery(document).on('click', "a[href=#removeProjectOrganization]", function(e) {
                e.preventDefault();
                $(this).parent().parent().parent().remove();
            });

        }
    } catch (e) {

        console.log( e );

    }
    //autocomplete project client
    try {
        if (arr_project_client_user != undefined) {
            jQuery("#project_client_user_ac").autocomplete({
                source: function (request, response) {
                    var term = $.ui.autocomplete.escapeRegex(request.term)
                        , startsWithMatcher = new RegExp("^" + term, "i")
                        , startsWith = $.grep(arr_project_client_user, function (value) {
							if ( $("input[name*='post\[project_organization\]']").length > 0 ) {
								org = new Array();
								$.each( $("input[name*='post\[project_organization\]']"), function(i,item) {
									org.push( parseInt( $(item).val() ) );
								} );
								flag = false;
								for(i=0;i<org.length;i++) {
									if(!$.inArray(org[i],value.organization)) {
										flag = true;
									}
								}
								if(!flag) {
									return false;
								}
							}
                            return startsWithMatcher.test(value.label || value.value || value);
                        })
                        , containsMatcher = new RegExp(term, "i")
                        , contains = $.grep(arr_project_client_user, function (value) {
							if ( $("input[name*='post\[project_organization\]']").length > 0 ) {
								org = new Array();
								$.each( $("input[name*='post\[project_organization\]']"), function(i,item) {
									org.push( parseInt( $(item).val() ) );
								} );
								flag = false;
								for(i=0;i<org.length;i++) {
									if(!$.inArray(org[i],value.organization)) {
										flag = true;
									}
								}
								if(!flag) {
									return false;
								}
							}
							return $.inArray(value, startsWith) < 0 &&
                                containsMatcher.test(value.label || value.value || value);
                        });

                    response(startsWith.concat(contains));
                },
                focus: function(event, ui) {

                },
                select: function(event, ui) {
                    if (jQuery("#project-client-auth-" + ui.item.id).length < 1) {
                        jQuery("#divProjectClientList").append("<li id='project-client-auth-" + ui.item.id + "' class='contact-list' ><div class='row'><div class='small-2 column'>" + ui.item.imghtml + "</div><div class='small-9 columns vertical-center'><a target='_blank' href='"+ui.item.user_edit_link+"'>" + ui.item.label + "</a></div><div class='small-1 columns vertical-center'><a href='#removeProjectClient' class='right'><i class='foundicon-remove'></i></a><input type='hidden' name='post[project_client][]' value='" + ui.item.id + "' /></div></div></li>")
                    }
                    jQuery("#project_client_user_ac").val("");
                    return false;
                }
            }).data("ui-autocomplete")._renderItem = function(ul, item) {
                return $("<li></li>").data("ui-autocomplete-item", item).append("<a class='ac-project-client =-selected'>" + item.imghtml + "&nbsp;" + item.label + "</a>").appendTo(ul);
            };

            jQuery(document).on('click', "a[href=#removeProjectClient]", function(e) {
                e.preventDefault();
                $(this).parent().parent().parent().remove();
            });

        }
    } catch (e) {

    }

    //datetime picker
    if( $(".datetimepicker").length > 0 ) {
        $(".datetimepicker").datetimepicker({
            dateFormat: "M d, yy",
            timeFormat: "hh:mm TT",
            //onClose: function(newDate,inst) {
            //
            //    if( $(this).hasClass("moment-from-now") ) {
            //        var oldDate = $(this).attr("title");
            //
            //        if( newDate != "" && moment(newDate).isValid() ) {
            //            $(this).val(moment(new Date(newDate)).fromNow());
            //            $(this).attr("title",newDate);
            //
            //            if( $(this).next().length > 0 ) {
            //                $(this).next().val(newDate);
            //            }
            //        } else if( oldDate != "" ) {
            //            $(this).val(moment(new Date(oldDate)).fromNow());
            //            $(this).attr("title",oldDate);
            //
            //            if( $(this).next().length > 0 ) {
            //                $(this).next().val(newDate);
            //            }
            //        }
            //    }
            //}
        });
    }

    if( $(".datepicker").length > 0 ) {

        $( document ).on( 'focus', ".datepicker", function() {
            $( this ).datepicker( {
                'dateFormat': 'dd/mm/yy'
            } );
        } );
    }


    $(document).on("click", ".moment-from-now", function(e) {
        var oldDate = $(this).attr("title");

        if( oldDate != "" ) {
            $(this).datepicker("setDate",new Date($(this).attr("title")));
        }
    });
    
    $(".datepicker-toggle").click(function(e) {
        $(this).parent("div").prev().find(".hasDatepicker").click();
        $(this).parent("div").prev().find(".hasDatepicker").datepicker("show");
    });

    //$(".moment-from-now").each(function() {
    //    if($(this).is("input[type='text']") && $(this).val()!="")
    //        $(this).val(moment(new Date($(this).attr("title"))).fromNow());
    //    else if($(this).is(".comment-date"))
    //        $(this).html(moment(new Date($(this).attr("title"))).fromNow());
    //    else
    //        $(this).html(moment(new Date($(this).html())).fromNow());
    //});

	$("#button-trash").click(function(){
		var r = confirm("Are you sure you want to move this project to trash?");
		if (r != true) {
			return false;
		}
		window.location = $(this).data('href');
		return false;
    });

	$('a.close').on('click', function(e) { e.preventDefault(); $(this).parent().remove(); });

    $("#form-add-post").submit(function(e) {
        try {
            var eleAccountName = $("#new_" + $(this).data("posttype") + "_title");
            if ($(eleAccountName).val().trim() == "") {
                addError(eleAccountName, "Please Enter the Title");
                return false;
            }
            
            var eleCreateDate = $("#create_" + $(this).data("posttype") + "_date");
            
            if ($(this).data("posttype")=="rt_task") {   
                
                var eleDueDate = $("#due_" + $(this).data("posttype") + "_date");   
                
                if ( $(eleDueDate).datepicker( "getDate" ) <= $(eleCreateDate).datepicker( "getDate" ) ) {
                     addError(eleDueDate, "Please Enter valid Date");
                     return false;
                }
                
                removeError(eleDueDate);
            }else if ($(this).data("posttype")=="rt_time_entry") {
                
                var curDate = new Date();
                
                if ( curDate < $(eleCreateDate).datepicker( "getDate" ) ) {
                     addError(eleCreateDate, "Please Enter valid Date");
                     return false;
                }
                
                removeError(eleCreateDate);
                
            }
            
            removeError(eleAccountName);
           
        } catch (e) {
            console.log(e);
            return false;
        }
    });

    function addError(element, message) {
        $(element).addClass("error");
        if ($(element).next().length > 0) {
            if ($(element).next().hasClass("error")) {
                $(element).next().html(message);
            } else {
                $(element).after("<small class='error'>" + message + "</small>");
            }
        } else {
            $(element).after("<small class='error'>" + message + "</small>");
        }
    }
    function removeError(element) {
        $(element).removeClass("error");
        if ($(element).next().length > 0) {
            if ($(element).next().hasClass("error")) {
                $(element).next().remove();
            }
        }
    }

    jQuery(document).on('click', '.rtpm_delete_attachment',function(e) {
        e.preventDefault();
        jQuery(this).parents('tr').remove();
    });

    var file_frame_task
    jQuery('#add_pm_attachment').on('click', function(e) {
        e.preventDefault();
        if (file_frame_task) {
            file_frame_task.open();
            return;
        }
        file_frame_task = wp.media.frames.file_frame = wp.media({
            title: jQuery(this).data('uploader_title'),
            searchable: true,
            button: {
                text: 'Attach Selected Files'
            },
            multiple: true // Set to true to allow multiple files to be selected
        });
        file_frame_task.on('select', function() {
            var selection = file_frame_task.state().get('selection');
            var strAttachment = '';
            selection.map(function(attachment) {
                attachment = attachment.toJSON();
                strAttachment = '<tr class="large-12 mobile-large-3 attachment-item" data-attachment-id="'+attachment.id+'">';
				strAttachment += '<td scope="column"><img height="20px" width="20px" src="' +attachment.icon + '"'+attachment.filename+'" /></td>';
                strAttachment += '<td scope="column"><a target="_blank" href="'+attachment.url+'"> '+attachment.filename+'</a></td>';
				strAttachment += '<td scope="column"><span>&nbsp;&nbsp;&nbsp;</span></td>';
                strAttachment += '<td scope="column"><a href="#" class="rtpm_delete_attachment right">x</a>';
                strAttachment += '<input type="hidden" name="attachment[]" value="' + attachment.id +'" /></td></tr>';

                jQuery("#attachment-container .scroll-height table").append(strAttachment);

                // Do something with attachment.id and/or attachment.url here
            });
            // Do something with attachment.id and/or attachment.url here
        });
        file_frame_task.open();
    });

    jQuery('#add_project_attachment').on('click', function(e) {
        e.preventDefault();
        var project_id = $(this).data("projectid");
        if (file_frame_task) {
            file_frame_task.open();
            return;
        }
        file_frame_task = wp.media.frames.file_frame = wp.media({
            title: jQuery(this).data('uploader_title'),
            searchable: true,
            button: {
                text: 'Attach Selected Files'
            },
            multiple: true // Set to true to allow multiple files to be selected
        });
        file_frame_task.on('select', function() {
            var selection = file_frame_task.state().get('selection');
            var strAttachment = '';
            selection.map(function(attachment) {
                attachment = attachment.toJSON();
                strAttachment = '<div class="large-12 mobile-large-3 columns attachment-item" data-attachment-id="'+attachment.id+'">';
                strAttachment += '<a target="_blank" href="'+attachment.url+'"><img height="20px" width="20px" src="' +attachment.icon + '" > '+attachment.filename+'</a>';
                strAttachment += '<a href="#" data-attachmentid="' + attachment.id +'" class="rtpm_delete_project_attachment right">x</a>';
                strAttachment += '<input type="hidden" name="attachment[]" value="' + attachment.id +'" /></div>';

                jQuery("#attachment-container .scroll-height").append(strAttachment);
                $.ajax({
                    url: ajaxurl,
                    dataType: "json",
                    type: 'post',
                    data: {
                        action:'rtpm_add_attachement',
                        project_id: project_id,
                        attachment_id:attachment.id
                    },
                    success: function (data) {

                    }
                });
                // Do something with attachment.id and/or attachment.url here
            });
            // Do something with attachment.id and/or attachment.url here
        });
        file_frame_task.open();
    });

    jQuery(document).on('click', '.rtpm_delete_project_attachment',function(e) {
        e.preventDefault();
        var project_id = $("#add_project_attachment").data("projectid");

        if( typeof project_id == "undefined") {

            project_id = $("#project_id").val();
        }
        var attachment_id = $(this).data("attachmentid");

            $.ajax({
                url: ajaxurl,
                type: 'post',
                data: {
                    action:'rtpm_remove_attachment',
                    project_id: project_id,
                    attachment_id:attachment_id
                },
                success: function (data) {
                    //$("#attachment-error").html('Deleted Sucessfully <a href="" class="close">&times;</a>');
                    $("#attachment-error").removeClass();
                    //$("#attachment-error").addClass('alert-box success');
                }
            });
            jQuery(this).parent().parent().remove();
    });


    //open model
    $(document).on("click",".add-task",function(e){
        $("#div-add-task").reveal({
            opened: function(){
                /*$("input[name='post[post_title]']").val("");
                $("textarea[name='post[post_content]']").text("");
                $("input[name='post[post_date]']").val("");
                $("input[name='post[post_duedate]']").val("");
                $("select[name='post[post_author]']").val(0);
                $("select[name='post[post_status]']").val();*/
            }
        });
    });
    $(document).on("click",".add-time-entry",function(e){
        $("#div-add-time-entry").reveal({
            opened: function(){

            }
        });
    });
    $(document).on("click",".add-external-link",function(e){
        $("#div-add-external-link").reveal({
            opened: function(){

            }
        });
    });
	
	/** New Js for frontend **/
	$(".deletepostlink").click(function(){
		var r = confirm("Are you sure you want to delete this?");
		if (r != true) {
			return false;
		}
    });
	
	$('#rtpm_post_status').on('change', function(e) {
		if($(this).val() == 'closed') {
			$('#rtpm_closing_reason_wrapper').show();
		} else {
			$('#rtpm_closing_reason_wrapper').hide();
		}
	});
	
	$('span.rtpm_readmore').readmore({
	  speed: 75,
	  maxHeight: 20,
	  sectionCSS: 'display: inline-block;'
	});
	
	$("#top-button-trash").click(function(){
		var r = confirm("Are you sure you want to move this project to trash?");
		if (r != true) {
			return false;
		}
		window.location = $(this).data('href');
		return false;
    });
	
	$("#button-archive").click(function(){
		var r = confirm("Are you sure you want to move this project to archive?");
		if (r != true) {
			return false;
		}
		window.location = $(this).data('href');
		return false;
    });
	
	$("#button-unarchive").click(function(){
		var r = confirm("Are you sure you want to move this project to unarchive?");
		if (r != true) {
			return false;
		}
		window.location = $(this).data('href');
		return false;
    });
	
	$("#top-button-archive").click(function(){
		var r = confirm("Are you sure you want to move this project to archive?");
		if (r != true) {
			return false;
		}
		window.location = $(this).data('href');
		return false;
    });
	
	$("#top-button-unarchive").click(function(){
		var r = confirm("Are you sure you want to move this project to unarchive?");
		if (r != true) {
			return false;
		}
		window.location = $(this).data('href');
		return false;
    });
	
	$(".add-task").click(function(){
		$( ".add-task" ).text("Update Task");
    });
	
	$(".add-task").click(function(){
		$( ".add-task" ).text( "Update Task" );
    });
	
	$(".add-time-entry").click(function(){
		$( ".add-time-entry" ).text( "Update Time Entry" );
    });
	
	$(document).on('click',".removeMeta",function(){
        $(this).parent().parent().remove();
    });
	$( "#add_ex_file_title" ).change(function() {
		var title = $("#add_ex_file_title").val();
        $('#add_modal_title').val(title);
	});
	$( "#add_ex_file_link" ).change(function() {
		var link = $("#add_ex_file_link").val();
        $('#add_modal_link').val(link);
	});
	
	// Attachment section JS start
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
                    attachment_id: value.id,
                    action: "rtbiz_add_new_documents",
                    post_id: $("#project_id").val(),
                    filename: value.url,

                }
				console.log(data);
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
                action: 'rtattachment_metadata',
                attachment_id: attachment_id
            };

            $.post(ajaxurl,data,function( res ) {
                $('.filetype').html( '<strong>File type: </strong> ' + res.post_mime_type );
                $('.uploaded').html( ' <strong>Uploaded on: </strong>' + res.post_modified );
                $('#fileurl').val( res.guid );
                $('#filetitle').val( res.post_title );
                $('#filecaption').val( res.post_excerpt );
                $('#filedescription').val( res.post_content );
                $('.details>a').attr( 'href', res.guid );
            }, 'json' );


        }else{
            $('.filetype').html('');
            $('.uploaded').html('');
            $('#fileurl').val('');
            $('#filetitle').val('');
            $('#filecaption').val('');
            $('#filedescription').val('');
            $('.details>a').attr( 'href', '#' );
            $(this).attr( 'aria-checked', 'false' );
            $(this).removeClass(' details selected');
        }

    });

    $("#save-attachment").on('click', function(){
        var attachment_id = $(".actions").data( 'attachment-id' );


        var data = {
            action:'rtattachment_save_data',
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
            action: "rtbiz_remove_document",
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
            $('.details>a').attr( 'href', '#' );
        } );

    });


    $('a.document-check').live('click', function(e){
        e.preventDefault();
        $element =  $(this).parent();
        var data = {
            action: "rtbiz_remove_document",
            attachment_id: $(this).data('document-id'),
        }
        $.post(ajaxurl,data,function( res ) {
            $element.remove();
        } );

    });

    // Add multiple occasion button
    $('a.add-multiple').click(function(){

        $input = $(this).parents('div.collapse').find('input');

        var $emptyFields = $input.filter(function() {
            // remove the $.trim if whitespace is counted as filled
            return $.trim(this.value) === "";
        });

        if ( $emptyFields.length )
            return false;


        $element = $(this).parents('div.collapse').clone();

        $element.find('a').removeClass('add-multiple').addClass('delete-multiple').find('i').removeClass('fa fa-plus').addClass('fa fa-times');


        $parent = $(this).parents('div.main').append( $element );

        $input.val('');

    });

    // Remove occassion from list
    $( document ).on( 'click', "a.delete-multiple", function( e ) {
        $(this).parents('.collapse').remove();
    } );

    // Attachment section js end
	
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



