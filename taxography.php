<?php
 /*
	Plugin Name: Taxography - Graphical Taxonomy
	Plugin URI: http://zourbuth.com/?p=871
	Description:  An advance widget that gives you total control over the output of your taxonomy. Support multiwidget and taxonomy images.
	Version: 0.0.3
	Author: zourbuth
	Author URI: http://zourbuth.com
	License: Under GPL2
 
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


/* Initializes the plugin and it's features
 * @since 0.0.1
 **/
add_action( 'plugins_loaded', 'taxography_plugins_loaded' );


/* Initializes the plugin and it's features
 * @since 0.0.1
 **/
function taxography_plugins_loaded() {

	// Define constant
	define( 'TAXOGRAPHY_VERSION', '0.0.3' );
	define( 'TAXOGRAPHY_DIR', plugin_dir_path( __FILE__ ) );
	define( 'TAXOGRAPHY_URL', plugin_dir_url( __FILE__ ) );
	
	require_once( TAXOGRAPHY_DIR . 'taxography-utility.php' );
	require_once( TAXOGRAPHY_DIR . 'taxography-images.php' );
	require_once( TAXOGRAPHY_DIR . 'taxography-walker.php' );	
	require_once( TAXOGRAPHY_DIR . 'taxography-shortcode.php' );
	
	load_plugin_textdomain( 'taxography', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	
	// Loads and registers the new widgets
	add_action( 'widgets_init', 'taxography_load_widgets' );
}


/* Register the extra widgets. 
 * Each widget is meant to replace or extend the current default
 * @since 0.0.1
 **/
function taxography_load_widgets() {
	require_once( TAXOGRAPHY_DIR . 'taxography-widget.php' );
	register_widget( 'Taxonomy_Image_Widget' );
}
?>