<?php

/**
 * Deterministic dynamic value formatting support.
 *
 * @package DynamicElementorAcfRepeater
 */

namespace DynamicElementorAcfRepeater\Support;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Deterministic output normalization for the ACF return formats we support.
 */
final class DynamicValueFormatter {
	public static function media( $value ) {
		$id  = self::attachment_id( $value );
		$url = self::attachment_url( $value, $id );

		if ( ! $id && ! $url ) {
			return array(
				'id'  => 0,
				'url' => '',
			);
		}

		return array(
			'id'  => $id,
			'url' => $url,
		);
	}

	public static function url( $value, $field_type = '' ) {
		if ( is_array( $value ) && isset( $value['url'] ) ) {
			return esc_url_raw( $value['url'] );
		}

		if ( in_array( $field_type, array( 'image', 'file', 'gallery' ), true ) ) {
			$is_list = is_array( $value ) && array_keys( $value ) === range( 0, count( $value ) - 1 );
			$media   = self::media( $is_list ? reset( $value ) : $value );
			return $media['url'];
		}

		if ( in_array( $field_type, array( 'post_object', 'relationship' ), true ) ) {
			$item    = is_array( $value ) ? reset( $value ) : $value;
			$post_id = is_object( $item ) && isset( $item->ID ) ? (int) $item->ID : absint( $item );
			return $post_id ? (string) get_permalink( $post_id ) : '';
		}

		if ( 'taxonomy' === $field_type ) {
			$item = is_array( $value ) ? reset( $value ) : $value;
			$term = is_object( $item ) && isset( $item->term_id ) ? $item : get_term( absint( $item ) );
			if ( ! $term || is_wp_error( $term ) ) {
				return '';
			}
			$link = get_term_link( $term );
			return is_wp_error( $link ) ? '' : (string) $link;
		}

		if ( is_string( $value ) && is_email( $value ) ) {
			return 'mailto:' . sanitize_email( $value );
		}

		if ( is_scalar( $value ) ) {
			$value = (string) $value;
			return wp_http_validate_url( $value ) ? esc_url_raw( $value ) : '';
		}

		return '';
	}

	public static function text( $value ) {
		if ( null === $value ) {
			return '';
		}

		if ( is_scalar( $value ) ) {
			return is_bool( $value ) ? ( $value ? '1' : '0' ) : (string) $value;
		}

		if ( is_object( $value ) ) {
			foreach ( array( 'post_title', 'name', 'title', 'label' ) as $property ) {
				if ( isset( $value->{$property} ) && is_scalar( $value->{$property} ) ) {
					return (string) $value->{$property};
				}
			}
			return '';
		}

		if ( is_array( $value ) ) {
			if ( isset( $value['title'] ) && is_scalar( $value['title'] ) ) {
				return (string) $value['title'];
			}
			if ( isset( $value['label'] ) && is_scalar( $value['label'] ) ) {
				return (string) $value['label'];
			}
			$items = array_filter( array_map( array( __CLASS__, 'text' ), $value ), 'strlen' );
			return implode( ', ', $items );
		}

		return '';
	}

	private static function attachment_id( $value ) {
		if ( is_numeric( $value ) ) {
			return absint( $value );
		}
		if ( is_object( $value ) && isset( $value->ID ) ) {
			return absint( $value->ID );
		}
		if ( is_array( $value ) ) {
			if ( isset( $value['ID'] ) ) {
				return absint( $value['ID'] );
			}
			if ( isset( $value['id'] ) ) {
				return absint( $value['id'] );
			}
		}
		return 0;
	}

	private static function attachment_url( $value, $id ) {
		if ( is_array( $value ) && ! empty( $value['url'] ) ) {
			return esc_url_raw( $value['url'] );
		}
		if ( is_object( $value ) && ! empty( $value->url ) ) {
			return esc_url_raw( $value->url );
		}
		if ( is_string( $value ) && wp_http_validate_url( $value ) ) {
			return esc_url_raw( $value );
		}
		return $id ? (string) wp_get_attachment_url( $id ) : '';
	}
}
