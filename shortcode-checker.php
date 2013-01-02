<?php

/*
Plugin Name: Shortcode Checker
Plugin URI: http://aramzs.me
Description: A plugin to detect shortcodes and give devs hooks for checking for existing shortcodes.
Version: 0.001
Author: Aram Zucker-Scharff
Author URI: http://aramzs.me
License: GPL2
*/

/*  Copyright 2012  Aram Zucker-Scharff  (email : azuckers@gmu.edu)

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

//Remember, if it works here, it'll work in functions.php for your theme and vice versa.
/* Additional important reference material 
	http://codex.wordpress.org/
	http://www.php.net/manual/en/
*/

//use this for testing when things go wrong:
//	print_r($troubleVariable); 
//	die();

//Function to return an array object containing all currently active shortcodes. 
function sc_get_shortcodes(){
	//Get the WordPress global variable that contains an array of active shortcodes. 
	global $shortcode_tags;
	$shortcode_names;
	foreach ($shortcode_tags as $shortcode => $gen_function){
		$shortcode_names[] = $shortcode;
	}
	return $shortcode_names;

}

function sc_shortcodes(){
	$sc_array = sc_get_shortcodes();
//	echo '<pre><code>';
//	print_r($sc_array);
//	echo '</code></pre>';
		echo '<ul>';
	foreach ($sc_array as $sc){
/**		if (is_array($sc)){
			foreach ($sc as $sc_sub)
			{
				echo '<li>' . $sc_sub . '</li>';
			}
		} else {
**/			echo '<li>' . $sc . '</li>';
//		}
		
	}
	echo '</ul>';
	
}

function sc_shortcode_exists($sc_to_check){
	if (in_array($sc_to_check, sc_get_shortcodes())){
		return true;
		//print_r('Shortcode check true');
	}
	else {
		return false;
		//print_r('Shortcode check false');
	}
}

function sc_set_best_shortcode($shortcode, $plugin_slug){
	$op_name = $plugin_slug . '_sc_' . $shortcode;
	//Why check for multisite? Because we don't want to chance the same plugin using different shortcodes across a network.
	if (is_multisite()){
		//If the option doesn't already exist, set it.
		if (false == get_site_option($op_name)){
			add_site_option($op_name, $shortcode);
		}
		// else return the existing option
		else {
			$shortcode = get_site_option($op_name);
		}		
	} else {
		//If the option doesn't already exist, set it.
		if (false == get_option($op_name)){
			add_option($op_name, $shortcode);
		}
		// else return the existing option
		else {
			$shortcode = get_option($op_name);
		}
	}
	//print_r('Shortcode option set as ' . $op_name .' for shortcode - ' . $shortcode . ' <br />');
	return $shortcode;
}

function sc_get_best_shortcode($shortcode, $plugin_slug, $slug = 'sc_', $fallback_slug = false){
	if (false == $fallback_slug) { $fallback_slug = rand() . '_'; }
	if (sc_shortcode_exists($shortcode)){
		$new_shortcode = $slug . $shortcode;
		if (sc_shortcode_exists($new_shortcode)){
			$new_new_shortcode = sc_get_best_shortcode($new_shortcode, $plugin_slug, $fallback_slug);
			$the_shortcode = $new_new_shortcode;
		} else {
			$the_shortcode = $new_shortcode;
		}
	} else {
		$the_shortcode = $shortcode;
	}
	
	$final_shortcode =  sc_set_best_shortcode($the_shortcode, $plugin_slug);
	
	return $final_shortcode;
}


//The following is an example of how to use this plugin.
function sc_a_safe_shortcode_for_this_plugin(){
	$sc_our_shortcode = sc_get_best_shortcode('ourshortcode', 'shortcode_checker', 'sc_', 'thesc_');
	return $sc_our_shortcode;
}

$sc_global_shortcode_variable = sc_a_safe_shortcode_for_this_plugin();

function sc_shortcode_of_shortcode($atts){
	extract( shortcode_atts( array(
		'is' => 'nonexistent'
	), $atts ) );
	echo 'This plugin\'s shortcode: ' . $is;	
}
add_shortcode($sc_global_shortcode_variable, 'sc_shortcode_of_shortcode');


//Add a page for users to view shortcodes. 
//This is really for the dev process only. Once you've got your site up and running, deactivate this function.
add_action('admin_menu', 'sc_admin_add_page');
function sc_admin_add_page() {
	//Add that options page title, menu item, user capability, the slug to refer to, the output function. 
	add_plugins_page('Shortcodes', 'Shortcodes', 'activate_plugins', 'sc', 'sc_options_page');
}

// display the admin options page
function sc_options_page() {
	global $sc_global_shortcode_variable;
	?>
	<div>
		<h2>A list of all shortcodes currently active on your site.</h2>
		<?php
		sc_shortcodes();
		?>
		<h5><?php echo 'This plugin\'s shortcode: ' . $sc_global_shortcode_variable; ?></h5>
		<h5>This plugin's shortcode output: <?php do_shortcode('[' . $sc_global_shortcode_variable . ' is="' . $sc_global_shortcode_variable . '"]'); ?></h5>
	</div>
	<?php
	//print_r(sc_a_safe_shortcode_for_this_plugin());
}
?>