<?php

namespace DynamicElementorAcfRepeater\DynamicTags;

if ( !defined( 'ABSPATH' ) ) {
    exit;
    // Exit if accessed directly.
}
use DynamicElementorAcfRepeater\MasterMind;
use DynamicElementorAcfRepeater\Controls\DynamicTagControls;
class RepeaterTagManager {
    private static $instance = null;

    private $controls;

    private $mastermind;

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->mastermind = MasterMind::instance();
        $this->controls = DynamicTagControls::instance();
        $this->load_tag_classes();
    }

    public function load_tag_classes() {
        require_once plugin_dir_path( __FILE__ ) . 'TagBaseTrait.php';
        $tag_classes = self::get_tag_classes_names();
        foreach ( $tag_classes as $class ) {
            $file_path = plugin_dir_path( __FILE__ ) . 'AcfRepeaterTags/' . $class . '.php';
            if ( file_exists( $file_path ) ) {
                require_once $file_path;
                $full_class_name = 'DynamicElementorAcfRepeater\\DynamicTags\\' . $class;
                class_exists( $full_class_name );
            }
        }
    }

    public static function get_tag_classes_names() {
        $available_tags = ['AcfRepeaterImage', 'AcfRepeaterPostTitle', 'AcfRepeaterText'];
        return $available_tags;
    }

    private static function get_all_repeater_fields( $types, $post_id ) {
        $repeater_fields = [];
        $acf_groups = acf_get_field_groups();
        foreach ( $acf_groups as $group ) {
            $fields = acf_get_fields( $group['key'] );
            self::process_fields( $fields, $types, $repeater_fields );
        }
        return $repeater_fields;
    }

    public static function process_fields(
        $fields,
        $types,
        &$repeater_fields,
        $parent_key = ''
    ) {
        foreach ( $fields as $field ) {
            if ( $field['type'] === 'repeater' ) {
                $options = [];
                foreach ( $field['sub_fields'] as $sub_field ) {
                    if ( in_array( $sub_field['type'], $types, true ) ) {
                        $sub_key = ( $parent_key ? $parent_key . '_' . $sub_field['name'] : $sub_field['name'] );
                        $options[$sub_key] = $sub_field['label'];
                    }
                }
                if ( !empty( $options ) ) {
                    $key = ( $parent_key ? $parent_key . '_' . $field['name'] : $field['name'] );
                    $repeater_fields[] = [
                        'label'   => $field['label'],
                        'options' => $options,
                    ];
                }
                // Process nested repeaters
                self::process_fields(
                    $field['sub_fields'],
                    $types,
                    $repeater_fields,
                    $key
                );
            }
        }
    }

}
