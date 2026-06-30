<?php

namespace DynamicElementorAcfRepeater\DynamicTags;


if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class AcfRepeaterImage extends AcfRepeaterTagBase {
    public function get_name() {
        return 'acf-repeater-image';
    }

    public function get_title() {
        return __('ACF Repeater Image', 'dynamic-elementor-acf-repeater');
    }

    public function get_group() {
        return 'acf';
    }

    public function get_categories() {
        return [
            \Elementor\Modules\DynamicTags\Module::IMAGE_CATEGORY,
            \Elementor\Modules\DynamicTags\Module::URL_CATEGORY,
        ];
    }

    public function get_supported_fields() {
        return ['image'];
    }

    public function render() {
        $value = $this->get_value();
        if (!empty($value['url'])) {
            echo esc_url($value['url']);
        }
    }

    public function get_value(array $options = []) {
        try {
            if ($this->mastermind->is_in_widgets_context() || $this->mastermind->is_all_processing_disabled()) {
                return ['id' => null, 'url' => ''];
            }
            $field_key = $this->get_settings('repeater_field');
        
            if (empty($field_key)) {
                return ['id' => null, 'url' => ''];
            }
        
            $value = $this->get_repeater_value($field_key);
        
            if ($value === null) {
                return ['id' => null, 'url' => ''];
            }
        
            $image_data = ['id' => null, 'url' => ''];
            if (is_array($value) && isset($value['ID']) && isset($value['url'])) {
                $image_data['id'] = $value['ID'];
                $image_data['url'] = $value['url'];
            } elseif (is_numeric($value)) {
                $image_data['id'] = $value;
                $image_data['url'] = wp_get_attachment_url($value);
            } elseif (is_string($value) && filter_var($value, FILTER_VALIDATE_URL)) {
                $image_data['id'] = attachment_url_to_postid($value);
                $image_data['url'] = $value;
            }
        
            return $image_data;
        } catch (\Exception $e) {
            return ['id' => null, 'url' => ''];
        }
    }
}