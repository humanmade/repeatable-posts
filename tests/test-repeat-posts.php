<?php

namespace HM\Post_Repeat;

class PostRepeatTests extends \WP_UnitTestCase {

	function tearDown() {
		_delete_all_posts();
	}

	function test_setting_post_repeating_status_no() {

		$post_id = $this->factory->post->create();
		$this->assertFalse( is_repeating_post( $post_id ) );

		save_post_repeating_status( $post_id, 'no' );
		$this->assertFalse( is_repeating_post( $post_id ) );

	}

	function test_setting_post_repeating_status_yes() {

		$post_id = $this->factory->post->create();
		$this->assertFalse( is_repeating_post( $post_id ) );

		save_post_repeating_status( $post_id, 'weekly' );
		$this->assertTrue( is_repeating_post( $post_id ) );

	}

	function test_setting_post_repeating_status_blank() {

		$post_id = $this->factory->post->create();
		$this->assertFalse( is_repeating_post( $post_id ) );

		save_post_repeating_status( $post_id, 'weekly' );
		$this->assertTrue( is_repeating_post( $post_id ) );

		save_post_repeating_status( $post_id );
		$this->assertTrue( is_repeating_post( $post_id ) );

	}

	function test_repeating_post_types_filter() {

		$this->assertContains( 'post', repeating_post_types() );

		add_filter( 'hm_post_repeat_post_types', function( $post_types ) {
			$post_types[] = 'page';
			return $post_types;
		} );

		$this->assertContains( 'page', repeating_post_types() );

	}

	function test_is_repeating_post_save_post_hook() {

		$post_id = $this->factory->post->create();
		$this->assertFalse( is_repeating_post( $post_id ) );

		$_POST['ID'] = $post_id;
		$_POST['hm-post-repeat'] = 'weekly';
		$this->assertTrue( is_repeating_post( $post_id ) );

	}

	function test_repeat_post_no() {

		$post_id = $this->factory->post->create();
		$this->assertFalse( is_repeat_post( $post_id ) );

	}

	function test_repeat_post_yes() {

		$parent_post_id = $this->factory->post->create();
		$post_id = $this->factory->post->create( array( 'post_parent' => $parent_post_id ) );

		$this->assertFalse( is_repeat_post( $post_id ) );
		$this->assertEquals( $parent_post_id, get_post( $post_id )->post_parent );

		save_post_repeating_status( $parent_post_id, 'weekly' );

		$this->assertTrue( is_repeat_post( $post_id ) );

	}

	function test_post_states() {

		global $post_states;

		$parent_post_id = $this->factory->post->create();
		$post_id = $this->factory->post->create( array( 'post_parent' => $parent_post_id ) );
		save_post_repeating_status( $parent_post_id, 'weekly' );

		// Hack to allow us access to $post_states so we can test it
		add_filter( 'display_post_states', function( $states, $post ) {

			global $post_states;
			$post_states = $states;

			return $states;

		}, 11, 2 );

		// We need to output buffer _post_states as it echo's
		ob_start();
		_post_states( get_post( $post_id ) );
		ob_end_clean();

		$this->assertEquals( array( 'hm-post-repeat' => __( 'Repeat', 'hm-post-repeat' ) ), $post_states );

		ob_start();
		_post_states( get_post( $parent_post_id ) );
		ob_end_clean();

		$this->assertEquals( array( 'hm-post-repeat' => __( 'Repeating', 'hm-post-repeat' ) ), $post_states );

	}

	function test_create_get_repeating_post() {

		$parent_post_id = $this->factory->post->create();
		$post_id = $this->factory->post->create( array( 'post_parent' => $parent_post_id ) );
		save_post_repeating_status( $parent_post_id, 'weekly' );

		$this->assertEquals( $parent_post_id, get_repeating_post( $parent_post_id ) );
		$this->assertEquals( $parent_post_id, get_repeating_post( $post_id ) );

	}

	function test_create_repeat_post_from_not_repeating() {

		$post_id = $this->factory->post->create();
		$this->assertFalse( create_next_repeat_post( $post_id ) );
		$this->assertCount( 0, get_posts( array( 'post_status' => 'future' ) ) );

	}

	function test_create_repeat_post_from_published_repeating_post() {

		$post_id = $this->factory->post->create();
		save_post_repeating_status( $post_id, 'weekly' );

		$this->assertCount( 0, get_posts( array( 'post_status' => 'future' ) ) );

		$this->assertEquals( $post_id + 1, create_next_repeat_post( $post_id ) );
		$this->assertCount( 1, get_posts( array( 'post_status' => 'future' ) ) );

		// Prove that another repeat post isn't created
		$this->assertFalse( create_next_repeat_post( $post_id ) );
		$this->assertCount( 1, get_posts( array( 'post_status' => 'future' ) ) );

	}

