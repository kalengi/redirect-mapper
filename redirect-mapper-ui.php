<?php

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


/* 
	File Information: Redirect Mapper ui page
*/

redirmap_ui_page();


function redirmap_ui_page() {
	
	if(!empty($_POST['uninstall'])) {
		redirmap_remove_settings();
		return;
	}
	
	if(!empty($_POST['verify'])) {
		redirmap_show_verification_page();
		return;
	}
	
	redirmap_show_settings_page();
}


function redirmap_remove_settings(){
	if($_POST['uninstall'] == 'UNINSTALL Redirect Mapper'){
		?> 
			<div id="message" class="updated fade">
				<?php 
					$redirmap_options = array('Activated' => 'redirmap_activated');
					foreach($redirmap_options as $option_key => $option_value){
						$delete_setting = delete_option($option_value);
						if($delete_setting) {
							?> 
							<p class="setting_removed">Setting: <?php echo $option_key; ?> => Removed</p>
							<?php
						} 
						else {
							?> 
							<p class="setting_not_removed">Setting: <?php echo $option_key; ?> => Not Removed </p>
							<?php
						}
					}
				?>
			</div>
		<?php
		$deactivate_url = 'plugins.php?action=deactivate&amp;plugin=redirect-mapper%2Fredirect-mapper.php';
		if(function_exists('wp_nonce_url')) { 
			$deactivate_url = wp_nonce_url($deactivate_url, 'deactivate-plugin_redirect-mapper/redirect-mapper.php');
		}
		
		?>
			<div class="wrap">
				<h2>Deactivate Redirect Mapper</h2>
				<p class="deactivation_message">
					<a href="<?php echo $deactivate_url; ?>">Click Here</a> to deactivate the plugin automatically
				</p>
			</div>
		<?php
	}
}

function redirmap_show_settings_page() {
	?>
		<!-- Options Form -->
		<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?page=<?php echo plugin_basename(__FILE__); ?>">
			<div class="wrap">
				<?php screen_icon(); ?>
				<h2>Redirect Mapper Options</h2>
				
				<p class="submit">
					<input type="submit" name="verify" class="button-primary" value="<?php _e('Proceed to Redirect Panel') ?>" />
				</p>
			</div>
		</form>
			
		<!-- Uninstall Plugin -->
		<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?page=<?php echo plugin_basename(__FILE__); ?>">
			<div id="redirmap_uninstall" class="wrap"> 
				<h3>Uninstall Redirect Mapper plugin</h3>
				<p>
					The uninstall action removes all Redirect Mapper plugin settings that have been saved in your WordPress database. Use this prior to deactivating the plugin.
				</p>
				<p class="warning">
					Please note that the deleted settings cannot be recovered. Proceed only if you do not wish to use these settings any more.
				</p>
				<p class="uninstall_confirmation">
					<input type="submit" name="uninstall" value="UNINSTALL Redirect Mapper" class="button" onclick="return confirm('You Are About To Uninstall Redirect Mapper From WordPress.\n\n Choose [Cancel] To Stop, [OK] To Uninstall.')" />
				</p>
			</div> 
		</form>
	<?php
}

function get_404_links_list(){
	//load 404 list
	
	$links_file = WP_CONTENT_DIR  . '/redirect-mapper/the_ojai_post_404_list.txt';
	//$contents = file_get_contents($links_file) or die("can't read from 404 list file");
	$contents = file_get_contents($links_file);// or die("can't read from 404 list file");
	if(empty($contents)){
		$contents = 'http://www.ojaipost.com/' . '||';
		$contents .= 'http://staging.ojaipost.com/' . '||';
		$contents .= '404' . '||';
		$contents .= '/index.php' . '||';
		$contents .= 'No 404 links found' . "\r\n";
	}
	
	$_404_links = explode("\r\n", $contents);
	$js_404_links = array();
	
	foreach($_404_links as $_404_link){
		$_404_link = explode('||', $_404_link);
		
		$js_404_link = array();
		$js_404_link[] = '"original_url":"' . str_replace('"', '\"', $_404_link[0]) . '"';
		$js_404_link[] = '"new_url":"' . str_replace('"', '\"', $_404_link[1]) . '"';
		$js_404_link[] = '"original_slug":"' . str_replace('"', '\"', $_404_link[3]) . '"';
		$js_404_link[] = '"post_title":"' . str_replace('"', '\"', $_404_link[4]) . '"';
		
		$js_404_links[] = "{" . implode(",",$js_404_link) . "}";
		
	}	
	
	if(empty($js_404_links)){
		die("The 404 list file is empty");
	}
	return '[' . implode(",", $js_404_links) . "]";
	
}

