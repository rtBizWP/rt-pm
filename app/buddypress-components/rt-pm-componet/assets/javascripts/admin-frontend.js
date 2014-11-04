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
                        jQuery("#divProjectMemberList").append("<li id='project-member-auth-" + ui.item.id + "' class='contact-list' ><div class='row'><div class='large-2 column'>" + ui.item.imghtml + "</div><div class='large-9 columns'><a class='heading' target='_blank' href='"+ui.item.user_edit_link+"'>" + ui.item.label + "</a></div><div class='large-1 columns'><a href='#removeProjectMember' class='right'><i class='foundicon-remove'></i></a><input type='hidden' name='post[project_member][]' value='" + ui.item.id + "' /></div></div></li>")
                    }
                    jQuery("#project_member_user_ac").val("");
                    return false;
                }
            }).data("ui-autocomplete")._renderItem = function(ul, item) {
                return $("<li></li>").data("ui-autocomplete-item", item).append("<a class='ac-project-member-selected'>" + item.imghtml + "&nbsp;" + item.label + "</a>").appendTo(ul);
            };

            jQuery(document).on('click', "a[href=#removeProjectMember]", function(e) {
                e.preventDefault();
                $(this).parent().parent().remove();
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
                        jQuery("#divAccountsList").append("<li id='project-org-auth-" + ui.item.id + "' class='contact-list pull-1' >" + ui.item.imghtml + "<a class='heading' target='_blank' href='"+ui.item.user_edit_link+"'>" + ui.item.label + "</a><a href='#removeProjectOrganization' class='right'><i class='foundicon-remove'></i></a><input type='hidden' name='post[project_organization][]' value='" + ui.item.id + "' /></li>")
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
                        jQuery("#divProjectClientList").append("<li id='project-client-auth-" + ui.item.id + "' class='contact-list' ><div class='row'><div class='large-2 column'>" + ui.item.imghtml + "</div><div class='large-9 columns'><a class='heading' target='_blank' href='"+ui.item.user_edit_link+"'>" + ui.item.label + "</a></div><div class='large-1 columns'><a href='#removeProjectClient' class='right'><i class='foundicon-remove'></i></a><input type='hidden' name='post[project_client][]' value='" + ui.item.id + "' /></div></div></li>")
                    }
                    jQuery("#project_client_user_ac").val("");
                    return false;
                }
            }).data("ui-autocomplete")._renderItem = function(ul, item) {
                return $("<li></li>").data("ui-autocomplete-item", item).append("<a class='ac-project-client =-selected'>" + item.imghtml + "&nbsp;" + item.label + "</a>").appendTo(ul);
            };

            jQuery(document).on('click', "a[href=#removeProjectClient]", function(e) {
                e.preventDefault();
                $(this).parent().parent().remove();
            });

        }
    } catch (e) {

    }

    //datetime picker
    if( $(".datetimepicker").length > 0 ) {
        $(".datetimepicker").datetimepicker({
            dateFormat: "M d, yy",
            timeFormat: "hh:mm TT",
            onClose: function(newDate,inst) {

                if( $(this).hasClass("moment-from-now") ) {
                    var oldDate = $(this).attr("title");

                    if( newDate != "" && moment(newDate).isValid() ) {
                        $(this).val(moment(new Date(newDate)).fromNow());
                        $(this).attr("title",newDate);

                        if( $(this).next().length > 0 ) {
                            $(this).next().val(newDate);
                        }
                    } else if( oldDate != "" ) {
                        $(this).val(moment(new Date(oldDate)).fromNow());
                        $(this).attr("title",oldDate);

                        if( $(this).next().length > 0 ) {
                            $(this).next().val(newDate);
                        }
                    }
                }
            }
        });
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

    $(".moment-from-now").each(function() {
        if($(this).is("input[type='text']") && $(this).val()!="")
            $(this).val(moment(new Date($(this).attr("title"))).fromNow());
        else if($(this).is(".comment-date"))
            $(this).html(moment(new Date($(this).attr("title"))).fromNow());
        else
            $(this).html(moment(new Date($(this).html())).fromNow());
    });

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
    
    });



