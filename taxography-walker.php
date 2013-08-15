<?php
/**
 * Create HTML list of taxonomies.
 *
 * @package WordPress
 * @since 2.1.0
 * @uses Walker
 */
class Taxography_Walker extends Walker_Category {
	/**
	 * @see Walker::$tree_type
	 * @since 2.1.0
	 * @var string
	 */
	var $tree_type = 'category';

	/**
	 * @see Walker::$db_fields
	 * @since 2.1.0
	 * @todo Decouple this
	 * @var array
	 */
	var $db_fields = array ('parent' => 'parent', 'id' => 'term_id');

	/**
	 * @see Walker::start_lvl()
	 * @since 2.1.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int $depth Depth of category. Used for tab indentation.
	 * @param array $args Will only append content if style argument value is 'list'.
	 */
	function start_lvl(&$output, $depth = 0, $args = array()) {
		if ( 'list' != $args['style'] )
			return;

		$indent = str_repeat("\t", $depth);
		$output .= "$indent<ul class='taxography-children'>\n";
	}

	/**
	 * @see Walker::end_lvl()
	 * @since 2.1.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int $depth Depth of category. Used for tab indentation.
	 * @param array $args Will only append content if style argument value is 'list'.
	 */
	function end_lvl(&$output, $depth = 0, $args = array()) {
		if ( 'list' != $args['style'] )
			return;

		$indent = str_repeat("\t", $depth);
		$output .= "$indent</ul>\n";
	}

