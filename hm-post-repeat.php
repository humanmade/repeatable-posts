<?php

/*
Plugin Name: Repeatable Posts
Description: Designate a post as repeatable and it'll be copied and re-published on a weekly basis.
Author: Human Made Limited
Author URI: http://hmn.md/
Version: 0.2
License: GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.txt
Text Domain: hm-post-repeat
Domain Path: /languages
*/

/*
Copyright Human Made Limited  (email : hello@hmn.md)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

namespace HM\Post_Repeat;

/**
 * Setup the actions and filters required by this class.
 */
add_action( 'post_submitbox_misc_actions', __NAMESPACE__ . '\publish_box_ui' );
add_action( 'save_post', __NAMESPACE__ . '\create_next_post', 999, 2 );
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\enqueue_scripts' );
add_filter( 'display_post_states', __NAMESPACE__ . '\post_states' );

/**
 * Enqueue the scripts and styles that are needed by this plugin
 */
function enqueue_scripts( $hook ) {

	// Ensure we only load them on the edit post and add new post admin screens
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ) ) ) {
		return;
	}

	$plugin_data = get_plugin_data( __FILE__ );
	$plugin_dir_url = plugin_dir_url( __FILE__ );

	wp_enqueue_script( 'hm-post-repeat', $plugin_dir_url . 'hm-post-repeat.js', 'jquery', $plugin_data['Version'], true );
	wp_enqueue_style( 'hm-post-repeat', $plugin_dir_url . 'hm-post-repeat.css', array(), $plugin_data['Version'] );

}

/**
 * Output the Post Repeat UI that is shown in the Publish post meta box.
 *
 * The UI varies depending on whether the post is the original repeating post
 * or itself a repeat.
 */
function publish_box_ui() {

	if ( ! in_array( get_post_type(), repeating_post_types() ) ) {
		return;
	} ?>

	<div class="misc-pub-section misc-pub-hm-post-repeat">

		<span class="dashicons dashicons-controls-repeat"></span>

		<?php esc_html_e( 'Repeat:', 'hm-post-repeat' ); ?>

		<?php if ( is_repeat_post( get_the_id() ) ) : ?>

			<strong><?php printf( esc_html__( 'Repeat of %s', 'hm-post-repeat' ), '<a href="' . esc_url( get_edit_post_link( get_post()->post_parent ) ) . '">' . esc_html( get_the_title( get_post_field( 'post_parent', get_the_id() ) ) ) . '</a>' ); ?></strong>

		<?php else : ?>

			<?php $repeating_schedule = get_repeating_schedule( get_the_id() ); ?>
			<?php $is_repeating_post = is_repeating_post( get_the_id() ) && isset( $repeating_schedule ); ?>

			<strong><?php echo ! $is_repeating_post ? esc_html__( 'No', 'hm-post-repeat' ) : esc_html( $repeating_schedule['display'] ); ?></strong>

			<a href="#hm-post-repeat" class="edit-hm-post-repeat hide-if-no-js"><span aria-hidden="true"><?php esc_html_e( 'Edit', 'hm-post-repeat' ); ?></span> <span class="screen-reader-text"><?php esc_html_e( 'Edit Repeat Settings', 'hm-post-repeat' ); ?></span></a>

			<span class="hide-if-js" id="hm-post-repeat">

				<select name="hm-post-repeat">
					<option<?php selected( ! $is_repeating_post ); ?> value="no"><?php esc_html_e( 'No', 'hm-post-repeat' ); ?></option>
				<?php foreach ( repeating_schedules() as $schedule_slug => $schedule ) : ?>
					<option<?php selected( $is_repeating_post && $schedule_slug === $repeating_schedule['slug'] ); ?> value="<?php echo esc_attr( $schedule_slug ); ?>"><?php echo esc_html( $schedule['display'] ); ?></option>
				<?php endforeach; ?>
				</select>

				<a href="#hm-post-repeat" class="save-post-hm-post-repeat hide-if-no-js button"><?php esc_html_e( 'OK', 'hm-post-repeat' ); ?></a>

			</span>

		<?php endif; ?>

	</div>

<?php }

/**
 * Add some custom post states to cover repeat and repeating posts.
 *
 * By default post states are displayed on the Edit Post screen in bold after the post title
 *
 * @param array $post_states The original array of post states.
 * @return array The array of post states with ours added.
 */
function post_states( $post_states ) {

	if ( is_repeating_post( get_the_id() ) ) {
		// If the schedule has been removed since publishing, let the user know.
		if ( get_repeating_schedule( get_the_id() ) ) {
			$post_states['hm-post-repeat'] = __( 'Repeating', 'hm-post-repeat' );
		} else {
			$post_states['hm-post-repeat'] = __( 'Invalid Repeating Schedule', 'hm-post-repeat' );
		}
	}

	if ( is_repeat_post( get_the_id() ) ) {
		$post_states['hm-post-repeat'] = __( 'Repeat', 'hm-post-repeat' );
	}

	return $post_states;

}

/**
 * Save repeating status and possibly create the next repeat post.
 *
 * When a repeat post (or the original) is published we copy and schedule a new post
 * to publish in the chosen interval. That way the next repeat post is always ready to go.
 * This is hooked into save_post to make sure that all post fields and meta are up to date.
 *
 * @param integer $post_id The ID of the post.
 * @param WP_Post $post    The WP_Post object of the post.
 */
