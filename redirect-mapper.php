<?php
/*
Plugin Name: Related Links Menu 
Plugin URI: http://www.dennisonwolfe.com/
Description: The  Related Links Menu plugin places links from selected Link Categories into a sidebar.
Version: 1.2.1
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
	add_action('activate_related-links-menu/related-links-menu.php', 'relinkme_init');
	//settings menu
	add_action('admin_menu', 'relinkme_settings_menu');
	//load css
	add_action('admin_head', 'relinkme_load_stylesheets');
	//load js
	add_action('wp_print_scripts', 'relinkme_load_scripts' );
	//hook into Page Edit screen
	add_action('admin_menu', 'relinkme_add_custom_box');
	//hook into metabox processing to perform ordering
	add_filter('do_meta_boxes', 'relinkme_order_meta_boxes', 10, 3);
	//hook into Page/Post save action
	add_action('save_post', 'relinkme_save_postdata');
}
else{
	//load css
	add_action('wp_head', 'relinkme_load_stylesheets');
	
}


/* Configuration Screen*/

function relinkme_settings_menu() {
	add_submenu_page( 'link-manager.php', 'Related Links Menu Settings', 'Related Links', 'manage_options', 'related-links-menu/related-links-menu-options.php');
	
	//call register settings function
	add_action( 'admin_init', 'register_relinkme_settings' );
	$plugin = plugin_basename(__FILE__); 
	add_filter( 'plugin_action_links_' . $plugin, 'relinkme_plugin_actions' );
}


/* initialize the plugin settings*/
function relinkme_init() {
	add_option('relinkme_show_widget', '1');
	add_option('relinkme_default_category', '2'); //ID of blogroll
	$relinkme_selected_categories = array('blogroll' => '2'); //assumption is this category is typically availa ble at this ID
	add_option('relinkme_selected_categories', $relinkme_selected_categories);
	add_option('relinkme_show_category_title', '1');
}

/* register settings*/
function register_relinkme_settings() {
	register_setting( 'relinkme_settings', 'relinkme_show_widget' );
	register_setting( 'relinkme_settings', 'relinkme_default_category' );
	register_setting( 'relinkme_settings', 'relinkme_selected_categories' );
	register_setting( 'relinkme_settings', 'relinkme_show_category_title' );
	register_setting( 'relinkme_settings', 'relinkme_category_link_order' );
}


/* Add Settings link to the plugins page*/
function relinkme_plugin_actions($links) {
    $settings_link = '<a href="link-manager.php?page=related-links-menu/related-links-menu-options.php">Settings</a>';

    $links = array_merge( array($settings_link), $links);

    return $links;

}

/* Load js files*/
function relinkme_load_scripts() {
	wp_enqueue_script('inline-links-list', WP_PLUGIN_URL . '/' . plugin_basename( dirname(__FILE__) ) . '/inline-links-list.js', array('jquery', 'jquery-ui-core', 'jquery-ui-sortable'), '1.0');
	wp_enqueue_script('related-links-menu', WP_PLUGIN_URL . '/' . plugin_basename( dirname(__FILE__) ) . '/related-links-menu.js', array('jquery'), '1.0');
}

/* Load css files*/
function relinkme_load_stylesheets() {
	$style_file = plugins_url('related-links-menu/related-links-menu.css');
	
	echo '<link rel="stylesheet" type="text/css" href="' . $style_file . '" />' . "\r\n";
	
}


/* Add a custom box to the Page Edit admin screen*/
function relinkme_add_custom_box() {
	 add_meta_box( 'relinkme_category_list', 'Related Link Categories', 'relinkme_custom_box_html', 'page', 'side', 'low' );
	// add_meta_box( 'relinkme_category_list', 'Related Link Categories', 'relinkme_custom_box_html', 'post', 'side', 'low' );
	 
}


/* Ordering meta box*/
function relinkme_order_meta_boxes($page, $context, $object) {
    
    if (($context == 'side') && (($page == 'page') || ($page == 'post')) ) {
        // Place meta box  as the  second in order
		relinkme_position_metabox('relinkme_category_list', $page, $context, 1);
    }
    
}


