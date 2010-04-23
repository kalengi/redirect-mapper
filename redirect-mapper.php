<?php
/*
Plugin Name: Redirect Mapper 
Plugin URI: http://www.dennisonwolfe.com/
Description: The  Redirect Mapper plugin provides a visiual interface to aid the  mapping of old links to new links.
Version: 1.0.0
Author: Dennison+Wolfe Internet Group
Author URI: http://www.dennisonwolfe.com/
*/

/*  Copyright 2009  Dennison+Wolfe Internet Group  (email : tyler@dennisonwolfe.com)

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


if ( is_admin() ) {
	//plugin activation
	add_action('activate_redirect-mapper/redirect-mapper.php', 'redirmap_init');
	//settings menu
	add_action('admin_menu', 'redirmap_tools_menu');
	//load css
	add_action('admin_head', 'redirmap_load_stylesheets');
	//load js
	add_action('wp_print_scripts', 'redirmap_load_scripts' );
	//ajax handling
	add_action('wp_ajax_redirmap_action', 'redirmap_ajax_callback');
	
}
else{
	//load css
	add_action('wp_head', 'redirmap_load_stylesheets');
	
}

//ajax handling
function redirmap_ajax_callback(){
	global $wpdb;
	
	//check_ajax_referer( 'find-posts' );

	if ( empty($_GET['s']) )
		exit;

	$what = 'post';
	$s = stripslashes($_GET['s']);
	preg_match_all('/".*?("|$)|((?<=[\\s",+])|^)[^\\s",+]+/', $s, $matches);
	$search_terms = array_map(create_function('$a', 'return trim($a, "\\"\'\\n\\r ");'), $matches[0]);

	$searchand = $search = '';
	foreach( (array) $search_terms as $term) {
		$term = addslashes_gpc($term);
		$search .= "{$searchand}(($wpdb->posts.post_title LIKE '%{$term}%') OR ($wpdb->posts.post_content LIKE '%{$term}%'))";
		$searchand = ' AND ';
	}
	$term = $wpdb->escape($s);
	if ( count($search_terms) > 1 && $search_terms[0] != $s )
		$search .= " OR ($wpdb->posts.post_title LIKE '%{$term}%') OR ($wpdb->posts.post_content LIKE '%{$term}%')";

	$posts = $wpdb->get_results( "SELECT ID, post_title, post_status, post_date FROM $wpdb->posts WHERE post_type = '$what' AND $search ORDER BY post_date_gmt DESC LIMIT 50" );

	$html = ''; 
	if ( ! $posts ){
		$html .= '<li>' . __('No posts found.') . '</li>' . "\r\n";
	}
	else{
		foreach ( $posts as $post ) {
			if($post->post_status == 'publish'){
				$html .= '<li>'; 
				$html .= '<a class="redirmap-search-link" href="' . get_permalink($post->ID) . '">';
				$html .= esc_html( $post->post_title ) . '</a>';
				$html .= '</li>' . "\r\n";
			}
		}
		
	}
	$html .= '||';
	echo $html;
	return true;
}

/* Control Screen*/

function redirmap_tools_menu() {
	add_submenu_page( 'tools.php', 'Redirect Mapper UI', 'Redirect Mapper', 'manage_options', 'redirect-mapper/redirect-mapper-ui.php');
	
	//call register settings function
	add_action( 'admin_init', 'register_redirmap_settings' );
	$plugin = plugin_basename(__FILE__); 
	add_filter( 'plugin_action_links_' . $plugin, 'redirmap_plugin_actions' );
}


/* initialize the plugin settings*/
function redirmap_init() {
	add_option('redirmap_activated', '1');
	
}

/* register settings*/
function register_redirmap_settings() {
	register_setting( 'redirmap_settings', 'redirmap_activated' );
}


/* Add Settings link to the plugins page*/
function redirmap_plugin_actions($links) {
    $settings_link = '<a href="link-manager.php?page=redirect-mapper/redirect-mapper-ui.php">Settings</a>';

    $links = array_merge( array($settings_link), $links);

    return $links;

}

/* Load js files*/
function redirmap_load_scripts() {
	wp_enqueue_script('redirect-mapper', WP_PLUGIN_URL . '/' . plugin_basename( dirname(__FILE__) ) . '/redirect-mapper.js', array('jquery'), '1.0');
	wp_enqueue_script('jquery.simplemodal', WP_PLUGIN_URL . '/' . plugin_basename( dirname(__FILE__) ) . '/jquery.simplemodal-1.3.5.js', array('jquery'), '1.3.5');
}

/* Load css files*/
function redirmap_load_stylesheets() {
	$style_file = plugins_url('redirect-mapper/redirect-mapper.css');
	
	echo '<link rel="stylesheet" type="text/css" href="' . $style_file . '" />' . "\r\n";
	
}


?>