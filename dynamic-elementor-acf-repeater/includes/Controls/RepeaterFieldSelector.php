<?php
namespace DynamicElementorAcfRepeater\Controls;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use DynamicElementorAcfRepeater\MasterMind;
use Elementor\Controls_Manager;
use ElementorPro\Modules\LoopBuilder\Documents\Loop;

class RepeaterFieldSelector {
    private static $instance = null;
    private $mastermind;
    const SETTINGS_KEY = 'RepeaterFieldSelector';

    private function __construct() {
        $this->mastermind = MasterMind::instance();
        add_action('elementor/documents/register_controls', [$this, 'register_controls']);
        add_action('elementor/document/before_save', [$this, 'save_settings'], 10, 2);
    }

    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function register_controls($document) {
        if (!$document instanceof Loop || !$document::get_property('has_elements')) {
            return;
        }

        $document->start_controls_section(
            'earluna_loop_settings_section',
            [
                'label' => __('ACF Repeater Loop Settings', 'dynamic-elementor-acf-repeater'),
                'tab' => Controls_Manager::TAB_SETTINGS,
            ]
        );

        $repeater_fields = $this->get_repeater_fields();
        $saved_repeater_field = $this->get_saved_repeater_field($document->get_main_id());

        $document->add_control(
            'earluna_loop_repeater_field',
            [
				'label' => earluna_can_use_premium_code() ? __('ACF Row Schema for Loop', 'dynamic-elementor-acf-repeater') : __('ACF Repeater Field for Loop', 'dynamic-elementor-acf-repeater'),
                'type' => Controls_Manager::SELECT,
                'options' => $repeater_fields,
                'default' => $saved_repeater_field ?: '',
				'description' => earluna_can_use_premium_code() ? __('Select the Repeater row or Flexible Content layout represented by this Loop template.', 'dynamic-elementor-acf-repeater') : __('Select an ACF repeater field to use in this loop template.', 'dynamic-elementor-acf-repeater'),
            ]
        );

        $document->end_controls_section();
    }

    public function save_settings($document, $data) {
        if (isset($data['settings']['earluna_loop_repeater_field'])) {
            $selected_field = $data['settings']['earluna_loop_repeater_field'];
            update_post_meta($document->get_main_id(), 'earluna_loop_repeater_field', $selected_field);
        }
    }
    
    public function get_saved_repeater_field($document_id) {
        $field = get_post_meta($document_id, 'earluna_loop_repeater_field', true);
        return $field;
    }

    private function get_repeater_fields() {
        $repeater_fields = ['' => __('Select...', 'dynamic-elementor-acf-repeater')];

        if (!function_exists('acf_get_field_groups')) {
            return $repeater_fields;
        }

        $acf_groups = acf_get_field_groups();

        foreach ($acf_groups as $group) {
            $fields = acf_get_fields($group['key']);

            foreach ($fields as $field) {
                if ('repeater' === $field['type']) {
                    $repeater_fields[$field['key']] = $field['label'];
                }
            }
        }

		if ( earluna_can_use_premium_code() && class_exists( '\\DynamicElementorAcfRepeater\\LoopGrid\\RowSourceRegistry' ) ) {
			$repeater_fields += \DynamicElementorAcfRepeater\LoopGrid\RowSourceRegistry::instance()->get_schema_options();
		}

        return $repeater_fields;
    }
}
