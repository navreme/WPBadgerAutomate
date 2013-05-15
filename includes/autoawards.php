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

define("AUTOAWARD_AFTER_REGISTRATION", 1);
define("AUTOAWARD_ON_URL", 2);
define("AUTOAWARD_BONUS_BADGE", 3);
				
class WPbadger_AutoAward_Schema {
	private $post_type_name;

	function __construct() {

		$this->set_post_type_name();

		add_action('init', array(&$this, 'register_post_type'));

		// Add rewrite rules
		add_action('generate_rewrite_rules', array(&$this, 'generate_rewrite_rules'));
		
		load_plugin_textdomain('autoaward', '', dirname( plugin_basename( __FILE__ ) ) . '/../lang' );
		
	}

	public function get_post_type_name() {
		return $this->post_type_name;
	}

	private function set_post_type_name() {
		$this->post_type_name = apply_filters('wpbadger_autoaward_post_type_name', 'autoaward');
	}

	function register_post_type() {
		$labels = array(
			'name' => _x('Autoawards', 'post type general name'),
			'singular_name' => _x('Autoaward', 'post type singular name'),
			'add_new' => __('Add New', 'autoaward'),
			'add_new_item' => __('Add New Autoaward'),
			'edit_item' => __('Edit Autoaward'),
			'new_item' => __('New Autoaward'),
			'all_items' => __('All Autoawards'),
			'view_item' => __('View Autoaward'),
			'search_items' => __('Search Autoawards'),
			'not_found' =>  __('No autoawards found'),
			'not_found_in_trash' => __('No autoaward found in Trash'),
			'parent_item_colon' => '',
			'menu_name' => __('Autoawards')
		);

		$args = array(
			'labels' => $labels,
			'public' => true,
			'query_var' => true,
			'rewrite'      => array(
				'slug'       => 'autoawards',
				'with_front' => false
			),
			'capability_type' => 'post',
			'has_archive' => false,
			'hierarchical' => false,
			'supports' => array( 'editor' )
		);

		register_post_type( $this->get_post_type_name(), $args );

		$this->add_rewrite_tags();
	}

	/**
	 * Add rewrite tags
	 *
	 * @since 1.2
	 */
	function add_rewrite_tags() {
		add_rewrite_tag('%%accept%%', '([1]{1,})');
		add_rewrite_tag('%%url%%', '([1]{1,})');
		add_rewrite_tag('%%list%%', '([1]{1,})');
	}

	/**
	 * Generates custom rewrite rules
	 *
	 * @since 1.2
	 */
	function generate_rewrite_rules($wp_rewrite) {
		$rules = array(
			// Create rewrite rules for each action
			'autoawards/([^/]+)/?$' =>
				'index.php?post_type=' . $this->get_post_type_name() . '&name=' . $wp_rewrite->preg_index(1),
			'autoawards/([^/]+)/accept/?$' =>
				'index.php?post_type=' . $this->get_post_type_name() . '&name=' . $wp_rewrite->preg_index(1) . '&accept=1',
			'wpba/([^/]+)/?$' => 
				'index.php?post_type=' . $this->get_post_type_name() . '&name=' . $wp_rewrite->preg_index(1) . '&url=1',
			'autobadges/?$' => 
				'index.php?post_type=' . $this->get_post_type_name() . '&name=' . $wp_rewrite->preg_index(1) . '&list=1'
		);
		$wp_rewrite->rules = array_merge($rules, $wp_rewrite->rules);
		return $wp_rewrite;
	}
}
new WPbadger_autoaward_Schema();

add_action( 'load-post.php', 'wpbadger_autoawards_meta_boxes_setup' );
add_action( 'load-post-new.php', 'wpbadger_autoawards_meta_boxes_setup' );

function wpbadger_autoawards_meta_boxes_setup() {
	add_action( 'add_meta_boxes', 'wpbadger_add_autoaward_meta_boxes' );
	add_action( 'save_post', 'wpbadger_save_autoaward_meta', 10, 2 );
}

