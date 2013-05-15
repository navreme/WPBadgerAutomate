<?php
/**
 * @package WPBadgerAutomate
 * @version 0.1
 */
/*
Plugin Name: WPBadgerAutomate
Depends: WPBadger
Plugin URI: https://github.com/navreme/WPBadgerAutomate 
Description: Badge processing automate.
Author: Navreme Boheme
Version: 0.1
Author URI: http://www.navreme.cz/
Domain Path: /lang

WPBadgerAutomate

Copyright 2013 by Navreme Boheme.
All rights reserved.

This program is free software; you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation; either version 2 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT
ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
details.

You should have received a copy of the GNU General Public License along with
this program; if not, write to the Free Software Foundation, Inc., 59 Temple
Place, Suite 330, Boston, MA 02111-1307 USA
*/

register_activation_hook(__FILE__, 'WPBadgerAutomate_activate');
register_deactivation_hook(__FILE__, 'WPBadgerAutomate_deactivate');

require_once(dirname(__FILE__) . '/includes/autoawards.php');
require_once(dirname(__FILE__) . '/includes/autoawards_widget.php');

function WPBadgerAutomate_activate() {

	$wpbadgerinstalled = false;
	$plugins = get_option ( 'active_plugins', array () );
	foreach ( $plugins as $plugin ) {
		$wpbadgerinstalled = $wpbadgerinstalled || ($plugin == "wpbadger/wpbadger.php");
	}
	
	if (!$wpbadgerinstalled) {
		error_log(__("WPBadgerAutomate can't be activated without WPBadger!", "autoaward"));
    	die(__("WPBadgerAutomate can't be activated without WPBadger!", "autoaward"));
	}
	
	if (!get_option('permalink_structure')) {
		error_log(__("WPBadgerAutomate can't be activated without permalinks enabled!", "autoaward"));
    	die(__("WPBadgerAutomate can't be activated without permalinks enabled!", "autoaward"));
	}	
	
	// Flush rewrite rules
	global $wp_rewrite;
	$wp_rewrite->flush_rules();
}

function WPBadgerAutomate_deactivate() {
	global $wp_rewrite;
	$wp_rewrite->flush_rules();
}

function WPBadgerAutomate_user_register($user_id) {
	
	// store user_id to use on login page
	setcookie('WPBadgerAutomate_user_id', $user_id, 0, COOKIEPATH, COOKIE_DOMAIN);
	
}

function WPBadgerAutomate_login_enqueue_scripts() {

	wp_enqueue_style('autoawards_css', plugins_url('autoawards.css', __FILE__));
	wp_enqueue_script('openbadges', 'http://beta.openbadges.org/issuer.js', array(), null);
	wp_enqueue_script('jquery_ajax', 'http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js');
	
}

