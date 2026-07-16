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

		$post_id      = get_the_ID();
		$field_object = get_field_object( $field_key );

		if ( ! $field_object ) {
			return null;
		}

		// Virtual rows already carry the formatted ACF row that Elementor is
		// rendering. Reading it directly supports nested paths and Flexible
		// Content without trying to reconstruct the row from a synthetic ID.
		if ( is_object( $post ) && isset( $post->acf_repeater_data ) && is_array( $post->acf_repeater_data ) ) {
			return $this->get_row_field_value( $post->acf_repeater_data, $field_object );
		}

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
			$saved_row_schema   = null;
			if ( $saved_repeater_field && function_exists( 'earluna_can_use_premium_code' ) && earluna_can_use_premium_code() && class_exists( '\\DynamicElementorAcfRepeater\\LoopGrid\\RowSourceRegistry' ) ) {
				$saved_row_schema = \DynamicElementorAcfRepeater\LoopGrid\RowSourceRegistry::instance()->get_schema( $saved_repeater_field );
			}
			$repeater_field = ( $saved_field_object && isset( $saved_field_object['type'] ) && 'repeater' === $saved_field_object['type'] ) || $saved_row_schema
				? $saved_repeater_field
				: $this->get_repeater_field( $post_id );
		} else {
			$repeater_field = $this->get_repeater_field( $post_id );
		}

		if ( ! empty( $context['repeater_field'] ) ) {
			$repeater_field = $context['repeater_field'];
		}

		// Premium Loop templates can select a nested row schema or one Flexible
		// Content layout. Resolve a representative preview row through the same
		// registry used by frontend virtual posts.
		if ( function_exists( 'earluna_can_use_premium_code' ) && earluna_can_use_premium_code() && class_exists( '\\DynamicElementorAcfRepeater\\LoopGrid\\RowSourceRegistry' ) ) {
			$registry = \DynamicElementorAcfRepeater\LoopGrid\RowSourceRegistry::instance();
			if ( $registry->get_schema( $repeater_field ) ) {
				$preview_rows = $registry->resolve_schema_rows( $repeater_field, $post_id );
				if ( empty( $preview_rows ) ) {
					return null;
				}
				$preview_index = isset( $preview_rows[ $current_index ] ) ? $current_index : 0;
				return $this->get_row_field_value( $preview_rows[ $preview_index ]['data'], $field_object );
			}
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

		$field_name = $field_object['name'];

		if ( ! isset( $repeater_data[ $current_index ][ $field_name ] ) ) {
			return null;
		}

		return $repeater_data[ $current_index ][ $field_name ];
	}

	private function get_row_field_value( array $row, array $field_object ) {
		$field_name = isset( $field_object['name'] ) ? $field_object['name'] : '';
		$field_key  = isset( $field_object['key'] ) ? $field_object['key'] : '';

		if ( $field_name && array_key_exists( $field_name, $row ) ) {
			return $row[ $field_name ];
		}
		if ( $field_key && array_key_exists( $field_key, $row ) ) {
			return $row[ $field_key ];
		}

		return null;
	}
}
