<?php
/**
 * Team taxonomy and editor functions for Podcast CPT
 */
namespace WWOPN_Teams;

class Team {

	static $prefix;
	static $slug = 'teams';

	static function init() {

		self::$prefix = PREFIX . '_team';

		\add_action('init', [__CLASS__, 'register']);

		\add_action('pre_get_posts', [__CLASS__, 'orderBy']);

		// Podcast list filters
		\add_action('restrict_manage_posts', [__CLASS__, 'list_AddFilterDropdown']);
		\add_filter('parse_query', [__CLASS__, 'list_alterFilterQuery']);

		// Make Podcast list genre column sortable
		\add_action(
			'manage_edit-' . PREFIX . '_sortable_columns',
			[__CLASS__, 'list_sortableColumn']
		);

		\add_action('init', [__CLASS__, 'rewriteRule']);

	}

	static function register() {
		\register_taxonomy(
			self::$prefix,
			PREFIX,
			array(
				'label' => esc_html__( 'Teams' ),
				'labels' => array(
					'name'               => esc_html__( 'Teams' ),
					'items_list'         => esc_html__( 'Teams' ),
					'singular_name'      => esc_html__( 'Team' ),
					'menu_name'          => esc_html__( 'Teams' ),
					'name_admin_bar'     => esc_html__( 'Teams' ),
					'all_items'          => esc_html__( 'All Teams' ),
					'parent_item'        => esc_html__( 'Parent Team' ),
					'add_new'            => esc_html__( 'Add New' ),
					'add_new_item'       => esc_html__( 'Add New Team' ),
					'edit'               => esc_html__( 'Edit' ),
					'edit_item'          => esc_html__( 'Edit Team' ),
					'new_item'           => esc_html__( 'New Team' ),
					'view'               => esc_html__( 'View Team' ),
					'view_item'          => esc_html__( 'View Team' ),
					'search_items'       => esc_html__( 'Search Teams' ),
					'not_found'          => esc_html__( 'No Teams found' ),
					'not_found_in_trash' => esc_html__( 'No Teams found in Trash' ),
					'no_terms'           => esc_html__( 'No Teams' ),
				),
				'meta_box_cb' => [__CLASS__, 'editor_feature_metaBox'],
				'hierarchical' => true,
				'rewrite' => array('slug' => self::$slug, 'with_front' => false),
				'show_in_rest' => true,
				'show_admin_column' => true,
				'query_var' => true,
			)
		);
	}

	/**
	 * Add a rewrite rule so /teams goes to /team
	 * @return void
	 */
	static function rewriteRule() {
		\add_rewrite_rule(
			'^' . self::$slug . '/?$',
			'index.php?name=team',
			'top'
		);
	}

	static function orderBy($query) {
		if( ! is_admin() )
			return;

		$orderby = $query->get('orderby');

		if( 'slice' == $orderby ) {
			$query->set('meta_key',self::$prefix);
			$query->set('orderby','meta_value_num');
		}
	}

	static function editor_feature_metaBox($post, $box) {
		\post_categories_meta_box($post, $box);
	}

	static function list_AddFilterDropdown() {
		global $typenow;
		$post_type = PREFIX;
		$taxonomy  = self::$prefix;
		if ($typenow == $post_type) {
			$selected      = isset($_GET[$taxonomy]) ? $_GET[$taxonomy] : '';
			$info_taxonomy = \get_taxonomy($taxonomy);
			\wp_dropdown_categories(array(
				'show_option_all' => __("Show All {$info_taxonomy->label}"),
				'taxonomy'        => $taxonomy,
				'name'            => $taxonomy,
				'orderby'         => 'name',
				'selected'        => $selected,
				'show_count'      => true,
				'hide_empty'      => true,
			));
		};
	}

	static function list_alterFilterQuery($query) {
		global $pagenow;
		$post_type = PREFIX; // change to your post type
		$taxonomy  = self::$prefix; // change to your taxonomy
		$q_vars    = &$query->query_vars;
		if (
			$pagenow == 'edit.php' &&
			isset($q_vars['post_type']) &&
			$q_vars['post_type'] == $post_type &&
			isset($q_vars[$taxonomy]) &&
			is_numeric($q_vars[$taxonomy]) &&
			$q_vars[$taxonomy] != 0
		) {
			$term = \get_term_by('id', $q_vars[$taxonomy], $taxonomy);
			$q_vars[$taxonomy] = $term->slug;
		}
	}

	static function list_sortableColumn($columns) {
		$columns['taxonomy-' . self::$prefix] = self::$prefix;
		return $columns;
	}

}

Team::init();