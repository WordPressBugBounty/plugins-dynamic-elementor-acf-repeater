<?php

/**
 * Resolve the ACF object that owns a repeater field.
 *
 * @package DynamicElementorAcfRepeater
 */

namespace DynamicElementorAcfRepeater\Support;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Produces one normalized context contract for Loop widget consumers.
 */
final class ContextResolver {
	const SETTING_TYPE              = 'earluna_context_type';
	const SETTING_EXPLICIT_ID       = 'earluna_context_id';
	private static $request_context = null;
	private $premium;

	public function __construct( $premium = null ) {
		$this->premium = null === $premium
			? ( function_exists( 'earluna_can_use_premium_code' ) && earluna_can_use_premium_code() )
			: (bool) $premium;
	}

	/**
	 * Restore a signed context while Elementor re-renders a widget over REST.
	 *
	 * @param array<string, mixed> $context Verified render-token context.
	 */
	public static function set_request_context( array $context ) {
		if ( empty( $context['acf_context_id'] ) || empty( $context['acf_context_type'] ) ) {
			self::$request_context = null;
			return;
		}

		self::$request_context = array(
			'type'          => sanitize_key( $context['acf_context_type'] ),
			'acf_object_id' => $context['acf_context_id'],
			'object_id'     => isset( $context['acf_context_object_id'] ) ? $context['acf_context_object_id'] : $context['acf_context_id'],
			'label'         => isset( $context['acf_context_label'] ) ? (string) $context['acf_context_label'] : (string) $context['acf_context_id'],
			'reason'        => '',
		);
	}

	public static function clear_request_context() {
		self::$request_context = null;
	}

	/**
	 * Resolve a widget's requested ACF context.
	 *
	 * @param array<string, mixed> $settings Elementor widget settings.
	 * @return array<string, int|string>
	 */
	public function resolve( $settings = array() ) {
		$requested = isset( $settings[ self::SETTING_TYPE ] ) ? sanitize_key( $settings[ self::SETTING_TYPE ] ) : 'auto';
		if ( in_array( $requested, array( 'current_user', 'explicit' ), true ) && ! $this->premium ) {
			$requested = 'auto';
		}
		if ( self::$request_context ) {
			$context              = self::$request_context;
			$context['requested'] = $requested;
			return $context;
		}

		switch ( $requested ) {
			case 'current_post':
				return $this->resolve_post( get_the_ID(), $requested );

			case 'queried_object':
				return $this->resolve_queried_object( $requested );

			case 'options':
				return $this->context( $requested, 'options', 'options', __( 'Options page', 'dynamic-elementor-acf-repeater' ) );

			case 'current_user':
				return $this->resolve_user( get_current_user_id(), $requested );

			case 'explicit':
				$explicit_id = isset( $settings[ self::SETTING_EXPLICIT_ID ] ) ? $settings[ self::SETTING_EXPLICIT_ID ] : '';
				return $this->resolve_explicit( $explicit_id, $requested );
		}

		$queried = $this->resolve_queried_object( 'auto' );
		if ( in_array( $queried['type'], array( 'term', 'user' ), true ) ) {
			return $queried;
		}

		$preview_id = $this->get_elementor_preview_id();
		if ( $preview_id ) {
			return $this->resolve_post( $preview_id, 'auto' );
		}
		if ( 'post' === $queried['type'] ) {
			return $queried;
		}

		$post_id = get_the_ID();
		if ( $post_id ) {
			return $this->resolve_post( $post_id, 'auto' );
		}

		return 'none' !== $queried['type'] ? $queried : $this->missing( 'auto', __( 'No current or queried ACF object could be resolved.', 'dynamic-elementor-acf-repeater' ) );
	}

	/**
	 * Convert a normalized context back into an ACF-compatible object ID.
	 *
	 * @param mixed $value User-supplied explicit object ID.
	 * @return array<string, int|string>
	 */
	private function resolve_explicit( $value, $requested ) {
		$value = strtolower( trim( (string) $value ) );

		if ( preg_match( '/^[1-9][0-9]*$/', $value ) ) {
			return $this->resolve_post( absint( $value ), $requested );
		}

		if ( in_array( $value, array( 'option', 'options' ), true ) ) {
			return $this->context( $requested, 'options', 'options', __( 'Options page', 'dynamic-elementor-acf-repeater' ) );
		}

		if ( preg_match( '/^user_([1-9][0-9]*)$/', $value, $matches ) ) {
			return $this->resolve_user( absint( $matches[1] ), $requested );
		}

		if ( preg_match( '/^([a-z][a-z0-9_-]*)_([1-9][0-9]*)$/', $value, $matches ) ) {
			$term_id  = absint( $matches[2] );
			$taxonomy = 'term' === $matches[1] ? '' : sanitize_key( $matches[1] );
			$term     = $taxonomy ? get_term( $term_id, $taxonomy ) : get_term( $term_id );

			if ( $term && ! is_wp_error( $term ) ) {
				$taxonomy = $taxonomy ? $taxonomy : sanitize_key( $term->taxonomy );
				return $this->context( $requested, 'term', $taxonomy . '_' . $term_id, $term->name, $term_id );
			}

			return $this->missing( $requested, __( 'The requested taxonomy term does not exist.', 'dynamic-elementor-acf-repeater' ) );
		}

		return $this->missing( $requested, __( 'Enter a post ID, user_# ID, taxonomy_# ID, or options.', 'dynamic-elementor-acf-repeater' ) );
	}

