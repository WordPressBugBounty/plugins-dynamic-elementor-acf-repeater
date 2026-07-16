<?php


namespace DynamicElementorAcfRepeater\Controls;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use DynamicElementorAcfRepeater\LoopGrid\LoopGridProvider;
use DynamicElementorAcfRepeater\DynamicTags\RepeaterTagManager;
use Elementor\Controls_Manager;
use DynamicElementorAcfRepeater\Controls\EarSwitcherControl;

class LoopGridControlsBase {
    protected $mastermind;
    protected $provider;
    protected $context_controls;

    public function __construct($mastermind, $provider) {
        $this->mastermind = $mastermind;
        $this->provider = $provider;
        $this->context_controls = new ContextControls();
        $this->register_earluna_switcher_control();
    }

    protected function register_earluna_switcher_control() {
        add_action('elementor/controls/register', function($controls_manager) {
            if (!$controls_manager->get_control('earluna_switcher')) {
                $controls_manager->register(new EarSwitcherControl());
            }
        });
    }

    public function register_query_controls($element, $args) {
        // Removed debug logging for production.

        // Check if another Loop Grid in this post is already using a repeater field
        $repeater_in_use = false;
        $elementor_data = json_decode(get_post_meta(get_the_ID(), '_elementor_data', true), true);
        
        if (is_array($elementor_data)) {
            // Search recursively for loop grid with repeater enabled
            $this->find_first_loop_grid_with_repeater($elementor_data, $element->get_id(), $repeater_in_use);
        }
        
        if ($repeater_in_use) {
            // Another loop grid is using a repeater - show disabled control with upgrade notice
            $element->add_control(
                'use_acf_repeater',
                [
                    'label' => __('Use ACF Repeater', 'dynamic-elementor-acf-repeater'),
                    'type' => 'earluna_switcher',
                    'classes' => 'elementor-control-disabled',
                    'description' => earluna_get_upgrade_notice(__('Multiple Loop Grids with ACF Repeater in the same post are', 'dynamic-elementor-acf-repeater')),
                    'return_value' => 'no',
                    'default' => 'no',
                ]
            );
        } else {
            // Normal control
            $element->add_control(
                'use_acf_repeater',
                [
                    'label' => __('Use ACF Repeater', 'dynamic-elementor-acf-repeater'),
                    'type' => Controls_Manager::SWITCHER,
                    'default' => '',
                    'label_on' => __('Yes', 'dynamic-elementor-acf-repeater'),
                    'label_off' => __('No', 'dynamic-elementor-acf-repeater'),
                    'description' => __('To see your repeater content, select a preview post with repeater data in Elementor settings. Make sure it matches post type of source chosen below.', 'dynamic-elementor-acf-repeater'),
                ]
            );
        }

        $repeater_fields = $this->provider->get_acf_repeater_fields();
        
        if (!$element->get_controls('acf_repeater_field')) {
            $element->add_control(
                'acf_repeater_field',
                [
                    'label' => __('ACF Repeater Field', 'dynamic-elementor-acf-repeater'),
                    'type' => Controls_Manager::SELECT,
                    'options' => $repeater_fields,
                    'condition' => [
                        'use_acf_repeater' => 'yes',
                    ],
                ]
            );
        }

        $this->context_controls->register($element, false);
        
        $element->add_control(
            'query_current_post_only',
            [
                'label' => __('Query Current Post Only', 'dynamic-elementor-acf-repeater'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
                'label_on' => __('Yes', 'dynamic-elementor-acf-repeater'),
                'label_off' => __('No', 'dynamic-elementor-acf-repeater'),
                'description' => __('When enabled, only repeater fields from the current post will be used. When disabled, repeater fields from all posts of the selected source will be used.', 'dynamic-elementor-acf-repeater'),
                'condition' => [
                    'use_acf_repeater' => 'yes',
                ],
            ]
        );
    }

    public function register_lightbox_section($element, $args) {
        $element->start_controls_section(
            'section_earluna_lightbox',
            [
                'label' => __('Repeater Lightbox', 'dynamic-elementor-acf-repeater'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $element->add_control(
            'earluna_enable_lightbox',
            [
                'label' => __('Enable Lightbox', 'dynamic-elementor-acf-repeater'),
                'type' => 'earluna_switcher',
                'description' => earluna_get_upgrade_notice(__('Lightbox functionality', 'dynamic-elementor-acf-repeater')),
                'classes' => 'elementor-control-disabled',
                'return_value' => 'no',
                'default' => 'no',
                'label_off' => __('On', 'dynamic-elementor-acf-repeater'),
                'label_on' => __('Off', 'dynamic-elementor-acf-repeater'),
            ]
        );   

        $element->end_controls_section();
    }   

    /**
     * Find first loop grid using a repeater field (excluding current one)
     * Uses reference to set $found flag when a match is found
     */
    private function find_first_loop_grid_with_repeater($elements, $current_id, &$found) {
        if ($found) return; // Stop early if already found
        
        foreach ($elements as $element) {
            // Check if we found a loop grid with repeater enabled that's not the current one
            if (isset($element['widgetType']) && $element['widgetType'] === 'loop-grid' &&
                $element['id'] !== $current_id &&
                isset($element['settings']['use_acf_repeater']) && $element['settings']['use_acf_repeater'] === 'yes') {
                $found = true;
                return;
            }
            
            // Check nested elements if any
            if (!$found && isset($element['elements']) && is_array($element['elements'])) {
                $this->find_first_loop_grid_with_repeater($element['elements'], $current_id, $found);
            }
        }
    }
}
