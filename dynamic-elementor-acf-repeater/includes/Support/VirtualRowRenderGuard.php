<?php

/**
 * Preserve virtual repeater rows while Elementor renders Loop documents.
 *
 * @package DynamicElementorAcfRepeater
 */

namespace DynamicElementorAcfRepeater\Support;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Keeps Elementor Pro's Theme Builder preview query from replacing virtual rows.
 */
class VirtualRowRenderGuard {
	/**
	 * Active plugin-owned queries and their current virtual posts.
	 *
	 * @var array<string, array{post: object, global_query: object|null}>
	 */
	private $active_rows = array();

	/**
	 * Register render-lifecycle hooks.
	 */
	public function register() {
		add_action( 'the_post', array( $this, 'capture_virtual_row' ), 10, 2 );
		add_action( 'loop_end', array( $this, 'release_virtual_query' ), 10, 1 );
		add_action( 'elementor/frontend/before_get_builder_content', array( $this, 'restore_virtual_row' ), 1, 2 );
	}

	/**
	 * Capture the row that WP_Query is about to hand to Elementor.
	 *
	 * @param object $post  Current post object.
	 * @param object $query Current query.
	 */
	public function capture_virtual_row( $post, $query ) {
		if ( ! $this->is_virtual_query( $query ) || ! VirtualRowContext::from_post( $post ) ) {
			return;
		}

		global $wp_query;

		$key = $this->query_key( $query );
		// Reinsert the query so the innermost active loop remains last.
		unset( $this->active_rows[ $key ] );
		$this->active_rows[ $key ] = array(
			'post'         => $post,
			'global_query' => is_object( $wp_query ) ? $wp_query : null,
		);
	}

	/**
	 * Stop retaining a virtual row after its query finishes rendering.
	 *
	 * @param object $query Finished query.
	 */
	public function release_virtual_query( $query ) {
		if ( ! is_object( $query ) ) {
			return;
		}

		unset( $this->active_rows[ $this->query_key( $query ) ] );
	}

	/**
	 * Restore the active virtual row before a Theme Builder document renders.
	 *
	 * Elementor Pro asks its preview manager to resolve get_the_ID() before every
	 * Theme Document render. Elementor's document manager applies absint(), so a
	 * virtual ID such as -14 can resolve real post 14 and replace the row query.
	 * Unwind that preview switch, restore the captured row, and push a balanced
	 * no-op query frame for Theme_Document::after_get_content().
	 *
	 * @param object $document  Elementor document about to render.
	 * @param bool   $is_excerpt Whether Elementor is rendering an excerpt.
	 */
	public function restore_virtual_row( $document, $is_excerpt = false ) {
		unset( $is_excerpt );

		$active = $this->get_active_row();
		if ( ! $active || ! $this->is_theme_document( $document ) ) {
			return;
		}

		global $post, $wp_query;

		$db                   = $this->get_elementor_db();
		$outer_global_query   = isset( $active['global_query'] ) && is_object( $active['global_query'] ) ? $active['global_query'] : null;
		$preview_was_switched = $outer_global_query && $wp_query !== $outer_global_query;

		if ( $preview_was_switched && $db && method_exists( $db, 'restore_current_query' ) ) {
			$db->restore_current_query();
		}

		// The row query is local to Elementor's loop provider; it is not normally
		// the global query. Restore the global query captured before the preview
		// switch, then make only the virtual row the active global post.
		if ( $outer_global_query && $wp_query !== $outer_global_query ) {
			$wp_query = $outer_global_query; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		}
		$post = $active['post']; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

		if ( method_exists( $wp_query, 'setup_postdata' ) ) {
			$wp_query->setup_postdata( $post );
		}

		// Theme_Document::after_get_content() always restores once. When its
		// preview manager did not switch (or was unwound above), give that restore
		// a matching no-op frame so it cannot pop an outer document's query.
		if ( $db && method_exists( $db, 'switch_to_query' ) && isset( $wp_query->query ) && is_array( $wp_query->query ) ) {
			$db->switch_to_query( $wp_query->query, true );
		}
	}

	/**
	 * Clear request-local tracking. Used by focused tests and long-lived workers.
	 */
	public function reset() {
		$this->active_rows = array();
	}

	/**
	 * @return array{post: object, global_query: object|null}|null
	 */
	private function get_active_row() {
		if ( empty( $this->active_rows ) ) {
			return null;
		}

		$active = end( $this->active_rows );
		reset( $this->active_rows );

		return $active;
	}

	/**
	 * @param object $query Query candidate.
	 * @return bool
	 */
	private function is_virtual_query( $query ) {
		return is_object( $query ) && ! empty( $query->query_vars['earluna_virtual_posts'] );
	}

	/**
	 * @param object $query Query object.
	 * @return string
	 */
	private function query_key( $query ) {
		return function_exists( 'spl_object_id' ) ? (string) spl_object_id( $query ) : spl_object_hash( $query );
	}

	/**
	 * @param object $document Elementor document.
	 * @return bool
	 */
	protected function is_theme_document( $document ) {
		return is_object( $document ) && is_a( $document, 'ElementorPro\\Modules\\ThemeBuilder\\Documents\\Theme_Document' );
	}

	/**
	 * @return object|null
	 */
	protected function get_elementor_db() {
		if ( ! class_exists( '\\Elementor\\Plugin' ) || ! isset( \Elementor\Plugin::$instance->db ) ) {
			return null;
		}

		return \Elementor\Plugin::$instance->db;
	}
}
