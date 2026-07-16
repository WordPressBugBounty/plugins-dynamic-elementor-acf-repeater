<?php

namespace DynamicElementorAcfRepeater\Data;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// This trait is used to get the repeater field and value for a given post ID
trait RepeaterDataTrait {


	public function get_repeater_field( $post_id = null ) {
		$fields = get_field_objects( $post_id );

		if ( ! $fields ) {
			return null;
		}

		foreach ( $fields as $field_key => $field ) {
			if ( $field['type'] === 'repeater' ) {
				return $field_key;
			}
		}
		return null;
	}

	public function get_repeater_value( $field_key ) {
		global $post;

		$post_id = get_the_ID();

		// Get document once and reuse it
		$document = \Elementor\Plugin::$instance->documents->get_current();

		// Default index (matching the original get_current_item_index behavior)
		$current_index = ( $document instanceof \ElementorPro\Modules\LoopBuilder\Documents\Loop )
			? ( $document->get_settings( 'loop' )['index'] ?? 0 )
			: 0;

		// Resolve request-local virtual row context without encoding source data
		// into a potentially overflowing negative post ID.
		if ( $post_id < 0 ) {
			$context = \DynamicElementorAcfRepeater\Support\VirtualRowContext::from_post( $post );
			if ( ! $context ) {
				$context = \DynamicElementorAcfRepeater\Support\VirtualRowContext::get( $post_id );
			}
			if ( ! $context ) {
				return null;
			}
			$post_id       = $context['source_id'];
			$current_index = (int) $context['row_index'];
		}

		// Determine repeater field to use
		if ( $document instanceof \ElementorPro\Modules\LoopBuilder\Documents\Loop ) {
			$document_id          = $document->get_main_id();
			$saved_repeater_field = get_post_meta( $document_id, 'earluna_loop_repeater_field', true );

			$saved_field_object = $saved_repeater_field && function_exists( 'acf_get_field' ) ? acf_get_field( $saved_repeater_field ) : null;
			$repeater_field     = $saved_field_object && isset( $saved_field_object['type'] ) && 'repeater' === $saved_field_object['type']
				? $saved_repeater_field
				: $this->get_repeater_field( $post_id );
		} else {
			$repeater_field = $this->get_repeater_field( $post_id );
		}

		if ( ! empty( $context['repeater_field'] ) ) {
			$repeater_field = $context['repeater_field'];
		}

		// Fallback: If no repeater field was detected on the current post, look for one on the global ACF Options page.
		if ( ! $repeater_field ) {
			$post_id        = 'options';
			$repeater_field = $this->get_repeater_field( $post_id );
		}

		if ( ! $repeater_field ) {
			return null;
		}

		$repeater_data = get_field( $repeater_field, $post_id );

		// Fallback: If no data was returned for the post-specific repeater, try the Options page.
		if ( ( ! $repeater_data || ! is_array( $repeater_data ) ) && $post_id !== 'options' ) {
			$post_id       = 'options';
			$repeater_data = get_field( $repeater_field, $post_id );
		}

		if ( ! $repeater_data || ! is_array( $repeater_data ) ) {
			return null;
		}

		$field_object = get_field_object( $field_key );

		if ( ! $field_object ) {
			return null;
		}

		$field_name = $field_object['name'];

		if ( ! isset( $repeater_data[ $current_index ][ $field_name ] ) ) {
			return null;
		}

		return $repeater_data[ $current_index ][ $field_name ];
	}
}
