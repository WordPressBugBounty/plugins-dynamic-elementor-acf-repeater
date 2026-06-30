<?php

namespace DynamicElementorAcfRepeater\LoopGrid;

use DynamicElementorAcfRepeater\Controls\LoopGridControlsBase;
use DynamicElementorAcfRepeater\MasterMind;
class LoopGridProvider {
    protected static $instance = null;

    protected $mastermind;

    protected $controls;

    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    protected function __construct() {
        $this->mastermind = \DynamicElementorAcfRepeater\MasterMind::instance();
        $this->init_controls();
        $this->register_controls();
        // Add filter for virtual post classes
        add_filter(
            'post_class',
            [$this, 'add_virtual_post_classes'],
            10,
            3
        );
        // Add filter to clean up WHERE clause
        add_filter(
            'posts_where',
            [$this, 'clean_posts_where'],
            10,
            2
        );
    }

    protected function init_controls() {
        $this->controls = new \DynamicElementorAcfRepeater\Controls\LoopGridControlsBase($this->mastermind, $this);
    }

    protected function register_controls() {
        add_action(
            'elementor/element/loop-grid/section_query/after_section_start',
            [$this->controls, 'register_query_controls'],
            10,
            2
        );
        add_action(
            'elementor/element/loop-grid/section_query/after_section_end',
            [$this->controls, 'register_lightbox_section'],
            10,
            2
        );
    }

    public function add_virtual_posts( $posts, $query ) {
        // Only run this filter for our specific post type
        if ( !isset( $query->query_vars['earluna_virtual_posts'] ) || !$query->query_vars['earluna_virtual_posts'] ) {
            return $posts;
        }
        $repeater_field = $query->get( 'acf_repeater_field' );
        if ( !$repeater_field ) {
            return $posts;
        }
        $virtual_posts = [];
        // If no posts provided, create a dummy post for options page data
        if ( empty( $posts ) ) {
            $repeater_data = get_field( $repeater_field, 'options' );
            if ( $repeater_data && is_array( $repeater_data ) ) {
                // Create virtual posts from options page data
                foreach ( $repeater_data as $index => $row ) {
                    $virtual_post = new \stdClass();
                    $virtual_post->ID = -1 * (999999 . $this->mastermind::VIRTUAL_POST_ID_SEPARATOR . $index);
                    // Use a dummy ID
                    $virtual_post->post_parent = 0;
                    $virtual_post->post_title = 'Options - ' . $repeater_field . ' ' . ($index + 1);
                    $virtual_post->post_status = 'publish';
                    $virtual_post->post_type = 'options';
                    $virtual_post->filter = 'raw';
                    // Add our custom data
                    $virtual_post->acf_repeater_data = $row;
                    $virtual_post->earluna_loop_index = $index;
                    $virtual_posts[] = $virtual_post;
                }
                return $virtual_posts;
            }
        }
        foreach ( $posts as $post ) {
            $repeater_data = get_field( $repeater_field, $post->ID );
            // Fallback to global Options page if no data on the post itself
            if ( !$repeater_data || !is_array( $repeater_data ) ) {
                $repeater_data = get_field( $repeater_field, 'options' );
                // If still empty, skip this post
                if ( !$repeater_data || !is_array( $repeater_data ) ) {
                    continue;
                }
            }
            foreach ( $repeater_data as $index => $row ) {
                $virtual_post = new \stdClass();
                $virtual_post->ID = -1 * ($post->ID . $this->mastermind::VIRTUAL_POST_ID_SEPARATOR . $index);
                $virtual_post->post_parent = $post->ID;
                $virtual_post->post_title = $post->post_title . ' - ' . $repeater_field . ' ' . ($index + 1);
                $virtual_post->post_status = 'publish';
                $virtual_post->post_type = $post->post_type;
                $virtual_post->filter = 'raw';
                // Add our custom data
                $virtual_post->acf_repeater_data = $row;
                $virtual_post->earluna_loop_index = $index;
                $virtual_posts[] = $virtual_post;
            }
        }
        return $virtual_posts;
    }

    public function filter_elementor_query_args( $query_args, $widget ) {
        $settings = $widget->get_settings();
        if ( isset( $settings['use_acf_repeater'] ) && $settings['use_acf_repeater'] === 'yes' ) {
            // Add our virtual posts flags
            $query_args['earluna_virtual_posts'] = 1;
            $query_args['acf_repeater_field'] = $settings['acf_repeater_field'];
            $current_only = ( isset( $settings['query_current_post_only'] ) ? $settings['query_current_post_only'] : 'yes' );
            // Store in query vars so other filters can read it later.
            $query_args['query_current_post_only'] = $current_only;
            // Only modify query if we want current post
            if ( $current_only === 'yes' ) {
                $query_args['post__in'] = [get_the_ID()];
            }
        }
        return $query_args;
    }

    public function get_acf_repeater_fields() {
        $fields = [];
        try {
            if ( function_exists( 'acf_get_field_groups' ) && function_exists( 'acf_get_fields' ) ) {
                $groups = acf_get_field_groups();
                foreach ( $groups as $group ) {
                    $group_fields = acf_get_fields( $group );
                    $this->process_fields( $group_fields, $fields );
                }
            }
        } catch ( \Exception $e ) {
            // Silently handle any exceptions
        }
        return $fields;
    }

    private function process_fields( $fields, &$result, $parent = '' ) {
        foreach ( $fields as $field ) {
            if ( $field['type'] === 'repeater' ) {
                $key = ( $parent ? $parent . '_' . $field['name'] : $field['name'] );
                $result[$key] = $field['label'];
            } elseif ( $field['type'] === 'group' && !empty( $field['sub_fields'] ) ) {
                $this->process_fields( $field['sub_fields'], $result, $field['name'] );
            }
        }
    }

    public function get_original_post_title( $post_id ) {
        if ( $post_id < 0 ) {
            // This is a virtual post
            $original_post_id = abs( $post_id );
            $original_post_id = explode( $this->mastermind::VIRTUAL_POST_ID_SEPARATOR, $original_post_id )[0];
            $post = get_post( $original_post_id );
        } else {
            $post = get_post( $post_id );
        }
        if ( !$post ) {
            return '';
        }
        return get_the_title( $post->ID );
    }

    public function add_virtual_post_classes( $classes, $class, $post_id ) {
        if ( is_string( $post_id ) && strpos( $post_id, '-' ) === 0 ) {
            // Add standard WordPress classes
            $classes[] = 'post-' . abs( $post_id );
            $classes[] = 'type-earluna-repeater-field-post';
            $classes[] = 'status-publish';
            $classes[] = 'hentry';
        }
        return $classes;
    }

    public function clean_posts_where( $where, $query ) {
        // Only clean WHERE clause if we're not in current post only mode
        if ( !isset( $query->query_vars['query_current_post_only'] ) || $query->query_vars['query_current_post_only'] !== 'yes' ) {
            // Remove any NOT IN clauses
            $where = preg_replace( '/AND\\s+wp_posts\\.ID\\s+NOT\\s+IN\\s*\\([^)]+\\)/', '', $where );
        }
        return $where;
    }

}