function create_next_post( $post_id, $post ) {

	if ( ! in_array( $post->post_type, repeating_post_types() ) ) {
		return;
	}

	// Are we publishing a repeat post
	if ( is_repeat_post( $post_id ) ) {

		// Only allow if it is actually being published
		if ( 'publish' !== $post->post_status ) {
			return;
		}

		// From here on we need the original post_id
		$post_id = $post->post_parent;
	}

	// Or the original
	elseif ( is_repeating_post( $post_id ) ) {

		if ( isset( $_POST['hm-post-repeat'] ) ) {
			if ( 'no' === $_POST['hm-post-repeat'] ) {
				delete_post_meta( $post_id, 'hm-post-repeat' );
			} else {
				update_post_meta( $post_id, 'hm-post-repeat', $_POST['hm-post-repeat'] );
			}
		}
	}

	// Or else we shouldn't even be here!
	else {
		return;
	}

	$original_post = get_post( $post_id, ARRAY_A );

	// Bail if we're not publishing a repeat(ing) post
	if ( ! $original_post ) {
		return;
	}

	// Bail if the saved schedule doesn't exist
	$repeating_schedule = get_repeating_schedule( $post_id );
	if ( ! $repeating_schedule ) {
		return;
	}

	// Bail if the original post isn't already published
	if ( 'publish' !== $original_post['post_status'] ) {
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

	// Set the next post to publish in the future
	$next_post['post_status'] = 'future';

	// Use the date of the current post being saved as the base
	$next_post['post_date'] = date( 'Y-m-d H:i:s', strtotime( $post->post_date . ' + ' . $repeating_schedule['interval'] ) );

	// Make sure the next post will be in the future
	if ( strtotime( $next_post['post_date'] ) <= time() ) {
		return;
	}

	// All checks done, get that post scheduled!
	$next_post_id = wp_insert_post( wp_slash( $next_post ), true );
	if ( is_wp_error( $next_post_id ) ) {
		return;
	}

	// Mirror any post_meta
	if ( $post_meta = get_post_meta( $post_id ) ) {
		// Ignore some internal meta fields
		unset( $post_meta['_edit_lock'] );
		unset( $post_meta['_edit_last'] );

		// Don't copy the post repeat meta as only the original post should have that
		unset( $post_meta['hm-post-repeat'] );

		foreach ( $post_meta as $key => $value ) {
			add_post_meta( $next_post_id, $key, wp_slash( $value ) );
		}
	}

	// Mirror any term relationships
	$taxonomies = get_object_taxonomies( $original_post['post_type'] );

	foreach ( $taxonomies as $taxonomy ) {
		wp_set_object_terms( $next_post_id, wp_list_pluck( wp_get_object_terms( $post_id, $taxonomy ), 'slug' ), $taxonomy );
	}

}

/**
 * The post types the feature is enabled on
 *
 * By default only posts have the feature enabled but others can be added with the `hm_post_repeat_post_types` filter.
 *
 * @return array An array of post types
 */
function repeating_post_types() {

	/**
	 * Enable support for additional post types.
	 *
	 * @param string[] $post_types Post type slugs.
	 */
	return apply_filters( 'hm_post_repeat_post_types', array( 'post' ) );

}

/**
 * All available repeat schedules.
 *
 * @return array An array of all available repeat schedules
 */
function repeating_schedules() {

	/**
	 * Enable support for additional schedules.
	 *
	 * @param array[] $schedules Schedule array items.
	 */
	$schedules = apply_filters( 'hm_post_repeat_schedules', array(
		'daily'   => array( 'interval' => '1 day',   'display' => __( 'Daily',   'hm-post-repeat' ) ),
		'weekly'  => array( 'interval' => '1 week',  'display' => __( 'Weekly',  'hm-post-repeat' ) ),
		'monthly' => array( 'interval' => '1 month', 'display' => __( 'Monthly', 'hm-post-repeat' ) ),
	) );

	foreach ( $schedules as $slug => &$schedule ) {
		$schedule['slug'] = $slug;
	}

	return $schedules;

}

/**
 * Get the repeating schedule of the given post_id.
 *
 * @param int $post_id The id of the post you want to check.
 * @return array|null The schedule to repeat by, or null if invalid.
 */
function get_repeating_schedule( $post_id ) {

	if ( ! is_repeating_post( $post_id ) ) {
		return;
	}

	if ( $repeating_schedule = get_post_meta( $post_id, 'hm-post-repeat', true ) ) {
		$schedules = repeating_schedules();
		if ( array_key_exists( $repeating_schedule, $schedules ) ) {
			return $schedules[ $repeating_schedule ];
		}
	}

}

/**
 * Check whether a given post_id is a repeating post.
 *
 * A repeating post is defined as the original post that was set to repeat.
 *
 * @param int $post_id The id of the post you want to check.
 * @return bool Whether the past post_id is a repeating post or not.
 */
function is_repeating_post( $post_id ) {

	// We check $_POST data so that this function works inside a `save_post` hook when the post_meta hasn't yet been saved
	if ( isset( $_POST['hm-post-repeat'] ) && isset( $_POST['ID'] ) && $_POST['ID'] === $post_id ) {
		return true;
	}

	if ( get_post_meta( $post_id, 'hm-post-repeat', true ) ) {
		return true;
	}

	return false;

}

/**
 * Check whether a given post_id is a repeat post.
 *
 * A repeat post is defined as any post which is a repeat of the original repeating post
 *
 * @param int $post_id The id of the post you want to check.
 * @return bool Whether the past post_id is a repeat post or not.
 */
function is_repeat_post( $post_id ) {

	$post_parent = get_post_field( 'post_parent', $post_id );

	if ( $post_parent && get_post_meta( $post_parent, 'hm-post-repeat', true ) ) {
		return true;
	}

	return false;

}
