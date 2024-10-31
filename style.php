<?php
$root = $_SERVER["DOCUMENT_ROOT"];
$self = $_SERVER["SCRIPT_NAME"];

for ( $i = 0; $i < 4; $i++ )
    $self = substr( $self, 0, -strlen( strrchr( $self, '/')));

if ( file_exists( $root . $self . '/wp-load.php')) {
    require_once $root . $self . '/wp-load.php';

} else {
    require_once( '../../../wp-load.php' );
}
header("Content-type: text/css");

if( !defined( 'ABSPATH' )):
	echo '/* Could not load the stylesheet */';
	exit();
endif;

w4_tabset_stylesheet_default();
w4_tabset_stylesheets();
?>