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
	
	
	// Export to PDF
	
	var specialElementHandlers = {
		'#editor': function (element, renderer) {
			return true;
		}
	};

	$('.export-pdf').click(function () {
		var doc = new jsPDF('p', 'pt', 'a4');
		var element = $('#item-body .rt-main-resources-container').clone();
		element.find('.rtpm-task-info-tooltip').remove();
		var left_table = $('#item-body .rt-left-container').clone();
		left_table.find('.rtpm-task-info-tooltip').remove();
		var right_table = $('#item-body .rt-right-container table').clone();
		right_table.find('.rtpm-task-info-tooltip').remove();
		var main_table = mergetwotables(left_table,right_table);
		
		doc.fromHTML(main_table.html(),10,10, {
                        //'width': 600,
                        'elementHandlers': specialElementHandlers
                    },
            function(dispose) {
                doc.save('Resources.pdf');
            });

	});

	function mergetwotables(left_table,right_table){
		var thead = right_table.find('thead th');
		var tbody = right_table.find('tbody tr');
		var tfoot = right_table.find('tfoot tr');
		var left_table_head = left_table.find('thead tr');
		for(var i=0;i<thead.length;i++){
			left_table_head.append(thead[i]);
		}
		for(var i=0;i<tbody.length;i++){
			var tr_element = tbody[i];
			var td_element = tr_element.innerHTML;
			left_table.find('tbody tr:nth-child('+ (i+1) +')').append(td_element);
		}
		for(var i=0;i<tfoot.length;i++){
			var tfoot_element = tfoot[i];
			var tfoot_text = tfoot_element.innerHTML;
			left_table.find('tfoot tr').append(tfoot_text);
		}
		return left_table;
	}
	
	// Export to CSV
	
			function exportTableToCSV($table_1, $table_2, filename) {

			var headers_1 = $table_1.find('thead td');
			var headers_2 = $table_2.find('thead td');
			var csv = '"';
			for( var i=0; i<headers_1.length;i++ ){
				var text = headers_1[i].innerText;
				var csv = csv + text + '","';
			}
			for( var i=0; i<headers_2.length;i++ ){
				var text = headers_2[i].innerText;
				var csv = csv + text + '","';
			}
			var csv = csv + '"\r\n"';

			var rows_1 = $table_1.find('tbody tr');
			var rows_2 = $table_2.find('tbody tr');

			for( var i=0; i<rows_1.length;i++ ){

				var td_1 = rows_1[i].getElementsByTagName('td');
				var text_1 = td_1[0].innerText;
				var csv = csv + text_1 + '","';
				var td_2 = rows_2[i].getElementsByTagName('td');
				for( var j=0; j<td_2.length;j++ ){
				var text_2 = td_2[j].innerText;
				var csv = csv + text_2 + '","';

					}
				var csv = csv + '"\r\n"';
			}
			
			var footer_1 = $table_1.find('tfoot tr');
			var footer_2 = $table_2.find('tfoot tr');



			for( var i=0; i<footer_1.length;i++ ){

				var td_1 = footer_1[i].getElementsByTagName('td');
				var text_1 = td_1[0].innerText;
				var csv = csv + text_1 + '","';
				var td_2 = footer_2[i].getElementsByTagName('td');
				for( var j=0; j<td_2.length;j++ ){
				var text_2 = td_2[j].innerText;
				var csv = csv + text_2 + '","';

					}
				var csv = csv + '"\r\n"';
			}
			
			var csv = csv + '"';
					
				// Data URI
                var csvData = 'data:application/csv;charset=utf-8,' + encodeURIComponent(csv);

                $(this)
                    .attr({
                    'download': filename,
					'href': csvData
                    //,'target' : '_blank' //if you want it to open in a new window
                });

            }
			
			jQuery('.export-csv').click(function (event) {
                 
                exportTableToCSV.apply(this, [$('.rt-left-container table'), $('.rt-right-container table'), 'Resource.csv']);
                
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
		var calender = jQuery(this).data("calender");
		var projectid = jQuery(this).data("project");
		jQuery.ajax({
            type: 'POST',
            dataType: 'json',
            url: ajaxurl,
            data: {
                'action': 'rtpm_get_resources_calender',
                'date': date,
                'flag': flag,
				'calender' : calender,
				'project_id' : projectid,
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
        if (typeof arr_project_organization !== 'undefined' ) {
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
        if ( typeof arr_project_client_user !== 'undefined' ) {
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
				var img_src = rt_biz_url + 'app/assets/file-type/' + attachment.url.split('.').pop() + '.png'; 
				strAttachment = '<tr class="large-12 mobile-large-3 attachment-item" data-attachment-id="'+attachment.id+'">';
				strAttachment += '<td scope="column"><img height="20px" width="20px" src="' +img_src + '" /></td>';
                strAttachment += '<td scope="column"><a target="_blank" href="'+attachment.url+'"> '+attachment.filename+'</a></td>';
				//strAttachment += '<td scope="column"><span>&nbsp;&nbsp;&nbsp;</span></td>';
                strAttachment += '<td scope="column"><a href="#" class="rtpm_delete_attachment right">x</a>';
                strAttachment += '<input type="hidden" name="attachment[]" value="' + attachment.id +'" /></td></tr>';

                jQuery("#attachment-container .scroll-height table tbody").append(strAttachment);

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
                                if( response_data.success ) {

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
                        jQuery( this ).val( ui.item.label );
                        jQuery( this ).siblings('input.contact-wp-user-id').val( ui.item.id );
                        return false;

                    }
                }).data("ui-autocomplete")._renderItem = function (ul, item) {
                    return $("<li></li>").data("ui-autocomplete-item", item).append("<a class='ac-subscribe-selected'>" + item.imghtml + "&nbsp;" + item.label + "</a>").appendTo(ul);
                };
            });
        }
    } catch(e) {

    }


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

    if( typeof readmore == 'function') {

        $('span.rtpm_readmore').readmore({
            speed: 75,
            maxHeight: 20,
            sectionCSS: 'display: inline-block;'
        });
    }

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


    // Add multiple occasion button
    $( document ).on('click', 'a.add-multiple', function( e ) {

        $main_div =  $(this).parents('div.rt-row');

        $input = $main_div.find('input');

       // console.log( $(this).parents('div.collapse').html() );
        var $emptyFields = $input.filter(function() {
            // remove the $.trim if whitespace is counted as filled
            return $.trim(this.value) === "";
        });

        if ( $emptyFields.length )
            return false;

        $element = $main_div.clone();

        $element.find('a').removeClass('add-multiple').addClass('delete-multiple').find('i').removeClass('fa fa-plus').addClass('fa fa-times');

        $parent = $(this).parents('div.rt-parent-row').append( $element );

        $input.val('');
    });

    // Remove occassion from list
    $( document ).on( 'click', 'a.delete-multiple',function( e ) {
        $(this).parents('div.rt-row').remove();
    });

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

var jq = $ = jQuery.noConflict();

//datetime picker
if( $(".datetimepicker").length > 0 ) {
    $( document ).on( 'focus', ".datetimepicker", function() {
        $( this ).datetimepicker( {
            dateFormat: "M d, yy",
            timeFormat: "hh:mm TT"
        } );
    } );
}

if( $(".datepicker").length > 0 ) {

    $( document ).on( 'focus', ".datepicker", function() {
        $( this ).datepicker( {
            'dateFormat': 'dd/mm/yy'
        } );
    } );
}

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
    jQuery("textarea[name='post[post_content]']").val("");
    jQuery("input[name='post[post_date]']").val("");
    jQuery("input[name='post[post_duedate]']").val("");
    jQuery("select[name='post[post_author]']").val(0);
    jQuery("select[name='post[post_status]']").val();
    jQuery("select[name='post[parent_task]']").val(0);
    jQuery("#attachment-container .scroll-height table tbody").html('');
    jQuery('div.resources-list>div.parent-row:gt(0)').remove();
}