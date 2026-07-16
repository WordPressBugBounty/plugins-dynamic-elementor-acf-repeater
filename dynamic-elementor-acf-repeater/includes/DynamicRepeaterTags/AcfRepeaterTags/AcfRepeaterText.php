<?php

namespace DynamicElementorAcfRepeater\DynamicTags;

use DynamicElementorAcfRepeater\MasterMind;
use DynamicElementorAcfRepeater\Support\DynamicValueFormatter;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AcfRepeaterText extends AcfRepeaterTagBase {

	public function __construct( $data = array() ) {
		parent::__construct( $data );
	}

	public function get_supported_fields() {
		$supported_fields = array( 'text', 'textarea' );
		$pro_fields       = $this->get_pro_fields();

		$all_fields = array_merge( $supported_fields, $pro_fields );

		return $all_fields;
	}

	/**
	 * Get a list of premium fields for display purposes in the free version.
	 * This method is used to show upgrade messages and does not provide premium functionality.
	 */

	public function get_pro_fields() {
		return array(
			'number',
			'email',
			'password',
			'wysiwyg',
			'select',
			'checkbox',
			'radio',
			'true_false',
			'oembed',
			'google_map',
			'date_picker',
			'time_picker',
			'date_time_picker',
			'color_picker',
		);
	}

	public function get_categories() {
		$categories = array( \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY );
		if ( earluna_can_use_premium_code() ) {
			$categories[] = \Elementor\Modules\DynamicTags\Module::NUMBER_CATEGORY;
		}
		return $categories;
	}

	public function get_value( array $options = array() ) {
		$field_key = $this->get_settings( 'repeater_field' );

		if ( empty( $field_key ) ) {
			return '';
		}

		$value = $this->get_repeater_value( $field_key );

		if ( $value === null ) {
			return '';
		}

		$field_type = $this->get_field_type( $field_key );

		if ( $field_type === null ) {
			return '';
		}

		if ( ! earluna_can_use_premium_code() ) {
			if ( ! in_array( $field_type, array( 'text', 'textarea' ), true ) ) {
				return '';
			}
		}

		return DynamicValueFormatter::text( $value );
	}

	private function get_field_type( $field_key ) {
		if ( function_exists( 'get_field_object' ) ) {
			$field_object = get_field_object( $field_key );
			if ( $field_object && isset( $field_object['type'] ) ) {
				return $field_object['type'];
			}
		}
		return null;
	}
}