/* Sort meta boxes */
function relinkme_position_metabox($id, $page = 'page', $context = 'side', $position = 1) {
	//handle the recursion
	static $been_here = false;
	
	$metabox_sort_order = get_user_option( "meta-box-order_$page", 0, false );
	
	if(!empty($metabox_sort_order) && (is_array($metabox_sort_order)) && (!empty($metabox_sort_order[$context]))){
		$metaboxes = $metabox_sort_order[$context];
		$metaboxes = explode(',', $metaboxes);
		
		$been_here = false;
		
		if($metaboxes[$position] == $id){
			return;
		}
		
		$flipped = array_flip($metaboxes);
		
		$new_metaboxes = array();
		$old = 0;
		$new = 0;
		$metabox_placed = false;
		
		if(!empty($flipped[$id])){
			if($position >= count($metaboxes)){
				return;
			}
			
			foreach($metaboxes as $metabox){
				if($metaboxes[$old] == $id){
					$old += 1;
				}
				
				if($new == $position){
					$new_metaboxes[$new] = $id;
					$new += 1;
					$metabox_placed = true;
				}
				
				if(!empty($metaboxes[$old])){
					$new_metaboxes[$new] = $metaboxes[$old];
				}
				$old += 1;
				$new += 1;
			}
		}
		else{
			if($position > count($metaboxes)){
				return;
			}
			
			while(1 == 1){
				if($new == $position){
					$new_metaboxes[$new] = $id;
					$new += 1;
					$metabox_placed = true;
				}
				
				if(empty($metaboxes[$old])){
					if($metabox_placed){
						break;
					}
				}
				
				$new_metaboxes[$new] = $metaboxes[$old];
				
				$old += 1;
				$new += 1;
			}
		}
		
		$metabox_sort_order[$context] = implode(',', $new_metaboxes);
		
		$user = wp_get_current_user();
		update_user_option($user->ID, "meta-box-order_$page", $metabox_sort_order);
		
		
		
	}
	else{
		if($been_here){
			return;
		}
		
		global $wp_meta_boxes;
		
		$metaboxes = $wp_meta_boxes[$page][$context];
		
		if(empty($metaboxes)){
			return;
		}
		
		$priorities = array('high', 'core', 'low');
		$sort_list = array();
		foreach($priorities as $priority){
			if(!empty($metaboxes[$priority])){
				$sort_list = array_merge($sort_list, array_keys($metaboxes[$priority]));
			}
		}
		
		if(!isset($metabox_sort_order)){
			$metabox_sort_order = array();
		}
		
		$metabox_sort_order[$context] = implode(',', $sort_list);
		
		$user = wp_get_current_user();
		update_user_option($user->ID, "meta-box-order_$page", $metabox_sort_order);
		
		//for some reason there was an infinite recursion scenario at some point. This static variable is in place to stop that from happening in future
		$been_here = true;
			relinkme_position_metabox($id, $page, $context, $position);
		$been_here = false;
		
	
	}
}


/* Add code for the Page Edit custom box*/
function relinkme_custom_box_html() {
	 //get previously selected categories
     global $post;
     $selected_categories = get_post_meta($post->ID,'relinkme_categories',true);
	
	if(empty($selected_categories)){
		 $selected_categories = array();
	}
     // use nonce for verification
     echo '<input type="hidden" name="relinkme_noncename" id="relinkme_noncename" value="'.wp_create_nonce(plugin_basename(__FILE__)).'" />' . "\r\n";

     // The list of categories for selection
	 $available_categories = get_option('relinkme_selected_categories');
	 if(!empty($available_categories)){
     ?>
		 <ul id="categorychecklist" class="list:category categorychecklist form-no-clear">
	<?php
		foreach ( $available_categories as $slug => $id ) {
			$category =  get_term($id, 'link_category');
			if(is_null($category)){
				continue;
			}
		?>
			<li id='link-category-<?php echo $id; ?>' ><label class="selectit" for="in-link-category-<?php echo $id; ?>"><input value="<?php echo $id; ?>" type="checkbox" name="link_category[]" id="in-link-category-<?php echo $id; ?>" <?php checked("$id", $selected_categories[$slug]);?>/> <?php echo $category->name; ?></label></li>
		<?php
			if(isset($selected_categories[$slug])){
				unset($selected_categories[$slug]);
			}
		}
		
		if(isset($selected_categories) && is_array($selected_categories)){
			foreach ( $selected_categories as $slug => $id ) {
				?>
					<li id='link-category-<?php echo $id; ?>' class="category-missing" ><label class="selectit" "category-missing" for="in-link-category-<?php echo $id; ?>"><?php echo $slug; ?> missing. <a href="link-manager.php?page=related-links-menu/related-links-menu-options.php">View Options</a></label></li>
				<?php
			}
		}
	?>
		</ul>
	<?php
	 }
}

/* Save Related Link Categories*/
function relinkme_save_postdata( $post_id ) {

	// verify this came from the our screen and with proper authorization,
	// because save_post can be triggered at other times

	if ( !wp_verify_nonce( $_POST['relinkme_noncename'], plugin_basename(__FILE__) )) {
		return $post_id;
	}

	// verify if this is an auto save routine. If it is our form has not been submitted, so we dont want
	// to do anything
	if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ){
		return $post_id;
	}


	// Check permissions
	if ( 'page' == $_POST['post_type'] ) {
		if ( !current_user_can( 'edit_page', $post_id ) ){
		  return $post_id;
		}
	} else {
		if ( !current_user_can( 'edit_post', $post_id ) ){
		  return $post_id;
		}
	}

	// OK, we're authenticated: we need to find and save the data

	$selected_categories = $_POST['link_category'];
	$update_categories = array();
	
	if(isset($selected_categories) && is_array($selected_categories)){
		foreach ($selected_categories as $id ) {
			$category =  get_term($id, 'link_category');
			if(is_null($category)){
				continue;
			}
			
			$update_categories[$category->slug] = $category->term_id;
		}
		
	}
	update_post_meta($post_id, 'relinkme_categories', $update_categories);
	
	return $selected_categories;
}


