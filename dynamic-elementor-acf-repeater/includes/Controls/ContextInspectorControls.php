<?php

/**
 * Elementor controls for the opt-in editor-only context inspector.
 *
 * @package DynamicElementorAcfRepeater
 */

namespace DynamicElementorAcfRepeater\Controls;

use DynamicElementorAcfRepeater\LoopGrid\LoopGridProvider;
use DynamicElementorAcfRepeater\Support\ContextInspector;
use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class ContextInspectorControls {
	private $premium;
	private $inspector;

	public function __construct( LoopGridProvider $provider, $premium ) {
		$this->premium   = (bool) $premium;
		$this->inspector = new ContextInspector( $provider, $premium );
	}

	public function register() {
		$this->inspector->register_ajax();
		add_action( 'elementor/element/loop-grid/section_query/after_section_end', array( $this, 'register_section' ), 40, 2 );
		if ( $this->premium ) {
			add_action( 'elementor/element/loop-carousel/section_query/after_section_end', array( $this, 'register_section' ), 40, 2 );
		}
	}

	public function register_section( $element, $args ) {
		if ( $element->get_controls( 'earluna_enable_context_inspector' ) ) {
			return;
		}

		$element->start_controls_section(
			'section_earluna_context_inspector',
			array(
				'label'     => __( 'Troubleshooting', 'dynamic-elementor-acf-repeater' ),
				'tab'       => Controls_Manager::TAB_CONTENT,
				'condition' => array( 'use_acf_repeater' => 'yes' ),
			)
		);

		$element->add_control(
			'earluna_enable_context_inspector',
			array(
				'label'              => __( 'Enable Context Inspector', 'dynamic-elementor-acf-repeater' ),
				'type'               => Controls_Manager::SWITCHER,
				'default'            => '',
				'return_value'       => 'yes',
				'label_on'           => __( 'Yes', 'dynamic-elementor-acf-repeater' ),
				'label_off'          => __( 'No', 'dynamic-elementor-acf-repeater' ),
				'description'        => __( 'Editor-only and metadata-only. Nothing is added to the preview canvas or frontend.', 'dynamic-elementor-acf-repeater' ),
				'frontend_available' => false,
				'render_type'        => 'none',
			)
		);

		$element->add_control(
			'earluna_context_inspector_output',
			array(
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => '<div class="ear-context-inspector" data-ear-context-inspector role="status" aria-live="polite"><p class="ear-context-inspector__loading">' . esc_html__( 'Resolving the current widget context…', 'dynamic-elementor-acf-repeater' ) . '</p></div>',
				'content_classes' => 'ear-context-inspector-control',
				'condition'       => array( 'earluna_enable_context_inspector' => 'yes' ),
			)
		);

		$element->end_controls_section();
	}
}
