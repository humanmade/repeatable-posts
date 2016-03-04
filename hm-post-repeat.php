<?php

/*
Plugin Name: Repeatable Posts
Description: Designate a post as repeatable and it'll be copied and re-published on your chosen interval.
Author: Human Made Limited
Author URI: http://hmn.md/
Version: 0.4
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
add_action( 'save_post', __NAMESPACE__ . '\save_post_repeating_status', 10 );
add_action( 'save_post', __NAMESPACE__ . '\create_next_repeat_post', 11 );
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\enqueue_scripts' );
add_filter( 'display_post_states', __NAMESPACE__ . '\post_states', 10, 2 );

/**
 * Enqueue the scripts and styles that are needed by this plugin.
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

			<?php

			$repeating_schedule = get_repeating_schedule( get_the_id() );
			$is_repeating_post = is_repeating_post( get_the_id() ) && isset( $repeating_schedule );

			$post_repeat_end = get_post_repeat_end_from_post_meta( get_the_id() );
			if ( is_null( $post_repeat_end ) ) {
				$post_repeat_end = array( 'type' => 'forever' );
			}

			?>

			<strong>
			<?php

			if ( $is_repeating_post ) {
				echo esc_html( $repeating_schedule['display'] ) . ', ';

				switch ( $post_repeat_end['type'] ) {
					case 'forever':
						esc_html_e( 'Forever', 'hm-post-repeat' );
						break;
					case 'until':
						echo esc_html( sprintf( __( 'Until %s', 'hm-post-repeat' ), date_i18n( 'M j, Y', $post_repeat_end['value'] ) ) );
						break;
					case 'times':
						echo esc_html( sprintf( __( '%d Times', 'hm-post-repeat' ), $post_repeat_end['value'] ) );
						break;
				}
			} else {
				esc_html_e( 'No', 'hm-post-repeat' );
			}

			?>
			</strong>

			<a href="#hm-post-repeat" class="edit-hm-post-repeat hide-if-no-js"><span aria-hidden="true"><?php esc_html_e( 'Edit', 'hm-post-repeat' ); ?></span> <span class="screen-reader-text"><?php esc_html_e( 'Edit Repeat Settings', 'hm-post-repeat' ); ?></span></a>

			<span class="hide-if-js" id="hm-post-repeat">

				<input type="hidden" id="hidden_hm-post-repeat-schedule" name="hidden_hm-post-repeat-schedule" value="<?php echo ( isset( $repeating_schedule['slug'] ) ) ? esc_attr( $repeating_schedule['slug'] ) : 'no'; ?>" />
				<select id="hm-post-repeat-schedule" name="hm-post-repeat-schedule">
					<option<?php selected( ! $is_repeating_post ); ?> value="no"><?php esc_html_e( 'No', 'hm-post-repeat' ); ?></option>
					<?php foreach ( get_repeating_schedules() as $schedule_slug => $schedule ) : ?>
						<option<?php selected( $is_repeating_post && ( $schedule_slug === $repeating_schedule['slug'] ) ); ?> value="<?php echo esc_attr( $schedule_slug ); ?>"><?php echo esc_html( $schedule['display'] ); ?></option>
					<?php endforeach; ?>
				</select>

				<div id="hm-post-repeat-end">

					<p>
						<label>
							<input type="radio" name="hm-post-repeat-end" value="forever"<?php checked( $post_repeat_end['type'], 'forever' ); ?>><?php esc_html_e( 'Forever', 'hm-post-repeat' ); ?>
						</label>
					</p>
					<p>
						<label>
							<input type="radio" name="hm-post-repeat-end" value="until"<?php checked( $post_repeat_end['type'], 'until' ); ?>><?php esc_html_e( 'Until', 'hm-post-repeat' ); ?>
						</label>
						<?php hm_date_input( ( 'until' === $post_repeat_end['type'] ) ? $post_repeat_end['value'] : time() ); ?>
					</p>
					<p>
						<label>
							<input type="radio" name="hm-post-repeat-end" value="times"<?php checked( $post_repeat_end['type'], 'times' ); ?>>
							<input type="number" id="hm-post-repeat-times" name="hm-post-repeat-times" min="1" value="<?php echo ( 'times' === $post_repeat_end['type'] ) ? $post_repeat_end['value'] : '1'; ?>" /><?php esc_html_e( 'Times', 'hm-post-repeat' ); ?>
						</label>
					</p>

				</div>

				<p>
					<a href="#edit-hm-post-repeat" class="save-post-hm-post-repeat hide-if-no-js button"><?php esc_html_e( 'OK', 'hm-post-repeat' ); ?></a>
					<a href="#edit-hm-post-repeat" class="cancel-post-hm-post-repeat hide-if-no-js button-cancel"><?php esc_html_e( 'Cancel', 'hm-post-repeat' ); ?></a>
				</p>

			</span>

		<?php endif; ?>

	</div>

<?php }


function hm_date_input( $date ) {

	global $wp_locale;

	/*[
		'schedule' => 'weekly',
		'repeat' => 'forever',
		'repeat' => 'times 4',
		'repeat' => 'until 2016-01-01',
	];*/

	if ( is_numeric( $date ) ) {
		$date = date( 'Y-m-d', $date );
	}

	$cur_date = current_time( 'timestamp' );
	$cur_d = gmdate( 'd', $cur_date );
	$cur_m = gmdate( 'm', $cur_date );
	$cur_Y = gmdate( 'Y', $cur_date );

	$d = ( $date ) ? mysql2date( 'd', $date, false ) : gmdate( 'd', $cur_d );
	$m = ( $date ) ? mysql2date( 'm', $date, false ) : gmdate( 'm', $cur_m );
	$Y = ( $date ) ? mysql2date( 'Y', $date, false ) : gmdate( 'Y', $cur_Y );

	$month = '<label><span class="screen-reader-text">' . __( 'Month', 'hm-post-repeat' ) . '</span><select id="hm-post-repeat-until-month" name="hm-post-repeat-until-month">';
	for ( $i = 1; $i < 13; $i++ ) {
		$monthnum = zeroise( $i, 2 );
		$monthtext = $wp_locale->get_month_abbrev( $wp_locale->get_month( $i ) );
		$month .= '<option value="' . $monthnum . '" data-text="' . $monthtext . '" ' . selected( $monthnum, $m, false ) . '>';
		/* translators: 1: month number (01, 02, etc.), 2: month abbreviation */
		$month .= sprintf( __( '%1$s-%2$s' ), $monthnum, $monthtext ) . "</option>\n";
	}
	$month .= '</select></label>';

	$day = '<label><span class="screen-reader-text">' . __( 'Day', 'hm-post-repeat' ) . '</span><input type="text" id="hm-post-repeat-until-day" name="hm-post-repeat-until-day" value="' . $d . '" size="2" maxlength="2" autocomplete="off" /></label>';
	$year = '<label><span class="screen-reader-text">' . __( 'Year', 'hm-post-repeat' ) . '</span><input type="text" id="hm-post-repeat-until-year" name="hm-post-repeat-until-year" value="' . $Y . '" size="4" maxlength="4" autocomplete="off" /></label>';

	echo '<span class="hm-post-repeat-until-wrap">';
	/* translators: 1: month, 2: day, 3: year */
	printf( __( '%1$s %2$s, %3$s', 'hm-post-repeat' ), $month, $day, $year );

	echo '</span>';

	$map = array(
		'hm-post-repeat-until-day'   => array( $d, $cur_d ),
		'hm-post-repeat-until-month' => array( $m, $cur_m ),
		'hm-post-repeat-until-year'  => array( $Y, $cur_Y ),
	);
	foreach ( $map as $timeunit => $value ) {
		list( $unit, $curr ) = $value;

		echo '<input type="hidden" id="hidden_' . $timeunit . '" name="hidden_' . $timeunit . '" value="' . $unit . '" />';
		echo '<input type="hidden" id="cur_' . $timeunit . '" name="cur_' . $timeunit . '" value="' . $curr . '" />';
	}

}