	/**
	 * Resolve WordPress's queried object without assuming a singular post.
	 */
	private function resolve_queried_object( $requested ) {
		$object = function_exists( 'get_queried_object' ) ? get_queried_object() : null;

		if ( is_object( $object ) && isset( $object->term_id, $object->taxonomy ) ) {
			$term_id  = absint( $object->term_id );
			$taxonomy = sanitize_key( $object->taxonomy );
			$label    = isset( $object->name ) ? $object->name : sprintf( '%s #%d', $taxonomy, $term_id );
			return $this->context( $requested, 'term', $taxonomy . '_' . $term_id, $label, $term_id );
		}

		if ( is_object( $object ) && isset( $object->ID, $object->user_login ) ) {
			return $this->resolve_user( $object->ID, $requested, $object );
		}

		if ( is_object( $object ) && isset( $object->ID ) ) {
			return $this->resolve_post( $object->ID, $requested, $object );
		}

		return $this->missing( $requested, __( 'The current query does not expose an ACF-compatible object.', 'dynamic-elementor-acf-repeater' ) );
	}

	private function resolve_post( $post_id, $requested, $post = null ) {
		$post_id = absint( $post_id );
		if ( ! $post_id ) {
			return $this->missing( $requested, __( 'No post could be resolved for this context.', 'dynamic-elementor-acf-repeater' ) );
		}

		$post = $post ? $post : get_post( $post_id );
		/* translators: %d: WordPress post ID. */
		$label = $post && isset( $post->post_title ) && $post->post_title ? $post->post_title : sprintf( __( 'Post #%d', 'dynamic-elementor-acf-repeater' ), $post_id );
		return $this->context( $requested, 'post', $post_id, $label, $post_id );
	}

	private function resolve_user( $user_id, $requested, $user = null ) {
		$user_id = absint( $user_id );
		if ( ! $user_id ) {
			return $this->missing( $requested, __( 'No user could be resolved for this context.', 'dynamic-elementor-acf-repeater' ) );
		}

		if ( ! $user && function_exists( 'get_user_by' ) ) {
			$user = get_user_by( 'id', $user_id );
		}

		/* translators: %d: WordPress user ID. */
		$label = $user && isset( $user->display_name ) && $user->display_name ? $user->display_name : sprintf( __( 'User #%d', 'dynamic-elementor-acf-repeater' ), $user_id );
		return $this->context( $requested, 'user', 'user_' . $user_id, $label, $user_id );
	}

	private function get_elementor_preview_id() {
		if ( ! class_exists( '\\Elementor\\Plugin' ) || ! isset( \Elementor\Plugin::$instance->documents ) ) {
			return 0;
		}

		$plugin          = \Elementor\Plugin::$instance;
		$is_edit_mode    = isset( $plugin->editor ) && method_exists( $plugin->editor, 'is_edit_mode' ) && $plugin->editor->is_edit_mode();
		$is_preview_mode = isset( $plugin->preview ) && method_exists( $plugin->preview, 'is_preview_mode' ) && $plugin->preview->is_preview_mode();

		// A Loop Item's Preview Post is an editor aid. On a normal frontend
		// request the queried post owns the repeater, even while Elementor's
		// current document is the Loop Item and carries saved preview settings.
		if ( ! $is_edit_mode && ! $is_preview_mode ) {
			return 0;
		}

		$document = $plugin->documents->get_current();
		if ( ! $document || ! method_exists( $document, 'get_settings' ) ) {
			return 0;
		}

		$settings = $document->get_settings();
		return is_array( $settings ) && ! empty( $settings['preview_id'] ) ? absint( $settings['preview_id'] ) : 0;
	}

	private function context( $requested, $type, $acf_object_id, $label, $object_id = 0 ) {
		return array(
			'requested'     => sanitize_key( $requested ),
			'type'          => sanitize_key( $type ),
			'acf_object_id' => $acf_object_id,
			'object_id'     => $object_id ? absint( $object_id ) : $acf_object_id,
			'label'         => (string) $label,
			'reason'        => '',
		);
	}

	private function missing( $requested, $reason ) {
		return array(
			'requested'     => sanitize_key( $requested ),
			'type'          => 'none',
			'acf_object_id' => '',
			'object_id'     => 0,
			'label'         => __( 'Unresolved', 'dynamic-elementor-acf-repeater' ),
			'reason'        => (string) $reason,
		);
	}
}
