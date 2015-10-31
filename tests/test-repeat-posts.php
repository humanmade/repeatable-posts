<?php

namespace HM\Post_Repeat;

class PostRepeatTests extends \WP_UnitTestCase {

	function tearDown() {
		_delete_all_posts();
	}

	function test_setting_post_repeating_status_no() {

		$post_id = self::factory()->post->create();
		$this->assertFalse( is_repeating_post( $post_id ) );

		save_post_repeating_status( $post_id, 'no' );
		$this->assertFalse( is_repeating_post( $post_id ) );

	}

	function test_setting_post_repeating_status_yes() {

		$post_id = self::factory()->post->create();
		$this->assertFalse( is_repeating_post( $post_id ) );

		save_post_repeating_status( $post_id, '1' );
		$this->assertTrue( is_repeating_post( $post_id ) );

	}

	function test_setting_post_repeating_status_blank() {

		$post_id = self::factory()->post->create();
		$this->assertFalse( is_repeating_post( $post_id ) );

		save_post_repeating_status( $post_id, '1' );
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

		$post_id = self::factory()->post->create();
		$this->assertFalse( is_repeating_post( $post_id ) );

		$_POST['ID'] = $post_id;
		$_POST['hm-post-repeat'] = '1';
		$this->assertTrue( is_repeating_post( $post_id ) );

	}

	function test_repeat_post_no() {

		$post_id = self::factory()->post->create();
		$this->assertFalse( is_repeat_post( $post_id ) );

	}

	function test_repeat_post_yes() {

		$parent_post_id = self::factory()->post->create();
		$post_id = self::factory()->post->create( array( 'post_parent' => $parent_post_id ) );

		$this->assertFalse( is_repeat_post( $post_id ) );
		$this->assertEquals( $parent_post_id, get_post( $post_id )->post_parent );

		save_post_repeating_status( $parent_post_id, '1' );

		$this->assertTrue( is_repeat_post( $post_id ) );

	}

	function test_post_states() {

		global $post_states;

		$parent_post_id = self::factory()->post->create();
		$post_id = self::factory()->post->create( array( 'post_parent' => $parent_post_id ) );
		save_post_repeating_status( $parent_post_id, '1' );

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

		$parent_post_id = self::factory()->post->create();
		$post_id = self::factory()->post->create( array( 'post_parent' => $parent_post_id ) );
		save_post_repeating_status( $parent_post_id, '1' );

		$this->assertEquals( $parent_post_id, get_repeating_post( $parent_post_id ) );
		$this->assertEquals( $parent_post_id, get_repeating_post( $post_id ) );

	}

	function test_create_repeat_post_from_not_repeating() {

		$post_id = self::factory()->post->create();
		$this->assertFalse( create_next_repeat_post( $post_id ) );
		$this->assertCount( 0, get_posts( array( 'post_status' => 'future' ) ) );

	}

	function test_create_repeat_post_from_published_repeating_post() {

		$post_id = self::factory()->post->create();
		save_post_repeating_status( $post_id, '1' );

		$this->assertCount( 0, get_posts( array( 'post_status' => 'future' ) ) );

		$this->assertEquals( $post_id + 1, create_next_repeat_post( $post_id ) );
		$this->assertCount( 1, get_posts( array( 'post_status' => 'future' ) ) );

		// Prove that another repeat post isn't created
		$this->assertFalse( create_next_repeat_post( $post_id ) );
		$this->assertCount( 1, get_posts( array( 'post_status' => 'future' ) ) );

	}

	function test_create_repeat_post_from_unpublished_repeating_post() {

		$post_id = self::factory()->post->create( array( 'post_status' => 'draft' ) );
		save_post_repeating_status( $post_id, '1' );

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

		$post_id = self::factory()->post->create( array( 'post_status' => 'draft' ) );
		save_post_repeating_status( $post_id, '1' );

		$this->assertCount( 0, get_posts( array( 'post_status' => 'future' ) ) );

		wp_update_post( array( 'ID' => $post_id, 'post_status' => 'publish' ) );
		$this->assertCount( 1, get_posts( array( 'post_status' => 'future' ) ) );

	}

	function test_create_repeat_unsupported_repeating_post_type() {

		$post_id = self::factory()->post->create( array( 'post_type' => 'middle-out-encryption' ) );
		save_post_repeating_status( $post_id, '1' );

		$this->assertFalse( create_next_repeat_post( $post_id ) );

	}

	function test_create_repeat_post_copies_meta() {

		$post_id = self::factory()->post->create();
		save_post_repeating_status( $post_id, '1' );

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

		$post_id = self::factory()->post->create();
		self::factory()->term->add_post_terms( $post_id, array( 'Tag 1', 'Tag 2' ), 'post_tag' );

		$cat1 = self::factory()->term->create_object( array( 'taxonomy' => 'category', 'name' => 'Cat 1' ) );
		$cat2 = self::factory()->term->create_object( array( 'taxonomy' => 'category', 'name' => 'Cat 2' ) );

		self::factory()->term->add_post_terms( $post_id, array( $cat1, $cat2 ), 'category' );

		$this->assertTrue( has_tag( 'Tag 1', $post_id ) );
		$this->assertTrue( has_tag( 'Tag 2', $post_id ) );

		$this->assertTrue( has_category( $cat1, $post_id ) );
		$this->assertTrue( has_category( $cat2, $post_id ) );

		save_post_repeating_status( $post_id, '1' );

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
		$post_id = self::factory()->post->create();
		$this->assertTrue( is_repeating_post( $post_id ) );

		$this->assertCount( 1, get_posts( array( 'post_status' => 'future' ) ) );

	}

}
