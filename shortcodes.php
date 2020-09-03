<?php
/**
 * Shortcode for Team display
 */
namespace WWOPN_Teams;

function shortcode_team($attr) {
	$attr = \shortcode_atts([
		'team' => 'all',
		'order' => 'ASC',
		'bio' => 'false'
	], $attr, 'team');

	$attr['order'] = strtoupper($attr['order']);
	if ($attr['order'] !== 'ASC' || $attr['order'] !== 'DESC') {
		$attr['order'] = 'ASC';
	}

	if ($attr['bio']) {
		$tbio = strtoupper($attr['bio']);
		if ($tbio === 'FALSE' || $tbio === 'NO') {
			$attr['bio'] = false;
		} else {
			$attr['bio'] = true;
		}
	}
	$show_bio = $attr['bio'] ? true : false;

	$team_query = array(
		'post_type' => PREFIX,
		'post_status' => 'publish',
		'orderby' => 'menu_order',
		'order' => $attr['order'],
		'posts_per_page' => -1,
	);
	if (strtoupper($attr['team']) !== 'ALL') {
		$team_query['tax_query'] = array(
			'taxonomy' => 'category',
			'field' => 'id',
			'terms' => $attr['team']
		);
	}

	$team = \get_posts($team_query);

	$ids = implode(',', array_map(function($l) {
		return "'" . \esc_sql($l) . "'";
	}, \wp_list_pluck($team, 'ID')));
	global $wpdb;

	// Get all images
	$images = $wpdb->get_results("
		SELECT
			ipmeta.post_id,
			ip.guid
		FROM
			{$wpdb->postmeta} AS ipmeta
		INNER JOIN
			{$wpdb->posts} AS ip
			ON
				ipmeta.meta_value=ip.ID
		WHERE
			ipmeta.post_id IN ({$ids})
			AND ipmeta.meta_key = '_thumbnail_id'
	");

	// Get all role tags
	$roles = $wpdb->get_results("
		SELECT
			post.id AS post_id,
			term.*
		FROM {$wpdb->terms} AS term
		INNER JOIN {$wpdb->term_taxonomy} AS tax ON term.term_id = tax.term_id
		INNER JOIN {$wpdb->term_relationships} AS relation ON relation.term_taxonomy_id = tax.term_taxonomy_id
		INNER JOIN {$wpdb->posts} AS post ON post.id = relation.object_id
		WHERE post.ID IN ({$ids}) AND tax.taxonomy = '" . PREFIX . "_role'
	");

	// Add image and roles to each team member
	array_walk($team, function($member) use ($images, $roles) {
		foreach($images as $image) {
			if ($image->post_id == $member->ID) {
				$member->image = $image->guid;
				break;
			}
		}
		$member->roles = array();
		foreach($roles as $role) {
			if ($role->post_id == $member->ID) {
				$member->roles[] = $role;
			}
		}
	});

	ob_start();
	include __DIR__ . '/templates/team.php';
	$output = ob_get_clean();

	return $output;

};
\add_shortcode('team', __NAMESPACE__ . '\shortcode_team');