	/**
	 * @see Walker::start_el()
	 * @since 2.1.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $category Category data object.
	 * @param int $depth Depth of category in reference to parents.
	 * @param array $args
	 */
	function start_el(&$output, $category, $cdepth = 0, $args = array(), $current_object_id = 0) {
		global $tax_count;
		
		if( ! $tax_count )
			$tax_count = 1;
		
		extract($args);

		$cat_name = esc_attr( $category->name );
		$cat_name = apply_filters( 'list_cats', $cat_name, $category );

		$img = get_option('taxonomy_image_' . $category->term_id );
		
		/*
		global $blog_id;
		if (isset($blog_id) && $blog_id > 0) {
			$img = explode('/taxography/files/', $img);
			if (isset($img[1])) {
				$img = '/blogs.dir/' . $blog_id . '/files/' . $img[1];
			}
		}
		*/
		$link = '';
		if ( $show_thumbnail && ! empty( $img ) ) {		
			$width  = apply_filters('taxography_image_width', ( 0 == $cdepth ) ? $icon_width  : $child_icon_width );
			$height = apply_filters('taxography_image_height', ( 0 == $cdepth ) ? $icon_height : $child_icon_height );
			
			if( 0 == $cdepth ) {
				$link  .= '<a href="' . esc_attr( get_term_link($category) ) . '">
						  <img class="taxography-image" src="' . TAXOGRAPHY_URL . 'timthumb.php?src=' . $img . '&amp;h=' . $height . '&amp;w=' . $width . '&amp;zc=1" alt="' . $cat_name . '" />
						  </a>';
			} else {
				 if( $show_child_thumbnail )
					$link  .= '<a href="' . esc_attr( get_term_link($category) ) . '">
							  <img class="taxography-image" src="' . TAXOGRAPHY_URL . 'timthumb.php?src=' . $img . '&amp;h=' . $height . '&amp;w=' . $width . '&amp;zc=1" alt="' . $cat_name . '" />
							  </a>';
			}
		}
		
		if ( $show_title ) {
			$link .= '<a class="taxography-title" href="' . esc_attr( get_term_link($category) ) . '" ';
		
			if ( $use_desc_for_title == 0 || empty($category->description) )
				$link .= 'title="' . esc_attr( sprintf(__( 'View all posts filed under %s' ), $cat_name) ) . '"';
			else
				$link .= 'title="' . esc_attr( strip_tags( apply_filters( 'category_description', $category->description, $category ) ) ) . '"';
			$link .= '>';
			$link .= $cat_name . '</a>';
		}

		if ( !empty($feed_image) || !empty($feed) ) {
			$link .= ' ';

			if ( empty($feed_image) )
				$link .= '(';

			$link .= '<a href="' . get_term_feed_link( $category->term_id, $category->taxonomy, $feed_type ) . '"';

			if ( empty($feed) ) {
				$alt = ' alt="' . sprintf(__( 'Feed for all posts filed under %s' ), $cat_name ) . '"';
			} else {
				$title = ' title="' . $feed . '"';
				$alt = ' alt="' . $feed . '"';
				$name = $feed;
				$link .= $title;
			}

			$link .= '>';

			if ( empty($feed_image) )
				$link .= $name;
			else
				$link .= "<img src='$feed_image'$alt$title" . ' />';

			$link .= '</a>';

			if ( empty($feed_image) )
				$link .= ')';
		}
		
		if ( $show_count )
			$link .= ' <span class="taxo-count">' . intval($category->count) . '</span>';
		
		
		// Check for show description and if not empty
		// Check for show child description $cdepth > 0
		if ( $show_description && !empty( $category->description) ) {
			$desc = taxography_substr( $category->description, $description_substr, '&hellip;' );
			if( 0 == $cdepth ) {
				$link .= "<br /><span class='taxography-description'>$desc</span>";
			} else {
				if( $show_child_description )
					$link .= "<br /><span class='taxography-description'>$desc</span>";
			}
		}
		
		if ( ! empty( $show_date ) )
			$link .= ' ' . gmdate( 'Y-m-d', $category->last_update_timestamp );

		if ( 'list' == $style ) {
		
			// Create clear section to new row group
			if( $args['columns'] > 1 &&  $tax_count > 1 &&  $tax_count % $args['columns'] == 1 )
				$output .= '<li class="clear"></li>';
				
			$output .= "\t<li ";
			
			// creat odd or even class
			if( $tax_count % 2 == 0 ) {
				$oddeven = 0 == $cdepth ? 'tax-odd ' : '';
			} else {
				$oddeven = 0 == $cdepth ? 'tax-even ' : '';
			}
			
			// create first li class in a row
			$first_child = $columns > 1 && $tax_count % $columns == 1 ? 'col0' : '';
			
			$tcol = $columns > 1 && 0 == $cdepth ? "col$columns " : '';
			
			$class = "$oddeven$tcol$first_child tax-term-{$category->term_id}";
			if ( !empty($current_category) ) {
				$_current_category = get_term( $current_category, $category->taxonomy );
				if ( $category->term_id == $current_category )
					$class .=  ' current-cat';
				elseif ( $category->term_id == $_current_category->parent )
					$class .=  ' current-cat-parent';
			}
			$output .=  ' class="' . $class . '"';
			$output .= ">$link\n";
		} else {
			$output .= "\t$link<br />\n";
		}
		
		
		// Load the posts term if set
		if( $show_posts ) {					  
			// Check if child category posts enable
			if( 0 == $cdepth || $show_child_posts  ) {
				
				// Check if the title is set
				$output .= "<span class='taxography-posts-header'>$posts_header_title</span>";
				
				$output .= "<ul class='taxography-posts$ul_class' $data_substr>";
				// If load style then print the posts, if ajax just print the title link
				if( 'onload' == $post_style ) {				
					// Get posts with paramaters
					$output .= taxography_get_posts( array(
									'category' 		 => $category->term_id,
									'numberposts' 	 => $numberposts
								), $args );						
				}
				$output .= "</ul>";	
											
				// Create the load more button for ajax click event
				$datapage = 'onload' == $post_style ? ' data-page = "1"' : '';
				$output .=  "<a class='taxography-load-posts' data-id='$widget_id' data-tax='{$category->term_id}' href='#'$datapage>Load posts</a>";
			}
		}
		
		if( 0 == $cdepth )
			$tax_count++;
	}

	/**
	 * @see Walker::end_el()
	 * @since 2.1.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $page Not used.
	 * @param int $depth Depth of category. Not used.
	 * @param array $args Only uses 'list' for whether should append to output.
	 */
	function end_el(&$output, $object, $depth = 0, $args = array()) {
		if ( 'list' != $args['style'] )
			return;

		$output .= '</li>';	
	}

}
?>