<?php

/**
 * Signed render context support.
 *
 * @package DynamicElementorAcfRepeater
 */

namespace DynamicElementorAcfRepeater\Support;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Creates signed, narrowly-scoped contexts for public Loop Grid refreshes.
 */
final class RenderContextToken {
	const DEFAULT_TTL = 43200;

	/**
	 * @param array<string, mixed> $context Render context.
	 */
	public static function issue( array $context ) {
		$payload               = self::normalize_context( $context );
		$payload['issued_at']  = time();
		$payload['expires_at'] = time() + (int) apply_filters( 'earluna_render_context_ttl', self::DEFAULT_TTL );
		$payload['user_id']    = self::is_public_context( $payload ) ? 0 : get_current_user_id();

		$encoded = self::base64url_encode( wp_json_encode( $payload ) );
		return $encoded . '.' . hash_hmac( 'sha256', $encoded, wp_salt( 'nonce' ) );
	}

	/**
	 * @return array<string, mixed>|\WP_Error
	 */
	public static function verify( $token ) {
		if ( ! is_string( $token ) || 2 !== count( explode( '.', $token ) ) ) {
			return new \WP_Error( 'invalid_render_context', __( 'Invalid render context.', 'dynamic-elementor-acf-repeater' ), array( 'status' => 403 ) );
		}

		list( $encoded, $signature ) = explode( '.', $token, 2 );
		$expected                    = hash_hmac( 'sha256', $encoded, wp_salt( 'nonce' ) );
		if ( ! hash_equals( $expected, $signature ) ) {
			return new \WP_Error( 'invalid_render_context', __( 'Invalid render context signature.', 'dynamic-elementor-acf-repeater' ), array( 'status' => 403 ) );
		}

		$decoded = self::base64url_decode( $encoded );
		$payload = json_decode( $decoded, true );
		if ( ! is_array( $payload ) ) {
			return new \WP_Error( 'invalid_render_context', __( 'Invalid render context payload.', 'dynamic-elementor-acf-repeater' ), array( 'status' => 403 ) );
		}

		$context    = self::normalize_context( $payload );
		$expires_at = isset( $payload['expires_at'] ) ? absint( $payload['expires_at'] ) : 0;
		$user_id    = isset( $payload['user_id'] ) ? absint( $payload['user_id'] ) : 0;

		if ( ! $expires_at || $expires_at < time() ) {
			return new \WP_Error( 'expired_render_context', __( 'The render context has expired. Refresh the page and try again.', 'dynamic-elementor-acf-repeater' ), array( 'status' => 403 ) );
		}

		if ( $user_id && get_current_user_id() !== $user_id && ! self::is_public_context( $context ) ) {
			return new \WP_Error( 'invalid_render_context_user', __( 'This render context belongs to another user.', 'dynamic-elementor-acf-repeater' ), array( 'status' => 403 ) );
		}

		$context['user_id']    = $user_id;
		$context['expires_at'] = $expires_at;
		return $context;
	}

	/**
	 * @param array<string, mixed> $context Raw context.
	 * @return array<string, int|string>
	 */
	private static function normalize_context( array $context ) {
		return array(
			'post_id'    => isset( $context['post_id'] ) ? absint( $context['post_id'] ) : 0,
			'doc_id'     => isset( $context['doc_id'] ) ? absint( $context['doc_id'] ) : 0,
			'widget_id'  => isset( $context['widget_id'] ) ? sanitize_key( $context['widget_id'] ) : '',
			'param_name' => isset( $context['param_name'] ) ? sanitize_key( $context['param_name'] ) : '',
		);
	}

	/**
	 * Public documents do not need user-bound tokens. This also keeps cached
	 * frontend markup usable when a logged-in visitor later reaches REST as an
	 * anonymous user because no REST nonce was sent with the public request.
	 *
	 * @param array<string, mixed> $context Normalized render context.
	 */
	private static function is_public_context( array $context ) {
		$post_id = isset( $context['post_id'] ) ? absint( $context['post_id'] ) : 0;
		$doc_id  = isset( $context['doc_id'] ) ? absint( $context['doc_id'] ) : 0;
		$doc_id  = $doc_id ? $doc_id : $post_id;

		foreach ( array_unique( array( $post_id, $doc_id ) ) as $object_id ) {
			$post = $object_id ? get_post( $object_id ) : null;
			if ( ! $post || 'publish' !== get_post_status( $post ) || ! empty( $post->post_password ) || ! is_post_publicly_viewable( $post ) ) {
				return false;
			}
		}

		return true;
	}

	private static function base64url_encode( $value ) {
		return rtrim( strtr( base64_encode( (string) $value ), '+/', '-_' ), '=' );
	}

	private static function base64url_decode( $value ) {
		$padding = strlen( $value ) % 4;
		if ( $padding ) {
			$value .= str_repeat( '=', 4 - $padding );
		}
		return (string) base64_decode( strtr( $value, '-_', '+/' ), true );
	}
}