	function test_create_repeat_post_from_unpublished_repeating_post() {

		$post_id = $this->factory->post->create( array( 'post_status' => 'draft' ) );
		save_post_repeating_status( $post_id, 'weekly' );

		$this->assertCount( 0, get_posts( array( 'post_status' => 'future' ) ) );

		$this->assertFalse( create_next_repeat_post( $post_id ) );
		$this->assertCount( 0, get_posts( array( 'post_status' => 'future' ) ) );

		wp_update_post( array( 'ID' => $post_id, 'post_status' => 'pending' ) );
		$this->assertFalse( create_next_repeat_post( $post_id ) );
		$this->assertCount( 0, get_posts( array( 'post_status' => 'future' ) ) );

		wp_update_post( array( 'ID' => $post_id, 'post_status' => 'custom' ) );
		$this->assertFalse( create_next_repeat_post( $post_id ) );
		$this->assertCount( 0, get_posts( array( 'post_status' => 'future' ) ) );

		// future post statuses are converted to publish if the post date is in the past
		wp_update_post( array( 'ID' => $post_id, 'post_status' => 'future' ) );
		$this->assertFalse( create_next_repeat_post( $post_id ) );
		$this->assertCount( 1, get_posts( array( 'post_status' => 'future' ) ) );

	}

	function test_create_repeat_post_from_repeating_post_publish_action() {

		$post_id = $this->factory->post->create( array( 'post_status' => 'draft' ) );
		save_post_repeating_status( $post_id, 'weekly' );

		$this->assertCount( 0, get_posts( array( 'post_status' => 'future' ) ) );

		wp_update_post( array( 'ID' => $post_id, 'post_status' => 'publish' ) );
		$this->assertCount( 1, get_posts( array( 'post_status' => 'future' ) ) );

	}

	function test_create_repeat_unsupported_repeating_post_type() {

		$post_id = $this->factory->post->create( array( 'post_type' => 'middle-out-encryption' ) );
		save_post_repeating_status( $post_id, 'weekly' );

		$this->assertFalse( create_next_repeat_post( $post_id ) );

	}

	function test_create_repeat_post_copies_meta() {

		$post_id = $this->factory->post->create();
		save_post_repeating_status( $post_id, 'weekly' );

		$meta = array( 'NonEmptyString' => 'Test', 'Int' => 134, 'EmptyString' => '', 'bool' => true, 'object' => get_post( $post_id ), 'array' => get_post( $post_id, ARRAY_A ) );

		foreach ( $meta as $key => $value ) {
			add_post_meta( $post_id, $key, $value );
		}

		$post_meta = get_post_meta( $post_id );

		// `hm-post-repeat` isn't copied over
		unset( $post_meta['hm-post-repeat'] );

		$repeat_post_id = create_next_repeat_post( $post_id );
		$repeat_post_meta = get_post_meta( $repeat_post_id );
		$this->assertEquals( $post_meta, $repeat_post_meta );

	}

	function test_create_repeat_post_copies_terms() {

		$post_id = $this->factory->post->create();
		$this->factory->term->add_post_terms( $post_id, array( 'Tag 1', 'Tag 2' ), 'post_tag' );

		$cat1 = $this->factory->term->create_object( array( 'taxonomy' => 'category', 'name' => 'Cat 1' ) );
		$cat2 = $this->factory->term->create_object( array( 'taxonomy' => 'category', 'name' => 'Cat 2' ) );

		$this->factory->term->add_post_terms( $post_id, array( $cat1, $cat2 ), 'category' );

		$this->assertTrue( has_tag( 'Tag 1', $post_id ) );
		$this->assertTrue( has_tag( 'Tag 2', $post_id ) );

		$this->assertTrue( has_category( $cat1, $post_id ) );
		$this->assertTrue( has_category( $cat2, $post_id ) );

		save_post_repeating_status( $post_id, 'weekly' );

		$repeat_post_id = create_next_repeat_post( $post_id );

		$this->assertTrue( has_tag( 'Tag 1', $repeat_post_id ) );
		$this->assertTrue( has_tag( 'Tag 2', $repeat_post_id ) );

		$this->assertTrue( has_category( $cat1, $repeat_post_id ) );
		$this->assertTrue( has_category( $cat2, $repeat_post_id ) );

	}

