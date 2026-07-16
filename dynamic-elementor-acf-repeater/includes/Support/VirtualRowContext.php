<?php

/**
 * Request-local virtual row context support.
 *
 * @package DynamicElementorAcfRepeater
 */

namespace DynamicElementorAcfRepeater\Support;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Request-local mapping between synthetic WordPress IDs and ACF rows.
 *
 * The mapping avoids encoding source IDs and row indexes into a potentially
 * overflowing or colliding negative integer.
 */
final class VirtualRowContext {
	private static $next_id  = -1;
	private static $contexts = array();

	public static function register( $source_id, $row_index, $repeater_field ) {
		$virtual_id                    = self::$next_id--;
		self::$contexts[ $virtual_id ] = array(
			'source_id'      => 'options' === $source_id ? 'options' : absint( $source_id ),
			'row_index'      => absint( $row_index ),
			'repeater_field' => sanitize_key( $repeater_field ),
		);
		return $virtual_id;
	}

	/**
	 * @return array<string, int|string>|null
	 */
	public static function get( $virtual_id ) {
		$virtual_id = (int) $virtual_id;
		return isset( self::$contexts[ $virtual_id ] ) ? self::$contexts[ $virtual_id ] : null;
	}

	/**
	 * @return array<string, int|string>|null
	 */
	public static function from_post( $post ) {
		if ( is_object( $post ) && isset( $post->acf_repeater_source_id, $post->earluna_loop_index ) ) {
			return array(
				'source_id'      => $post->acf_repeater_source_id,
				'row_index'      => absint( $post->earluna_loop_index ),
				'repeater_field' => isset( $post->acf_repeater_field ) ? sanitize_key( $post->acf_repeater_field ) : '',
			);
		}

		return is_object( $post ) && isset( $post->ID ) ? self::get( $post->ID ) : null;
	}

	public static function reset() {
		self::$next_id  = -1;
		self::$contexts = array();
	}
}