// Create metaboxes for post editor
function wpbadger_add_autoaward_meta_boxes() {

	add_meta_box(
		'wpbadger-autoaward-choose-badge',			// Unique ID
		esc_html__('Choose Badge', 'autoaward'),	// Title
		'wpbadger_autoaward_choose_badge_meta_box',	// Callback function
		'autoaward',								// Admin page (or post type)
		'side',										// Context
		'default'									// Priority
	);
	
	add_meta_box(
		'wpbadger-autoaward-choose-award-type-box',
		esc_html__('Choose Award Type', 'autoaward'),
		'wpbadger_autoaward_choose_award_type_meta_box',
		'autoaward',
		'side',
		'default'
	);
	
	add_meta_box(
		'wpbadger-autoaward-date',
		esc_html__('Valid', 'autoaward'),
		'wpbadger_autoaward_date',
		'autoaward',
		'side',
		'default'
	);
	
    add_meta_box(
    	'wpbadger-autoaward-visibility',
    	esc_html__('Visibility', 'autoaward'),
    	'wpbadger_autoaward_visibility',
    	'autoaward',
    	'side',
    	'default'
    );
	
    add_meta_box(
		'wpbadger-autoaward-url-box',
		esc_html__('URL', 'autoaward'),
		'wpbadger_autoaward_url_meta_box',
		'autoaward',
		'side',
		'default'
	);

    add_meta_box(
		'wpbadger-autoaward-badges-box',
		esc_html__('Bonus badge', 'autoaward'),
		'wpbadger_autoaward_badges_meta_box',
		'autoaward',
		'side',
		'default'
	);

	add_meta_box(
		'wpbadger-autoaward-status',
		esc_html__('Autoaward Status', 'autoaward'),
		'wpbadger_autoaward_status_meta_box',
		'autoaward',
		'side',
		'default'
	);
	
	add_meta_box(
		'wpbadger-autoaward-direct',
		esc_html__('Direct badge issue', 'autoaward'),
		'wpbadger_autoaward_direct_meta_box',
		'autoaward',
		'side',
		'default'
	);
	
}

// Display metaboxes
function wpbadger_autoaward_choose_badge_meta_box( $object, $box ) { ?>

	<?php wp_nonce_field( basename( __FILE__ ), 'wpbadger_autoaward_nonce' ); ?>
	
	<?php $chosen_badge_id = get_post_meta($object->ID, 'wpbadger-autoaward-choose-badge', true );?>
	
	<p>
	<select name="wpbadger-autoaward-choose-badge" id="wpbadger-autoaward-choose-badge">
	
	<?php 	
	$query = new WP_Query(array(
		'posts_per_page' => 9999,
		'post_type' => 'badge'
		)
	);
	
	while ($query->have_posts()) {
		$query->the_post();
		$badge_title_version = the_title(null, null, false) . " (" . get_post_meta(get_the_ID(), 'wpbadger-badge-version', true) . ")";

		// As we iterate through the list of badges, if the chosen badge has the same ID then mark it as selected
		if ($chosen_badge_id == get_the_ID()) { 
			$selected = " selected";
		} else {
			$selected = "";
		}
		echo "<option name='wpbadger-autoaward-choose-badge' value='" . get_the_ID() . "'". $selected . ">";
		echo $badge_title_version . "</option>";
	}
	?>
	
	</select>
	</p>
<?php }

function wpbadger_autoaward_choose_award_type_meta_box($object, $box) {

	$award_type = get_post_meta($object->ID, 'wpbadger-autoaward-choose-award-type', true);
	
?>
<script type="text/javascript"><!--
function change_award_type() {

	var award_type = document.getElementById("wpbadger-autoaward-choose-award-type");
	if (award_type.value == <?php echo AUTOAWARD_AFTER_REGISTRATION; ?>) {
		// registrace
		document.getElementById("wpbadger-autoaward-url-box").style.display = "none";
		document.getElementById("wpbadger-autoaward-badges-box").style.display = "none";
	} else if (award_type.value == <?php echo AUTOAWARD_ON_URL; ?>) {
		// url
		document.getElementById("wpbadger-autoaward-url-box").style.display = "block";
		document.getElementById("wpbadger-autoaward-badges-box").style.display = "none";
	} else if (award_type.value == <?php echo AUTOAWARD_BONUS_BADGE; ?>) {
		// kombinace
		document.getElementById("wpbadger-autoaward-url-box").style.display = "none";
		document.getElementById("wpbadger-autoaward-badges-box").style.display = "block";
	}
}
// --></script>
	<p><select name="wpbadger-autoaward-choose-award-type" id="wpbadger-autoaward-choose-award-type" onchange="change_award_type();">
		<option value="<?php echo AUTOAWARD_AFTER_REGISTRATION; ?>"<?php echo (($award_type == AUTOAWARD_AFTER_REGISTRATION) ? ' selected="selected"' : ''); ?>><?php _e('After registration', 'autoaward'); ?></option>
		<option value="<?php echo AUTOAWARD_ON_URL; ?>"<?php echo (($award_type == AUTOAWARD_ON_URL) ? ' selected="selected"' : ''); ?>><?php _e('On URL', 'autoaward'); ?></option>
		<option value="<?php echo AUTOAWARD_BONUS_BADGE; ?>"<?php echo (($award_type == AUTOAWARD_BONUS_BADGE) ? ' selected="selected"' : ''); ?>><?php _e('Bonus badge', 'autoaward'); ?></option>
	</select></p>
	<p><?php _e('Choose when the badge will be automatically awarded: after registration, as soon as anyone visits the URL or based on combination of badges in the WP.', 'autoaward'); ?></p>
<?php
}