/**
 * Add some custom post states to cover repeat and repeating posts.
 *
 * By default post states are displayed on the Edit Post screen in bold after the post title.
 *
 * @param array   $post_states The original array of post states.
 * @param WP_Post $post        The post object to get / return the states.
 * @return array The array of post states with ours added.
 */
function post_states( $post_states, $post ) {

	if ( is_repeating_post( $post->ID ) ) {

		// If the schedule has been removed since publishing, let the user know.
		if ( $schedule = get_repeating_schedule( $post->ID ) ) {
			$post_states['hm-post-repeat'] = __( 'Repeating', 'hm-post-repeat' ) . ': ' . $schedule['display'];
			$post_repeat_end = get_post_repeat_end_from_post_meta( $post->ID );
			switch ( $post_repeat_end['type'] ) {
				case 'forever':
					$post_repeat_end_string = __( 'Forever', 'hm-post-repeat' );
					break;
				case 'until':
					$post_repeat_end_string = sprintf( __( 'Until %s', 'hm-post-repeat' ), date_i18n( 'M j, Y', $post_repeat_end['value'] ) );
					break;
				case 'times':
					$post_repeat_end_string = sprintf( __( '%d more times', 'hm-post-repeat' ), $post_repeat_end['value'] );
					break;
			}
			if ( isset( $post_repeat_end_string ) ) {
				$post_states['hm-post-repeat'] .= ', ' . $post_repeat_end_string;
			}
		} else {
			$post_states['hm-post-repeat'] = __( 'Invalid Repeating Schedule', 'hm-post-repeat' );
		}

	}

	if ( is_repeat_post( $post->ID ) ) {
		$post_states['hm-post-repeat'] = __( 'Repeat', 'hm-post-repeat' );
	}

	return $post_states;

}

