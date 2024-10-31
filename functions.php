<?php
function w4_delete_tabset( $tabset_id){
	global $wpdb;

	$tabset_id = (int) $tabset_id;
	
	if( !$tabset_id)
		return false;

		
	if( !w4_get_tabset($tabset_id))
		return false;
		
		global $wpdb;
		$del = $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->tabset WHERE tabset_id = %d", $tabset_id ));
		
		if( !$del)
			return false;
		
		return $tabset_id;
	}
	
// Save options
function w4_save_tabset( $options = array()){
	global $wpdb;

	if( !is_array($options))
		$options = array();
		
	extract( $options );
	$tabset_id = (int) $tabset_id;
	$tabset_title = sanitize_title( $tabset_title );
		
	if ( $tabset_id){

		$update = true;
		$old_options = w4_get_tabset( $tabset_id, 'tabset_option');
		$tabset_option = maybe_serialize( stripslashes_deep( $tabset_option ));
		$tabset_title = w4_tabset_unique_title( $tabset_title, $tabset_id);
			
		$options = compact( 'tabset_option', 'tabset_title');
		$result = $wpdb->update( $wpdb->tabset, $options, array( 'tabset_id' => $tabset_id));
	}
	else{
		$update = false;

		$tabset_option = w4_tabset_default_options();

		$options['tabset_option'] = maybe_serialize( stripslashes_deep( $tabset_option ));
			
		$result = $wpdb->insert( $wpdb->tabset, $options );
		$tabset_id = $wpdb->insert_id;
	}
		
	if( !isset( $tabset_title ) || empty( $tabset_title )){
		$tabset_title = w4_tabset_unique_title( 'tabset-'.$tabset_id, $tabset_id );
		$wpdb->update( $wpdb->tabset, compact( 'tabset_title' ), array( 'tabset_id' => $tabset_id));
	}
		
	return $tabset_id;
}

function w4_tabset_unique_title( $title, $id ){
	global $wpdb;
	
	$tabset_title_check = $wpdb->get_var( 
	$wpdb->prepare("SELECT tabset_id FROM $wpdb->tabset WHERE tabset_title = %s AND tabset_id != %s LIMIT 1" , $title, $id));

	if ( $tabset_title_check ){
		$suffix = 2;
		while($tabset_title_check) {
	
			$alt_title = $title . "-$suffix";
			$tabset_title_check = $wpdb->get_var( 
			$wpdb->prepare("SELECT tabset_id FROM $wpdb->tabset WHERE tabset_title = %s AND tabset_id != %s LIMIT 1" , $alt_title, $id));
			$suffix++;
		}
		$title = $alt_title;
	}
	return $title;
}

function w4_get_tabset( $tabset_id_or_class = '', $col = null){
	global $wpdb;
		
	if( is_numeric( $tabset_id_or_class )){
		$field = 'tabset_id';
		$tabset_id_or_class = (int) $tabset_id_or_class;
		
	}else{
		$field = 'tabset_title';
		$tabset_id_or_class = sanitize_html_class( $tabset_id_or_class);
	}

	$query = $wpdb->prepare( "SELECT * FROM  $wpdb->tabset WHERE $field = %d", $tabset_id_or_class );
		
	if ( !$row = $wpdb->get_row( $query, ARRAY_A ))
		return false;
		
	$row['tabset_option'] = maybe_unserialize( $row['tabset_option'] );
	$row = (array) $row;
		
	if(isset($col) && in_array($col, array_keys($row)))
		return $row[$col];
		
	return $row;
}

function w4_tabset_stylesheets(){
	global $wpdb;

	$query = $wpdb->prepare( "SELECT * FROM  $wpdb->tabset ORDER BY tabset_id ASC" );
		
	if ( !$tabsets = $wpdb->get_results( $query, ARRAY_A ))
		$tabsets = array();

	foreach( $tabsets as $tabset){

		if( empty( $tabset['tabset_title'] ))
			continue;

		$tabset['tabset_option'] = maybe_unserialize( $tabset['tabset_option']);
		w4_tabset_generate_stylesheet( $tabset );
	}		
}

