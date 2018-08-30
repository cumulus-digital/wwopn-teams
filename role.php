<?php
/**
 * Role taxonomy for Team CPT
 */
namespace WWOPN_Teams;

class Role {

	static $prefix;
	static $slug = 'role';

	static function init() {

		self::$prefix = PREFIX . '_role';

		\add_action('init', [__CLASS__, 'register']);

	}

	static function register() {
		\register_taxonomy(
			self::$prefix,
			PREFIX,
			array(
				'hierarchical' => false,
				'label' => esc_html__( 'Roles' ),
				'labels' => array(
					'name'               => esc_html__( 'Roles' ),
					'items_list'         => esc_html__( 'Team Member Roles' ),
					'singular_name'      => esc_html__( 'Role' ),
					'menu_name'          => esc_html__( 'Roles' ),
					'name_admin_bar'     => esc_html__( 'Roles' ),
					'all_items'          => esc_html__( 'All Roles' ),
					'parent_item'        => esc_html__( 'Parent Role' ),
					'add_new'            => esc_html__( 'Add New' ),
					'add_new_item'       => esc_html__( 'Add New Role' ),
					'edit'               => esc_html__( 'Edit' ),
					'edit_item'          => esc_html__( 'Edit Role' ),
					'new_item'           => esc_html__( 'New Role' ),
					'view'               => esc_html__( 'View Role' ),
					'view_item'          => esc_html__( 'View Role' ),
					'search_items'       => esc_html__( 'Search Roles' ),
					'not_found'          => esc_html__( 'No Roles found' ),
					'not_found_in_trash' => esc_html__( 'No Roles found in Trash' ),
					'no_terms'           => esc_html__( 'No Roles' ),
				),
				'meta_box_cb' => [__CLASS__, 'editor_addInstructions'],
				'rewrite' => array('slug' => self::$slug, 'with_front' => false),
				'show_in_rest' => true,
				'query_var' => true,
			)
		);
	}


	static function editor_addInstructions($post, $box) {
		\post_tags_meta_box($post, $box);
		if (\current_user_can('edit_published_pages')) {
			?>
			<p class="howto">
				Assign the Team Member's role as a tag. Team Members may have multiple roles.
			</p>
			<?php
		}
	}

}

Role::init();