<?php

namespace DynamicElementorAcfRepeater\DynamicTags;


if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
require_once DYNAMIC_ELEMENTOR_ACF_REPEATER_PLUGIN_PATH . 'includes/Data/RepeaterDataTrait.php';

trait TagBaseTrait {
    use \DynamicElementorAcfRepeater\Data\RepeaterDataTrait;
    protected $mastermind;
    protected $controls;

    public function __construct($data = []) {
        parent::__construct($data);
        $this->mastermind = \DynamicElementorAcfRepeater\MasterMind::instance();
        $this->controls = \DynamicElementorAcfRepeater\Controls\DynamicTagControls::instance();
    }


    public function get_name() {
        return 'acf-repeater-' . strtolower(str_replace('AcfRepeater', '', (new \ReflectionClass($this))->getShortName()));
    }
    
    public function get_title() {
        $class_name = (new \ReflectionClass($this))->getShortName();
        $tag_type = ucfirst(str_replace('AcfRepeater', '', $class_name));
        /* translators: %s: Dynamic tag type */
        return sprintf(__('ACF Repeater %s', 'dynamic-elementor-acf-repeater'), $tag_type);
    }

    public function get_categories() {
        $class_name = get_class($this);
        if (strpos($class_name, 'Image') !== false) {
            return [\Elementor\Modules\DynamicTags\Module::IMAGE_CATEGORY];
        } elseif (strpos($class_name, 'URL') !== false) {
            return [\Elementor\Modules\DynamicTags\Module::URL_CATEGORY];
        } else {
            return [\Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY];
        }
    }

    public function get_group() {
        return 'acf';
    }

    protected function get_field_name($field_key) {
        $field = get_field_object($field_key);
        return $field ? $field['name'] : $field_key;
    }

    public function get_supported_fields() {
        return [];  // Override this in child classes
    }
}

abstract class AcfRepeaterTagBase extends \Elementor\Core\DynamicTags\Data_Tag {
    use TagBaseTrait;
}