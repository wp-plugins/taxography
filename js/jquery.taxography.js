/**
	The Categories widget replaces the default WordPress Categories widget. This version gives total
	control over the output to the user by allowing the input of all the arguments typically seen
	in the wp_list_categories() function.

	Copyright 2012 zourbuth (email : zourbuth@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

jQuery(document).ready(function($) {
	
	// Ajax load posts function
	$("a.taxography-load-posts").click(function() {
		
		var t, c, p, i;
		
		t = $(this); 					
		i = t.attr("data-id");		
		p = t.attr("data-page") ? t.attr("data-page") : 0;		
		c = t.attr("data-tax");
		
		t.after("<span class='taxography-loading'>Loading...</span>").fadeIn();
		
		$.post( taxography.ajaxurl, { action: taxography.action, id: i, category: c, page: p, nonce : taxography.nonce }, function(data){
			if( ! data ) {
				t.remove();
				$(".taxography-loading").remove();
				return;
			}
			
			p++;
			$(".taxography-loading").remove();
			t.prev().append(data).fadeIn();
			t.attr("data-page", p);			
		});
		
		return false;
	});

	// Category icon hover function
	$("img.taxography-image").live("mouseover", function(){
		$(this).stop().animate({opacity:0.75},400);
	}).live("mouseout", function(){
		$(this).stop().animate({opacity:1},400);
	});	
});