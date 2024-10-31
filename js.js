jQuery('#w4_tabset_widget').tabs();

var effect1 = jQuery('.tabset_effect_1' ).tabs() ;
var effect2 = jQuery('.tabset_effect_2' ).tabs() ;
var effect3 = jQuery('.tabset_effect_3' ).tabs() ;

effect2.tabs( "option", "fx", { height: 'toggle', duration : 300 }) ;
effect3.tabs( "option", "fx", { opacity : 'toggle', duration : 400 }) ;

var events1 = jQuery('.on_click' ).tabs() ;
var events2 = jQuery('.on_hover' ).tabs() ;

jQuery( ".on_click" ).tabs( "option", "event", 'click' );
jQuery( ".on_hover" ).tabs( "option", "event", 'mouseover' );