function w4_tabset_stylesheet_default(){

	$options = w4_tabset_default_options();

	extract( $options );
		
	$style = ".w4_content_tabset{
	margin:10px 0px;border:none;
}
.w4_content_tabset .tab_content{
	padding:10px;
}
.w4_content_tabset .tab_content_wrapper{
	position:relative;
	overflow:hidden;
	border-bottom:1px solid {$tabset_content_border_color};
	padding:0 0 1px 0;
}
.w4_content_tabset div.tab_container{
	padding:0;
	margin:0;
	border-width:0 0 1px 0;
	border-bottom:1px solid {$tabset_content_border_color};
	background-color:{$tabset_content_bg_color};
}
.w4_content_tabset div.ui-tabs-hide{
	display:none;
}
.w4_content_tabset ul.tab_links{
	overflow:hidden;
	padding:0;
	margin:0 !important;
	list-style-type:none;
	list-style-position:outside;
	text-align:left;
	-moz-border-radius:0px;
	border:none;
	background:none;
}
.w4_content_tabset ul.tab_links li{
	display:inline;
	float:left;
	position:relative;
	list-style-type:none;
	list-style-position:outside;
	padding:0;
	margin:0 2px 0 0;
	border:none;
	background:none;
}
.w4_content_tabset ul.tab_links li a{
	color:{$tabset_menu_text_color};
	text-decoration:none;
	font-family:Geneva, Arial, Helvetica, sans-serif;
	font-size:{$tabset_menu_font_size};
	line-height:normal;
	font-weight:bold;
	padding:7px 15px 5px 15px;
	display:block;
	position:relative;
	background-color:{$tabset_menu_bg_color};
	border:1px solid #{$tabset_menu_bg_color};
	-moz-border-radius-topleft:5px;
	-moz-border-radius-topright:5px;
}
.w4_content_tabset ul.tab_links li a:hover,
.w4_content_tabset ul.tab_links li a.active,
.w4_content_tabset ul.tab_links li.ui-tabs-selected a{
	background-color:{$tabset_menu_bg_color_hover};
	color:{$tabset_menu_text_color_hover};
}
.ui-tabs-hide, ui-tabs-panel{
	display:none;
}
.ui-widget-content{
	display:block;
}";
		$stylesheet = "\n<!--W4 content tabset stylesheet starts-->\n<style type=\"text/css\">\n";
		$stylesheet .= $style;
		$stylesheet .= "\n</style>\n<!-- W4 content tabset stylesheet ends-->\n\n";
		echo $style;
	}

function w4_tabset_generate_stylesheet( $style = '' ){
		$style_class = '.' . $style['tabset_title'];
		$tabset_option = $style['tabset_option'];
		extract( $tabset_option );
		
		$_stylesheet ="$style_class .tab_content_wrapper{
border-bottom:1px solid {$tabset_content_border_color};
}
$style_class div.tab_container{
	border-bottom:1px solid {$tabset_content_border_color};
	background-color:{$tabset_content_bg_color};
}
$style_class ul.tab_links li a{
	color:{$tabset_menu_text_color};
	font-size:{$tabset_menu_font_size};
	background-color:{$tabset_menu_bg_color};
}
$style_class ul.tab_links li a:hover,
$style_class ul.tab_links li a.active,
$style_class ul.tab_links li.ui-tabs-selected a{
	background-color:{$tabset_menu_bg_color_hover};
	color:{$tabset_menu_text_color_hover};
}";

		$stylesheet = "\n<style type=\"text/css\">\n";
		$stylesheet .= $_stylesheet;
		$stylesheet .= "\n</style>\n";
		echo $_stylesheet;
	}


function w4_tabset_default_options(){
	
	if( get_option( 'w4_content_tabset_default') && w4_get_tabset( get_option('w4_content_tabset_default')))
		return w4_get_tabset( get_option('w4_content_tabset_default'), 'tabset_option');

	$default_options 	= array(
			'tabset_menu_bg_color' 				=> '#67A54B',
			'tabset_menu_text_color'			=> '#FFFFFF',
			'tabset_menu_bg_color_hover'		=> '#EEEEEE',
			'tabset_menu_text_color_hover' 		=> '#333333',

			'tabset_menu_font_size'				=> '13px',

			'tabset_content_bg_color'			=> '#EEEEEE',
			'tabset_content_border_color'		=> '#333333',

			'tabset_effect'						=> '1',
			'tabset_event'						=> 'on_click'
		);

	if( get_option( 'w4_content_tabset_default' ))
		delete_option('w4_content_tabset_default');

	return $default_options;
}
?>