function redirmap_show_verification_page() {
	$js_404_links_list = get_404_links_list(); 
	
	?>
		<script type="text/javascript">
			//<![CDATA[ 
				var js_404_links_list = <?php echo $js_404_links_list; ?>;
				var js_siteUrl = '<?php echo get_bloginfo('url', 'display'); ?>';
			//]]>
		</script>
	<?php
	
	?>
		<!-- Verification page -->
		
		<div class="wrap">
			<?php screen_icon(); ?>
			<h2>Redirect Mapper: Verify Urls</h2>
			<div id="redirmap-nav-url" class="verify">
				<h3 id="current-url"><?php echo $_SERVER['PHP_SELF']; ?></h3>
				<input type="button" id="previous" name="previous" value="Previous" class="button" />
				<input type="button" id="next" name="next" value="Next" class="button"  />
			</div>
			<div id="redirmap-search-post" class="verify">
				Search for post having text: 
				<input type="text" id="search" class="search-input" name="search" value="" />
				<input type="button" id="search_button" name="search_button" value="Search" class="button" />
				<img src="<?php echo plugins_url('redirect-mapper/images/ajax_busy.gif'); ?>" id="ajax_busy"  alt="ajax busy"/>
			</div>
			<div id="redirmap-verify-post" class="verify">
				<div id="redirmap-search-results" class="verify">
					<p>Search results: </p>
					<ul id="redirmap-search-results-list">
						<li>No search done</li>
					</ul>
				</div>
				<div id="redirmap-original-post" class="verify">
					<p>Original page: </p>
					<object id="redirmap-original-post-page" type="text/html" data="" > 
						 
					</object>
				</div>
				
			</div>
			<div id="redirmap-matching-urls" class="verify">
				
			</div>
			<div id="redirmap-new-post" class="verify">
				<p>Current search url page: </p>
					<object id="redirmap-search-url-page" type="text/html" data="" > 
						 
					</object>
			</div>
			<div id="redirmap-save-match" class="verify">
				<input type="button" id="save_match" name="save_match" value="Map Url" class="button"  />
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				Current search url: 
				<input type="text" id="search_match" class="search-input" name="search_match" value="" />
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				Original url: 
				<input type="text" id="original_url" class="search-input" name="original_url" value="" />
			</div>
			
		</div>
		
		<!-- modal content -->
		<div id="redirmap-map-url-redirect">
			<form  method="post" accept-charset="utf-8" action="" style="padding: 3px">
				<h3>Redirect URL </h3>
				<table class="redirmap-form-fields" width="100%" >
					<tbody>
						<tr>
							<th width="100">
								Title:
							</th>
							<td>
								<input class="redirmap-form-field" style="width: 85%" type="text" id="redirmap-title" name="title" value="test"/>
								
							</td>
						</tr>
						<tr>
							<th width="100">
								Source URL:
							</th>
							<td>
								<input class="redirmap-form-field" type="text" name="source" style="width: 95%" id="old"/>
							</td>
						</tr>
						<tr>
							<th width="100">
								Match redirection:
							</th>
							<td>
								<select class="redirmap-form-field" name="match">
									<option selected="selected" value="url">URL only</option>
								</select>
							</td>
						</tr>
						<tr>
							<th width="100">
								Action:
							</th>
							<td>
								<select class="redirmap-form-field" name="red_action">
									<option value="url" selected="selected">Redirect to URL</option>
								</select>
							</td>
						</tr>
						<tr>
							<th>
								Target URL:
							</th>
							<td>
								<input class="redirmap-form-field" style="width: 95%" type="text" id="redirmap-target" name="target" />
							</td>
						</tr>
						<tr>
							<th>HTTP Code:</th>
							<td>
								<select class="redirmap-form-field" name="action_code">
									<option value="301" selected="selected">301 - Moved Permanently</option>
								</select>
							</td>
						</tr>
						<tr>
							<th/>
							<td>
								<input class="button-primary" type="submit" name="save" value="Save"/>
								<input class="button-secondary" type="submit" name="cancel" value="Cancel"/>
								<img src="<?php echo plugins_url('redirect-mapper/images/ajax_busy.gif'); ?>" id="redirmap_busy"  alt="redirect submitting"/>
								<input type="hidden" name="regex"/>
								<input type="hidden" name="group" value="1"/>
								<input type="hidden" name="action" value="red_redirect_add"/>
								<input type="hidden" name="_ajax_nonce" value="<?php echo wp_create_nonce( 'redirection-redirect_add' ); ?>"/>
								
							</td>
						</tr>
					</tbody>
				</table>
			</form>
			<div id="error_message"></div>
			<div id="info_message"></div>
		</div>
		
	<?php
}

?>