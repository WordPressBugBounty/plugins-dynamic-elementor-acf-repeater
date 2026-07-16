<?php

namespace DynamicElementorAcfRepeater\DynamicTags;

use DynamicElementorAcfRepeater\Support\DynamicValueFormatter;


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class AcfRepeaterImage extends AcfRepeaterTagBase {
	public function get_name() {
		return 'acf-repeater-image';
	}

	public function get_title() {
		return __( 'ACF Repeater Image', 'dynamic-elementor-acf-repeater' );
	}

	public function get_group() {
		return 'acf';
	}

	public function get_categories() {
		return array(
			\Elementor\Modules\DynamicTags\Module::IMAGE_CATEGORY,
			\Elementor\Modules\DynamicTags\Module::URL_CATEGORY,
		);
	}

	public function get_supported_fields() {
		return array( 'image' );
	}

	public function render() {
		$value = $this->get_value();
		if ( ! empty( $value['url'] ) ) {
			echo esc_url( $value['url'] );
		}
	}

	public function get_value( array $options = array() ) {
		try {
			if ( $this->mastermind->is_in_widgets_context() || $this->mastermind->is_all_processing_disabled() ) {
				return array(
					'id'  => null,
					'url' => '',
				);
			}
			$field_key = $this->get_settings( 'repeater_field' );

			if ( empty( $field_key ) ) {
				return array(
					'id'  => null,
					'url' => '',
				);
			}

			$value = $this->get_repeater_value( $field_key );

			if ( $value === null ) {
				return array(
					'id'  => null,
					'url' => '',
				);
			}

			return DynamicValueFormatter::media( $value );
		} catch ( \Throwable $e ) {
			return array(
				'id'  => null,
				'url' => '',
			);
		}
	}
}