function wpbadger_autoaward_date($object, $box) { ?>
	<p><?php _e('from', 'autoaward'); ?> <input type="text" name="wpbadger-autoaward-date-start" id="wpbadger-autoaward-date-start" value="<?php echo esc_attr( get_post_meta( $object->ID, 'wpbadger-autoaward-date-start', true ) ); ?>" /> [YYYY-mm-dd]<br />
	<?php _e('to', 'autoaward'); ?> <input type="text" name="wpbadger-autoaward-date-end" id="wpbadger-autoaward-date-end" value="<?php echo esc_attr( get_post_meta( $object->ID, 'wpbadger-autoaward-date-end', true ) ); ?>" /> [YYYY-mm-dd]</p>
<?php
}

function wpbadger_autoaward_visibility( $object, $box ) { ?>
	<p>
		<input type="checkbox" name="wpbadger-autoaward-visibility-page" id="wpbadger-autoaward-visibility-page" value="1" <?php echo (get_post_meta($object->ID, 'wpbadger-autoaward-visibility-page', true) ? 'checked="checked" ' : ''); ?>/>
		<?php _e('Visible on autoawards list'); ?><br />
		<input type="checkbox" name="wpbadger-autoaward-visibility-image" id="wpbadger-autoaward-visibility-image" value="1" <?php echo (get_post_meta($object->ID, 'wpbadger-autoaward-visibility-image', true) ? 'checked="checked" ' : ''); ?>/>
		<?php _e('Thumbnail visible', 'autoaward'); ?><br />
		<input type="checkbox" name="wpbadger-autoaward-visibility-title" id="wpbadger-autoaward-visibility-title" value="1" <?php echo (get_post_meta($object->ID, 'wpbadger-autoaward-visibility-title', true) ? 'checked="checked" ' : ''); ?>/>
		<?php _e('Name visible', 'autoaward'); ?><br />
		<input type="checkbox" name="wpbadger-autoaward-visibility-description" id="wpbadger-autoaward-visibility-description" value="1" <?php echo (get_post_meta($object->ID, 'wpbadger-autoaward-visibility-description', true) ? 'checked="checked" ' : ''); ?>/>
		<?php _e('Description visible', 'autoaward'); ?>
	</p>
	<p><?php _e('Selected information about the criteria and the badge will be public. Everything else will be hidden.', 'autoaward'); ?></p>
<?php 
}

function wpbadger_autoaward_url_meta_box( $object, $box ) { ?>

	<p>
		<input type="hidden" name="wpbadger-autoaward-post-name" id="wpbadger-autoaward-post-name" value="<?php echo esc_attr($object->post_name); ?>" />
		<input class="widefat" type="text" name="wpbadger-autoaward-url" id="wpbadger-autoaward-url" value="wpba/<?php echo esc_attr($object->post_name); ?>" size="30" readonly="readonly" />
	</p>
	<p><?php _e('This is the secret URL. If anybody visits this URL, selected badge will be automatically awarded to him/her.', 'autoaward'); ?></p>
<?php }

