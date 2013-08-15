<?php
/**
	Taxography Images Class
	This is a class for uploading or editing taxonomy image. 
	Uses: get_option('taxonomy_image_' . $tag_ID );
	
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
**/

class Taxography_Images {

	/**
	 * Variable for the instance.
	 * @since 2.0.0
	 */
	var $prefix;
	var $textdomain;

	
	/**
	 * Constructor for this class.
	 * @since 1.0
	 **/
	function __construct()  {
		// Set vars for this instance eq. your-theme-slug-
		$this->prefix 		= $this->textdomain;
		$this->textdomain	= $this->textdomain;
		
		add_action( 'admin_init', array(&$this, 'admin_init' ) );
		add_action( 'load-edit-tags.php', array(&$this, 'load_edit_tags' ) );
		add_action( 'edit_term', array(&$this, 'edit_term'), 10, 3 );
		add_action( 'create_term', array(&$this, 'edit_term'), 10, 3 );
	}
	
	
	/**
	 * Add the custom column and quick edit box
	 * @since 2.0.0
	 **/
	function admin_init() {
		if ( isset( $_REQUEST['taxonomy'] ) && taxonomy_exists( $_REQUEST['taxonomy'] ) )
			$taxnow = $_REQUEST['taxonomy'];
		else
			return;
		
		if( isset( $taxnow ) && 'link_category' != $taxnow ) {
			$option = get_option( 'taxography' );
			if( in_array( $taxnow, array("category") ) ) {						
				add_action( $taxnow. '_edit_form_fields', array(&$this, 'taxonomy_edit_form_fields') );		// add image form for edit current tag
			}
		}
	}
	
	
	/**
	 * Push the custom stylesheets and scripts to edit tags admin page
	 * @since 2.0.0
	 **/
	function load_edit_tags() {
		// Print custom styles
		wp_enqueue_style( 'thickbox' );
		wp_enqueue_style( 'taxography-edit-tags', TAXOGRAPHY_URL . 'css/edit-tags.css' );
		
		// Print custom scripts
		wp_enqueue_script( 'thickbox' );
		wp_enqueue_script( 'taxography-dialog', TAXOGRAPHY_URL . 'js/jquery.dialog.js', array( 'jquery', 'thickbox' ) );
		wp_enqueue_script( 'taxography-inline-edit', TAXOGRAPHY_URL . 'js/inline-edit-tax.js' );
	}
	
	
	/**
	 * Update the taxonomy image to the options database with the tag
	 * Uses update_option with taxonomy_image_(the-tag-id) name
	 * @since 2.0.0
	 **/	
	function edit_term( $term_id, $tt_id, $taxonomy ) {
		if ( ! isset( $_POST['taxonomy-image'] ) )
			return;
			
		$file_img = esc_url( $_POST['taxonomy-image'] );
		update_option( 'taxonomy_image_' . $term_id, $file_img );
	}
	
	
	/**
	 * Return additional column for taxonomy image
	 * Checking if the 'image' column slug is not exist
	 * @since 2.0.0
	 **/
	function manage_edit_taxonomy_columns( $columns ) {
		if ( isset( $columns['image'] ) )
			return;	
	
		$columns['image'] = 'Image';
		return $columns;
	}

	
	/**
	 * Generate additional column for the taxonomy images
	 * @since 2.0.0
	 **/	
	function manage_taxonomy_custom_column( $c, $column_name, $term_id ) {  
		if( $column_name == 'image' ) {  
			$image = get_option( 'taxonomy_image_' . $term_id );
			if( $image ) {
				echo "<a href='$image' target='_blank'>";
					echo "<img src='".TAXOGRAPHY_URL."timthumb.php?src=$image&amp;h=30&amp;w=30&amp;zc=1' alt='' title='$image' data-src='$image' />";
				echo "</a>";
			}
		}  
	}
	
	
	/**
	 * Constructor for this class. 
	 * @since 2.0.0
	 **/
	function quick_edit_custom_box( $column_name, $taxonomy ) { ?>
		<fieldset>
			<div class="inline-edit-col">
				<label>
					<span class="title">Image</span>
					<span class=""><input type="text" value="" class="taxonomy-image" name="taxonomy-image"></span>
					<a class="addimage button-secondary" title="Add image" href="#" >Add</a>
					<a class="deleteimage button-secondary" title="Delete image" href="#" >Delete</a>					
				</label>
			</div>
		</fieldset><?php
	}
	
	
	/**
	 * Constructor for this class. 
	 * @since 2.0.0
	 **/
	function taxonomy_edit_form_fields( $tag ) {
		$option = get_option( 'taxonomy_image_' . $tag->term_id );
		$image = esc_attr( $option );
		?>
		<tr class="form-field">
			<th valign="top" scope="row"><label><?php _e( 'Image', $this->textdomain ); ?></label></th>
			<td class="totalControls">		
				<img alt="" class="optionImage" src="<?php echo $image; ?>" />
				<a href="#" class="addImage button"><?php _e( 'Add Image', $this->textdomain ); ?></a>
				<a class="<?php if ( empty( $image ) ) : ?>hideRemove<?php else : ?>showRemove<?php endif; ?> removeImage button" href="#"><?php _e( 'Remove', $this->textdomain ); ?></a>
				<input type="hidden" name="taxonomy-image" id="taxonomy-image" value="<?php echo $image; ?>" />
				<p class="description"><?php _e( 'Please set the "Link URL" to "File URL" when selecting the image from uploader.</p>', $this->textdomain ); ?></p>
			</td>
		</tr>
		<?php
	}
	
	
	/**
	 * Constructor for this class. 
	 * @since 2.0.0
	 **/
	function taxonomy_add_form_fields( $tag ) {
		?>
		<div class="form-field">
			<label><?php _e( 'Image', $this->textdomain ); ?></label>
			<div class="totalControls">				
				<img alt="" class="optionImage" src="<?php echo $image; ?>" />
				<a href="#" class="addImage button"><?php _e( 'Add Image', $this->textdomain ); ?></a>
				<a class="hidden removeImage button" href="#"><?php _e( 'Remove', $this->textdomain ); ?></a>
				<input type="hidden" name="taxonomy-image" id="taxonomy-image" value="<?php echo $image; ?>" />			
			</div>
			<p><?php _e( 'Please set the "Link URL" to "File URL" when selecting the image from uploader.</p>', $this->textdomain ); ?></p>
		</div>
		
		<?php
	}	
}

$taxography = new Taxography_Images();	// create new instance
?>