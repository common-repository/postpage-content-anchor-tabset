<?php
/*
Plugin Name: W4 content tabset
Plugin URI: http://w4dev.com/w4-plugin/post-page-custom-tabset-shortcode/
Description: Lets you embed tabset in your post or page content area. Also capable to show your custom field values in a post or page content area by your selection.
Version: 1.4.3
Author: sajib1223, Shazzad Hossain Khan
Author URI: http://w4dev.com/
*/

/*  Copyright 2011  Shazzad Hossain Khan  (email : sajib1223@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

define( 'W4CT_DIR', plugin_dir_path(__FILE__));
define( 'W4CT_URL', plugin_dir_url(__FILE__));
define( 'W4CT_BASENAME', plugin_basename( __FILE__ ));
define( 'W4CT_VERSION', '1.4.3' );
define( 'W4CT_NAME', 'W4 content tabset' );
define( 'W4CT_SLUG', strtolower(str_replace(' ', '-', W4CT_NAME )));

global $wpdb;
$w4_tabset_table = $wpdb->prefix . 'content_tabset';

if( file_exists( W4CT_DIR . '/functions.php'))
	include( W4CT_DIR . '/functions.php');

if( is_admin() && file_exists( W4CT_DIR . '/admin.php'))
	include( W4CT_DIR . '/admin.php');

// Filters
add_filter('widget_text', 'w4_tabset_replace_callback' );

add_filter('the_content', 'w4_tabset_replace_callback' );
add_filter('the_excerpt', 'w4_tabset_replace_callback' );
add_filter('the_content', 'w4_tabset_custom_field_replace_callback' );
add_filter('the_excerpt', 'w4_tabset_custom_field_replace_callback' );

function w4_tabset_replace( $matches ){
	$pattern = '/\[\s*tabs\s*tabname\s*=\s*[\'\"](.*?)[\'\"]\s*\]?(.*?)\[\s*\/\s*tabs\s*\]/sm' ;
	if( !preg_match_all( $pattern, $matches[2], $tabs, PREG_SET_ORDER ))
		return false;

	$stylesheet = '';
	$tabset_prefix = '';
	$tabset_style = '';
	
	if( $matches[1] ){
		extract( shortcode_parse_atts($matches[1]), EXTR_SKIP);

		if( isset( $class ) && !empty( $class ))
			$style = $class;

		if( isset( $style ) && !empty( $style )){

			$style = sanitize_title( $style );
			$tabset_style = $style;

			if( w4_get_tabset( $style ))
				$options = w4_get_tabset( $style, 'tabset_option');
		}

		if( isset( $id ))
			$tabset_prefix = 'tabset-'.$id.'-';

		elseif( isset( $ID ))
			$tabset_prefix = 'tabset-'.$ID.'-';
	}

	if( empty( $options ))
		$options = w4_tabset_default_options();

	$tabset_effect = $options['tabset_effect'];
	$tabset_event = $options['tabset_event'];

	$i = 0 ;
	$tab_links = '';
	foreach( $tabs as $tab){
		$i++;
		$tab_name = $tab[1] ;
		$tab_id = $tabset_prefix . sanitize_title( $tab_name."-".$i );
		
		$tab_links .= "\t\t<li><a title=\"$tab_name\" class=\"$tab_id\" href=\"#$tab_id\">$tab_name</a></li>\n";
		$tabs_content[$tab_id] = $tab[2];
	}

	$content = '';
	foreach( $tabs_content as $tab_key => $tab_cont ){
		$content .= "\t\t<div class=\"tab_container\" id=\"$tab_key\">\n";
		$content .= "\t\t\t<div class=\"tab_content\">$tab_cont</div>\n";
		$content .= "\t\t</div>\n" ;
	}
	
	if( !$content )
		return false;
	
	$content = $stylesheet . $content;
	$class 		= "w4_content_tabset tabset_effect_{$tabset_effect} $tabset_event $tabset_style";
	$links 		= "\t<ul class=\"tab_links\">\n$tab_links\t</ul>\n";
	$content 	= "\n\n<div class=\"$class\">\n$links\t<div class=\"tab_content_wrapper\">\n$content\t</div>\n</div>\n<!-- W4 Content Tabset @ http://w4dev.com/w4-plugin/post-page-custom-tabset-shortcode/-->\n\n";

	return $content;
}

//Retrive and Replace the tabset shortcode
function w4_tabset_replace_callback($text){
	$pattern = '/\[\s*tabset(.*?)\](.*?)\[\s*\/\s*tabset\s*\]/sm';
	return preg_replace_callback($pattern,'w4_tabset_replace',$text);
}

//Show your custom field value in post/page
function w4_tabset_custom_field_replace( $matches ) {
	global $wp_query;
	if( isset( $GLOBALS['post'] ))
		$post = $GLOBALS['post'] ;
	
	$post = $wp_query->get_queried_object();

	if( !$matches[1] )
		return false ;
	
	
	if( $custom = get_post_meta( $post->ID, $matches[1], true ))
		return $custom ;
}

function w4_tabset_custom_field_replace_callback($text){
	$pattern = '/\[\s*custom\s*key\s*=\s*[\'\"](.*?)[\'\"]\s*\]/sm' ;
	return preg_replace_callback( $pattern, 'w4_tabset_custom_field_replace', $text ) ;
}

function w4_tabset_loaded(){
	global $wpdb;
	
	$wpdb->tabset = $wpdb->prefix . 'content_tabset';

	if( is_admin() && isset( $_GET['page'] ) && $_GET['page'] == 'w4-content-tabset' ){
		wp_enqueue_style( 'w4_tabset_admin_css', W4CT_URL . 'admin_style.css', '', W4CT_VERSION );
		wp_enqueue_script( 'color_picker', W4CT_URL . 'colorpicker/jscolor.js', array( 'jquery','jquery-ui-core' ), W4CT_VERSION, true);
	}

	add_action( 'wp_head', 'w4_tabset_head' );
	wp_enqueue_script( 'w4_tabset_js', W4CT_URL . 'js.js', array( 'jquery' , 'jquery-ui-core', 'jquery-ui-tabs' ), W4CT_VERSION, true );
}
add_action( 'plugins_loaded', 'w4_tabset_loaded');

function w4_tabset_head(){
	echo '<link rel="stylesheet" type="text/css" media="screen" href="' . W4CT_URL . 'style.php" />';
}
?>