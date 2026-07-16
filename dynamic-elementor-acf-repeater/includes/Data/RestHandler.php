<?php

/**
 * REST route registration, authorization, and rendering.
 *
 * @package DynamicElementorAcfRepeater
 */
namespace DynamicElementorAcfRepeater\Data;

use DynamicElementorAcfRepeater\Controls\DynamicTagControls;
use DynamicElementorAcfRepeater\Controls\RepeaterFieldSelector;
use DynamicElementorAcfRepeater\MasterMind;
use DynamicElementorAcfRepeater\Support\ContextResolver;
use DynamicElementorAcfRepeater\Support\RenderContextToken;
use WP_Error;
use WP_REST_Request;
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
class RestHandler {
    private $mastermind;

    private $controls;

    private $settings;

    public function __construct() {
        $this->mastermind = MasterMind::instance();
        $this->controls = DynamicTagControls::instance();
        $this->settings = RepeaterFieldSelector::instance();
    }

    public function register_rest_routes() {
        register_rest_route( 'elementor-acf-repeater/v1', '/update-dynamic-tag-controls', array(
            'methods'             => 'POST',
            'callback'            => array($this, 'get_updated_dynamic_tag_controls'),
            'permission_callback' => array($this, 'can_edit_requested_post'),
            'args'                => array(
                'post_id'           => array(
                    'required'          => true,
                    'sanitize_callback' => 'absint',
                ),
                'selected_repeater' => array(
                    'required'          => true,
                    'sanitize_callback' => 'sanitize_text_field',
                ),
                'tags'              => array(
                    'required' => true,
                ),
            ),
        ) );
        register_rest_route( 'elementor-acf-repeater/v1', '/get-saved-repeater-field', array(
            'methods'             => 'GET',
            'callback'            => array($this, 'handle_get_saved_repeater_field_request'),
            'permission_callback' => array($this, 'can_edit_requested_post'),
            'args'                => array(
                'post_id' => array(
                    'required'          => true,
                    'sanitize_callback' => 'absint',
                ),
            ),
        ) );
        if ( earluna_can_use_premium_code() ) {
            register_rest_route( 'elementor-acf-repeater/v1', '/filter-loop-grid', array(
                'methods'             => 'POST',
                'callback'            => array($this, 'render_filtered_loop_grid__premium_only'),
                'permission_callback' => array($this, 'can_render_signed_context__premium_only'),
                'args'                => array(
                    'context' => array(
                        'required' => true,
                        'type'     => 'string',
                    ),
                    'slugs'   => array(
                        'required' => false,
                    ),
                ),
            ) );
        }
    }

    public function can_edit_requested_post( WP_REST_Request $request ) {
        $post_id = absint( $request->get_param( 'post_id' ) );
        if ( !$post_id || !get_post( $post_id ) ) {
            return new WP_Error('invalid_post_id', __( 'The requested document does not exist.', 'dynamic-elementor-acf-repeater' ), array(
                'status' => 404,
            ));
        }
        if ( !current_user_can( 'edit_post', $post_id ) ) {
            return new WP_Error('rest_forbidden', __( 'You cannot edit this document.', 'dynamic-elementor-acf-repeater' ), array(
                'status' => 403,
            ));
        }
        return true;
    }

    public function get_updated_dynamic_tag_controls( $request = null ) {
        try {
            if ( $request instanceof WP_REST_Request ) {
                $data = $request->get_params();
            } else {
                check_ajax_referer( 'elementor-editing', 'nonce' );
                // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Recursively sanitized immediately below because Elementor may send a nested tag array.
                $posted_tags = ( isset( $_POST['tags'] ) ? wp_unslash( $_POST['tags'] ) : '' );
                $posted_tags = ( is_array( $posted_tags ) ? map_deep( $posted_tags, 'sanitize_text_field' ) : sanitize_text_field( $posted_tags ) );
                $data = array(
                    'post_id'           => ( isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0 ),
                    'selected_repeater' => ( isset( $_POST['selected_repeater'] ) ? sanitize_text_field( wp_unslash( $_POST['selected_repeater'] ) ) : '' ),
                    'tags'              => $posted_tags,
                );
                if ( !current_user_can( 'edit_post', $data['post_id'] ) ) {
                    return new WP_Error('rest_forbidden', __( 'You cannot edit this document.', 'dynamic-elementor-acf-repeater' ), array(
                        'status' => 403,
                    ));
                }
            }
            $post_id = absint( $data['post_id'] );
            $selected_repeater = sanitize_text_field( $data['selected_repeater'] );
            $tags = ( is_array( $data['tags'] ) ? $data['tags'] : json_decode( (string) $data['tags'], true ) );
            if ( !$post_id || !$selected_repeater || !is_array( $tags ) ) {
                return new WP_Error('invalid_data', __( 'Invalid input data provided.', 'dynamic-elementor-acf-repeater' ), array(
                    'status' => 400,
                ));
            }
            $selected_repeater = apply_filters(
                'earluna_pre_update_controls',
                $selected_repeater,
                $post_id,
                $this->mastermind->is_edit_mode( $post_id )
            );
            $updated_tags = $this->mastermind->get_updated_dynamic_tag_controls(
                $post_id,
                $selected_repeater,
                $tags,
                $request
            );
            return array(
                'tags'              => $updated_tags,
                'selected_repeater' => $selected_repeater,
            );
        } catch ( \Throwable $throwable ) {
            return new WP_Error('rest_error', $throwable->getMessage(), array(
                'status' => 500,
            ));
        }
    }

    public function handle_get_saved_repeater_field_request( WP_REST_Request $request ) {
        $post_id = absint( $request->get_param( 'post_id' ) );
        $repeater_field = $this->settings->get_saved_repeater_field( $post_id );
        $field_object = ( $repeater_field ? get_field_object( $repeater_field ) : null );
        $field_label = ( $field_object && isset( $field_object['label'] ) ? $field_object['label'] : '' );
        if ( !$field_label && earluna_can_use_premium_code() && class_exists( '\\DynamicElementorAcfRepeater\\LoopGrid\\RowSourceRegistry' ) ) {
            $schema = \DynamicElementorAcfRepeater\LoopGrid\RowSourceRegistry::instance()->get_schema( $repeater_field );
            $field_label = ( $schema && isset( $schema['option_label'] ) ? $schema['option_label'] : '' );
        }
        return array(
            'repeater_field'      => sanitize_text_field( $repeater_field ),
            'repeater_field_name' => sanitize_text_field( $field_label ),
        );
    }

}
