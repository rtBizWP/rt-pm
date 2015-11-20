jQuery(document).ready(function($) {
    $(document).foundation();

    //ajex loder
    var LOADER_OVERLAY = $("<div class='loading-overlay'><i class='loader-icon'></i></div>");
    $.ajaxSetup({
        beforeSend : function(jqXHR, settings) {
            if(settings.data.indexOf('heartbeat') === -1 && settings.data.indexOf('closed-postboxes') === -1 && settings.data.indexOf('meta-box-order') === -1) {
                $("body").append(LOADER_OVERLAY);
            }
        },
        complete : function(jqXHR, settings) {
            $("body").find(".loading-overlay").remove();
        }
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
                        jQuery("#divProjectMemberList").append("<li id='project-member-auth-" + ui.item.id + "' class='contact-list' >" + ui.item.imghtml + "<a class='heading' target='_blank' href='"+ui.item.user_edit_link+"'>" + ui.item.label + "</a><a href='#removeProjectMember' class='right'><i class='foundicon-remove'></i></a><input type='hidden' name='post[project_member][]' value='" + ui.item.id + "' /></li>")
                    }
                    jQuery("#project_member_user_ac").val("");
                    return false;
                }
            }).data("ui-autocomplete")._renderItem = function(ul, item) {
                return $("<li></li>").data("ui-autocomplete-item", item).append("<a class='ac-project-member-selected'>" + item.imghtml + "&nbsp;" + item.label + "</a>").appendTo(ul);
            };

            jQuery(document).on('click', "a[href=#removeProjectMember]", function(e) {
                e.preventDefault();
                $(this).parent().remove();
            });

        }
    } catch (e) {

    }

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
                        jQuery("#divAccountsList").append("<li id='project-org-auth-" + ui.item.id + "' class='contact-list' >" + ui.item.imghtml + "<a class='heading' target='_blank' href='"+ui.item.user_edit_link+"'>" + ui.item.label + "</a><a href='#removeProjectOrganization' class='right'><i class='foundicon-remove'></i></a><input type='hidden' name='post[project_organization][]' value='" + ui.item.id + "' /></li>")
                    }
                    jQuery("#project_org_search_account").val("");
                    return false;
                }
            }).data("ui-autocomplete")._renderItem = function(ul, item) {
                return $("<li></li>").data("ui-autocomplete-item", item).append("<a class='ac-project-client =-selected'>" + item.imghtml + "&nbsp;" + item.label + "</a>").appendTo(ul);
            };

            jQuery(document).on('click', "a[href=#removeProjectOrganization]", function(e) {
                e.preventDefault();
                $(this).parent().remove();
            });

        }
    } catch (e) {

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
                        jQuery("#divProjectClientList").append("<li id='project-client-auth-" + ui.item.id + "' class='contact-list' >" + ui.item.imghtml + "<a class='heading' target='_blank' href='"+ui.item.user_edit_link+"'>" + ui.item.label + "</a><a href='#removeProjectClient' class='right'><i class='foundicon-remove'></i></a><input type='hidden' name='post[project_client][]' value='" + ui.item.id + "' /></li>")
                    }
                    jQuery("#project_client_user_ac").val("");
                    return false;
                }
            }).data("ui-autocomplete")._renderItem = function(ul, item) {
                return $("<li></li>").data("ui-autocomplete-item", item).append("<a class='ac-project-client =-selected'>" + item.imghtml + "&nbsp;" + item.label + "</a>").appendTo(ul);
            };

            jQuery(document).on('click', "a[href=#removeProjectClient]", function(e) {
                e.preventDefault();
                $(this).parent().remove();
            });

        }
    } catch (e) {

    }

    //datetime picker
    if( $(".datetimepicker").length > 0 ) {
        $( document ).on( 'focus', ".datetimepicker", function() {
            $(".datetimepicker").datetimepicker({
                dateFormat: "M d, yy",
                timeFormat: "hh:mm TT",
            });
        });
    }

    $(document).on("click", ".moment-from-now", function(e) {
        var oldDate = $(this).attr("title");

        if( oldDate != "" ) {
            $(this).datepicker("setDate",new Date($(this).attr("title")));
        }
    });

    $(document).on('click', 'a.add-multiple', function(e){

        $input = $(this).parents('div.parent-row').find('input');

        var $emptyFields = $input.filter(function() {
            // remove the $.trim if whitespace is counted as filled
            return $.trim(this.value) === "";
        });

        if ( $emptyFields.length )
            return false;


        $element = $(this).parents('div.parent-row').clone();

        $element.find('a').removeClass('add-multiple').addClass('delete-multiple').find('i').removeClass('fa fa-plus').addClass('fa fa-times');


        $parent = $(this).parents('div.resources-list').append( $element );

        $input.val('');

    });

    try {
        if ('undefined' != typeof jQuery('input.search-contact')) {
            jQuery( document ).on( 'keydown.autocomplete', function() {
                jQuery("input.search-contact").autocomplete({
                    source: function (request, response) {
                        $.ajax({
                            url: ajaxurl,
                            dataType: "json",
                            type: 'post',
                            data: {
                                action: "rtbiz_search_person",
                                query: request.term,
                            },
                            success: function (response_data) {
                                if (response_data.success) {

                                    response($.map(response_data.data, function (item) {
                                        return {
                                            id: item.contact_wp_user_id,
                                            label: item.contact_display_name,
                                            imghtml: item.contact_imghtml,
                                        }
                                    }));
                                }

                            }
                        });
                    }, minLength: 2,
                    select: function (event, ui) {
                        jQuery(this).val(ui.item.label);
                        jQuery(this).siblings('input.contact-wp-user-id').val(ui.item.id);
                        return false;

                    }
                }).data("ui-autocomplete")._renderItem = function (ul, item) {
                    return $("<li></li>").data("ui-autocomplete-item", item).append("<a class='ac-subscribe-selected'>" + item.imghtml + "&nbsp;" + item.label + "</a>").appendTo(ul);
                };
            });
        }
    } catch(e) {

    }


