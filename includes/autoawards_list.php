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

// wp_enqueue_script('openbadges', 'http://beta.openbadges.org/issuer.js', array(), null);
// wp_enqueue_script('jquery_ajax', 'http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js');
wp_enqueue_style('autoawards_css', plugins_url('autoawards.css', dirname(__FILE__)));
get_header(); 

// Pass query parameters differently based upon site permalink structure
if (get_option('permalink_structure') == '') {
	$query_separator = '&';
} else {
	$query_separator = '?';
}

$autoaward_direct = get_post_meta($post->ID, 'wpbadger-autoaward-direct', true);

?>

		<div id="primary" class="site-content">		
			<header class="entry-header">
				<h1 class="entry-title"><?php _e("Automatically assigned badges", "autoaward"); ?></h1>
			</header>
		
			<table class="autoawards">
				<tr>
					<th><?php _e("badge", "autoaward"); ?></th>
				 	<th><?php _e("title", "autoaward"); ?></th>
				 	<th><?php _e("description", "autoaward"); ?></th>
				</tr>
<?php

	$query = new WP_Query(array(
		'posts_per_page' => 9999,
		'post_type' => 'autoaward',
		'meta_query' => array(
			'relation' => 'AND',
			array(
				'key' => 'wpbadger-autoaward-visibility-page',	// only visible autoawards
				'value' => '1'
			),
		)
	));
	while ($query->have_posts()) {
		$query->the_post();
		
		$autobadges_image = '';
		$autobadges_name = the_title(null, null, false);
		$autobadges_description = get_the_content();
		
		if ($autobadges_badge_id = get_post_meta(get_the_ID(), 'wpbadger-autoaward-choose-badge', true)) {
			$autobadges_image = get_the_post_thumbnail($autobadges_badge_id, array(100, 100));
			$autobadges_name = get_the_title($autobadges_badge_id);
		}
		
		$autobadges_visibility_image = get_post_meta(get_the_ID(), 'wpbadger-autoaward-visibility-image', true);
		$autobadges_visibility_title = get_post_meta(get_the_ID(), 'wpbadger-autoaward-visibility-title', true);
		$autobadges_visibility_description = get_post_meta(get_the_ID(), 'wpbadger-autoaward-visibility-description', true);
		
?>				<tr>
					<td><div class="thumbnail"><?php echo $autobadges_visibility_image ? $autobadges_image : ('(' . __("hidden", "autoaward") . ')'); ?></div></td>
				 	<td><?php echo $autobadges_visibility_title ? $autobadges_name : ('(' . __("hidden", "autoaward") . ')'); ?></td>
				 	<td><?php echo $autobadges_visibility_description ? $autobadges_description : ('(' . __("hidden", "autoaward") . ')'); ?></td>
				</tr>
<?php		
	}

?>
			</table>			
		</div>

<?php get_sidebar(); ?>
<?php get_footer(); ?>