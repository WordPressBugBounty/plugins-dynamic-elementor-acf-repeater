<?php

namespace DynamicElementorAcfRepeater\Controls;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;

/**
 * Shared source-context controls with a clean Free/Pro capability boundary.
 */
final class ContextControls {
	/**
	 * Register context selection after the repeater field control.
	 *
	 * @param \Elementor\Widget_Base $element Elementor Loop widget.
	 * @param bool                    $premium Whether premium selectors are available.
	 */
	public function register( $element, $premium = false ) {
		if ( $element->get_controls( 'earluna_context_type' ) ) {
			return;
		}

		$options = array(
			'auto'           => __( 'Automatic', 'dynamic-elementor-acf-repeater' ),
			'current_post'   => __( 'Current Post', 'dynamic-elementor-acf-repeater' ),
			'queried_object' => __( 'Queried Object', 'dynamic-elementor-acf-repeater' ),
			'options'        => __( 'ACF Options Page', 'dynamic-elementor-acf-repeater' ),
		);

		if ( $premium ) {
			$options['current_user'] = __( 'Current User', 'dynamic-elementor-acf-repeater' );
			$options['explicit']     = __( 'Explicit ACF Object ID', 'dynamic-elementor-acf-repeater' );
		}

		$element->add_control(
			'earluna_context_type',
			array(
				'label'       => __( 'Repeater Context', 'dynamic-elementor-acf-repeater' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => $options,
				'default'     => 'auto',
				'description' => __( 'Choose the ACF object that owns the repeater. Automatic preserves the current-post and Options fallback behavior.', 'dynamic-elementor-acf-repeater' ),
				'condition'   => array(
					'use_acf_repeater' => 'yes',
				),
			)
		);

		if ( ! $premium ) {
			return;
		}

		$element->add_control(
			'earluna_context_id',
			array(
				'label'       => __( 'Explicit ACF Object ID', 'dynamic-elementor-acf-repeater' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => '123, user_12, category_34, options',
				'description' => __( 'Use a post ID, user_# ID, taxonomy_# ID, or options.', 'dynamic-elementor-acf-repeater' ),
				'condition'   => array(
					'use_acf_repeater'     => 'yes',
					'earluna_context_type' => 'explicit',
				),
			)
		);
	}
}
