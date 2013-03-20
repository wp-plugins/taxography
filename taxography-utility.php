<?php 
/*
	Version: 2.0.0
	Author: zourbuth
	Author URI: http://zourbuth.com
	License: Under GPL2
	
	(1) The PHP code is licensed under the GPL license as is WordPress itself.
	You will find a copy of the license text in the downloaded zip plugin file. 
	Or you can read it here: http://codex.wordpress.org/GPL
	
	(2) All other parts of this plugin including, but not limited to the CSS code, 
	images, and design are licensed according to the license purchased. 
	Read about licensing details here: http://wiki.envato.com/support/legal-terms/licensing-terms/		
 
	Copyright 2013 zourbuth.com (email : zourbuth@gmail.com)

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


 
/**
 * Get the author 5 recent posts wrapped by list style type
 * @param $id the author id
 * @return output as HTML
 * @since 2.0.0
**/
function taxography_get_posts( $args, $params, $html = '' ) {
	
	$defaults = array(
		'numberposts' => 5, 'offset' => 0,
		'category' => 0, 'orderby' => 'post_date',
		'order' => 'DESC', 'include' => array(),
		'exclude' => array(), 'meta_key' => '',
		'meta_value' =>'', 'post_type' => 'post',
		'suppress_filters' => true
	);
	$r = wp_parse_args( $args, $defaults );
	
    $posts = get_posts( $r );
	
	foreach ( $posts as $post ) {
		$thumb = '';
		
		if( $params['show_post_thumbnail'] ) {
			
			if( get_post_thumbnail_id( $post->ID) ) {
			
				$image_id = get_post_thumbnail_id( $post->ID );
				$imgsrc = wp_get_attachment_image_src( $image_id, 'thumbnail', true );
				$i = $imgsrc[0];
				
				// Timthumb modded for multisite
				if( is_multisite() ) {
					global $blog_id;
					if( isset($blog_id) && $blog_id > 0 ) {
						$imageParts = explode('/taxography/files/', $i);
						if( isset($imageParts[1] ) ) {
							$i = '/blogs.dir/' . $blog_id . '/files/' . $imageParts[1];
						}
					}
				}
				
			} else {
				$i = $params['empty_thumbnail'];
			}					
			
			$w = $params['post_thumbnail_width'];
			$h = $params['post_thumbnail_height'];
			
			$thumb = "<img class='taxography-image' src='".TAXOGRAPHY_URL."timthumb.php?src=$i&amp;h=$h&amp;w=$w&amp;zc=1' alt='{$post->post_title}' />";				
		}
		
		// Check if the post has no title
		$post_title = empty( $post->post_title ) ? __('(no title)') : $post->post_title;
		
		// Output the list		
		$html .= "<li>$thumb<a title='$post_title' href='" . get_permalink( $post->ID ) . "'>". taxography_substr( $post_title, $params['post_title_substr'] ) ."</a></li>";
	}
	
	if( $posts ) 
		return $html;
	else
		return;
}


/**
 * Get the author utility via ajax.
 * Checking for the $_POST nonce, author and type
 * @param $_POST['nonce'] is the nonce for verifying the current $_POST
 * 		  $_POST['nonce'] the author ID
 * 		  $_POST['type'] the request type, author recent posts, biography, or comments
 * @default "info" request is the default
 * @return output as HTML
 * @since 1.4
**/
function taxography_load_utility() {
		
	// Check the nonce and if not isset the id, just die
	// not best, but maybe better for avoid errors
	$nonce = $_POST['nonce'];
	if ( ! wp_verify_nonce( $nonce, 'taxography-nonce' ) ) 
		die();
	
	$option = get_option('widget_taxography');
	$id = $_POST['id'];

	// Get posts with paramaters
	echo taxography_get_posts( array(
		'category' 		 => $_POST['category'],
		'numberposts' 	 => $option[$id]['numberposts'],
		'offset'		 => (int) $_POST['page'] * $option[$id]['numberposts']
	), $option[$id] );
	
	exit;
}


/**
 * Function for substring the long text based on character set
 * @return title
 * @since 2.0.0
**/
function taxography_substr( $text, $length = false, $ellipsis = '&hellip;' ) {
	
	if( ! $length or 0 == $length or ! $text )
		return $text;
		
	if( strlen( $text ) > $length ) {
		$text = substr( $text, 0, $length );
		$text = apply_filters( 'taxography_substr', $text . $ellipsis, $text, $ellipsis );			
	}
	
	return $text;
}





?>