	/**
	 * Specifically test that the repeating status is saved to post meta and the next
	 * repeat post is created & scheduled when publishing a new repeating post
	 */
	function test_publish_repeating_post_creates_repeat_post() {

		$_POST['hm-post-repeat'] = 'weekly';
		$post_id = $this->factory->post->create();
		$this->assertTrue( is_repeating_post( $post_id ) );

		$this->assertCount( 1, get_posts( array( 'post_status' => 'future' ) ) );

	}

	function test_repeating_post_interval_invalid() {

		$_POST['hm-post-repeat'] = 'some-day';
		$post = $this->factory->post->create_and_get();
		$this->assertFalse( is_repeating_post( $post->ID ) );

		$future_posts = get_posts( array( 'post_status' => 'future' ) );
		$this->assertCount( 0, $future_posts );

	}

	function test_add_custom_repeating_schedule() {

		add_filter( 'hm_post_repeat_schedules', function( $schedules ) {

			$schedules['yearly'] = array( 'interval' => '1 year', 'display' => 'Yearly' );
			return $schedules;

		} );

		$this->assertTrue( key_exists( 'yearly', get_repeating_schedules() ) );

	}

	function test_repeating_post_interval_custom() {

		add_filter( 'hm_post_repeat_schedules', function( $schedules ) {

			$schedules['3-days'] = array( 'interval' => '3 days', 'display' => 'Every 3 days' );
			return $schedules;

		} );

		$_POST['hm-post-repeat'] = '3-days';
		$post = $this->factory->post->create_and_get();
		$this->assertTrue( is_repeating_post( $post->ID ) );

		$future_posts = get_posts( array( 'post_status' => 'future' ) );
		$this->assertCount( 1, $future_posts );

		$repeat_post = reset( $future_posts );
		$this->assertTrue( is_repeat_post( $repeat_post->ID ) );

		$next_post_date = date( 'Y-m-d H:i:s', strtotime( $post->post_date . ' + 3 days' ) );
		$this->assertSame( $repeat_post->post_date, $next_post_date );

	}

	function test_repeating_post_interval_daily() {

		$_POST['hm-post-repeat'] = 'daily';
		$post = $this->factory->post->create_and_get();
		$this->assertTrue( is_repeating_post( $post->ID ) );

		$future_posts = get_posts( array( 'post_status' => 'future' ) );
		$this->assertCount( 1, $future_posts );

		$repeat_post = reset( $future_posts );
		$this->assertTrue( is_repeat_post( $repeat_post->ID ) );

		$next_post_date = date( 'Y-m-d H:i:s', strtotime( $post->post_date . ' + 1 day' ) );
		$this->assertSame( $repeat_post->post_date, $next_post_date );

	}

	function test_repeating_post_interval_weekly() {

		$_POST['hm-post-repeat'] = 'weekly';
		$post = $this->factory->post->create_and_get();
		$this->assertTrue( is_repeating_post( $post->ID ) );

		$future_posts = get_posts( array( 'post_status' => 'future' ) );
		$this->assertCount( 1, $future_posts );

		$repeat_post = reset( $future_posts );
		$this->assertTrue( is_repeat_post( $repeat_post->ID ) );

		$next_post_date = date( 'Y-m-d H:i:s', strtotime( $post->post_date . ' + 1 week' ) );
		$this->assertSame( $repeat_post->post_date, $next_post_date );

	}

	function test_repeating_post_interval_monthly() {

		$_POST['hm-post-repeat'] = 'monthly';
		$post = $this->factory->post->create_and_get();
		$this->assertTrue( is_repeating_post( $post->ID ) );

		$future_posts = get_posts( array( 'post_status' => 'future' ) );
		$this->assertCount( 1, $future_posts );

		$repeat_post = reset( $future_posts );
		$this->assertTrue( is_repeat_post( $repeat_post->ID ) );

		$next_post_date = date( 'Y-m-d H:i:s', strtotime( $post->post_date . ' + 1 month' ) );
		$this->assertSame( $repeat_post->post_date, $next_post_date );

	}

	/**
	 * This test assumes that the meta data was invalidly set directly in the database.
	 */
	function test_get_repeating_schedule_invalid_direct_db_entry() {

		$post_id = $this->factory->post->create();
		add_post_meta( $post_id, 'hm-post-repeat', 'some-day' );
		$this->assertNull( get_repeating_schedule( $post_id ) );

	}

	function test_get_repeating_schedule_invalid() {

		$_POST['hm-post-repeat'] = 'some-day';
		$post_id = $this->factory->post->create();
		$this->assertNull( get_repeating_schedule( $post_id ) );

	}