/* Related Link widget*/
class Relinkme_Widget extends WP_Widget {
	function Relinkme_Widget() {
		parent::WP_Widget(false, $name = 'Related Links');
	}

	function form($instance) {
		$title = esc_attr($instance['title']);
        ?>
            <p><label for="<?php echo $this->get_field_id('title'); ?>">
				<?php _e('Title:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
			</label></p>
        <?php
	}

	function update($new_instance, $old_instance) {
		return $new_instance;
	}

	function widget($args, $instance) {
		extract( $args );
        $title = apply_filters('widget_title', $instance['title']);
		echo $before_widget; 
			if ( $title ){
				echo $before_title . $title . $after_title; 
			}
			echo $this->generate_menu_items(); 
		echo $after_widget; 
	}
	
	function generate_menu_items(){
		global $post;
		$selected_categories = get_post_meta($post->ID,'relinkme_categories',true);
	
		$category_found = true;
		if(!empty($selected_categories)){
			foreach ( $selected_categories as $slug => $id ) {
				$category =  get_term($id, 'link_category');
				if(is_null($category)){
					$category_found = false;
					unset($selected_categories[$slug]);
					continue;
				}
				$category_found = true;
				break;
			}
		}
					
					
		if(empty($selected_categories) || !$category_found){
			$selected_categories = array();
			$default_category = get_option('relinkme_default_category');
			
			if(!empty($default_category)){
				$category =  get_term($default_category, 'link_category');
				if(is_null($category)){
					$available_categories = get_option('relinkme_selected_categories');
					if(!empty($available_categories)){
						foreach ( $available_categories as $slug => $id ) {
							if($id == $default_category){
								continue;
							}
							
							$category =  get_term($id, 'link_category');
							if(is_null($category)){
								continue;
							}
							break;
						}
						
						if(!is_null($category)){
							$selected_categories = array("$category->slug" => "$category->term_id");
						}
					}
				}
				else{
					$selected_categories = array("$category->slug" => "$category->term_id");
				}
			}
		}
		
		
		$output = '';
		if(!empty($selected_categories)){
			$show_category_title = get_option('relinkme_show_category_title');
			$category_link_order = get_option('relinkme_category_link_order');
			$args = array('category' => 0, 'hide_invisible' => 0, 'orderby' => 'name', 'hide_empty' => 0);
			$displayed = array();
			foreach ( $selected_categories as $slug => $id ) {
				$args['category'] = $id;
				$links = get_bookmarks( $args );
				
				if(!empty($links)){
					if($show_category_title){
						$category_title = $links[0]->description;
						$output .= "<h2 class='related_links_menu_title' id='related_links_menu_title-$id'>$category_title</h2>" . "\r\n";
					}
					else{
						if(!empty($displayed)){
							$output .= '<br class="category_separator" />' . "\r\n";
						}
					}
					$output .= "<ul class='related_links_menu' id='related_links_menu-$id'>" . "\r\n";
					
					$links = relinkme_sort_category_links($links, $category_link_order[$slug]);
					
					foreach ( $links as $link ) {
						if(!isset($displayed[$link->link_name])){
							$displayed[$link->link_name] = $link->link_id ;
							$current = '';
							if(preg_replace('/\//', '', $_SERVER['REQUEST_URI']) == preg_replace('/\//', '', $link->link_url)){
								$current = 'current';
							}
							$output .= '<li class="related_link related_link_' . $link->link_id . ' ' . $current . '"><a href="' . $link->link_url . '">' . $link->link_name . '</a></li>' . "\r\n";
						}
					}
					
					$output .= '</ul>' . "\r\n";
				}
			}
		}
		
		return $output;
	}

}

add_action('widgets_init', create_function('', 'return register_widget("Relinkme_Widget");'));

function relinkme_sort_category_links($links, $link_order = ''){
	if(!isset($links) || !is_array($links)){
		return array();
	}
	
	if(empty($link_order)){
		return $links;
	}
	
	$loadedLinks = array();
	foreach ( $links as $link ) {
		$loadedLinks[$link->link_id] = $link;
	}
	
	$sortedLinks = array();
	if(isset($link_order)){
		$seralized_sort = '&' . $link_order;
		$sortedLinks = explode('&category_link[]=', $seralized_sort);
		array_shift($sortedLinks);
	}
	
	$links = array();
	while(!empty($sortedLinks)){
		$link_id = array_shift($sortedLinks);
		if(isset($loadedLinks[$link_id])){
			$links[$link_id] = $loadedLinks[$link_id];
			unset($loadedLinks[$link_id]);
			if(empty($loadedLinks)){
				break;
			}
		}
	}
	
	foreach ( $loadedLinks as $link ) {
		$links[$link->link_id] = $link;
	}
	
	return $links;
}
?>