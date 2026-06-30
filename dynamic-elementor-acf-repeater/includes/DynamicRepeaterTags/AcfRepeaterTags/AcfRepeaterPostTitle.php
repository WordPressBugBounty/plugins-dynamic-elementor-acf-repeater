<?php

namespace DynamicElementorAcfRepeater\DynamicTags;

use DynamicElementorAcfRepeater\LoopGrid\LoopGridProvider;



if (!defined('ABSPATH')) {
    exit;
}

class AcfRepeaterPostTitle extends \Elementor\Core\DynamicTags\Data_Tag
{
    public function get_name() {
        return 'acf-repeater-original-title';
    }

    public function get_title() {
        return __('ACF Repeater Original Post Title', 'dynamic-elementor-acf-repeater');
    }

    public function get_group() {
        return 'acf';
    }

    public function get_categories() {
        return [\Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY];
    }

    public function get_value(array $options = []) {
        $post_id = get_the_ID();
        $loop_grid_provider = LoopGridProvider::instance();
        return $loop_grid_provider->get_original_post_title($post_id);
    }
}