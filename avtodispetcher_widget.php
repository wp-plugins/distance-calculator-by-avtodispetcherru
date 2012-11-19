<?php 

class DistanceAvtodispetcherRu_Widget extends WP_Widget {

	const WIDGET_ID = 'distance_avtodispetcher_ru_widget';
	
    function DistanceAvtodispetcherRu_Widget() {
        $widgetSettings     = array (
										'classname'     => 'DistanceAvtodispetcherRu_Widget',
										'description'   => 'Display a distance calculator in a sidebar widget'
                                    );

        $controlSettings    = array (
										'width'         => 400,
										'height'        => 400,
										'id_base'       => self::WIDGET_ID
                                    );

        $this->WP_Widget('distance_avtodispetcher_ru_widget', 'Distance Calculator Widget', $widgetSettings, $controlSettings);

        
    }

    // Displaying the widget on the blog
    function widget($args, $instance) {
        extract($args);

		echo $before_widget;
		echo $before_title . __('Distance calculator', 'distance-calculator') . $after_title;
		echo get_form();
		echo $after_widget;
    }
	
}

//Registering the widget 
function distance_avtodispetcher_ru_widget() {
    register_widget('DistanceAvtodispetcherRu_Widget');
}

function get_form()
{
	if ( get_option('dc_type') == 'advanced' )
		return get_advanced_form();
	else
		return get_simple_form();
}

function get_simple_form() {
	return '<form action="http://www.avtodispetcher.ru/distance/" method="get" accept-charset="UTF-8" target="_blank">
				<table border="0" cellspacing="0" cellpadding="4">
					<tr><td>' . __('From', 'distance-calculator') . ' </td><td><input type="text" size="20" name="from"></td></tr>
					<tr><td>' . __('To', 'distance-calculator') . ' </td><td><input type="text" size="20" name="to"></td></tr>
					<tr><td colspan="2" align="center"><input type="submit" value="' . __('Calculate', 'distance-calculator') .'"></td></tr>
				</table>
			</form>';
}

function get_advanced_form() {
		$autodisp_page_id = (int) get_option( 'autodisp_page_id' );
		
		//let's check permalink structure and based on that use needed query sign
		global $wp_rewrite;
		if ($wp_rewrite->permalink_structure == '')
			$query_sign = '&';
		else 
			$query_sign = '?';
		
        return "<a href='http://www.avtodispetcher.ru/distance/' id='avtd-mini-embed-link'> " . __('Distance calculator by Avtodispetcher.Ru','distance-calculator') . "</a>" .
			 "<script type='text/javascript'>(function(d, t, a){
			var s = d.createElement(t);
			s.async = true;
			s.src = 'http://www.avtodispetcher.ru/distance/export/mini.js';
			s.onload = s.onreadystatechange = function(){
				var st = this.readyState;
				if(st && st != 'complete' && st != 'loaded') {return;}
				avtodispetcher_distance_miniform.setSubmitActionUrlPattern(a);
				this.onload = this.onreadystatechange = null;
			}
			var x = d.getElementsByTagName(t)[0];
			x.parentNode.insertBefore(s,x);
		})(document, 'script', '" . get_permalink($autodisp_page_id) . $query_sign . "from={from}&to={to}&inter_city={inter_city}');</script>";
}

// Adding the functions to the WP widget
add_action('widgets_init', 'distance_avtodispetcher_ru_widget');

?>