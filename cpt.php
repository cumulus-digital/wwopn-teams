<?php
/**
 * Team Member Custom Post Type
 */
namespace WWOPN_Teams;

class CPT {

	static $slug = 'team';
	static $metakeys = [];
	static $meta_save_callbacks = [];

	static function init() {

		\add_action('init', [__CLASS__, 'register']);

		\add_action('init', [__CLASS__, 'rewriteRule']);

		\add_filter( 'wp_insert_post_data', [__CLASS__, 'editor_stripWhitespace'], 9, 2 );

		\add_filter('gutenberg_can_edit_post_type', [__CLASS__, 'editor_disableGutenberg'], 10, 2);

		\add_action('admin_enqueue_scripts', [__CLASS__, 'editor_loadScriptsAndStyles']);

		\add_action( 'wp_ajax_autosave_wwopn_teams_meta', [__CLASS__, 'editor_meta_handleAutosave']);

		self::$metakeys['favoritePodcast'] = '_' . PREFIX . '_meta_favoritePodcast';
		\add_action('add_meta_boxes', [__CLASS__, 'editor_meta_favoritePodcast']);
		\add_action('save_post', [__CLASS__, 'editor_meta_favoritePodcast_save'], 10, 1);
		self::$meta_save_callbacks[] = [__CLASS__, 'editor_meta_favoritePodcast_save'];

		\add_filter('gettext',[__CLASS__, 'editor_customEnterTitle']);


	}

	/**
	 * Register CPT
	 * @return void
	 */
	static function register() {
		\register_post_type( PREFIX, // Register Custom Post Type
			array(
				'labels'       => array(
					'name'                  => esc_html__( 'Team Members' ),
					'singular_name'         => esc_html__( 'Team Member' ),
					'menu_name'             => esc_html__( 'Team Members' ),
					'name_admin_bar'        => esc_html__( 'Team Member' ),
					'all_items'             => esc_html__( 'All Team Members' ),
					'add_new'               => esc_html__( 'Add New' ),
					'add_new_item'          => esc_html__( 'Add New Team Member' ),
					'edit'                  => esc_html__( 'Edit' ),
					'edit_item'             => esc_html__( 'Edit Team Member' ),
					'new_item'              => esc_html__( 'New Team Member' ),
					'view'                  => esc_html__( 'View Team Member' ),
					'view_item'             => esc_html__( 'View Team Member' ),
					'search_items'          => esc_html__( 'Search Team Members' ),
					'not_found'             => esc_html__( 'No Team Members found' ),
					'not_found_in_trash'    => esc_html__( 'No Team Members found in Trash' ),
					'featured_image'        => esc_html__( 'Team Member Photo' ),
					'set_featured_image'    => esc_html__( 'Set Team Member Photo' ),
					'remove_featured_image' => esc_html__( 'Remove Team Member Photo' ),
					'use_featured_image'    => esc_html__( 'Use as Team Member Photo' )
				),
				'description'           => 'Landing pages for Team Members.',
				'public'                => true,
				'capability_type'       => 'page',
				'show_in_rest'          => true,
				'rest_base'             => 'team',
				'rest_controller_class' => '\WP_REST_Posts_Controller',
				'rewrite'               => array('slug' => self::$slug),
				'menu_position'         => 20,
				'menu_icon'             => 'dashicons-nametag',
				'hierarchical'          => false,
				'has_archive'           => true,
				'can_export'            => true,
				'supports' => array(
					'title',
					'editor',
					'revisions',
					'thumbnail',
					'page-attributes'
				),
				'taxonomies' => array(
					'team',
					PREFIX . '_role',
				),
			)
		);
	}

	/**
	 * Add a rewrite rule so /team/* goes to /team
	 * @return void
	 */
	static function rewriteRule() {
		\add_rewrite_rule(
			'^' . self::$slug . '/.*',
			'index.php?name=team',
			'top'
		);
	}

	/**
	 * Register scripts and styles for the post editor
	 * @param  string $hook
	 * @return void
	 */
	static function editor_loadScriptsAndStyles($hook) {
		if ($hook !== 'post-new.php' && $hook !== 'post.php') {
			return;
		}
		$screen = \get_current_screen();
		if ($screen->id !== PREFIX) {
			return;
		}

		\wp_enqueue_script( PREFIX . '_editor_scripts', \plugin_dir_url(__FILE__) . 'assets/editor/scripts.js' );
	}


