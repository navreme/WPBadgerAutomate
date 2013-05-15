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

class WPBadgerAutomateWidget extends WP_Widget {
	
	public function __construct() {
		parent::__construct(
	 		'WPBadgerAutomateWidget',
			'WPBadgerAutomate Widget',
			array('description' => __('Widget for direct badges collection. It also contains a link to autoawards list.', 'autoaward'))
		);
	}
 
	function form($instance) {
		$instance = wp_parse_args((array) $instance, array('title' => ''));
		$title = $instance['title'];
	?>
	<p><label for="<?php echo $this->get_field_id('title'); ?>">Title: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo attribute_escape($title); ?>" /></label></p>
	<?php
	}

	function widget($args, $instance) {		

	    global $post;
    	global $current_user;
		
		extract($args);
		$title = apply_filters( 'widget_title', $instance['title'] );

		echo '<aside id="wpbadgerautomate_widget" class="widget">';

		if (!empty($title))
			echo $before_title . $title . $after_title;;

		$wp_root_path = str_replace($_SERVER['DOCUMENT_ROOT'], '', 
			str_replace('/wp-content/themes', '', get_theme_root()));
		echo '<p><a href="' . $wp_root_path . '/autobadges/">' . __('Automatically assigned badges', 'autoaward') . "</a></p>";
		
		
		// v parametrech mohou být ID awardů, která potřebujeme označit za použitá
		if ($ids = $_GET['ids']) {
			$ids = explode(',', $ids);
			foreach ($ids as $id_) {
				update_post_meta($id_, 'wpbadger-award-status', 'Accepted');		
			}
		};
	
		// zjistit jestli je přihlášen
		$email = is_user_logged_in() ? $current_user->data->user_email : '';
		if (!$email) {
			return;
		}

		// Pass query parameters differently based upon site permalink structure
		$query_separator = (get_option('permalink_structure') == '') ? '&' : '?';
	
		$query = new WP_Query(array(
			'posts_per_page' => 9999,
			'post_type' => 'award',
			'meta_query' => array(
				'relation' => 'AND',
				array(
					'key' => 'wpbadger-award-email-address',		// přihlášený uživatel
					'value' => $email
				),
				array(
					'key' => 'wpbadger-award-status',				// udělený, čekající na sebrání
					'value' => 'Awarded'
				),
			)
		));
		$direct_issuing_urls = array();
		$direct_issuing_ids = array();	
		while ($query->have_posts()) {
			$query->the_post();

			$direct_issuing_urls[] = get_permalink(get_the_ID()) . $query_separator . "json=1";
			$direct_issuing_ids[] = get_the_ID();
		}

		// zkusíme jestli nemá splněnu nějakou kombinaci na přidělení bonusového badge
		
		// is here an autoaward after badge combination?
		$query = new WP_Query(array(
			'posts_per_page' => 9999,
			'post_type' => 'autoaward',
			'meta_query' => array(
				'relation' => 'AND',
				array(
					'key' => 'wpbadger-autoaward-choose-award-type',
					'value' => AUTOAWARD_BONUS_BADGE
				),
				array(
					'key' => 'wpbadger-autoaward-status',
					'value' => 'Enabled'
				)
			)
		));
		$autoawards = array();				
		while ($query->have_posts()) {
			$query->the_post();

			$usedby = get_post_meta(get_the_ID(), 'wpbadger-autoaward-usedby', true);

			$date_start = get_post_meta(get_the_ID(), 'wpbadger-autoaward-date-start', true);
			$date_end = get_post_meta(get_the_ID(), 'wpbadger-autoaward-date-end', true);
			
			if ((strpos($usedby, $email) === false) &&
				(($date_start == '') || ($today_date >= $date_start)) &&
				(($date_end == '') || ($today_date <= $date_end))) {
				
				$prev_badges = get_post_meta(get_the_ID(), 'wpbadger-autoaward-badges', true);
				if ($prev_badges) {
					// jsou definovány nějaké badge

					// odeslat e-mail nebo přímo přejít na stránku pro získání autoaward?
					// Yes = přímo přejít na stránku pro získání autoaward
					// No = odeslat e-mail s odkazem pro získání autoaward
					$direct = get_post_meta(get_the_ID(), 'wpbadger-autoaward-direct', true);					

					// jaký badge přidělit
					$badge_id = get_post_meta(get_the_ID(), 'wpbadger-autoaward-choose-badge', true);					
										
					// má přihlášený uživatel všechny tyto badge?
					$autoawards[] = array(
						'autoaward_id'	=> get_the_ID(),
						'post_author'	=> $post->post_author,
						'post_content'	=> $post->post_content,
						'post_title'	=> $post->post_title,
						'post_name'		=> $post->post_name,
						'prev_badges'	=> explode(',', $prev_badges),	
						'direct'		=> $direct,	
						'badge_id'		=> $badge_id	
					);										
				}					
			}
		}

		$query = new WP_Query(array(
			'posts_per_page' => 9999,
			'post_type' => 'award',
			'meta_query' => array(
				'relation' => 'AND',
				array(
					'key' => 'wpbadger-award-email-address',
					'value' => $email
				),
				array(
					'key' => 'wpbadger-award-status',
					'value' => 'Accepted'
				)
			)
		));
		$collected_badges = array();				
		while ($query->have_posts()) {
			$query->the_post();
			if ($badge_id = get_post_meta(get_the_ID(), 'wpbadger-award-choose-badge', true)) {
				$collected_badges[] = $badge_id; 
			}					
		}

		foreach ($autoawards as $autoaward) {
			
			// aby splnil, musím vlastnit všechny z $autoaward['prev_badges']
			$has_all = true;
			foreach ($autoaward['prev_badges'] as $badge_id) {
				$has_all = $has_all && in_array($badge_id, $collected_badges);
			}
			if ($has_all) {
				// má splněno
				
				// vytvořit mu award
				$add_award_post = array(
					'post_author'	=> $autoaward['post_author'],
					'post_content'	=> $autoaward['post_content'],
					'post_title'	=> $autoaward['post_title'],
					'post_status'	=> 'publish',
					'post_name'		=> $autoaward['post_name'],		
					'post_type'		=> 'award'
				);
				$post_id = wp_insert_post($add_award_post, $wp_error);

				// do meta polí uložit potřebné údaje
				add_post_meta($post_id, 'wpbadger-award-choose-badge', $autoaward['badge_id']);
				add_post_meta($post_id, 'wpbadger-award-email-address', $email);
				add_post_meta($post_id, 'wpbadger-award-status', 'Awarded');
				$salt = substr(str_shuffle(str_repeat("0123456789abcdefghijklmnopqrstuvwxyz", 8)), 0, 8);
				add_post_meta($post_id, 'wpbadger-award-salt', $salt);

				// uložit informaci, že už tento autoaward obdržel (ochrana proti opakovanému získávání)
				$usedby = get_post_meta($autoaward['autoaward_id'], 'wpbadger-autoaward-usedby', true);
				$usedby .= (trim($usedby) ? ',' : '') . $email;
				update_post_meta($autoaward['autoaward_id'], 'wpbadger-autoaward-usedby', $usedby);
						
				$direct_issuing_urls[] = get_permalink($post_id) . $query_separator . "json=1";
				$direct_issuing_ids[] = $post_id;

			}
		}
		if ($direct_issuing_urls) {
		
			echo '<div class="awardassertation">';
		
			_e("Waiting awards: ", "autoaward");
			echo count($direct_issuing_urls);
?>

<!-- najít způsob jak přesunout do HEAD -->
<script type="text/javascript" src="http://beta.openbadges.org/issuer.js"></script>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
<script type="text/javascript"><!--
$(document).ready(function() {
	$('.js-required').hide();
	
	if (/MSIE (\d+\.\d+);/.test(navigator.userAgent)){  //The Issuer API isn't supported on MSIE browsers
		$('.backPackLink-right').hide();
		$('.login-info').hide();
		$('.browserSupport').show();
	}
	
	// Function that issues the badge
	$('.backPackLink-right').click(function() {
		OpenBadges.issue(["<?php echo implode('","', $direct_issuing_urls); ?>"], function(errors, successes) {
			if (successes.length > 0) {
				$.ajax({
					url: '<?php echo $_SERVER['REQUEST_URI'] . $query_separator; ?>accept=1&ids=<?php echo implode(',', $direct_issuing_ids); ?>',
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
<p><?php _e('Please choose to', 'autoaward'); ?> <a href="#" class="backPackLink-right"><?php _e('accept badge', 'autoaward'); ?></a>.</p>
<?php
			echo '</div>';
		}
		
		echo "</aside>";
	}
 
}

?>