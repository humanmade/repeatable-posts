<?php

/*
Plugin Name: Repeating Posts
Description: Auto duplicate and re-publish a posts at a set interval
Author: Human Made Limited
Author URI: http://hmn.md/
Version: 1.0
*/

define( 'HM_POST_REPEAT_PLUGIN_FILE', __FILE__ );

require_once( plugin_dir_path( __FILE__ ) . 'class-hm-post-repeat.php' );

new HM_Post_Repeat();