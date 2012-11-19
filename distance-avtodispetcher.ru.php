<?php
/*
Plugin Name: Distance calculator (Avtodispetcher.Ru)
Plugin URI: http://www.avtodispetcher.ru/distance/export/wordpress/
Description: Display a distance calculator.
Version: 1.0.0
Author: Ilya Guk
Author URI: http://www.avtodispetcher.ru/

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St - 5th Floor, Boston, MA  02110-1301, USA.

*/

// Include plugins files

require_once('avtodispetcher_admin.php');
require_once('avtodispetcher_widget.php');

// Hooks

register_activation_hook(__FILE__, 'activate_widget'); 
register_deactivation_hook( __FILE__, 'autodisp_remove' );
add_action('plugins_loaded', 'language_init');
add_action( 'wp_trash_post', 'trash_post_menu_item' );
add_action( 'revive_trashed_post', 'revive_trashed_post_menu_item' );

// Localization

function language_init(){
	load_plugin_textdomain('distance-calculator', false, basename( dirname( __FILE__ ) ) . '/languages/' );
}

// Add item to the menu

function add_new_menu_item($autodisp_page_id) {
	
	global $wpdb;
	
	//let's get the first menu
	$menu_id = $wpdb->get_var("SELECT t1.term_id FROM $wpdb->terms t1 JOIN $wpdb->term_taxonomy t2 ON ( t1.term_id = t2.term_id ) WHERE t2.taxonomy = 'nav_menu' LIMIT 0,1");
	
	if ( $menu_id ) {
		$item_id = wp_update_nav_menu_item($menu_id, 0, array(
				'menu-item-object-id' 	=> $autodisp_page_id,
				'menu-item-object'		=> 'page',
				'menu-item-type'     	=> 'post_type',
				'menu-item-status'		=> 'publish'
			)
		);
		
		update_option('auto_disp_menu_item_id', array( 'menu' => $menu_id, 'item' => $item_id ) );
	}
}

// Trash item in the menu in "Simple mode"

function trash_post_menu_item( $post_id ) {
	$the_page_id = get_option( 'autodisp_page_id' );
	$menu_and_item = get_option('auto_disp_menu_item_id');
	if ( $the_page_id && isset( $menu_and_item['item'] ) && ( $the_page_id == $post_id ) )
		wp_trash_post( $menu_and_item['item'] );
}

// Restore item in the menu in "Advanced mode"

function revive_trashed_post_menu_item( $post_id ) {
	$menu_and_item = get_option('auto_disp_menu_item_id');
	if ( isset( $menu_and_item['item'] ) && isset( $menu_and_item['menu'] ) )
		wp_update_nav_menu_item( $menu_and_item['menu'], $menu_and_item['item'], array(
				'menu-item-object-id' 	=> $post_id,
				'menu-item-object'		=> 'page',
				'menu-item-type'     	=> 'post_type',
				'menu-item-status'		=> 'publish'
			)
		);
}

// Add widget to the sidebar

function activate_widget()
{
	$sidebars = $final_sidebars = get_option('sidebars_widgets');
	unset($sidebars['wp_inactive_widgets']);
	unset($sidebars['array_version']);
	
	$widget_exists = false;
	$widget_id = DistanceAvtodispetcherRu_Widget::WIDGET_ID;
	
	//let's iterate trough each sidebar to find if there was our widget before
	foreach ( $sidebars as $sidebar ) 
	{
		$matched = preg_grep("/^" . $widget_id . ".*/", $sidebar);
		if ( ! empty ( $matched ) ) {
			$widget_exists = true;
			break;
		}
	}

	// if we have sidebars and the widget didn't exist
	if ( ! empty($sidebars) && ! $widget_exists ) {
		reset($sidebars);
		$first_sidebar = key($sidebars);
		$final_sidebars[$first_sidebar][] = $widget_id . '-1';
		update_option('sidebars_widgets', $final_sidebars);
		
		$widget = get_option($widget_id);
		$widget[1] = array();
		update_option('widget_' . $widget_id, $widget);
	}
}

function autodisp_advanced_form_install() {

    $autodisp_page_title = __('Distance calculator','distance-calculator');
    $autodisp_page_name = 'distance-calculator';

	//look in the db if the previous post exists and save it's id to some temp variable
	$temp_page_id = get_option('autodisp_page_id');
	
    delete_option("autodisp_page_id");
    add_option("autodisp_page_id", '0', '', 'yes');

	$the_page = null;
	
	// let's assume page has been renamed, let's try to get it by id
	if (  $temp_page_id ) 
	{
		$temp_page_id = (int) $temp_page_id;
		$the_page = get_page ( $temp_page_id );
	}
			

    if ( ! $the_page ) {

        // Create post object
        $_p = array();
        $_p['post_title'] = $autodisp_page_title;
		$_p['post_name'] = $autodisp_page_name;
        $_p['post_content'] = "<a href='http://www.avtodispetcher.ru/distance/' id='avtd-embed-link'>" . __('Distance calculator by Avtodispetcher.Ru','distance-calculator') . "</a><script type='text/javascript' src='http://www.avtodispetcher.ru/distance/export/frame.js'></script>";
        $_p['post_status'] = 'publish';
        $_p['post_type'] = 'page';
        $_p['comment_status'] = 'closed';
        $_p['ping_status'] = 'closed';
        $_p['post_category'] = array(1); // the default 'Uncatrgorised'

        // Insert the post into the database
        $the_page_id = wp_insert_post( $_p );
		
		add_new_menu_item( $the_page_id );

    } else {
	
        // the plugin may have been previously active and the page may just be trashed...
        $the_page_id = $the_page->ID;

        //make sure the page is not trashed...
        $the_page->post_status = 'publish';
        $the_page_id = wp_update_post( $the_page );

    }

    delete_option( 'autodisp_page_id' );
    add_option( 'autodisp_page_id', $the_page_id );
	
}

function autodisp_advanced_form_activate() {
    $the_page_id = get_option( 'autodisp_page_id' );
	
	// if there is no page or the object has been removed
    if ( ! $the_page_id )  {
		autodisp_advanced_form_install();
	} else {
		if ( $the_page = get_page( $the_page_id ) ) {
			$the_page->post_status = 'publish';
			wp_update_post( $the_page );
			do_action('revive_trashed_post', $the_page_id);
		} else {
			autodisp_advanced_form_install();
		}
	}
		
}

function autodisp_advanced_form_deactivate() {
	$the_page_id = get_option( 'autodisp_page_id' );
	
	// if there is the page let's move it to the trash
    if (  $the_page_id && $the_page = get_page( $the_page_id ) )
		wp_delete_post( $the_page_id );
}


function autodisp_remove() {
	$the_page_id = get_option( 'autodisp_page_id' );
	// force to delete post
	wp_delete_post( $the_page_id , true );
	delete_option( 'auto_disp_menu_item_id' );
    delete_option( 'autodisp_page_id' );
	delete_option( 'dc_type' );
}
