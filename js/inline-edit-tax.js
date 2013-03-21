/**
	Copyright 2013 zourbuth (email : zourbuth@gmail.com)

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
**/

(function($) {
inlineEditTaxography = {

	init : function() {
		var t = this, row = $('#inline-edit');
		
		$('.editinline').live('click', function(){
			inlineEditTaxography.edit(this);
			return false;
		});
		
		$('a.deleteimage', row).click(function() { return inlineEditTaxography.deleted(this); });
		$('a.addimage', row).click(function() { return inlineEditTaxography.deleted(this); });
	},

	edit : function(id) {
		var editRow, rowData;
		inlineEditTax.revert();

		if ( typeof(id) == 'object' )
			id = inlineEditTax.getId(id);

		editRow = $('#inline-edit');
		rowData = inlineEditTax.what+id;

		$(':input[name="taxonomy-image"]', editRow).val( $('img', rowData).attr("data-src") );			

		return false;
	},
	
	deleted : function(id) {
		var editRow, rowData;

		if ( typeof(id) == 'object' )
			id = inlineEditTax.getId(id);

		editRow = $('#edit-'+id);

		$(':input[name="taxonomy-image"]', editRow).val("");
		console.log(editRow);
		return false;
	}
};

$(document).ready(function(){inlineEditTaxography.init();});
})(jQuery);