function wpbadger_autoaward_badges_meta_box($object, $box) {

	$prevbadges = get_post_meta($object->ID, 'wpbadger-autoaward-badges', true);
	if ($prevbadges) {
		$prevbadges = explode(",", $prevbadges);
	} else {
		$prevbadges = array();
	}	
	
?>

<script type="text/javascript"><!--
var badge_num = <?php echo count($prevbadges); ?>;

function fce_addbadge() {
	
	var badge_id = document.getElementById('badge_id');
	var badgesdiv = document.getElementById('badgesdiv');
	if (badge_id && badgesdiv) {
		badge_num = badge_num + 1;
		if (badge_num <= 100) {
			// teoreticke maximum 100 pridanych poli
			var newdiv  = document.createElement('div');			
			newdiv.id = 'badgediv' + badge_num;
			badgesdiv.appendChild(newdiv);

			var newfield  = document.createElement('input');
			newfield.type = 'hidden';
			newfield.name = 'badgeid' + badge_num;
			newfield.value = badge_id.value;
			newdiv.appendChild(newfield);
			
			badgename = ""; 
			for (i = 0; i < badge_id.options.length; i++) {
				if (badge_id.value == badge_id.options[i].value) {
					badgename = badge_id.options[i].text;
				}
			}
			
			var newfield  = document.createElement('span');			
			newfield.innerHTML = badgename + " "; 
			newdiv.appendChild(newfield);
			
			var newfield  = document.createElement('input');
			newfield.type = 'button';
			newfield.id = 'badge' + badge_num;
			newfield.name = 'badge' + badge_num;
			newfield.value = '<?php _e('remove', 'autoaward'); ?>';
			var id = badge_num;
			newfield.onclick = function() { removebadge(id); };
			newdiv.appendChild(newfield);
			
			var newbr  = document.createElement('br');
			newdiv.appendChild(newbr);
		}
	}
	
}

function removebadge(id) {
	var badgesdiv = document.getElementById('badgediv' + id);
	if (badgesdiv) {
		badgesdiv.parentNode.removeChild(badgesdiv);
	}
}
// --></script>

	<div id="badgesdiv"><?php
$i = 1;	
foreach ($prevbadges as $badgeid) {
	echo "<div id=\"badgediv$i\"><input type=\"hidden\" name=\"badgeid$i\" value=\"$badgeid\"><span>" .
		get_the_title($badgeid) . " (" . get_post_meta($badgeid, 'wpbadger-badge-version', true) . ")" . 
		"</span> <input type=\"button\" id=\"badge$i\" name=\"badge$i\" value=\"" . 
		__('remove', 'autoaward') . "\" onclick=\"removebadge($i);\" /><br /></div>";
}
	 
		?></div>
	<p><select name="badge_id" id="badge_id">
<?php 	
	$query = new WP_Query(array(
		'posts_per_page' => 9999,
		'post_type' => 'badge'
		)
	);	
	while ($query->have_posts()) {
		$query->the_post();
		$badge_title_version = the_title(null, null, false) . " (" . get_post_meta(get_the_ID(), 'wpbadger-badge-version', true) . ")";

		echo "<option name='wpbadger-autoaward-choose-badge' value='" . get_the_ID() . "'>" . $badge_title_version . "</option>";
	}
?>
	</select> <input type="button" name="addbadge" value="<?php _e('Add badge', 'autoaward'); ?>" onclick="fce_addbadge();" /></p>
	<p><?php _e('Select badge(s) that needs to be already awarded in this WP to the user to be able to receive bonus badge automatically.', 'autoaward'); ?></p>
	
<?php }

function wpbadger_autoaward_status_meta_box( $object, $box ) { ?>
	<p><select name="wpbadger-autoaward-status" id="wpbadger-autoaward-status">
<?php
	$autoaward_status = get_post_meta( $object->ID, 'wpbadger-autoaward-status', true );
	$autoaward_status_options = array('Enabled', 'Disabled');
	foreach ($autoaward_status_options as $status_option) {
		$selected = ($status_option == $autoaward_status) ? ' selected="selected"' : '';
		echo "<option name='wpbadger-autoaward-status'" . $selected . ">" . $status_option . "</option>\n";
	}
	?></select>
<script><!--
change_award_type();
//--></script>
	</p>
	<p><?php _e('Autoawarding could be set to enabled (in use) or disabled (not functional).', 'autoaward'); ?></p>

<?php }

function wpbadger_autoaward_direct_meta_box($object, $box) { ?>

	<p><select name="wpbadger-autoaward-direct" id="wpbadger-autoaward-direct">
<?php
	$autoaward_direct = get_post_meta( $object->ID, 'wpbadger-autoaward-direct', true );
	$autoaward_direct_options = array('No', 'Yes');	
	foreach ($autoaward_direct_options as $direct_option) {
		$selected = ($direct_option == $autoaward_direct) ? ' selected="selected"' : '';
		echo "<option name='wpbadger-autoaward-direct'" . $selected . ">" . $direct_option . "</option>";
	}
	?>
	</select></p>
	<p><?php _e('If selected, no emails are going to be sent and users have to accept or reject badges immediately.', 'autoaward'); ?></p>

<?php }

