<?php
/**
 * The Categories widget replaces the default WordPress Categories widget. This version gives total
 * control over the output to the user by allowing the input of all the arguments typically seen
 * in the wp_list_categories() function.
 *
 */
class Taxonomy_Image_Widget extends WP_Widget {

	/**
	 * Variable for the instance.
	 * @since 0.1
	 */
	var $prefix;
	var $textdomain;

	/**
	 * Set up the widget's unique name, ID, class, description, and other options.
	 * @since 1.0
	 */
	function __construct() {
	
		// Give your own prefix name eq. your-theme-name-
		$this->prefix 		= 'taxography';
		$this->textdomain	= 'taxography';
		
		// Load the widget stylesheet for the widgets admin screen
		add_action( 'load-widgets.php', array(&$this, 'taxography_widget_admin_style') );
		add_action( 'wp_ajax_taxography_load_utility', 'taxography_load_utility' );
		add_action( 'wp_ajax_nopriv_taxography_load_utility', 'taxography_load_utility' );			
		
		// Set up the widget options
		$widget_options = array(
			'classname' => $this->prefix.'-widget',
			'description' => esc_html__( '[+] Advanced taxonomy image widget.', $this->textdomain )
		);

		// Set up the widget control options
		$control_options = array( 'width' => 460, 'height' => 350, 'id_base' => $this->prefix );

		// Create the widget
		$this->WP_Widget( $this->prefix, esc_attr__( 'Taxography', $this->textdomain ), $widget_options, $control_options );
		
		// Print the user costum style sheet
		if ( is_active_widget(false, false, $this->id_base, false ) && ! is_admin() ) {
			wp_enqueue_style( 'taxography', TAXOGRAPHY_URL . 'css/taxography.css' );
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'masonry', TAXOGRAPHY_URL . 'js/jquery.masonry.min.js');
			wp_enqueue_script( 'taxography', TAXOGRAPHY_URL . 'js/jquery.taxography.js' );
			wp_localize_script( 'taxography', 'taxography', array(
				'nonce'		=> wp_create_nonce( 'taxography-nonce' ),  // Generate a nonce for further checking below
				'action'	=> 'taxography_load_utility',
				'ajaxurl'	=> admin_url('admin-ajax.php')
			));
			add_action( 'wp_head', array( &$this, 'head_hook') );
		}			
	}
	
	
	/**
	 * Setup an widget admin style or scripts to the widget administration page
	 * Uses wp_enqueue_style and wp_enqueue_script
	 * @since 1.0
	 */
	function taxography_widget_admin_style() {
		wp_enqueue_style('thickbox');
		wp_enqueue_style( 'taxography-dialog', TAXOGRAPHY_URL . 'css/dialog.css' );
		wp_enqueue_script('taxography-dialog', TAXOGRAPHY_URL .'js/jquery.dialog.js', array( 'jquery', 'thickbox' ) );		
	}

	
	/**
	 * Print all widget settings to the wp head
	 * @since 1.0
	 */	
	function head_hook() {
		$all_widgets = $this->get_settings();
		foreach ($all_widgets as $key => $rc_setting){
			$widget_id = $this->id_base . '-' . $key;
			if( is_active_widget( false, $widget_id, $this->id_base ) ){
				if ( !empty( $rc_setting['customstylescript'] ) )
					echo $rc_setting['customstylescript'];
			}
		}
	}
	
	
	/**
	 * Outputs the widget based on the arguments input through the widget controls.
	 * @since 1.0
	 */
	function widget( $args, $instance ) {
		extract( $args );

		/* Set up the arguments for wp_list_categories(). */
		$args = array(
			'widget_id'				=> $this->number,
			'show_title'			=> !empty( $instance['show_title'] ) ? true : false,
			'title_icon'			=> $instance['title_icon'],
			'taxonomy' 				=> $instance['taxonomy'],
			'style' 				=> $instance['style'],
			'orderby' 				=> $instance['orderby'],
			'order' 				=> $instance['order'],
			'include' 				=> !empty( $instance['include'] ) ? join( ', ', $instance['include'] ) : '',
			'exclude' 				=> !empty( $instance['exclude'] ) ? join( ', ', $instance['exclude'] ) : '',
			'child_indent' 			=> $instance['child_indent'],
			'columns' 				=> $instance['columns'],
			'exclude_tree' 			=> $instance['exclude_tree'],
			'depth' 				=> intval( $instance['depth'] ),
			'number' 				=> intval( $instance['number'] ),
			'child_of' 				=> intval( $instance['child_of'] ),
			'current_category' 		=> intval( $instance['current_category'] ),
			'feed' 					=> $instance['feed'],
			'feed_type' 			=> isset( $instance['feed_type'] ) ? $instance['feed_type'] : '',
			'feed_image' 			=> esc_url( $instance['feed_image'] ),
			'search'				=> $instance['search'],
			'hierarchical' 			=> !empty( $instance['hierarchical'] ) ? true : false,
			'use_desc_for_title'	=> !empty( $instance['use_desc_for_title'] ) ? true : false,
			'description_substr'	=> $instance['description_substr'],
			'show_description'		=> !empty( $instance['show_description'] ) ? true : false,
			'show_child_description'	=> !empty( $instance['show_child_description'] ) ? true : false,
			'show_thumbnail'		=> !empty( $instance['show_thumbnail'] ) ? true : false,
			'show_child_thumbnail'	=> !empty( $instance['show_child_thumbnail'] ) ? true : false,
			'show_last_update' 		=> !empty( $instance['show_last_update'] ) ? true : false,
			'show_count'			=> !empty( $instance['show_count'] ) ? true : false,
			'hide_empty'			=> !empty( $instance['hide_empty'] ) ? true : false,
			'icon_height' 			=> $instance['icon_height'],
			'icon_width' 			=> $instance['icon_width'],
			'child_icon_height' 	=> $instance['child_icon_height'],
			'child_icon_width' 		=> $instance['child_icon_width'],
			
			'show_posts'			=> !empty( $instance['show_posts'] ) ? true : false,
			'show_child_posts'		=> !empty( $instance['show_child_posts'] ) ? true : false,
			'show_post_thumbnail'	=> !empty( $instance['show_post_thumbnail'] ) ? true : false,
			'numberposts'			=> intval( $instance['numberposts'] ),
			'post_style' 			=> $instance['post_style'],
			'posts_header_title' 	=> $instance['posts_header_title'],
			'empty_thumbnail' 		=> $instance['empty_thumbnail'],
			'post_title_substr' 	=> (int) $instance['post_title_substr'],
			'post_thumbnail_height' => $instance['post_thumbnail_height'],
			'post_thumbnail_width' 	=> $instance['post_thumbnail_width'],			
			
			'type' 					=> 'widget',
			'intro_text' 			=> $instance['intro_text'],
			'outro_text' 			=> $instance['outro_text'],
			'customstylescript'		=> $instance['customstylescript']
		);

		// Output the theme's widget wrapper
		echo $before_widget;

		// If a title was input by the user, display it
		if ( !empty( $instance['title'] ) )
			echo $before_title . apply_filters( 'widget_title',  $instance['title'], $instance, $this->id_base ) . $after_title;

		// Print intro text if exist
		if ( !empty( $instance['intro_text'] ) )
			echo '<p class="'. $this->id . '-intro-text intro-text">' . $instance['intro_text'] . '</p>';
			
		// Get the categories list.
		echo taxography_get( $args );
		
		// Print outro text if exist
		if ( !empty( $instance['outro_text'] ) )
			echo '<p class="'. $this->id . '-outro_text outro_text">' . $instance['outro_text'] . '</p>';

		// Close the theme's widget wrapper
		echo $after_widget;
	}


	/**
	 * Updates the widget control options for the particular instance of the widget.
	 * @since 1.0
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		// Set the instance to the new instance.
		$instance = $new_instance;

		// If new taxonomy is chosen, reset includes and excludes.
		if ( $instance['taxonomy'] !== $old_instance['taxonomy'] && '' !== $old_instance['taxonomy'] ) {
			$instance['include'] = array();
			$instance['exclude'] = array();
		}

		$instance['title'] 				= strip_tags( $new_instance['title'] );
		$instance['title_icon']			= strip_tags( $new_instance['title_icon'] );
		$instance['show_title'] 		= ( isset( $new_instance['show_title'] ) ? 1 : 0 );
		$instance['taxonomy'] 			= $new_instance['taxonomy'];
		$instance['exclude_tree'] 		= strip_tags( $new_instance['exclude_tree'] );
		
		$instance['depth'] 				= (int) $new_instance['depth'];
		$instance['child_indent'] 		= (int) $new_instance['child_indent'];
		$instance['number'] 			= (int) $new_instance['number'];
		$instance['columns'] 			= (int) $new_instance['columns'];
		
		$instance['child_of'] 			= strip_tags( $new_instance['child_of'] );
		$instance['current_category'] 	= strip_tags( $new_instance['current_category'] );
		$instance['feed'] 				= strip_tags( $new_instance['feed'] );
		$instance['feed_image'] 		= strip_tags( $new_instance['feed_image'] );
		$instance['search'] 			= strip_tags( $new_instance['search'] );

		$instance['hierarchical'] 			= isset( $new_instance['hierarchical'] ) ? 1 : 0;
		$instance['use_desc_for_title'] 	= isset( $new_instance['use_desc_for_title'] ) ? 1 : 0;
		$instance['description_substr'] 	= (int) $new_instance['description_substr'];
		$instance['show_description'] 		= isset( $new_instance['show_description'] ) ? 1 : 0;
		$instance['show_child_description']	= isset( $new_instance['show_child_description'] ) ? 1 : 0;
		$instance['show_thumbnail'] 		= isset( $new_instance['show_thumbnail'] ) ? 1 : 0;
		$instance['show_child_thumbnail'] 	= isset( $new_instance['show_child_thumbnail'] ) ? 1 : 0;
		$instance['show_last_update'] 		= isset( $new_instance['show_last_update'] ) ? 1 : 0;
		$instance['show_count'] 			= isset( $new_instance['show_count'] ) ? 1 : 0;
		$instance['hide_empty'] 			= isset( $new_instance['hide_empty'] ) ? 1 : 0;
		
		$instance['icon_height'] 		= (int) $new_instance['icon_height'];
		$instance['icon_width'] 		= (int) $new_instance['icon_width'];
		$instance['child_icon_height']	= (int) $new_instance['child_icon_height'];
		$instance['child_icon_width']	= (int) $new_instance['child_icon_width'];
		
		$instance['show_posts'] 		 = ( isset( $new_instance['show_posts'] ) ? 1 : 0 );	
		$instance['show_child_posts'] 	 = ( isset( $new_instance['show_child_posts'] ) ? 1 : 0 );	
		$instance['show_post_thumbnail'] = ( isset( $new_instance['show_post_thumbnail'] ) ? 1 : 0 );	
		$instance['numberposts'] 		 = (int) $new_instance['numberposts'];
		$instance['post_title_substr'] 	 = (int) $new_instance['post_title_substr'];
		$instance['post_style'] 		 = strip_tags( $new_instance['post_style'] );
		$instance['posts_header_title']  = strip_tags( $new_instance['posts_header_title'] );
		$instance['empty_thumbnail']  	 = strip_tags( $new_instance['empty_thumbnail'] );
		$instance['post_thumbnail_height']	= (int) $new_instance['post_thumbnail_height'];
		$instance['post_thumbnail_width']	= (int) $new_instance['post_thumbnail_width'];		
		
		$instance['intro_text'] 		= $new_instance['intro_text'];
		$instance['outro_text'] 		= $new_instance['outro_text'];
		$instance['customstylescript']	= $new_instance['customstylescript'];
		
		return $instance;
	}

	/**
	 * Displays the widget control options in the Widgets admin screen.
	 * @since 1.0
	 */
	function form( $instance ) {

		/* Set up the default form values. */
		$defaults = array(
			'title' 				=> esc_attr__( 'Taxography', $this->textdomain ),
			'show_title' 			=> true,
			'taxonomy' 				=> 'category',
			'style' 				=> 'list',
			'include' 				=> array(),
			'exclude' 				=> array(),
			'exclude_tree' 			=> '',
			'child_indent' 			=> 0,
			'columns' 				=> 1,
			'child_of' 				=> '',
			'current_category' 		=> '',
			'search' 				=> '',
			'hierarchical' 			=> true,
			'hide_empty' 			=> true,
			'order' 				=> 'ASC',
			'orderby' 				=> 'name',
			'depth' 				=> 0,
			'number' 				=> '',
			'feed' 					=> '',
			'feed_type' 			=> '',
			'feed_image' 			=> '',
			'use_desc_for_title' 	=> false,
			'description_substr' 	=> 0,
			'show_description' 		=> true,
			'show_child_description'	=> false,
			'show_thumbnail' 		=> true,
			'show_child_thumbnail' 	=> false,
			'show_last_update' 		=> false,
			'show_count' 			=> false,
			'icon_height' 			=> 52,
			'icon_width' 			=> 52,
			'child_icon_height' 	=> 13,
			'child_icon_width' 		=> 13,
			
			'show_posts' 			=> false,		
			'show_child_posts' 		=> false,		
			'show_post_thumbnail' 	=> false,		
			'numberposts' 			=> 5,
			'post_style' 			=> 'list',
			'post_title_substr' 	=> 0,
			'posts_header_title' 	=> '',
			'post_thumbnail_height'	=> 13,
			'post_thumbnail_width' 	=> 13,
			'empty_thumbnail' 		=> TAXOGRAPHY_URL . 'images/thumbnail.png',			
			
			'title_icon'			=> '',
			'bgImage' 				=> '',
			'toggle_active'			=>  array( 0 => true, 1 => false, 2 => false, 3 => false ),
			'intro_text' 			=> '',
			'outro_text' 			=> '',
			'customstylescript'		=> ''
		);

		// Merge the user-selected arguments with the defaults.
		$instance = wp_parse_args( (array) $instance, $defaults );
		
		$taxonomies = get_taxonomies( array( 'show_tagcloud' => true, 'name' => 'category', '_builtin' => true ), 'objects' );
		$terms = get_terms( $instance['taxonomy'], 'hide_empty=0' ); // update 0.2 to show the empty taxonomy.
		$post_styles = array( 'onload' => esc_attr__( 'On Load', $this->textdomain ), 'onclick' => esc_attr__( 'On Click', $this->textdomain ) );
		$style = array( 
			'list' => esc_attr__( 'List', $this->textdomain ), 
			'none' => esc_attr__( 'None', $this->textdomain )
		);
		$taxcols = array( 
			'1' => esc_attr__( '1', $this->textdomain ), 
			'2' => esc_attr__( '2', $this->textdomain ),
			'3' => esc_attr__( '3', $this->textdomain ),
			'4' => esc_attr__( '4', $this->textdomain )
		);
		$order = array( 
			'ASC' => esc_attr__( 'Ascending', $this->textdomain ), 
			'DESC' => esc_attr__( 'Descending', $this->textdomain )
		);
		$orderby = array( 
			'count' 		=> esc_attr__( 'Count', $this->textdomain ), 
			'ID' 			=> esc_attr__( 'ID', $this->textdomain ), 
			'name' 			=> esc_attr__( 'Name', $this->textdomain ), 
			'slug' 			=> esc_attr__( 'Slug', $this->textdomain ), 
			'term_group'	=> esc_attr__( 'Term Group', $this->textdomain ) 
		);
		$feed_type = array( 
			'' 		=> '', 
			'atom' 	=> esc_attr__( 'Atom', $this->textdomain ), 
			'rdf' 	=> esc_attr__( 'RDF', $this->textdomain ), 
			'rss' 	=> esc_attr__( 'RSS', $this->textdomain ), 
			'rss2' 	=> esc_attr__( 'RSS 2.0', $this->textdomain )
		);
		$intro_text	= esc_textarea($instance['intro_text']);
		$outro_text	= esc_textarea($instance['outro_text']);
		?>

		<div class="pluginName">Taxography<span class="pluginVersion"><?php echo TAXOGRAPHY_VERSION . ' (free)'; ?></span></div>
		
		<div id="tupro-<?php echo $this->id ; ?>" class="totalControls tabbable tabs-left">
			
			<ul class="nav nav-tabs">
				<li class="<?php if ( $instance['toggle_active'][0] ) : ?>active<?php endif; ?>"><?php _e( 'General', $this->textdomain ); ?><input type="hidden" name="<?php echo $this->get_field_name( 'toggle_active' ); ?>[]" value="<?php echo esc_attr( $instance['toggle_active'][0] ); ?>" /></li>
				<li class="<?php if ( $instance['toggle_active'][1] ) : ?>active<?php endif; ?>"><?php _e( 'Layout', $this->textdomain ); ?><input type="hidden" name="<?php echo $this->get_field_name( 'toggle_active' ); ?>[]" value="<?php echo esc_attr( $instance['toggle_active'][1] ); ?>" /></li>								
				<li class="<?php if ( $instance['toggle_active'][2] ) : ?>active<?php endif; ?>"><?php _e( 'Customs', $this->textdomain ); ?><input type="hidden" name="<?php echo $this->get_field_name( 'toggle_active' ); ?>[]" value="<?php echo esc_attr( $instance['toggle_active'][2] ); ?>" /></li>
				<li class="<?php if ( $instance['toggle_active'][3] ) : ?>active<?php endif; ?>"><?php _e( 'Premium', $this->textdomain ); ?><input type="hidden" name="<?php echo $this->get_field_name( 'toggle_active' ); ?>[]" value="<?php echo esc_attr( $instance['toggle_active'][3] ); ?>" /></li>
			</ul>		
				
			<ul class="tab-content">
				<li class="tab-pane <?php if ( $instance['toggle_active'][0] ) : ?>active<?php endif; ?>">
					<ul>
						<li>
							<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', $this->textdomain ); ?></label>
							<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" />
							<span class="controlDesc"><?php _e( 'The widget title, leave it empty for no title.', $this->textdomain ); ?></span>
						</li>
						<li>
							<label for="<?php echo $this->get_field_id( 'taxonomy' ); ?>"><?php _e( 'Taxonomy', $this->textdomain ); ?></label> 
							<span class="controlDesc"><?php _e( 'Select the taxonomy as given below.', $this->textdomain ); ?></span>
							<select onchange="wpWidgets.save(jQuery(this).closest('div.widget'),0,1,0);" id="<?php echo $this->get_field_id( 'taxonomy' ); ?>" name="<?php echo $this->get_field_name( 'taxonomy' ); ?>">
								<?php foreach ( $taxonomies as $taxonomy ) { ?>
									<option value="<?php echo $taxonomy->name; ?>" <?php selected( $instance['taxonomy'], $taxonomy->name ); ?>><?php echo $taxonomy->labels->singular_name; ?></option>
								<?php } ?>
							</select>
						</li>
						<li>
							<label for="<?php echo $this->get_field_id( 'order' ); ?>"><?php _e( 'Ordering', $this->textdomain ); ?></label>
							<span class="controlDesc"><?php _e( 'Sort categories with ascending or descending ordering.', $this->textdomain ); ?></span>
							<select id="<?php echo $this->get_field_id( 'order' ); ?>" name="<?php echo $this->get_field_name( 'order' ); ?>">
								<?php foreach ( $order as $option_value => $option_label ) { ?>
									<option value="<?php echo esc_attr( $option_value ); ?>" <?php selected( $instance['order'], $option_value ); ?>><?php echo esc_html( $option_label ); ?></option>
								<?php } ?>
							</select>

							<select id="<?php echo $this->get_field_id( 'orderby' ); ?>" name="<?php echo $this->get_field_name( 'orderby' ); ?>">
								<?php foreach ( $orderby as $option_value => $option_label ) { ?>
									<option value="<?php echo esc_attr( $option_value ); ?>" <?php selected( $instance['orderby'], $option_value ); ?>><?php echo esc_html( $option_label ); ?></option>
								<?php } ?>
							</select>
						</li>
						<li>
							<label for="<?php echo $this->get_field_id( 'depth' ); ?>"><?php _e( 'Depth', $this->textdomain ); ?></label>
							<span class="controlDesc"><?php _e( 'How many levels in the hierarchy of categories included.', $this->textdomain ); ?></span>
							<input type="text" id="<?php echo $this->get_field_id( 'depth' ); ?>" name="<?php echo $this->get_field_name( 'depth' ); ?>" value="<?php echo esc_attr( $instance['depth'] ); ?>" />
						</li>
						<li>
							<label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number', $this->textdomain ); ?></label>
							<span class="controlDesc"><?php _e( 'Sets the number of categories to display.', $this->textdomain ); ?></span>
							<input type="text" id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" value="<?php echo esc_attr( $instance['number'] ); ?>" />
						</li>
						<li>
							<label for="<?php echo $this->get_field_id( 'include' ); ?>"><?php _e( 'Include', $this->textdomain ); ?></label> 
							<span class="controlDesc"></span>
							<select class="widefat" id="<?php echo $this->get_field_id( 'include' ); ?>" name="<?php echo $this->get_field_name( 'include' ); ?>[]" size="4" multiple="multiple">
								<?php foreach ( $terms as $term ) { ?>
									<option value="<?php echo esc_attr( $term->term_id ); ?>" <?php echo ( in_array( $term->term_id, (array) $instance['include'] ) ? 'selected="selected"' : '' ); ?>><?php echo esc_html( $term->name ); ?></option>
								<?php } ?>
							</select>
						</li>
						<li>
							<label for="<?php echo $this->get_field_id( 'exclude' ); ?>"><?php _e( 'Exclude', $this->textdomain ); ?></label> 
							<span class="controlDesc"></span>
							<select class="widefat" id="<?php echo $this->get_field_id( 'exclude' ); ?>" name="<?php echo $this->get_field_name( 'exclude' ); ?>[]" size="4" multiple="multiple">
								<?php foreach ( $terms as $term ) { ?>
									<option value="<?php echo esc_attr( $term->term_id ); ?>" <?php echo ( in_array( $term->term_id, (array) $instance['exclude'] ) ? 'selected="selected"' : '' ); ?>><?php echo esc_html( $term->name ); ?></option>
								<?php } ?>
							</select>
						</li>
					</ul>
				</li>

				<li class="tab-pane <?php if ( $instance['toggle_active'][1] ) : ?>active<?php endif; ?>">
					<ul>
						<li>
							<label for="<?php echo $this->get_field_id( 'style' ); ?>"><?php _e( 'Style', $this->textdomain ); ?></label> 
							<span class="controlDesc"><?php _e( 'With HTML list or none. Some theme generate styles differently.', $this->textdomain ); ?></span>
							<select id="<?php echo $this->get_field_id( 'style' ); ?>" name="<?php echo $this->get_field_name( 'style' ); ?>">
								<?php foreach ( $style as $option_value => $option_label ) { ?>
									<option value="<?php echo esc_attr( $option_value ); ?>" <?php selected( $instance['style'], $option_value ); ?>><?php echo esc_html( $option_label ); ?></option>
								<?php } ?>
							</select>
						</li>
						<li>
							<label for="<?php echo $this->get_field_id( 'icon_height' ); ?>"><?php _e( 'Thumbnail Height & Width', $this->textdomain ); ?></label>
							<span class="controlDesc"><?php _e( 'The categories image height and width size in pixels units.', $this->textdomain ); ?></span>
							<input type="text"  id="<?php echo $this->get_field_id( 'icon_height' ); ?>" name="<?php echo $this->get_field_name( 'icon_height' ); ?>" value="<?php echo esc_attr( $instance['icon_height'] ); ?>" />
							<input type="text" id="<?php echo $this->get_field_id( 'icon_width' ); ?>" name="<?php echo $this->get_field_name( 'icon_width' ); ?>" value="<?php echo esc_attr( $instance['icon_width'] ); ?>" />
						</li>
						<li>
							<label for="<?php echo $this->get_field_id( 'show_thumbnail' ); ?>">
							<input class="checkbox" type="checkbox" <?php checked( $instance['show_thumbnail'], true ); ?> id="<?php echo $this->get_field_id( 'show_thumbnail' ); ?>" name="<?php echo $this->get_field_name( 'show_thumbnail' ); ?>" /> <?php _e( 'Show thumbnail?', $this->textdomain ); ?></label>
						</li>
						<li>
							<label for="<?php echo $this->get_field_id( 'show_child_thumbnail' ); ?>">
							<input class="checkbox" type="checkbox" <?php checked( $instance['show_child_thumbnail'], true ); ?> id="<?php echo $this->get_field_id( 'show_child_thumbnail' ); ?>" name="<?php echo $this->get_field_name( 'show_child_thumbnail' ); ?>" /> <?php _e( 'Show child thumbnail?', $this->textdomain ); ?></label>
						</li>
						<li>
							<label for="<?php echo $this->get_field_id( 'show_title' ); ?>">
							<input class="checkbox" type="checkbox" <?php checked( $instance['show_title'], true ); ?> id="<?php echo $this->get_field_id( 'show_title' ); ?>" name="<?php echo $this->get_field_name( 'show_title' ); ?>" /> <?php _e( 'Show title', $this->textdomain ); ?></label>
						</li>
						<li>
							<label for="<?php echo $this->get_field_id( 'use_desc_for_title' ); ?>">
							<input class="checkbox" type="checkbox" <?php checked( $instance['use_desc_for_title'], true ); ?> id="<?php echo $this->get_field_id( 'use_desc_for_title' ); ?>" name="<?php echo $this->get_field_name( 'use_desc_for_title' ); ?>" /> <?php _e( 'Use description for title?', $this->textdomain ); ?></label>
						</li>							
						<li>
							<label for="<?php echo $this->get_field_id( 'show_description' ); ?>">
							<input class="checkbox" type="checkbox" <?php checked( $instance['show_description'], true ); ?> id="<?php echo $this->get_field_id( 'show_description' ); ?>" name="<?php echo $this->get_field_name( 'show_description' ); ?>" /> <?php _e( 'Show description?', $this->textdomain ); ?></label>
						</li>
						<li>
							<label for="<?php echo $this->get_field_id( 'hierarchical' ); ?>">
							<input class="checkbox" type="checkbox" <?php checked( $instance['hierarchical'], true ); ?> id="<?php echo $this->get_field_id( 'hierarchical' ); ?>" name="<?php echo $this->get_field_name( 'hierarchical' ); ?>" /> <?php _e( 'Hierarchical?', $this->textdomain ); ?></label>
						</li>
		
						<li>
							<label for="<?php echo $this->get_field_id( 'show_last_update' ); ?>">
							<input class="checkbox" type="checkbox" <?php checked( $instance['show_last_update'], true ); ?> id="<?php echo $this->get_field_id( 'show_last_update' ); ?>" name="<?php echo $this->get_field_name( 'show_last_update' ); ?>" /> <?php _e( 'Show last update?', $this->textdomain ); ?></label>
						</li>
						<li>
							<label for="<?php echo $this->get_field_id( 'show_count' ); ?>">
							<input class="checkbox" type="checkbox" <?php checked( $instance['show_count'], true ); ?> id="<?php echo $this->get_field_id( 'show_count' ); ?>" name="<?php echo $this->get_field_name( 'show_count' ); ?>" /> <?php _e( 'Show count?', $this->textdomain ); ?></label>
						</li>
						<li>
							<label for="<?php echo $this->get_field_id( 'hide_empty' ); ?>">
							<input class="checkbox" type="checkbox" <?php checked( $instance['hide_empty'], true ); ?> id="<?php echo $this->get_field_id( 'hide_empty' ); ?>" name="<?php echo $this->get_field_name( 'hide_empty' ); ?>" /> <?php _e( 'Hide empty?', $this->textdomain ); ?></label>
						</li>						
					</ul>
				</li>

				<li class="tab-pane <?php if ( $instance['toggle_active'][2] ) : ?>active<?php endif; ?>">
					<ul>				
						<li>
							<label for="<?php echo $this->get_field_id('intro_text'); ?>"><?php _e( 'Intro Text', $this->textdomain ); ?></label>
							<span class="controlDesc"><?php _e( 'This option will display addtional text before the widget content and supports HTML.', $this->textdomain ); ?></span>
							<textarea name="<?php echo $this->get_field_name( 'intro_text' ); ?>" id="<?php echo $this->get_field_id( 'intro_text' ); ?>" rows="2" class="widefat"><?php echo $intro_text; ?></textarea>
						</li>
						<li>
							<label for="<?php echo $this->get_field_id('outro_text'); ?>"><?php _e( 'Outro Text', $this->textdomain ); ?></label>
							<span class="controlDesc"><?php _e( 'This option will display addtional text after widget and supports HTML.', $this->textdomain ); ?></span>
							<textarea name="<?php echo $this->get_field_name( 'outro_text' ); ?>" id="<?php echo $this->get_field_id( 'outro_text' ); ?>" rows="2" class="widefat"><?php echo $outro_text; ?></textarea>
						</li>	
						<li>
							<label for="<?php echo $this->get_field_id('customstylescript'); ?>"><?php _e( 'Custom Script & Stylesheet', $this->textdomain );?></label>
							<span class="controlDesc"><?php _e( 'Use this box for custom widget CSS style of javascript. <br />Current widget selector: ', $this->textdomain ); ?><?php echo '<tt>#' . $this->id . '</tt>'; ?></span>
							<textarea name="<?php echo $this->get_field_name( 'customstylescript' ); ?>" id="<?php echo $this->get_field_id( 'customstylescript' ); ?>" rows="4" class="widefat"><?php echo htmlentities($instance['customstylescript']); ?></textarea>
						</li>				
					</ul>
				</li>
				
				<li class="tab-pane <?php if ( $instance['toggle_active'][3] ) : ?>active<?php endif; ?>">
					<ul>
						<li>
							<a href="http://goo.gl/HDhZx"><img class="spimg" src="<?php echo TAXOGRAPHY_URL . 'images/taxography.png'; ?>" alt="" /></a>
							<h3 style="margin-bottom: 3px;"><?php _e( 'Upgrade To Premium Version', $this->textdomain ); ?></h3>
							<span class="controlDesc">
								<?php _e( 'This premium version gives more abilities, features, advanced options and premium supports for displaying taxonomies 
										in a better way. You will get help soon if you have problems with the premium version. Full documentation will let 
										you customize this premium version easily. <br />
										See the full <a href="http://zourbuth.com/plugins/taxography/"><strong>Live Preview</strong></a>.
										<br /><br />
										Premium features:', $this->textdomain ); ?>
							</span>
							
						</li>
						<li>
							<ul>
								<li>
									<strong><?php _e( 'Premium Supports', $this->textdomain ) ; ?></strong>
									<span class="controlDesc"><?php _e( 'A premium supports, helps and documentation.', $this->textdomain ); ?></span>
								</li>
								<li>
									<strong><?php _e( 'All Taxonomies', $this->textdomain ) ; ?></strong>
									<span class="controlDesc"><?php _e( 'Not only supports category, but all your registered taxonomies and post tag, eq. posts from portfolio, testimonial, product etc.', $this->textdomain ); ?></span>
								</li>
								<li>
									<strong><?php _e( 'Quick Edit', $this->textdomain ) ; ?></strong>
									<span class="controlDesc"><?php _e( '
										- Add image with quick form<br />
										- Change or delete image with quick edit<br />
									', $this->textdomain ); ?></span>
								</li>
								<li>
									<strong><?php _e( 'Display Posts', $this->textdomain ) ; ?></strong>
									<span class="controlDesc"><?php _e( 'Display selected category posts via Ajax.', $this->textdomain ); ?></span>
								</li>
								<li>
									<strong><?php _e( 'Advanced Thumbnail', $this->textdomain ) ; ?></strong>
									<span class="controlDesc"><?php _e( '
										- Child thumbnail<br />
										- Default thumbnail for no image.
									', $this->textdomain ); ?></span>
								</li>
								<li>
									<strong><?php _e( 'Shortcode', $this->textdomain ) ; ?></strong>
									<span class="controlDesc"><?php _e( 'Shortcode to display widget in your content.', $this->textdomain ); ?></span>
								</li>
								<li>
									<strong><?php _e( 'And more', $this->textdomain ) ; ?></strong>...
									<span class="controlDesc"><?php _e( '
										- Cut long category description<br />
										- Grid display, multicolumn category<br />
										- Widget Background and Title Icon<br />
										- Exclude category-tree from the results.<br />
										- Display only categories children of the selected category.<br />
										- Display from searched categories<br />
										- Category feed link and image<br />
										- PHP function for using in template
									', $this->textdomain ); ?></span>
								</li>
							</ul>
						</li>						
						<li>
							<style type="text/css">
								.spimg { 
									border: 1px solid #DDDDDD;
									border-radius: 2px 2px 2px 2px;
									float: right;
									padding: 4px;
									margin-left: 8px;
								}
								.spimg:hover { 
									border: 1px solid #cccccc;
								}
								.wp-core-ui .btnremium { 
									border-color: #CCCCCC;
									height: auto;
									margin-top: 9px;
									padding-bottom: 0;
									padding-right: 0;
								}
								.wp-core-ui .btnremium span {
									background: none repeat scroll 0 0 #FFFFFF;
									border-left: 1px solid #F2F2F2;
									display: inline-block;
									font-size: 18px;
									line-height: 25px;
									margin-left: 9px;
									padding: 0 9px;
									border-radius: 0 3px 3px 0;
								}
							</style>							
							<a class="button btnremium" href="http://goo.gl/9iAd0">Get Premium<span>$5</span></a>
						</li>
					</ul>
				</li>
			</ul>
		</div>
			<?php
	}
}
?>