/**
 * Save the repeating status to post meta.
 *
 * Hooked into `save_post`. When saving a post that has been set to repeat we save a post meta entry.
 *
 * @param int    $post_id                 The ID of the post.
 * @param string $post_repeat_setting     Used to manually set the repeating schedule from tests.
 * @param array  $post_repeat_end_setting Used to manually set the repeat end from tests.
 */
function save_post_repeating_status( $post_id = null, $post_repeat_setting = null, $post_repeat_end_setting = null ) {

	if ( is_null( $post_repeat_setting ) ) {
		$post_repeat_setting = get_post_data( 'hm-post-repeat-schedule' );
	}

	if ( empty( $post_repeat_setting ) || ! in_array( get_post_type( $post_id ), repeating_post_types() ) ) {
		return;
	}

	// Make sure we have a valid schedule and repeat end
	if ( 'no' !== $post_repeat_setting && array_key_exists( $post_repeat_setting, get_repeating_schedules() ) ) {

		// If the repeat end has been reached, stop repeating
		if ( is_post_repeat_end_reached( $post_id ) ) {
			$post_repeat_setting = 'no';
		} else {
			if ( is_null( $post_repeat_end_setting ) ) {
				$post_repeat_end_setting = get_post_repeat_end_from_post_form( $post_id );
			}

			update_post_meta( $post_id, 'hm-post-repeat', $post_repeat_setting );
			update_post_repeat_end_meta( $post_id, $post_repeat_end_setting );
		}
	}

	if ( 'no' === $post_repeat_setting ) {
		update_post_meta( $post_id, 'hm-post-repeat', 'no' );
		//delete_post_meta( $post_id, 'hm-post-repeat' );
		//delete_post_meta( $post_id, 'hm-post-repeat-end' );
	}

}

/**
 * Check if the next repeat post should be created.
 *
 * @param int $post_id The ID of the post.
 * @return bool If all checks to create a repeat post have passed.
 */
function create_next_repeat_post_allowed( $post_id ) {
	// Bail if this post type isn't allowed to be repeated
	if ( ! in_array( get_post_type( $post_id ), repeating_post_types() ) ) {
		return false;
	}

	// Bail if this post hasn't been published
	if ( 'publish' !== get_post_status( $post_id ) ) {
		return false;
	}

	$original_post_id = get_repeating_post( $post_id );

	// Bail if we're not publishing a repeat(ing) post
	if ( ! $original_post_id ) {
		return false;
	}

	// Bail if there is already a repeat post scheduled, don't create another one
	if ( get_next_scheduled_repeat_post( $original_post_id ) ) {
		return false;
	}

	// Bail if the saved schedule doesn't exist
	$repeating_schedule = get_repeating_schedule( $original_post_id );

	if ( ! $repeating_schedule ) {
		return false;
	}

	// Bail if the original post isn't already published
	if ( 'publish' !== get_post_status( $original_post_id ) ) {
		return false;
	}

	// Bail if the repetition end has been reached
	if ( is_post_repeat_end_reached( $original_post_id ) ) {
		return false;
	}

	// All good!
	return true;
}