/**
 * one field helper
 * @param $post_id int
 * ID of current post (autoaward) to store
 * @param $fieldname string
 * field to store
 * @return void
 */
function wpbadger_save_autoaward_meta_($post_id, $fieldname) {
	
	$new_value = $_POST[$fieldname];
	$old_value = get_post_meta($post_id, $fieldname, true);
	
	if ($new_value) {
		update_post_meta($post_id, $fieldname, $new_value, $old_value);
	} else {
		delete_post_meta($post_id, $fieldname, $old_value);	
	}
	
}

function wpbadger_save_autoaward_meta($post_id, $post) {

	if ( !isset( $_POST['wpbadger_autoaward_nonce'] ) || !wp_verify_nonce( $_POST['wpbadger_autoaward_nonce'], basename( __FILE__ ) ) )
		return $post_id;

	$post_type = get_post_type_object( $post->post_type );

	if ( !current_user_can( $post_type->cap->edit_post, $post_id ) )
		return $post_id;

	$chosen_badge_new_meta_value = $_POST['wpbadger-autoaward-choose-badge'];
	$chosen_badge_meta_key = 'wpbadger-autoaward-choose-badge';
	$chosen_badge_meta_value = get_post_meta( $post_id, $chosen_badge_meta_key, true );

	wpbadger_save_autoaward_meta_($post_id, 'wpbadger-autoaward-choose-award-type');
	wpbadger_save_autoaward_meta_($post_id, 'wpbadger-autoaward-date-start');
	wpbadger_save_autoaward_meta_($post_id, 'wpbadger-autoaward-date-end');
	wpbadger_save_autoaward_meta_($post_id, 'wpbadger-autoaward-visibility-page');
	wpbadger_save_autoaward_meta_($post_id, 'wpbadger-autoaward-visibility-image');
	wpbadger_save_autoaward_meta_($post_id, 'wpbadger-autoaward-visibility-title');
	wpbadger_save_autoaward_meta_($post_id, 'wpbadger-autoaward-visibility-description');
	
	$prevbadges = array();
	foreach ($_POST as $key => $value) {
		if (substr($key, 0, strlen('badgeid')) == 'badgeid') {
			$prevbadges[] = $value;
		}
	}
	$prevbadges = implode(",", $prevbadges);
	$oldprevbadges = get_post_meta($post_id, 'wpbadger-autoaward-badges', true);
	if ($prevbadges) {
		update_post_meta($post_id, 'wpbadger-autoaward-badges', $prevbadges);
	} elseif ($prevbadges == '') {
		delete_post_meta($post_id, 'wpbadger-autoaward-badges', $oldprevbadges );
	}
	
	$status_new_meta_value = $_POST['wpbadger-autoaward-status'];
	$status_meta_key = 'wpbadger-autoaward-status';
	$status_meta_value = get_post_meta( $post_id, $status_meta_key, true );

	wpbadger_save_autoaward_meta_($post_id, 'wpbadger-autoaward-direct');
	
	$salt = substr(str_shuffle(str_repeat("0123456789abcdefghijklmnopqrstuvwxyz", 8)), 0, 8);

	if ( $chosen_badge_new_meta_value ) {
		update_post_meta( $post_id, $chosen_badge_meta_key, $chosen_badge_new_meta_value );
	} elseif ( '' == $chosen_badge_new_meta_value ) {
		delete_post_meta( $post_id, $chosen_badge_meta_key, $chosen_badge_meta_value );
	}
	
	if ( $status_new_meta_value && '' == $status_meta_value ) {
		add_post_meta( $post_id, $status_meta_key, $status_new_meta_value, true );
	} elseif ( $status_new_meta_value && $status_new_meta_value != $status_meta_value ) {
		update_post_meta( $post_id, $status_meta_key, $status_new_meta_value );
	} elseif ( '' == $status_new_meta_value && $status_meta_value ) {
		delete_post_meta( $post_id, $status_meta_key, $status_meta_value );	
	}
	
	// Add the salt only the first time, and do not update if already exists
	if (get_post_meta($post_id, 'wpbadger-autoaward-salt', true) == false) {
		add_post_meta($post_id, 'wpbadger-autoaward-salt', $salt);
	}
}

