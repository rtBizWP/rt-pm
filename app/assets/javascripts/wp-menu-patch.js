/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


jQuery(document).ready(function($) {
	/**
	 * WordPress Menu Hack for Time Entry Type Menu Page ( Taxonomy Page )
	 */
	if (typeof rtpm_time_entry_type_screen != 'undefined') {
		console.log(rtpm_time_entry_type_screen);
		$('#menu-posts').removeClass('wp-menu-open wp-has-current-submenu').addClass('wp-not-current-submenu');
		$('#menu-posts a.wp-has-submenu').removeClass('wp-has-current-submenu wp-menu-open menu-top');
		$('#menu-posts-rt_project').addClass('wp-has-current-submenu wp-menu-open menu-top menu-top-first').removeClass('wp-not-current-submenu');
		$('#menu-posts-rt_project a.wp-has-submenu').addClass('wp-has-current-submenu wp-menu-open menu-top');
		$('#menu-posts-rt_project ul li').removeClass('current');
		$('#menu-posts-rt_project ul li a').removeClass('current');
		$('#menu-posts-rt_project ul li a').each(function(e) {
			console.log(this.href);
			if ( this.href == rtpm_time_entry_type_screen ) {
				$(this).parent().addClass("current");
	            $(this).addClass('current');
			}
		});
		$(window).resize();
	}

});