// Remove occassion from list
    $( document ).on( 'click', "a.delete-multiple", function( e ) {
        $(this).parents('div.parent-row').remove();
    } );


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
				
				// check if estimated hours are correct
				
				//jQuery.ajax({
				//	type: 'POST',
				//	dataType: 'json',
				//	url: ajaxurl,
				//	async: false,
				//	data: {
				//		'action': 'rtpm_validate_estimated_date',
				//		'start_date': $("#create_rt_task_date").val(),
				//		'end_date': $("#due_rt_task_date").val(),
				//		'est_time' : $('input[name="post[post_estimated_hours]"]').val(),
				//		'project_id': $('#project_id').val(),
				//	},
				//	success: function (data) {
				//		if (data.fetched) {
				//
				//		} else {
				//			var hours_element = $('input[name="post[post_estimated_hours]"]');
				//			addError(hours_element, "Please Enter valid Time");
				//			e.preventDefault();
				//		}
				//	}
                //
				//});
				
                
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
        jQuery(this).parent().remove();
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
                strAttachment = '<div class="large-12 mobile-large-3 columns attachment-item" data-attachment-id="'+attachment.id+'">';
                strAttachment += '<a target="_blank" href="'+attachment.url+'"><img height="20px" width="20px" src="' +attachment.icon + '" > '+attachment.filename+'</a>';
                strAttachment += '<a href="#" class="rtpm_delete_attachment right">x</a>';
                strAttachment += '<input type="hidden" name="attachment[]" value="' + attachment.id +'" /></div>';

                jQuery("#attachment-container .scroll-height").append(strAttachment);

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
        var attachment_id = $(this).data("attachmentid");
        var r = confirm("Are you sure you want to remove this Attachment?");
        if (r != true) {
            return false;
        }
        $.ajax({
            url: ajaxurl,
            type: 'post',
            data: {
                action:'rtpm_remove_attachment',
                project_id: project_id,
                attachment_id:attachment_id
            },
            success: function (data) {
                $("#attachment-error").html('Deleted Sucessfully <a href="" class="close">&times;</a>');
                $("#attachment-error").removeClass();
                $("#attachment-error").addClass('alert-box success');
            }
        });
        jQuery(this).parent().remove();

    });


    //open model
    $(document).on("click","button.add-task",function(e){
        $("#div-add-task").reveal({
            opened: function(){
                rtpm_reset_task_form();
            }
        });
    });
    //open model
    $(document).on("click","button.add-sub-task",function(e){
        $("#div-add-task").reveal({
            opened: function() {
                rtpm_reset_task_form();
                $('.parent-task-dropdown').show();
            },
            closed: function() {
                $('.parent-task-dropdown').hide();
            }
        });
    });
    //open model
    $(document).on("click","button.add-milestone",function(e){
        $("#div-add-task").reveal({
            opened: function() {
                rtpm_reset_task_form();
                $('.hide-for-milestone').hide();
                $('input[name="post[task_type]"]').val('milestone');
                $('.parent-task-dropdown').show();
            },
            closed: function() {
                $('input[name="post[task_type]"]').val('');
                $('.hide-for-milestone').show();
                $('.parent-task-dropdown').hide();
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



});

var jq = $ = jQuery.noConflict();

if( $('#create_rt_task_date').length > 0 ) {

    $('#create_rt_task_date').datetimepicker({
        dateFormat: "M d, yy",
        timeFormat: "hh:mm TT",
        hour: 09,
        minute: 00
    });
}

if( $('#due_rt_task_date').length > 0 ) {
    $('#due_rt_task_date').datetimepicker({
        dateFormat: "M d, yy",
        timeFormat: "hh:mm TT",
        hour: 18,
        minute: 00
    });
}

function rtpm_reset_task_form() {
    jQuery('input[name="post[task_type]"]').val('');
    jQuery("input[name='post[post_title]']").val("");
    tinyMCE.activeEditor.setContent('');
    jQuery("input[name='post[post_date]']").val("");
    jQuery("input[name='post[post_duedate]']").val("");
    jQuery("select[name='post[post_author]']").val(0);
    jQuery("select[name='post[post_status]']").val();
    jQuery("select[name='post[parent_task]']").val(0);
    jQuery("#attachment-container .scroll-height").html('');
    jQuery('div.resources-list>div.parent-row:gt(0)').remove();
}