function WPBadgerAutomate_login_footer() {

    global $post;
    
	$message = '';
	$message2 = '';
	if (isset($_GET['checkemail']) && 'registered' == $_GET['checkemail']) {
		// jedná se o čerstvou registraci
		
		if ($user_id = $_COOKIE['WPBadgerAutomate_user_id']) {
			// má nastavené ID uživatele
			
			if ($user_info = get_userdata($user_id)) {
				// podařilo se načíst údaje
				
				$today_date = date('Y-m-d');
				
				// Pass query parameters differently based upon site permalink structure
				$query_separator = (get_option('permalink_structure') == '') ? '&' : '?';
				
				// is here an autoaward after registration?
				$query = new WP_Query(array(
					'posts_per_page' => 9999,
					'post_type' => 'autoaward',
					'meta_query' => array(
						'relation' => 'AND',
						array(
							'key' => 'wpbadger-autoaward-choose-award-type',
							'value' => AUTOAWARD_AFTER_REGISTRATION
						),
						array(
							'key' => 'wpbadger-autoaward-status',
							'value' => 'Enabled'
						)
					)
				));				
				$direct_issuing_urls = array();
				$direct_issuing_ids = array();
				
				while ($query->have_posts()) {
					$query->the_post();

					$chosen_badge_id = get_post_meta(get_the_ID(), 'wpbadger-autoaward-choose-badge', true);
					
					$date_start = get_post_meta(get_the_ID(), 'wpbadger-autoaward-date-start', true);
					$date_end = get_post_meta(get_the_ID(), 'wpbadger-autoaward-date-end', true);
					
					// odeslat e-mail nebo přímo přejít na stránku pro získání autoaward?
					// Yes = přímo přejít na stránku pro získání autoaward
					// No = odeslat e-mail s odkazem pro získání autoaward
					$direct = get_post_meta(get_the_ID(), 'wpbadger-autoaward-direct', true);
					
					if ((($date_start == '') || ($today_date >= $date_start)) &&
						(($date_end == '') || ($today_date <= $date_end))) {

						// vytvořit mu award
						$add_award_post = array(
							'post_author'	=> $post->post_author,
							'post_content'	=> $post->post_content,
							'post_title'	=> $post->post_title,
							'post_status'	=> 'publish',
							'post_name'		=> $post->post_name,		
							'post_type'		=> 'award'
						);
						$post_id = wp_insert_post($add_award_post, $wp_error);

						// do meta polí uložit potřebné údaje
						add_post_meta($post_id, 'wpbadger-award-choose-badge', $chosen_badge_id);
						add_post_meta($post_id, 'wpbadger-award-email-address', $user_info->user_email);
						add_post_meta($post_id, 'wpbadger-award-status', 'Awarded');
						$salt = substr(str_shuffle(str_repeat("0123456789abcdefghijklmnopqrstuvwxyz", 8)), 0, 8);
						add_post_meta($post_id, 'wpbadger-award-salt', $salt);

						if ($direct == 'Yes') { // Yes = přímo přejít na stránku pro získání autoaward
													
							$direct_issuing_urls[] = get_permalink($post_id) . $query_separator . "json=1";
							$direct_issuing_ids[] = $post_id;

						} else { // No = odeslat e-mail s odkazem pro jeho získání
							
							// zobrazit informaci
							$message .= "<li>" . __("You've signed up, thank you! Link to get badge was sent to your e-mail address.", "autoaward") . "</li>\n"; 
							
							wpbadger_award_send_email($post_id);
						}
					}
				}
				if ($direct_issuing_urls) {
					
					// zobrazit informaci
					$message .= "<li>" . __("You've signed up, thank you! Continue below to get badge.", "autoaward") . "</li>\n";
					$message2 = "<p class=\"awardassertation\">" .
						__('Please choose to', 'autoaward') . 
						" <a href=\"#\" class=\"backPackLink\">" . __('accept badge', 'autoaward') . "</a>.</p>";
				}
				if ($message || $message2) {
					echo "<div style=\"margin: 10px auto; width: 320px;\"><div class=\"message\" style=\"margin-left: 8px; width: 286px; \"><ul>\n" . 
						  $message . 
						  "</ul>\n" . $message2 . "</div></div>\n";
				}									
				if ($direct_issuing_urls) {
?>
<script type="text/javascript"><!--
$(document).ready(function() {
	$('.js-required').hide();
	
	if (/MSIE (\d+\.\d+);/.test(navigator.userAgent)){  //The Issuer API isn't supported on MSIE browsers
		$('.backPackLink').hide();
		$('.login-info').hide();
		$('.browserSupport').show();
	}
	
	// Function that issues the badge
	$('.backPackLink').click(function() {
		OpenBadges.issue(["<?php echo implode('","', $direct_issuing_urls); ?>"], function(errors, successes) {
			if (successes.length > 0) {
				$.ajax({
					url: '<?php echo get_permalink(get_the_ID()) . $query_separator; ?>accept=1&ids=<?php echo implode(',', $direct_issuing_ids); ?>',
					type: 'POST',
					success: function(data, textStatus) {
						$('.awardassertation').hide();
						// window.location.href = '<?php echo get_permalink(get_the_ID()); ?>';
					}
				});
			}
		});
	});
});
// -->
</script>
<?php				
				}
			}
		}
	}
}

add_action('user_register', 'WPBadgerAutomate_user_register');
add_action('login_enqueue_scripts', 'WPBadgerAutomate_login_enqueue_scripts');
add_action('login_footer', 'WPBadgerAutomate_login_footer');
add_action('widgets_init', create_function('', 'return register_widget("WPBadgerAutomateWidget");'));

?>