	/**
	 * Strip whitespace at the end of Podcast post content
	 * @param  string $data
	 * @param  object $post
	 * @return string
	 */
	static function editor_stripWhitespace($data, $post) {
		if ($post['post_type'] !== PREFIX) {
			return $data;
		}

		$clean = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $data['post_content']);
		$quotes = array(
		    "\xC2\xAB"     => '"', // « (U+00AB) in UTF-8
		    "\xC2\xBB"     => '"', // » (U+00BB) in UTF-8
		    "\xE2\x80\x98" => "'", // ‘ (U+2018) in UTF-8
		    "\xE2\x80\x99" => "'", // ’ (U+2019) in UTF-8
		    "\xE2\x80\x9A" => "'", // ‚ (U+201A) in UTF-8
		    "\xE2\x80\x9B" => "'", // ‛ (U+201B) in UTF-8
		    "\xE2\x80\x9C" => '"', // “ (U+201C) in UTF-8
		    "\xE2\x80\x9D" => '"', // ” (U+201D) in UTF-8
		    "\xE2\x80\x9E" => '"', // „ (U+201E) in UTF-8
		    "\xE2\x80\x9F" => '"', // ‟ (U+201F) in UTF-8
		    "\xE2\x80\xB9" => "'", // ‹ (U+2039) in UTF-8
		    "\xE2\x80\xBA" => "'", // › (U+203A) in UTF-8
		);
		$clean = strtr($clean, $quotes);
		$clean = str_replace('&nbsp;', '', $clean);

		$data['post_content'] = trim($clean);
		return $data;
	}

	/**
	 * Disable Gutenberg for this CPT
	 * @param  boolean $is_enabled
	 * @param  string $post_type
	 * @return boolean
	 */
	static function editor_disableGutenberg($is_enabled, $post_type = null) {
		if ($post_type === PREFIX) {
			return false;
		}

		return $is_enabled;
	}

	/**
	 * Determine if request is safe to save metadata
	 * @return boolean
	 */
	static function editor_meta_safeToSave() {
		if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return false;
		}

		if ( ! isPost()) {
			return false;
		}

		if ( ! \current_user_can('edit_pages')) {
			return false;
		}

		return true;
	}

	/**
	 * Handle custom autosave event
	 */
	static function editor_meta_handleAutosave() {
		if ( ! isPOST()) {
			return;
		}

		if ( ! testPostValue('post_ID')) {
			return;
		}
		
		foreach(self::$meta_save_callbacks as $cb) {
			$cb($_POST['post_ID']);
		}
		return true;
	}

	/**
	 * Add meta box for player embed code
	 * @return void
	 */
	static function editor_meta_favoritePodcast() {
		\add_meta_box(
			self::$metakeys['favoritePodcast'],
			esc_html__('Favorite Podcast'),
			[__CLASS__, 'editor_meta_favoritePodcast_show'],
			PREFIX,
			'normal',
			'high'
		);
	}

	/**
	 * Display the meta box for player embed
	 * @param  object $post
	 * @return void
	 */
	static function editor_meta_favoritePodcast_show($post) {
		$key = self::$metakeys['favoritePodcast'];
		$embed = \get_post_meta($post->ID, $key, true);

		?>
		<div class="wpn_meta_autosave">
			<?=\wp_nonce_field($key, $key . '-nonce');?>
			<label class="screen-reader-text" for="favorite_podcast">Team Member's Favorite Podcast</label>
			<input type="text" class="wpn-meta-autosave" name="<?=$key?>" id="favorite_podcast" style="display:block;width:100%;height:2em;margin:12px 0 0;" value="<?=esc_attr($embed) ?>">
		</div>
		<?php
	}

	/**
	 * Save data entered in player embed box
	 * @param  integer $post_id
	 * @return void
	 */
	static function editor_meta_favoritePodcast_save($post_id) {
		if ( ! self::editor_meta_safeToSave()) {
			return;
		}

		$key = self::$metakeys['favoritePodcast'];

		if (testPostValue($key, true)) {
			$_POST[$key] = \sanitize_text_field($_POST[$key]);
			\update_post_meta($post_id, $key, (string) $_POST[$key]);
			return;
		}

		\delete_post_meta($post_id, $key);
	}

	static function editor_customEnterTitle($input) {
	    global $post_type;

	    if(
	    	is_admin() &&
	    	'Enter title here' == $input &&
	    	$post_type == PREFIX)
	        return 'Enter the Team Member\'s Name';

	    return $input;
	}

}

CPT::init();