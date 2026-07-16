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

	public static function register( $source_id, $row_index, $repeater_field, $source_label = '', $row_path = array(), $row_type = 'repeater', $layout = '', $schema_selector = '' ) {
		$virtual_id                    = self::$next_id--;
		self::$contexts[ $virtual_id ] = array(
			'source_id'       => self::normalize_source_id( $source_id ),
			'source_label'    => (string) $source_label,
			'row_index'       => absint( $row_index ),
			'repeater_field'  => self::normalize_field_selector( $repeater_field ),
			'row_path'        => self::normalize_row_path( $row_path ),
			'row_type'        => in_array( $row_type, array( 'repeater', 'flexible_content' ), true ) ? $row_type : 'repeater',
			'layout'          => sanitize_key( $layout ),
			'schema_selector' => sanitize_text_field( $schema_selector ),
		);
		return $virtual_id;
	}

	/**
	 * @return array<string, mixed>|null
	 */
	public static function get( $virtual_id ) {
		$virtual_id = (int) $virtual_id;
		return isset( self::$contexts[ $virtual_id ] ) ? self::$contexts[ $virtual_id ] : null;
	}

	/**
	 * @return array<string, mixed>|null
	 */
	public static function from_post( $post ) {
		if ( is_object( $post ) && isset( $post->acf_repeater_source_id, $post->earluna_loop_index ) ) {
			return array(
				'source_id'       => $post->acf_repeater_source_id,
				'source_label'    => isset( $post->acf_repeater_source_label ) ? (string) $post->acf_repeater_source_label : '',
				'row_index'       => absint( $post->earluna_loop_index ),
				'repeater_field'  => isset( $post->acf_repeater_field ) ? self::normalize_field_selector( $post->acf_repeater_field ) : '',
				'row_path'        => isset( $post->earluna_row_path ) ? self::normalize_row_path( $post->earluna_row_path ) : array(),
				'row_type'        => isset( $post->earluna_row_type ) && in_array( $post->earluna_row_type, array( 'repeater', 'flexible_content' ), true ) ? $post->earluna_row_type : 'repeater',
				'layout'          => isset( $post->earluna_flexible_layout ) ? sanitize_key( $post->earluna_flexible_layout ) : '',
				'schema_selector' => isset( $post->earluna_schema_selector ) ? sanitize_text_field( $post->earluna_schema_selector ) : '',
			);
		}

		return is_object( $post ) && isset( $post->ID ) ? self::get( $post->ID ) : null;
	}

	private static function normalize_source_id( $source_id ) {
		if ( is_numeric( $source_id ) ) {
			return absint( $source_id );
		}

		$source_id = sanitize_key( $source_id );
		if ( 'option' === $source_id || 'options' === $source_id ) {
			return 'options';
		}

		return preg_match( '/^[a-z][a-z0-9_-]*_[1-9][0-9]*$/', $source_id ) ? $source_id : '';
	}

	private static function normalize_field_selector( $selector ) {
		$selector = sanitize_text_field( $selector );
		return preg_match( '/^[a-zA-Z0-9_:\/@.-]+$/', $selector ) ? $selector : '';
	}

	private static function normalize_row_path( $row_path ) {
		$normalized = array();
		foreach ( is_array( $row_path ) ? $row_path : array() as $segment ) {
			if ( ! is_array( $segment ) || empty( $segment['field'] ) ) {
				continue;
			}
			$normalized[] = array(
				'field'  => sanitize_key( $segment['field'] ),
				'index'  => isset( $segment['index'] ) ? absint( $segment['index'] ) : 0,
				'layout' => isset( $segment['layout'] ) ? sanitize_key( $segment['layout'] ) : '',
			);
		}

		return $normalized;
	}

	public static function reset() {
		self::$next_id  = -1;
		self::$contexts = array();
	}
}