/**
 * Create the next repeat post when the last one is published.
 *
 * When a repeat post (or the original) is published we copy and schedule a new post
 * to publish on the correct interval. That way the next repeat post is always ready to go.
 * This is hooked into publish_post so that the repeat post is only created when the original
 * is published.
 *
 * @param int $post_id The ID of the post.
 */
function create_next_repeat_post( $post_id ) {

	if ( ! create_next_repeat_post_allowed( $post_id ) ) {
		return false;
	}

	// if ( ! in_array( get_post_type( $post_id ), repeating_post_types() ) ) {
	// 	return false;
	// }

	// if ( 'publish' !== get_post_status( $post_id ) ) {
	// 	return false;
	// }

	// $original_post_id = get_repeating_post( $post_id );

	// // Bail if we're not publishing a repeat(ing) post
	// if ( ! $original_post_id ) {
	// 	return false;
	// }

	// $original_post = get_post( $original_post_id, ARRAY_A );

	// // If there is already a repeat post scheduled don't create another one
	// if ( get_next_scheduled_repeat_post( $original_post_id ) ) {
	// 	return false;
	// }

	// // Bail if the saved schedule doesn't exist
	// $repeating_schedule = get_repeating_schedule( $original_post_id );

	// if ( ! $repeating_schedule ) {
	// 	return false;
	// }

	// // Bail if the original post isn't already published
	// if ( 'publish' !== $original_post['post_status'] ) {
	// 	return false;
	// }

	// // Bail if the repetition end has been reached
	// if ( is_post_repeat_end_reached( $original_post_id ) ) {
	// 	return false;
	// }

	if ( /*is_repeat_post( $post_id ) &&*/ is_post_repeat_end_type( $original_post_id, 'times' ) ) {
		$post_repeat_end = get_post_repeat_end_from_post_meta( $original_post_id );
		--$post_repeat_end['value'];
		update_post_repeat_end_meta( $original_post_id, $post_repeat_end );
		/*if ( $post_repeat_end['value'] <= 0 ) {
			return false;
		}*/
	}

	$next_post = get_post( $original_post_id, ARRAY_A );

	// Create the repeat post as a copy of the original, but ignore some fields
	unset( $next_post['ID'] );
	unset( $next_post['guid'] );
	unset( $next_post['post_date_gmt'] );
	unset( $next_post['post_modified'] );
	unset( $next_post['post_modified_gmt'] );

	// We set the post_parent to the original post_id, so they're related
	$next_post['post_parent'] = $original_post_id;

	// Set the next post to publish in the future
	$next_post['post_status'] = 'future';

	// Use the date of the current post being saved as the base
	$next_post['post_date'] = date( 'Y-m-d H:i:s', strtotime( get_post_field( 'post_date', $post_id ) . ' + ' . $repeating_schedule['interval'] ) );

	// Make sure the next post will be in the future
	if ( strtotime( $next_post['post_date'] ) <= time() ) {
		return false;
	}

	// All checks done, get that post scheduled!
	$next_post_id = wp_insert_post( wp_slash( $next_post ), true );

	if ( is_wp_error( $next_post_id ) ) {
		return false;
	}

	// Mirror any post_meta
	$post_meta = get_post_meta( $original_post_id );

	if ( $post_meta  ) {

		// Ignore some internal meta fields
		unset( $post_meta['_edit_lock'] );
		unset( $post_meta['_edit_last'] );

		// Don't copy the post repeat meta as only the original post should have that
		unset( $post_meta['hm-post-repeat'] );
		unset( $post_meta['hm-post-repeat-end'] );

		foreach ( $post_meta as $key => $values ) {
			foreach ( $values as $value ) {
				add_post_meta( $next_post_id, $key, maybe_unserialize( $value ) );
			}
		}
	}

	// Mirror any term relationships
	$taxonomies = get_object_taxonomies( get_post_type( $original_post_id ) );

	foreach ( $taxonomies as $taxonomy ) {
		wp_set_object_terms( $next_post_id, wp_list_pluck( wp_get_object_terms( $original_post_id, $taxonomy ), 'slug' ), $taxonomy );
	}

	return $next_post_id;

}

