jQuery(document).ready(function($) {

	$("#front-button-trash").click(function(){
		var r = confirm("Are you sure you want to move this project to trash???");
		if (r != true) {
			return false;
		}
		window.location = $(this).data('href');
		return false;
    });
	var rtHRMFrontend = {
		/**
		 *
		 */
		init : function(){
            //rtHRMFrontend.leaveListing();
			//rtHRMFrontend.requestsListing();
		},
        leaveListing : function(){
			var paged = 1;
			var order = "";
			var attr = "";
			var max_num_pages = 999999;
			if ( 1 == paged ){
				$( "#leave-pagination li#prev" ).hide();
			} else {
				$( "#leave-pagination li#prev" ).show();
			}
			if ( max_num_pages == paged ){
				$( "#leave-pagination li#next" ).hide();
			} else {
				$( "#leave-pagination li#next" ).show();
			}
			
			$( ".leave-lists .lists-header th.order" ).click(function() {
				order = $(this).data("sorting-type");
				attr =  $(this).data("attr-type");
				
				if ( order === "DESC" ) {
					$(this).children().remove();
					$(this).append( '<span><i class="fa fa-caret-down"></i></span>' );
					$(this).data( "sorting-type", "ASC" );
				}
				if ( order === "ASC" ) {
					$(this).children().remove();
					$(this).append( '<span><i class="fa fa-caret-up"></i></span>' );
					$(this).data( "sorting-type", "DESC" );
				}
				$( ".leave-lists tr.lists-data" ).remove();
				$.ajax({
					url: ajaxurl,
					dataType: "json",
					type: 'POST',
					data: {
						action: "leave_listing_info",
						order:  order,
						attr:  attr,
						paged: paged
					},
					beforeSend : function(){
						$( ".leave-lists tr.lists-header" ).append('<tr id="loading" style="text-align:center"><td>' +
                            '<img src="' + rthrmurl +'app/assets/img/loading.gif"/>' +
                            '</td></tr>'
						);
					},
					success: function( data ) {
						$.each( data, function( i, val ) {
							$( ".leave-lists tr.lists-header" ).after( '<tr class="lists-data"><td class="leavetype">' + data[i].leavetype + '<br /><span><a href="' + data[i].editpostlink + '">Edit</a></span>&nbsp;&#124;<a href="' + data[i].permalink + '">View</a></td><td>' + data[i].leavestartdate + '</td><td>' + data[i].leaveenddate + '</td><td class="' + data[i].poststatus + '">' + data[i].poststatus + '</td></tr>' );
						});
						if ( data.length === 0 ){
							$( ".leave-lists tr.lists-data" ).remove();
							$( "ul#leave-pagination" ).remove();
							$( ".leave-lists tr.lists-header" ).after( '<tr class="lists-data"><td colspan="7" align="center" scope="row">No Leave Listing</td></tr>' );
							$( ".leave-lists #loading" ).remove();
							
						} else {
							$( ".leave-lists #loading" ).remove();
						}
					},
					error: function(jqXHR, textStatus, errorThrown) {
					    $( ".leave-lists #loading" ).remove();
						alert(jqXHR + " :: " + textStatus + " :: " + errorThrown);
					}
				});
				
			});
			$( "#leave-pagination li#next" ).click(function() {
				paged++;
				$( ".leave-lists tr.lists-data" ).remove();
				$.ajax({
					url: ajaxurl,
					dataType: "json",
					type: 'POST',
					data: {
						action: "leave_listing_info",
						order:  order,
						attr:  attr,
						paged: paged
					},
					beforeSend : function(){
						$( ".leave-lists tr.lists-header" ).append('<tr id="loading" style="text-align:center"><td>' +
                            '<img src="' + rthrmurl +'app/assets/img/loading.gif"/>' +
                            '</td></tr>'
						);
					},
					success: function( data ) {
						if ( data.length != 0 ){
							max_num_pages = data[0].max_num_pages;
						}
						if ( max_num_pages == paged ){
							$( "#leave-pagination li#next" ).hide();
						} else {
							$( "#leave-pagination li#next" ).show();
						}
						if ( 1 == paged ){
							$( "#leave-pagination li#prev" ).hide();
						} else {
							$( "#leave-pagination li#prev" ).show();
						}
						$.each( data, function( i, val ) {
							$( ".leave-lists tr.lists-header" ).after( '<tr class="lists-data"><td class="leavetype">' + data[i].leavetype + '<br /><span><a href="' + data[i].editpostlink + '">Edit</a></span>&nbsp;&#124;<a href="' + data[i].permalink + '">View</a></td><td>' + data[i].leavestartdate + '</td><td>' + data[i].leaveenddate + '</td><td class="' + data[i].poststatus + '">' + data[i].poststatus + '</td></tr>' );
						});
						if ( data.length === 0 ){
							$( ".leave-lists tr.lists-data" ).remove();
							$( "ul#leave-pagination" ).remove();
							$( ".leave-lists tr.lists-header" ).after( '<tr class="lists-data"><td colspan="7" align="center" scope="row">No Leave Listing</td></tr>' );
							$( ".leave-lists #loading" ).remove();
							
						} else {
							$( ".leave-lists #loading" ).remove();
						}
					},
					error: function(jqXHR, textStatus, errorThrown) {
					    $( ".leave-lists #loading" ).remove();
						alert(jqXHR + " :: " + textStatus + " :: " + errorThrown);
					}
				});
			});
			$( "#leave-pagination li#prev" ).click(function() {
				paged--;
				$( ".leave-lists tr.lists-data" ).remove();
				$.ajax({
					url: ajaxurl,
					dataType: "json",
					type: 'POST',
					data: {
						action: "leave_listing_info",
						order:  order,
						attr:  attr,
						paged: paged
					},
					beforeSend : function(){
						$( ".leave-lists tr.lists-header" ).append('<tr id="loading" style="text-align:center"><td>' +
                            '<img src="' + rthrmurl +'app/assets/img/loading.gif"/>' +
                            '</td></tr>'
						);
					},
					success: function( data ) {
						if ( data.length != 0 ){
							max_num_pages = data[0].max_num_pages;
						}
						if ( max_num_pages == paged ){
							$( "#leave-pagination li#next" ).hide();
						} else {
							$( "#leave-pagination li#next" ).show();
						}
						if ( 1 == paged ){
							$( "#leave-pagination li#prev" ).hide();
						} else {
							$( "#leave-pagination li#prev" ).show();
						}
						$.each( data, function( i, val ) {
							$( ".leave-lists tr.lists-header" ).after( '<tr class="lists-data"><td class="leavetype">' + data[i].leavetype + '<br /><span><a href="' + data[i].editpostlink + '">Edit</a></span>&nbsp;&#124;<a href="' + data[i].permalink + '">View</a></td><td>' + data[i].leavestartdate + '</td><td>' + data[i].leaveenddate + '</td><td class="' + data[i].poststatus + '">' + data[i].poststatus + '</td></tr>' );
						});
						if ( data.length === 0 ){
							$( ".leave-lists tr.lists-data" ).remove();
							$( "ul#leave-pagination" ).remove();
							$( ".leave-lists tr.lists-header" ).after( '<tr class="lists-data"><td colspan="7" align="center" scope="row">No Leave Listing</td></tr>' );
							$( ".leave-lists #loading" ).remove();
							
						} else {
							$( ".leave-lists #loading" ).remove();
						}
					},
					error: function(jqXHR, textStatus, errorThrown) {
					    $( ".leave-lists #loading" ).remove();
						alert(jqXHR + " :: " + textStatus + " :: " + errorThrown);
					}
				});
			});
        },
		requestsListing : function(){
			var paged = 1;
			var order = "";
			var attr = "";
			var max_num_pages = 999999;
			if ( 1 == paged ){
				$( "#requests-pagination li#prev" ).hide();
			} else {
				$( "#requests-pagination li#prev" ).show();
			}
			if ( max_num_pages == paged ){
				$( "#requests-pagination li#next" ).hide();
			} else {
				$( "#requests-pagination li#next" ).show();
			}
			$( ".requests-lists .lists-header th.order" ).click(function() {
				order = $(this).data("sorting-type");
				attr =  $(this).data("attr-type");
				if ( order === "DESC" ) {
					$(this).children().remove();
					$(this).append( '<span><i class="fa fa-caret-down"></i></span>' );
					$(this).data( "sorting-type", "ASC" );
				}
				if ( order === "ASC" ) {
					$(this).children().remove();
					$(this).append( '<span><i class="fa fa-caret-up"></i></span>' );
					$(this).data( "sorting-type", "DESC" );
				}
				$( ".requests-lists tr.lists-data" ).remove();
				$.ajax({
					url: ajaxurl,
					dataType: "json",
					type: 'POST',
					data: {
						action: "requests_listing_info",
						order:  order,
						attr:  attr,
						paged: paged
					},
					beforeSend : function(){
						$( ".requests-lists tr.lists-header" ).append('<tr id="loading" style="text-align:center"><td>' +
                            '<img src="' + rthrmurl +'app/assets/img/loading.gif"/>' +
                            '</td></tr>'
						);
					},
					success: function( data ) {
						$.each( data, function( i, val ) {
							$( ".requests-lists tr.lists-header" ).after( '<tr class="lists-data"><td align="center" scope="row">' + data[i].avatar + '</td><td class="leaveuservalue">' + data[i].leaveuservalue + '<br /><span><a href="' + data[i].editpostlink + '">Edit</a></span>&nbsp;&#124;<a href="' + data[i].permalink + '">View</a>&#124;&nbsp;<a class="deletepostlink" href="' + data[i].deletepostlink + '">Delete</a></td><td>' + data[i].leavetype + '</td><td>' + data[i].leavestartdate + '</td><td>' + data[i].leaveenddate + '</td><td>' + data[i].poststatus + '</td><td>' + data[i].approver + '</td></tr>' );
						});
						if ( data.length === 0 ){
							$( ".requests-lists tr.lists-data" ).remove();
							$( "ul#requests-pagination" ).remove();
							$( ".requests-lists tr.lists-header" ).after( '<tr class="lists-data"><td colspan="7" align="center" scope="row">No Leave Listing</td></tr>' );
							$( ".requests-lists #loading" ).remove();
							
						} else {
							$( ".requests-lists #loading" ).remove();
						}
					},
					error: function(jqXHR, textStatus, errorThrown) {
					    $( ".requests-lists #loading" ).remove();
						alert(jqXHR + " :: " + textStatus + " :: " + errorThrown);
					}
				});
				
			});
			$( "#requests-pagination li#next" ).click(function() {
				paged++;
				$( ".requests-lists tr.lists-data" ).remove();
				$.ajax({
					url: ajaxurl,
					dataType: "json",
					type: 'POST',
					data: {
						action: "requests_listing_info",
						order:  order,
						attr:  attr,
						paged: paged
					},
					beforeSend : function(){
						$( ".requests-lists tr.lists-header" ).append('<tr id="loading" style="text-align:center"><td>' +
                            '<img src="' + rthrmurl +'app/assets/img/loading.gif"/>' +
                            '</td></tr>'
						);
					},
					success: function( data ) {
						if ( data.length != 0 ){
							max_num_pages = data[0].max_num_pages;
						}
						if ( max_num_pages == paged ){
							$( "#requests-pagination li#next" ).hide();
						} else {
							$( "#requests-pagination li#next" ).show();
						}
						if ( 1 == paged ){
							$( "#requests-pagination li#prev" ).hide();
						} else {
							$( "#requests-pagination li#prev" ).show();
						}
						$.each( data, function( i, val ) {
							$( ".requests-lists tr.lists-header" ).after( '<tr class="lists-data"><td align="center" scope="row">' + data[i].avatar + '</td><td class="leaveuservalue">' + data[i].leaveuservalue + '<br /><span><a href="' + data[i].editpostlink + '">Edit</a></span>&nbsp;&#124;<a href="' + data[i].permalink + '">View</a>&#124;&nbsp;<a class="deletepostlink" href="' + data[i].deletepostlink + '">Delete</a></td><td>' + data[i].leavetype + '</td><td>' + data[i].leavestartdate + '</td><td>' + data[i].leaveenddate + '</td><td>' + data[i].poststatus + '</td><td>' + data[i].approver + '</td></tr>' );
						});
						if ( data.length === 0 ){
							$( ".requests-lists tr.lists-data" ).remove();
							$( "ul#requests-pagination" ).remove();
							$( ".requests-lists tr.lists-header" ).after( '<tr class="lists-data"><td colspan="7" align="center" scope="row">No Leave Listing</td></tr>' );
							$( ".requests-lists #loading" ).remove();
							
						} else {
							$( ".requests-lists #loading" ).remove();
						}
					},
					error: function(jqXHR, textStatus, errorThrown) {
					    $( ".requests-lists #loading" ).remove();
						alert(jqXHR + " :: " + textStatus + " :: " + errorThrown);
					}
				});
			});
			$( "#requests-pagination li#prev" ).click(function() {
				paged--;
				$( ".requests-lists tr.lists-data" ).remove();
				$.ajax({
					url: ajaxurl,
					dataType: "json",
					type: 'POST',
					data: {
						action: "requests_listing_info",
						order:  order,
						attr:  attr,
						paged: paged
					},
					beforeSend : function(){
						$( ".requests-lists tr.lists-header" ).append('<tr id="loading" style="text-align:center"><td>' +
                            '<img src="' + rthrmurl +'app/assets/img/loading.gif"/>' +
                            '</td></tr>'
						);
					},
					success: function( data ) {
						if ( data.length != 0 ){
							max_num_pages = data[0].max_num_pages;
						}
						if ( max_num_pages == paged ){
							$( "#requests-pagination li#next" ).hide();
						} else {
							$( "#requests-pagination li#next" ).show();
						}
						if ( 1 == paged ){
							$( "#requests-pagination li#prev" ).hide();
						} else {
							$( "#requests-pagination li#prev" ).show();
						}
						$.each( data, function( i, val ) {
							$( ".requests-lists tr.lists-header" ).after( '<tr class="lists-data"><td align="center" scope="row">' + data[i].avatar + '</td><td class="leaveuservalue">' + data[i].leaveuservalue + '<br /><span><a href="' + data[i].editpostlink + '">Edit</a></span>&nbsp;&#124;<a href="' + data[i].permalink + '">View</a>&#124;&nbsp;<a class="deletepostlink" href="' + data[i].deletepostlink + '">Delete</a></td><td>' + data[i].leavetype + '</td><td>' + data[i].leavestartdate + '</td><td>' + data[i].leaveenddate + '</td><td>' + data[i].poststatus + '</td><td>' + data[i].approver + '</td></tr>' );
						});
						if ( data.length === 0 ){
							$( ".requests-lists tr.lists-data" ).remove();
							$( "ul#requests-pagination" ).remove();
							$( ".requests-lists tr.lists-header" ).after( '<tr class="lists-data"><td colspan="7" align="center" scope="row">No Leave Listing</td></tr>' );
							$( ".requests-lists #loading" ).remove();
							
						} else {
							$( ".requests-lists #loading" ).remove();
						}
					},
					error: function(jqXHR, textStatus, errorThrown) {
					    $( ".requests-lists #loading" ).remove();
						alert(jqXHR + " :: " + textStatus + " :: " + errorThrown);
					}
				});
			});
        }
	}
	rtHRMFrontend.init();
});