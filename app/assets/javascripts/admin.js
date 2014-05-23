jQuery(document).ready(function($) {
    $(document).foundation();
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
});



