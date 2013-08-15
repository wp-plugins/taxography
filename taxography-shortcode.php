<?php
/*
    Taxography Shortcodes
    http://zourbuth.com/plugins/960-grid-system-shortcodes
    Copyright 2011  zourbuth.com  (email : zourbuth@gmail.com)

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

function taxography_shortcode( $atts, $content ) {
	extract( shortcode_atts( array( 
		'id' => ''
	), $atts )); 

	$option = get_option( 'widget_taxography' );
	$option[$id]['type'] = 'shortcode';
	$option[$id]['widget_id'] = $id;
	$html = taxography_get( $option[$id] );
	return $html;
}
add_shortcode('taxography', 'taxography_shortcode');


/**
 * Taxography main function for creating the taxonomy list to the front end
 * @params $args 
 * @echo 
 * @since 1.0
 */
function taxography( $id ) {
	$option = get_option( 'widget_taxography' );
	if( $option[$id] ){
		$option[$id]['type'] = 'shortcode';
		$option[$id]['widget_id'] = $id;
		$html = taxography_get( $option[$id] );
		echo $html;
	} else {
		echo __( 'Can\'t find settings from the given ID', 'taxogrphy' );
	}
}


/**
 * Taxography main function for creating the taxonomy list to the front end
 * @params $args 
 * @echo 
 * @since 1.0
 */
function taxography_get( $args ) {
	// Get the categories list.
	$categories = str_replace( array( "\r", "\n", "\t" ), '', taxography_walker( $args ) );

	// If 'list' is the user-selected style, wrap the categories in an unordered list
	if ( 'list' == $args['style'] )
		$categories = "<ul class='taxography taxography-{$args['type']}'>$categories</ul>";

	// Output the categories list.
	return $categories;
}
	

/**
 * Creates the end HTML for widget
 * @since 1.0
 */
function taxography_walker( $args = array() ) {
	$args = wp_parse_args( $args );
	$args['walker'] = new Taxography_Walker;
	$args['title_li'] = false;
	$args['echo'] = false;
	$output = wp_list_categories( $args );
	if ( $output )
		return $output;
}
?>