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
 * Keeps Elementor Pro's Theme Builder preview query from replacing virtual rows
 * and keeps Elementor's shared Loop skins bound to the widget still rendering.
 */
class VirtualRowRenderGuard {
	/**
	 * Active plugin-owned queries and their current virtual posts.
	 *
	 * @var array<string, array{post: object, global_query: object|null, document_id: int}>
	 */
	private $active_rows = array();

	/**
	 * Loop widgets currently rendering, outermost first.
	 *
	 * @var array<int, object>
	 */
	private $loop_widgets = array();

	/**
	 * Register render-lifecycle hooks.
	 */
	public function register() {
		add_action( 'the_post', array( $this, 'capture_virtual_row' ), 10, 2 );
		add_action( 'loop_end', array( $this, 'release_virtual_query' ), 10, 1 );
		add_action( 'elementor/frontend/before_get_builder_content', array( $this, 'restore_virtual_row' ), 1, 2 );
		// Frontend and Loop Item renders pass through print_element(); the editor
		// renders a widget through render_content() directly. Enter and leave on
		// both lifecycles so nesting is tracked either way.
		add_action( 'elementor/frontend/widget/before_render', array( $this, 'enter_loop_widget' ), 1, 1 );
		add_action( 'elementor/widget/before_render_content', array( $this, 'enter_loop_widget' ), 1, 1 );
		add_filter( 'elementor/widget/render_content', array( $this, 'leave_loop_widget_content' ), PHP_INT_MAX, 2 );
		add_action( 'elementor/frontend/widget/after_render', array( $this, 'leave_loop_widget' ), PHP_INT_MAX, 1 );
	}

	/**
	 * Track a Loop Grid or Loop Carousel that starts rendering.
	 *
	 * @param object $widget Elementor widget about to render.
	 */
	public function enter_loop_widget( $widget ) {
		if ( ! $this->is_loop_widget( $widget ) ) {
			return;
		}

		if ( ! empty( $this->loop_widgets ) && end( $this->loop_widgets ) === $widget ) {
			return;
		}

		$this->loop_widgets[] = $widget;
	}

	/**
	 * Filter-shaped wrapper for leave_loop_widget().
	 *
	 * @param string $content Rendered widget content.
	 * @param object $widget  Elementor widget that finished rendering.
	 * @return string
	 */
	public function leave_loop_widget_content( $content, $widget ) {
		$this->leave_loop_widget( $widget );

		return $content;
	}

	/**
	 * Rebind the enclosing Loop widget's skin after a nested Loop widget renders.
	 *
	 * Elementor keeps one skin object per widget type and points it at whichever
	 * widget renders last. A Loop Grid inside a Loop Item therefore leaves the
	 * outer Loop Grid's skin pointing at the inner widget, so every later outer
	 * row would use the inner widget's template and settings. Restore the outer
	 * widget as soon as the inner one finishes.
	 *
	 * @param object $widget Elementor widget that finished rendering.
	 */
	public function leave_loop_widget( $widget ) {
		if ( ! $this->is_loop_widget( $widget ) ) {
			return;
		}

		$position = array_search( $widget, $this->loop_widgets, true );
		if ( false === $position ) {
			return;
		}

		array_splice( $this->loop_widgets, $position );

		$outer = empty( $this->loop_widgets ) ? null : end( $this->loop_widgets );
		if ( ! is_object( $outer ) || ! method_exists( $outer, 'get_current_skin' ) ) {
			return;
		}

		$skin = $outer->get_current_skin();
		if ( is_object( $skin ) && method_exists( $skin, 'set_parent' ) ) {
			$skin->set_parent( $outer );
		}
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
			'document_id'  => $this->get_current_document_id(),
		);
	}

	/**
	 * Whether a Loop widget inside the given Elementor document is still
	 * rendering one of its virtual rows.
	 *
	 * A Loop Item that contains a Loop widget over its own rows renders that
	 * document again for every row. The row provider uses this to render such
	 * an inner widget empty instead of recursing without end.
	 *
	 * @param int $document_id Elementor document ID.
	 * @return bool
	 */
	public function is_document_rendering_virtual_row( $document_id ) {
		$document_id = absint( $document_id );
		if ( ! $document_id ) {
			return false;
		}

		foreach ( $this->active_rows as $active ) {
			if ( isset( $active['document_id'] ) && $document_id === (int) $active['document_id'] ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * The Elementor document currently being rendered, by main ID.
	 *
	 * @return int
	 */
	public function get_current_document_id() {
		if ( ! class_exists( '\\Elementor\\Plugin' ) || ! isset( \Elementor\Plugin::$instance->documents ) ) {
			return 0;
		}

		$documents = \Elementor\Plugin::$instance->documents;
		$document  = method_exists( $documents, 'get_current' ) ? $documents->get_current() : null;
		if ( ! is_object( $document ) ) {
			return 0;
		}

		if ( method_exists( $document, 'get_main_id' ) ) {
			return absint( $document->get_main_id() );
		}

		return method_exists( $document, 'get_id' ) ? absint( $document->get_id() ) : 0;
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

		$current_context      = VirtualRowContext::from_post( $post );
		$current_id           = is_object( $post ) && isset( $post->ID ) ? (int) $post->ID : 0;
		$virtual_id           = isset( $active['post']->ID ) ? (int) $active['post']->ID : 0;
		$outer_global_query   = isset( $active['global_query'] ) && is_object( $active['global_query'] ) ? $active['global_query'] : null;
		$preview_was_switched = $outer_global_query && $wp_query !== $outer_global_query;
		$db                   = $this->get_elementor_db();

		// Elementor's preview manager resolves get_the_ID() through absint(), so a
		// virtual ID such as -16 loads Theme Document 16 and switches the query to
		// that document's preview object: post 16 itself when no preview is
		// configured, or its configured preview post (often the front page). A
		// nested Loop Item never legitimately switches the query while a virtual
		// row is active, so any switch away from the captured query is that
		// collision, whichever post it landed on.
		$is_collision = ! $current_context && $virtual_id < 0 && ( $current_id === abs( $virtual_id ) || $preview_was_switched );

		// A real-post Loop Grid may be nested inside the mapped template for the
		// active Repeater/Flexible row. Its Loop Item must retain that real post
		// context. Theme_Document::after_get_content() still restores one query
		// frame, so balance that restore with a no-op frame before returning.
		if ( ! $current_context && ! $is_collision ) {
			if ( $db && method_exists( $db, 'switch_to_query' ) && is_object( $wp_query ) && isset( $wp_query->query ) && is_array( $wp_query->query ) ) {
				$db->switch_to_query( $wp_query->query, true );
			}

			return;
		}

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
		$this->active_rows  = array();
		$this->loop_widgets = array();
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
	 * @param mixed $widget Elementor element candidate.
	 * @return bool
	 */
	private function is_loop_widget( $widget ) {
		return is_object( $widget )
			&& method_exists( $widget, 'get_name' )
			&& in_array( $widget->get_name(), array( 'loop-grid', 'loop-carousel' ), true );
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
