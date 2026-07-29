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

	/**
	 * Register text-only output controls while preserving the plain Data_Tag contract.
	 */
	protected function register_advanced_section() {
		$this->register_text_output_controls();
	}

	/**
	 * Register text-only output controls from either Elementor's normal control
	 * lifecycle or the plugin's prebuilt dynamic-tag control stack.
	 */
	public function register_text_output_controls() {
		if ( $this->get_controls( 'before' ) ) {
			return;
		}

		$this->start_controls_section(
			'advanced',
			array(
				'label' => esc_html__( 'Advanced', 'elementor' ),
			)
		);

		$this->add_control(
			'before',
			array(
				'label'   => esc_html__( 'Before', 'elementor' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'ai'      => array(
					'active' => false,
				),
			)
		);

		$this->add_control(
			'after',
			array(
				'label'   => esc_html__( 'After', 'elementor' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'ai'      => array(
					'active' => false,
				),
			)
		);

		$this->add_control(
			'fallback',
			array(
				'label'   => esc_html__( 'Fallback', 'elementor' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'ai'      => array(
					'active' => false,
				),
			)
		);

		$this->end_controls_section();
	}

	public function get_value( array $options = array() ) {
		$field_key = $this->get_settings( 'repeater_field' );

		if ( empty( $field_key ) ) {
			return $this->get_fallback();
		}

		$value = $this->get_repeater_value( $field_key );

		if ( $value === null ) {
			return $this->get_fallback();
		}

		$field_type = $this->get_field_type( $field_key );

		if ( $field_type === null ) {
			return $this->get_fallback();
		}

		if ( ! earluna_can_use_premium_code() ) {
			if ( ! in_array( $field_type, array( 'text', 'textarea' ), true ) ) {
				return $this->get_fallback();
			}
		}

		$value = DynamicValueFormatter::text( $value );

		if ( '' === $value ) {
			return $this->get_fallback();
		}

		$before = wp_kses_post( (string) $this->get_settings( 'before' ) );
		$after  = wp_kses_post( (string) $this->get_settings( 'after' ) );

		return $before . $value . $after;
	}

	private function get_fallback() {
		return wp_kses_post( (string) $this->get_settings( 'fallback' ) );
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
