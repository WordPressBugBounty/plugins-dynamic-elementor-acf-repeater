<?php

namespace DynamicElementorAcfRepeater\DynamicTags;

use DynamicElementorAcfRepeater\MasterMind;
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
class AcfRepeaterText extends AcfRepeaterTagBase {
    public function __construct( $data = [] ) {
        parent::__construct( $data );
    }

    public function get_supported_fields() {
        $supported_fields = ['text', 'textarea'];
        $pro_fields = $this->get_pro_fields();
        $all_fields = array_merge( $supported_fields, $pro_fields );
        return $all_fields;
    }

    /**
     * Get a list of premium fields for display purposes in the free version.
     * This method is used to show upgrade messages and does not provide premium functionality.
     */
    public function get_pro_fields() {
        return [
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
            'color_picker'
        ];
    }

    public function get_categories() {
        $categories = [\Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY];
        return $categories;
    }

    public function get_value( array $options = [] ) {
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
        if ( !in_array( $field_type, ['text', 'textarea'] ) ) {
            return '';
        }
        $result = '';
        if ( is_array( $value ) ) {
            $result = implode( ', ', array_map( 'strval', $value ) );
        } elseif ( is_object( $value ) ) {
            $result = wp_json_encode( $value );
        } else {
            $result = (string) $value;
        }
        return $result;
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