	/**
	 * This test assumes that the meta data was correctly set directly in the database.
	 */
	function test_get_repeating_schedule_valid_direct_db_entry() {

		$post_id = $this->factory->post->create();
		add_post_meta( $post_id, 'hm-post-repeat', 'daily' );
		$this->assertSame( array(
			'interval' => '1 day',
			'display'  => 'Daily',
			'slug'     => 'daily',
		), get_repeating_schedule( $post_id ) );

	}

	function test_get_repeating_schedule_valid() {

		$_POST['hm-post-repeat'] = 'daily';
		$post_id = $this->factory->post->create();
		$this->assertSame( array(
			'interval' => '1 day',
			'display'  => 'Daily',
			'slug'     => 'daily',
		), get_repeating_schedule( $post_id ) );
	}

	/**
	 * This method assumes an existing old schedule format in the post meta.
	 */
	function test_get_repeating_schedule_backwards_compatible_old() {

		$post_id = $this->factory->post->create();
		add_post_meta( $post_id, 'hm-post-repeat', '1' );
		$this->assertSame( array(
			'interval' => '1 week',
			'display'  => 'Weekly',
			'slug'     => 'weekly',
		), get_repeating_schedule( $post_id ) );

	}

	/**
	 * Tests that a repeat post is modified via filter before being saved/scheduled.
	 * Only repeat posts with a specific schedule are modified.
	 */
	function test_edit_repeat_post_before_scheduling() {

		// Edit repeat post title if it's on weekly schedule.
		add_filter( 'hm_post_repeat_edit_repeat_post', function( $next_post, $repeating_schedule, $original_post ) {

			if ( $repeating_schedule['slug'] === 'weekly' ) {
				$next_post['post_title'] = 'Repeat Post Week 1, 2018';
			}

			return $next_post;
		}, 10, 3 );

		// Create main repeating post and schedule to repeat it weekly.
		$_POST['hm-post-repeat'] = 'weekly';
		$post_id = $this->factory->post->create( array(
			'post_title'  => 'Repeating Post',
			'post_status' => 'publish',
		) );

		// Check repeating post is there with its original title.
		$this->assertTrue( is_repeating_post( $post_id ) );
		$this->assertSame( get_the_title( $post_id ) , 'Repeating Post' );

		// Check repeat post is scheduled with a new title.
		$repeat_posts = get_posts( array( 'post_status' => 'future' ) );

		$this->assertNotEmpty( $repeat_posts );
		$this->assertTrue( is_repeat_post( $repeat_posts[0]->ID ) );
		$this->assertSame( $repeat_posts[0]->post_title, 'Repeat Post Week 1, 2018' );
	}

	/**
	 * Tests if an available repeat type is valid
	 */
	function test_is_allowed_repeat_type() {
		$valid_types = array_keys( get_available_repeat_types() );
		foreach ($valid_types as $valid_type ) {
			$this->assertTrue( is_allowed_repeat_type( $valid_type ) );
		}

		$invalid_types = array( 'publish', 'Repeat', 'Repeating', null );
		foreach ($invalid_types as $invalid_type ) {
			$this->assertNotTrue( is_allowed_repeat_type( $invalid_type ) );
		}
	}

	/**
	 * Tests a set URL query param passed with get_repeat_type_url_param() returns
	 * the same string
	 */
	function test_set_repeat_type_url_param() {
		$valid_types = get_available_repeat_types();
		foreach ( $valid_types as $valid_type ) {
			$_GET['hm-post-repeat'] = $valid_type;
			$this->assertEquals( $valid_type, get_repeat_type_url_param() );
		}
	}

	/**
	 * This test assumes the URL query param is not set
	 */
	function test_empty_repeat_type_url_param() {
		$not_set_param = array( '', null);
		foreach ( $not_set_param as $value ) {
			$_GET['hm-post-repeat'] = $value;
			$this->assertEquals( '', get_repeat_type_url_param() );
		}
	}

	/**
	 * Tests there is only valid post type repeat keys in get_available_repeat_types().
	 */
	function test_get_available_repeat_types() {
		$repeat_keys = array( 'repeating', 'repeat' );
		foreach ( $repeat_keys as $key ) {
			$this->assertArrayHasKey( $key, get_available_repeat_types() );
		}

		$invalid_keys = array( 'Repeat', 'Repeating', 'publish' );
		foreach ( $invalid_keys as $key ) {
			$this->assertArrayNotHasKey( $key, get_available_repeat_types() );
		}
	}

}
