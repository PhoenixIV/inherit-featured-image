<?php
/**
 * Plugin Name: JSM's Inherit Parent Featured Image
 * Text Domain: inherit-featured-image
 * Domain Path: /languages
 * Plugin URI: https://surniaulula.com/extend/plugins/inherit-featured-image/
 * Assets URI: https://jsmoriss.github.io/inherit-featured-image/assets/
 * Author: JS Morisset
 * Author URI: https://surniaulula.com/
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl.txt
 * Description: Inherit the featured image from the Post, Page, or Custom Post Type parent, grand-parent, etc.
 * Requires PHP: 5.6
 * Requires At Least: 4.2
 * Tested Up To: 5.4
 * Version: 1.1.1
 * 
 * Version Numbering: {major}.{minor}.{bugfix}[-{stage}.{level}]
 *
 *      {major}         Major structural code changes / re-writes or incompatible API changes.
 *      {minor}         New functionality was added or improved in a backwards-compatible manner.
 *      {bugfix}        Backwards-compatible bug fixes or small improvements.
 *      {stage}.{level} Pre-production release: dev < a (alpha) < b (beta) < rc (release candidate).
 *
 * Copyright 2013-2020 Jean-Sebastien Morisset (https://surniaulula.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {
        die( 'These aren\'t the droids you\'re looking for.' );
}

if ( ! class_exists( 'InheritFeaturedImage' ) ) {

        class InheritFeaturedImage {

		private static $instance;

		public function __construct() {

			add_action( 'plugins_loaded', array( __CLASS__, 'init_textdomain' ) );

			add_filter( 'get_post_metadata', array( __CLASS__, 'get_meta_thumbnail_id' ), 10, 4 );
		}

		public static function &get_instance() {

			if ( ! isset( self::$instance ) ) {
				self::$instance = new self;
			}

			return self::$instance;
		}

		public static function init_textdomain() {

			static $loaded = null;

			if ( null !== $loaded ) {
				return;
			}

			$loaded = true;

			load_plugin_textdomain( 'inherit-featured-image', false, 'inherit-featured-image/languages/' );
		}

		public static function get_meta_thumbnail_id( $meta_data, $obj_id, $meta_key, $single ) {

			/**
			 * We're only interested in the featured image (aka '_thumbnail_id').
			 */
			if ( $meta_key !== '_thumbnail_id' ) {
				return $meta_data;
			}

			$meta_cache = self::get_meta_cache( $obj_id, 'post' );

			/**
			 * If the meta already has a featured image, then no need to check the parents.
			 */
			if ( ! empty( $meta_cache[ $meta_key ] ) ) {
				return $meta_data;
			}

			/**
			 * Start with the parent and work our way up - return the first featured image found.
			 */
			foreach ( get_post_ancestors( $obj_id ) as $parent_id ) {

				$meta_cache = self::get_meta_cache( $parent_id, 'post' );

				if ( ! empty( $meta_cache[ $meta_key ][ 0 ] ) ) {
					if ( $single ) {
						return maybe_unserialize( $meta_cache[ $meta_key ][ 0 ] );
					} else {
						return array_map( 'maybe_unserialize', $meta_cache[ $meta_key ] );
					}
				}
			}

			return $meta_data;
		}

		private static function get_meta_cache( $obj_id, $meta_type ) {

			$meta_cache = wp_cache_get( $obj_id, $meta_type . '_meta' );

			if ( ! $meta_cache ) {
				$meta_cache = update_meta_cache( $meta_type, array( $obj_id ) );
				$meta_cache = $meta_cache[ $obj_id ];
			}

			return $meta_cache;
		}
	}

	InheritFeaturedImage::get_instance();
}
