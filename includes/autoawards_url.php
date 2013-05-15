<?php

/*
 * WPBadgerAutomate
 *
 * Copyright 2013 by Navreme Boheme.
 * All rights reserved.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, write to the Free Software Foundation, Inc., 59 Temple
 * Place, Suite 330, Boston, MA 02111-1307 USA
 *
 */

global $post;
global $current_user;

wp_enqueue_script('openbadges', 'http://beta.openbadges.org/issuer.js', array(), null);
wp_enqueue_script('jquery_ajax', 'http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js');
get_header(); 

// Pass query parameters differently based upon site permalink structure
if (get_option('permalink_structure') == '') {
	$query_separator = '&';
} else {
	$query_separator = '?';
}

$award_type = get_post_meta($post->ID, 'wpbadger-autoaward-choose-award-type', true);
$autoaward_direct = get_post_meta($post->ID, 'wpbadger-autoaward-direct', true);

?>

<div id="container">
	<div id="content" role="main">

<?php

if ($award_type == AUTOAWARD_ON_URL) {
	// je to autoaward na URL

	$email = is_user_logged_in() ? $current_user->data->user_email : '';
	if ($email || trim($_POST['email'])) {
		if ($email) {
			// zobrazíme, protože přihlášený neměl dialog, ve kterém se to zobrazilo
			_e('You found the hidden URL, congratulations!', 'autoaward');			
		}
		$email = $email ? $email : trim($_POST['email']);
		
		// zkontrolovat jestli ho už neobdržel - ochrana proti vícenásobnému udělení
		$usedby = get_post_meta($post->ID, 'wpbadger-autoaward-usedby', true);
		if (strpos($usedby, $email) === false) {
				
			// vytvořit mu award
			$chosen_badge_id = get_post_meta($post->ID, 'wpbadger-autoaward-choose-badge', true);
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
			add_post_meta($post_id, 'wpbadger-award-email-address', $email);
			add_post_meta($post_id, 'wpbadger-award-status', 'Awarded');
			$salt = substr(str_shuffle(str_repeat("0123456789abcdefghijklmnopqrstuvwxyz", 8)), 0, 8);
			add_post_meta($post_id, 'wpbadger-award-salt', $salt);
			
			// uložit informaci, že už tento autoaward obdržel (ochrana proti opakovanému získávání)
			$usedby .= (trim($usedby) ? ',' : '') . $email;
			update_post_meta($post->ID, 'wpbadger-autoaward-usedby', $usedby);
			
			if (is_user_logged_in() || $autoaward_direct == 'Yes') {

?>
<script type="text/javascript"><!--
$(document).ready(function() {
	var assertionUrl = "<?php echo get_permalink($post_id) . $query_separator; ?>json=1";
	OpenBadges.issue([''+assertionUrl+''], function(errors, successes) {					
		if (successes.length > 0) {
			$.ajax({
  				url: '<?php echo get_permalink($post_id) . $query_separator; ?>accept=1',
  				type: 'POST',
				success: function(data, textStatus) {
					window.location.href = '<?php echo get_permalink($post_id); ?>';
				}
			});
		}
	});
});
// -->
</script>
<?php
			} else {
				// zobrazit informaci
				_e("Link to get badge was sent to your e-mail.", "autoaward");
							
				wpbadger_award_send_email($post_id);
			}
		} else {
			_e('You have already earned this badge! You can get it only 1 time.', 'autoaward');
		}
		
	} else {
		// zobrazit pole pro zadání e-mailu

		_e('You found the hidden URL, congratulations!', 'autoaward');
?>
<script type="text/javascript"><!--
function email_validate(theForm) {

	if (theForm.email.value == "") {
		alert('<?php _e("Error: E-mail can't be emty!", 'autoaward'); ?>');
		theForm.email.focus();
		return (false);
	}
	
	var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
	if (reg.test(theForm.email.value) == false) {
		alert('<?php _e("Error: Invalid E-mail address!", 'autoaward'); ?>');
		theForm.email.focus();
		return (false);
   }
	
	
	return true;
}
// -->
</script>
		<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post" onsubmit="return email_validate(this);">
			<?php _e('e-mail:', 'autoaward'); ?> <input type="text" name="email" value="<?php echo trim($_POST['email']); ?>" /><br />
			<input type="submit" name="submit" value="<?php _e('Send link to get badge', 'autoaward'); ?>" />			
		</form>
<?php
	} 
}
?>

	</div>
</div>

<?php get_sidebar(); ?>
<?php get_footer(); ?>