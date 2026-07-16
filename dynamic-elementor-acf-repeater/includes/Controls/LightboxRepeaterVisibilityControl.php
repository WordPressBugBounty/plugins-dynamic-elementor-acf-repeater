<?php
namespace DynamicElementorAcfRepeater\Controls;

use Elementor\Controls_Manager;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class LightboxRepeaterVisibilityControl {
    public function __construct() {
        add_action('elementor/element/common/_section_style/after_section_end', [$this, 'register_controls']);
        add_action('elementor/element/section/_section_style/after_section_end', [$this, 'register_controls']);
        // Removed custom tab to avoid panel rebuilds/flicker; use core tabs instead
        add_action('elementor/frontend/before_render', [$this, 'apply_visibility_classes']);
    }

    public function register_controls($element) {
        // Only in Loop Item documents, not on regular pages/posts with Loop Grid
        try {
            $current_doc = \Elementor\Plugin::$instance->documents->get_current();
            if (!$current_doc || (method_exists($current_doc, 'get_type') && $current_doc->get_type() !== 'loop-item')) {
                return;
            }
        } catch (\Throwable $e) {
            return;
        }
        // Avoid duplicate registration when panel re-initializes
        if ($element->get_controls('earluna_lightbox_visibility')) {
            return;
        }
        $element->start_controls_section(
            'earluna_lightbox_visibility_section',
            [
                'tab' => Controls_Manager::TAB_CONTENT,
                'label' => __('Repeater Lightbox Visibility', 'dynamic-elementor-acf-repeater'),
                'condition' => [
                    'earluna_has_acf_repeater_tag' => 'yes',
                ],
            ]
        );

        // Hidden flag controlled via editor JS when a repeater tag is attached to this element
        if (!$element->get_controls('earluna_has_acf_repeater_tag')) {
            $element->add_control(
                'earluna_has_acf_repeater_tag',
                [
                    'type' => Controls_Manager::HIDDEN,
                    'default' => 'no',
                ]
            );
        }

        $lightboxVisibilityOptions = [
            'default' => [
                'title' => __('Default (show in both)', 'dynamic-elementor-acf-repeater'),
                'icon'  => 'eicon-dual-button',
            ],
            'hide' => [
                'title' => __('Hide in Lightbox (show in loop only)', 'dynamic-elementor-acf-repeater'),
                'icon'  => 'eicon-atomic',
            ],
            'show' => [
                'title' => __('Show Only in Lightbox (hide in loop)', 'dynamic-elementor-acf-repeater'),
                'icon'  => 'eicon-image-box',
            ],
        ];

        // Show upgrade notice for free users
        $pro_notice = !earluna_can_use_premium_code()
            ? earluna_get_upgrade_notice(__('Repeater Lightbox Visibility options are', 'dynamic-elementor-acf-repeater'))
            : '';

        $element->add_control(
            'earluna_lightbox_visibility',
            [
                'label' => __('Visibility', 'dynamic-elementor-acf-repeater'),
                'type' => !earluna_can_use_premium_code() ? 'earluna_switcher' : Controls_Manager::CHOOSE,
                'default' => 'default',
                'options' => $lightboxVisibilityOptions,
                'toggle' => false,
                'render_type' => 'none',
                'description' => $pro_notice,
                'classes' => !earluna_can_use_premium_code() ? 'elementor-control-disabled' : '',
            ]
        );

        $element->end_controls_section();
    }

    // Custom tab removed; control lives under core Content tab

    public function apply_visibility_classes($element) {
        // PRO-only behavior on frontend/editor render
        if (!earluna_can_use_premium_code()) {
            return;
        }
        $settings = $element->get_settings_for_display();
        
        if (isset($settings['earluna_has_acf_repeater_tag']) && $settings['earluna_has_acf_repeater_tag'] === 'yes') {
            $visibility = isset($settings['earluna_lightbox_visibility']) ? $settings['earluna_lightbox_visibility'] : 'default';
            
            if ($visibility === 'hide') {
                $element->add_render_attribute('_wrapper', 'class', 'ear-hide-in-lightbox');
            } elseif ($visibility === 'show') {
                $element->add_render_attribute('_wrapper', 'class', 'ear-hide-in-loop');
            }
        }
    }
}