add_filter('template_include', 'wpbadger_autoaward_template_check', 11);

function wpbadger_autoaward_template_check($template) {

	// Get query information
	$accept = get_query_var('accept');
	$url = get_query_var('url');
	$list = get_query_var('list');
	
	// Check if post type 'Autoawards'
	if ('autoaward' == get_post_type() ) {
		if ($accept) {
			$template_file = dirname(__FILE__) . '/autoawards_accept.php';
			return $template_file;
		} elseif ($url) {
			$template_file = dirname(__FILE__) . '/autoawards_url.php';
			return $template_file;
		} elseif ($list) {
			$template_file = dirname(__FILE__) . '/autoawards_list.php';
			return $template_file;
		} else {
			$template_file = dirname(__FILE__) . '/autoawards_template.php';
			return $template_file;
		}
	}

	return $template;
}

add_filter('user_can_richedit', 'wpbadger_disable_wysiwyg_for_autoawards');

function wpbadger_disable_wysiwyg_for_autoawards( $default ) {
    global $post;
    if ( 'autoaward' == get_post_type( $post ) )
        return false;
    return $default;
}

// Runs before saving a new post, and filters the post data
add_filter('wp_insert_post_data', 'wpbadger_autoaward_save_title', '99', 2);

function wpbadger_autoaward_save_title($data, $postarr) {
	
	if ($_POST['post_type'] == 'autoaward') {
		$data['post_title'] = __("Autoaward: ") . get_the_title($_POST['wpbadger-autoaward-choose-badge']);
		if ($_POST['wpbadger-autoaward-post-name']) {
			$data['post_name'] = trim($_POST['wpbadger-autoaward-post-name']);			
		}
	}
	return $data;
}

// Generate the autoaward slug. Shared by interface to autoaward single badges, as well as bulk
function wpbadger_autoaward_generate_slug() {
	$slug = rand(100000000000000, 999999999999999);
	return $slug;
}

// Runs before saving a new post, and filters the post slug
add_filter('name_save_pre', 'wpbadger_autoaward_save_slug');

function wpbadger_autoaward_save_slug($my_post_slug) {
	
	if ($_POST['post_type'] == 'autoaward') {
		$my_post_slug = wpbadger_autoaward_generate_slug();		
	}
	return $my_post_slug;
}

function manage_autoaward_table($column) {
    $column['autoaward_status'] = __("Status", "autoaward");
    $column['autoaward_type'] = __("Type", "autoaward");
    return $column;
}

function manage_autoaward_sortable_table($column) {
    $column['autoaward_status'] = 'autoaward_status';
    $column['autoaward_type'] = 'autoaward_type';
    return $column;
}

function manage_autoaward_table_row($column_name, $post_id) {
    switch ($column_name) {
        case 'autoaward_status':
			$autoaward_status = get_post_meta($post_id, 'wpbadger-autoaward-status', true);
            echo $autoaward_status;
            break;
        case 'autoaward_type':
			$autoaward_type = get_post_meta($post_id, 'wpbadger-autoaward-choose-award-type', true);
            switch ($autoaward_type) {
				case AUTOAWARD_AFTER_REGISTRATION: _e('After registration', 'autoaward'); break;
				case AUTOAWARD_ON_URL: _e('On URL', 'autoaward'); break;
				case AUTOAWARD_BONUS_BADGE: _e('Bonus badge', 'autoaward'); break;
			};
            break;
        default:
    }
}

function manage_autoaward_column_sorting($query) {
	if (!is_admin())
		return;
	
	$orderby = $query->get('orderby');
	if ('autoaward_status' == $orderby) {  
        $query->set('meta_key', 'wpbadger-autoaward-status');  
        $query->set('orderby', 'meta_value');  
    }  
	if ('autoaward_type' == $orderby) {  
        $query->set('meta_key', 'wpbadger-autoaward-choose-award-type');  
        $query->set('orderby', 'meta_value');  
    }  
}
 
add_filter('manage_edit-autoaward_columns', 'manage_autoaward_table');
add_filter('manage_edit-autoaward_sortable_columns', 'manage_autoaward_sortable_table');
add_filter('manage_posts_custom_column', 'manage_autoaward_table_row', 10, 2);
add_filter('pre_get_posts', 'manage_autoaward_column_sorting');

?>