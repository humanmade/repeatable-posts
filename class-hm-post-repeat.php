<?php

Class HM_Post_Repeat {

	/**
	 * Setup the actions and filters required by this class.
	 */
	function __construct() {

		add_action( 'post_submitbox_misc_actions', array( $this, 'publish_box_ui') );
		add_action( 'save_post', array( $this, 'save_post_repeating_status' ) );
		add_action( 'publish_post', array( $this, 'create_next_post' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		add_filter( 'display_post_states', array( $this, 'post_states' ) );

	}

	/**
	 * Enqueue the scripts and styles that are needed by this plugin
	 */
	function enqueue_scripts( $hook ) {

		// Ensure we only load them on the edit post and add new post admin screens
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ) ) ) {
			return;
		}

		$plugin_data = get_plugin_data( HM_POST_REPEAT_PLUGIN_FILE );

		wp_enqueue_script( 'hm-post-repeat', plugin_dir_url( __FILE__ ) . 'hm-post-repeat.js', 'jquery', $plugin_data['Version'], true );
		wp_enqueue_style( 'hm-post-repeat', plugin_dir_url( __FILE__ ) . 'hm-post-repeat.css', array(), $plugin_data['Version'] );

	}

	/**
	 * Output the Post Repeat UI that is shown in the Publish post meta box.
	 *
	 * The UI varies depending on whether the post is the original repeating post
	 * or itself a repeat.
	 */
	function publish_box_ui() {

		if ( ! in_array( get_post_type(), self::repeating_post_types() ) ) {
			return;
		} ?>

		<div class="misc-pub-section misc-pub-hm-post-repeat">

			<span class="dashicons dashicons-controls-repeat"></span>

			<?php _e( 'Repeat:', 'hm-post-repeat' ); ?>

			<?php if ( self::is_repeat_post( get_the_id() ) ) { ?>

				<strong><?php printf( __( 'Repeat of %s', 'hm-post-repeat' ), '<a href="' . esc_url( get_edit_post_link( get_post()->post_parent ) ) . '">' . get_the_title( get_post()->post_parent ) . '</a>' ); ?></strong>

			<?php } else { ?>

				<strong><?php echo ! self::is_repeating_post( get_the_id() ) ? __( 'No' ) : __( 'Weekly' ); ?></strong>

				<a href="#hm-post-repeat" class="edit-hm-post-repeat hide-if-no-js"><span aria-hidden="true"><?php _e( 'Edit' ); ?></span> <span class="screen-reader-text"><?php _( 'Edit Repeat Settings', 'hm-post-repeat' ); ?></span></a>

				<span class="hide-if-js" id="hm-post-repeat">

					<select name="hm-post-repeat">
						<option<?php selected( ! self::is_repeating_post( get_the_id() ) ); ?> value="no"><?php _e( 'No', 'hm-post-repeat' ); ?></option>
						<option<?php selected( self::is_repeating_post( get_the_id() ) ); ?> value="weekly"><?php _e( 'Weekly' ); ?></option>
					</select>

					<a href="#hm-post-repeat" class="save-post-hm-post-repeat hide-if-no-js button">OK</a>

				</span>

			<?php }	?>

		</div>

	<?php }

	/**
	 * Add some custom post states to cover repeat and repeating posts.
	 *
	 * By default post states are displayed on the Edit Post screen in bold after the post title
	 *
	 * @param array The original array of post states.
	 * @return array The array of post states with ours added.
	 */
	function post_states( $post_states ) {

		if ( self::is_repeating_post( get_the_id() ) ) {
			$post_states['hm-post-repeat'] = __( 'Repeating', 'hm-post-repeat' );
		}

		if ( self::is_repeat_post( get_the_id() ) ) {
			$post_states['hm-post-repeat'] = __( 'Repeat', 'hm-post-repeat' );
		}

		return $post_states;

	}

	/**
	 * Save the repeating status to post meta.
	 *
	 * Hooked into `save_post`. When saving a post that has been set to repeat we save a post meta entry.
	 */
	function save_post_repeating_status() {

		if ( ! in_array( get_post_type(), self::repeating_post_types() ) || ! isset( $_POST['hm-post-repeat'] ) ) {
			return;
		}

		if ( $_POST['hm-post-repeat'] === 'no' ) {
			delete_post_meta( get_the_id(), 'hm-post-repeat' );
		}

		else {
			update_post_meta( get_the_id(), 'hm-post-repeat', true );
		}

	}

	/**
	 * Create the next repeat post when the last one is published.
	 *
	 * When a repeat post (or the original) is published we copy and schedule a new post
	 * to publish in a weeks time. That way the next repeat post is always ready to go.
	 * This is hooked into publish_post so that the repeat post is only created when the original
	 * is published.
	 *
	 * @todo Support additional intervals
	 */
	function create_next_post() {

		if ( ! in_array( get_post_type(), self::repeating_post_types() ) ) {
			return;
		}

		$original_post = false;

		// Are we publishing a repeat post
		if ( self::is_repeat_post( get_the_id() ) ) {
			$original_post = get_post( get_post()->post_parent, ARRAY_A );
		}

		// Or the original
		elseif ( self::is_repeating_post( get_the_id() ) ) {
			$original_post = get_post( null, ARRAY_A );
		}

		// Bail if we're not publishing a repeating post
		if ( ! $original_post ) {
			return;
		}

		$next_post = $original_post;

		// Create the repeat post as a copy of the original, but ignore some fields
		unset( $next_post['ID'] );
		unset( $next_post['guid'] );
		unset( $next_post['post_date_gmt'] );
		unset( $next_post['post_modified'] );
		unset( $next_post['post_modified_gmt'] );

		// We set the post_parent to the original post_id, so they're related
		$next_post['post_parent'] = $original_post['ID'];

		// Set the next post to publish one week in the future
		$next_post['post_status'] = 'future';
		$next_post['post_date'] = date( 'Y-m-d H:i:s', strtotime( $original_post['post_date'] . ' + 1 week' ) );

		$next_post = wp_insert_post( $next_post );

		if ( is_wp_error( $next_post ) ) {
			return;
		}

		// Mirror any post_meta
		$post_meta = get_post_meta( get_the_id() );

		// Ingore some internal meta fields
		unset( $post_meta['_edit_lock'] );
		unset( $post_meta['_edit_last'] );

		// Don't copy the post repeat meta as only the original post should have that
		unset( $post_meta['hm-post-repeat'] );

		if ( $post_meta ) {
			foreach ( $post_meta as $key => $value ) {
				add_post_meta( $next_post, $key, $value );
			}
		}

		// Mirror any term relationships
		$taxonomies = get_object_taxonomies( get_post_type() );

		foreach( $taxonomies as $taxonomy ) {
			wp_set_object_terms( $next_post, wp_list_pluck( wp_get_object_terms( get_the_id(), $taxonomy ), 'slug' ), $taxonomy );
		}

	}

	/**
	 * The post types the feature is enabled on
	 *
	 * By default only posts have the feature enabled but others can be added with the `hm_post_repeat_post_types` filter.
	 *
	 * @return array An array of post types
	 */
	static function repeating_post_types() {

		/**
		 * Enable support for additional post types.
		 *
		 * @param array $post_types {
		 *     An array of post types
		 *
		 *     @type string $var Post type slug.
		 * }
		 */
		return apply_filters( 'hm_post_repeat_post_types', array( 'post' ) );

	}

	/**
	 * Check whether a given post_id is a repeating post.
	 *
	 * A repeating post is defined as the original post that was set to repeat.
	 *
	 * @param int  $post_id The id of the post you want to check
	 * @return bool Whether the past post_id is a repeating post or not.
	 */
	static function is_repeating_post( $post_id ) {

		// We check $_POST data so that this function works inside a `save_post` hook when the post_meta hasn't yet been saved
		if ( isset( $_POST['hm-post-repeat'] ) && $_POST['ID'] === $post_id ) {
			return true;
		}

		if ( get_post_meta( get_the_id(), 'hm-post-repeat', true ) ) {
			return true;
		}

		return false;

	}

	/**
	 * Check whether a given post_id is a repeat post.
	 *
	 * A repeat post is defined as any post which is a repeat of the original repeating post
	 *
	 * @param int  $post_id The id of the post you want to check
	 * @return bool Whether the past post_id is a repeat post or not.
	 */
	static function is_repeat_post( $post_id ) {

		if ( get_post( get_the_id() )->post_parent && get_post_meta( get_post( get_the_id() )->post_parent, 'hm-post-repeat', true ) ) {
			return true;
		}

		return false;

	}

}