function is_post_repeat_end_type( $post_id, $type ) {
	if ( $post_repeat_end = get_post_repeat_end_from_post_meta( $post_id ) ) {
		return ( $type === $post_repeat_end['type'] );
	}
	return false;
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
function get_repeating_schedules() {

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

	$repeating_schedule = get_post_meta( $post_id, 'hm-post-repeat', true );
	$schedules = get_repeating_schedules();

	if ( isset( $repeating_schedule['schedule'] ) ) {
		$repeating_schedule = $repeating_schedule['schedule'];
	} elseif ( '1' === $repeating_schedule ) {
		// Backwards compatibility with 0.3 when we only supported weekly
		$repeating_schedule = 'weekly';
	}

	if ( array_key_exists( $repeating_schedule, $schedules ) ) {
		return $schedules[ $repeating_schedule ];
	}

}

/**
 * Get the repetition end of the given post_id.
 *
 * @param int $post_id The id of the post you want to check.
 * @return array|null The repetition end set, or null if not a repeating post.
 */
/*function get_repeat_end( $post_id ) {

	if ( ! is_repeating_post( $post_id ) ) {
		return;
	}

	if ( $repetition_end = get_post_meta( $post_id, 'hm-post-repeat-end', true ) ) {
		if ( is_numeric( $repetition_end ) ) {
				// Number of times to repeat
				return $repetition_end;
		} elseif ( $date = \DateTime::createFromFormat( 'Y-m-d', $$repetition_end ) ) {
			// Repeat until a certain date
			return $date;
		}
	}

	// Backwards compatibility with 0.4 when no repetition end existed
	return 'forever';

}*/

/**
 * Check whether a given post_id has reached the end of its repetition.
 *
 * @param int $post_id The id of the post you want to check.
 * @return bool Whether the given post_id has reached the end of its repetition.
 */
function is_post_repeat_end_reached( $post_id ) {

/*	if ( ! is_repeating_post( $post_id ) ) {
		return true;
	}*/
	$post_id = get_repeating_post( $post_id );

	// We check $_POST data so that this function works inside a `save_post` hook when the post_meta hasn't yet been saved
	$repeat_end = get_post_repeat_end_from_post_form( $post_id );

	// If not from the post form data, let's try the post meta
	empty( $repeat_end ) && $repeat_end = get_post_repeat_end_from_post_meta( $post_id );

	switch ( $repeat_end['type'] ) {
		//case 'until': return ( $repeat_end['value'] < strtotime( get_post( $post_id )->post_date ) );
		//return ( $repeat_end['value'] < strtotime( get_post_field( 'post_date', $post_id ) . ' + ' . $repeating_schedule['interval'] ) );
		case 'until':
			$repeating_schedule = get_repeating_schedule( $post_id );
			return ( $repeat_end['value'] < strtotime( get_post_field( 'post_date', $post_id ) . ' + ' . $repeating_schedule['interval'] ) );
			//$date = ( strtotime( get_post_field( 'post_date', $post_id ) ) < time() ) ? date( 'Y-m-d' ) : get_post_field( 'post_date', $post_id );
			//return ( $repeat_end['value'] < strtotime( $date . ' + ' . $repeating_schedule['interval'] ) );
		case 'times': return ( is_null( $repeat_end['value'] ) || $repeat_end['value'] <= 0 );
	}

	// Ok, we're repeating forever
	return false;

}

/**
 * Update the post repeat end post meta.
 *
 * @param int   $post_id         The ID of the post.
 * @param array $post_repeat_end Array with post repeat end data.
 */
function update_post_repeat_end_meta( $post_id, $post_repeat_end ) {

	if ( is_array( $post_repeat_end ) && 2 === count( $post_repeat_end ) ) {
		$post_repeat_end_meta = 'forever';
		if ( in_array( $post_repeat_end['type'], array( 'until', 'times' ) ) ) {
			$post_repeat_end_meta = $post_repeat_end['type'] . ':' . $post_repeat_end['value'];
		}
		update_post_meta( $post_id, 'hm-post-repeat-end', $post_repeat_end_meta );
	}

}

/**
 * Get the post repeat end data from $_POST form.
 *
 * @param int $post_id The ID of the post to get the repeat end data.
 * @return array $post_repeat_end Array with post repeat end data.
 */
function get_post_repeat_end_from_post_form( $post_id ) {

	if ( ( $repeat_end = get_post_data( 'hm-post-repeat-end' ) ) && (int) get_post_data( 'ID' ) === $post_id ) {
		$end = array(
			'type'  => 'forever',
			'value' => null,
		);

		switch ( $repeat_end ) {
			case 'until':
				$end['type'] = 'until';
				$day = get_post_data( 'hm-post-repeat-until-day' );
				$month = get_post_data( 'hm-post-repeat-until-month' );
				$year = get_post_data( 'hm-post-repeat-until-year' );
				if ( isset( $day, $month, $year ) && checkdate( $month, $day, $year ) ) {
					$end['value'] = strtotime( "$year-$month-$day" );
				}
				break;
			case 'times':
				$end['type'] = 'times';
				if ( $times = get_post_data( 'hm-post-repeat-times' ) ) {
					$end['value'] = (int) $times;
				}
				break;
		}

		return $end;
	}

}

/**
 * Get the post repeat end data from the post's meta data.
 *
 * @param int $post_id The ID of the post to get the repeat end993743 data.
 * @return array $post_repeat_end Array with post repeat end data.
 */
function get_post_repeat_end_from_post_meta( $post_id ) {

	if ( $repeat_end = get_post_meta( $post_id, 'hm-post-repeat-end', true ) ) {
		$end = array(
			'type'  => 'forever',
			'value' => null,
		);

		if (
			( $parts = explode( ':', $repeat_end ) ) &&
			( 2 === count( $parts ) ) &&
			( in_array( $parts[0], array( 'until', 'times' ) ) )
		) {
			$end['type'] = $parts[0];
			$end['value'] = (int) $parts[1];
		}

		return $end;
	}

}


/*function decrease_post_repeat_end_times( $post_id ) {
	$repeat_end = get_post_meta( $post_id, 'hm-post-repeat-end', true );
	if ( ( $parts = explode( ':', $repeat_end ) ) && 2 === count( $parts ) && 'times' === $parts[0] ) {
		$times = max( (int) $parts[1] - 1, 0 );
		return update_post_meta( $post_id, 'hm-post-repeat-end', 'times:' . $times, $repeat_end );
	}
}*/

/**
 * Little helper to get form data from $_POST.
 *
 * @param string $key     Form element to fetch.
 * @param mixed  $default If value is null, return this default instead.
 * @return string|null Value of the fetched element, or null.
 */
function get_post_data( $key, $default = null ) {
	return ( isset( $_POST[ $key ] ) ) ? sanitize_text_field( $_POST[ $key ] ) : $default;
}

/**
 * Check whether a given post_id is a repeating post.
 *
 * A repeating post is defined as the original post that was set to repeat.
 *
 * @param int $post_id The id of the post you want to check.
 * @return bool Whether the passed post_id is a repeating post or not.
 */
function is_repeating_post( $post_id ) {

	// We check $_POST data so that this function works inside a `save_post` hook when the post_meta hasn't yet been saved
	if ( isset( $_POST['hm-post-repeat-schedule'] ) && (int) get_post_data( 'ID' ) === $post_id ) {
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
 * A repeat post is defined as any post which is a repeat of the original repeating post.
 *
 * @param int $post_id The id of the post you want to check.
 * @return bool Whether the passed post_id is a repeat post or not.
 */
function is_repeat_post( $post_id ) {

	$post_parent = get_post_field( 'post_parent', $post_id );

	if ( $post_parent && get_post_meta( $post_parent, 'hm-post-repeat', true ) ) {
		return true;
	}

	return false;

}

/**
 * Get the next scheduled repeat post
 *
 * @param int $post_id The id of a repeat or repeating post.
 * @return int|bool Return the ID of the next repeat post_id or false if it can't find one.
 */
function get_next_scheduled_repeat_post( $post_id ) {

	$repeat_posts = get_posts( array( 'post_status' => 'future', 'post_parent' => get_repeating_post( $post_id ) ) );

	if ( isset( $repeat_posts[0] ) ) {
		return $repeat_posts[0];
	}

	return false;

}

/**
 * Get the next scheduled repeat post
 *
 * @param int $post_id The id of a repeat or repeating post.
 * @return int|bool Return the original repeating post_id or false if it can't find it.
 */
function get_repeating_post( $post_id ) {

	$original_post_id = false;

	// Are we publishing a repeat post
	if ( is_repeat_post( $post_id ) ) {
		$original_post_id = get_post( $post_id )->post_parent;
	}

	// Or the original
	elseif ( is_repeating_post( $post_id ) ) {
		$original_post_id = $post_id;
	}

	return $original_post_id;

}
