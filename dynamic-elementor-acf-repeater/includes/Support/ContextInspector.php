<?php

/**
 * Editor-only diagnostics for Loop Grid and Loop Carousel row sources.
 *
 * @package DynamicElementorAcfRepeater
 */

namespace DynamicElementorAcfRepeater\Support;

use DynamicElementorAcfRepeater\LoopGrid\LoopGridProvider;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds a metadata-only snapshot of the current widget pipeline.
 */
final class ContextInspector {
	const AJAX_ACTION  = 'earluna_context_inspector';
	const NONCE_ACTION = 'earluna_context_inspector';

	private $provider;
	private $premium;

	public function __construct( LoopGridProvider $provider, $premium ) {
		$this->provider = $provider;
		$this->premium  = (bool) $premium;
	}

	/**
	 * Register the authenticated editor request.
	 */
	public function register_ajax() {
		add_action( 'wp_ajax_' . self::AJAX_ACTION, array( $this, 'handle_ajax' ) );
	}

	/**
	 * Respond with an editor-safe diagnostic snapshot.
	 */
	public function handle_ajax() {
		if ( false === check_ajax_referer( self::NONCE_ACTION, 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'The inspector session expired. Reload Elementor and try again.', 'dynamic-elementor-acf-repeater' ) ), 403 );
		}

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to inspect this widget.', 'dynamic-elementor-acf-repeater' ) ), 403 );
		}

		$document_id = isset( $_POST['document_id'] ) ? absint( $_POST['document_id'] ) : 0;
		if ( ! $document_id || ! current_user_can( 'edit_post', $document_id ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to inspect this Elementor document.', 'dynamic-elementor-acf-repeater' ) ), 403 );
		}

		$raw_settings = isset( $_POST['settings'] ) ? wp_unslash( $_POST['settings'] ) : '';
		if ( ! is_string( $raw_settings ) || strlen( $raw_settings ) > 200000 ) {
			wp_send_json_error( array( 'message' => __( 'The widget settings could not be inspected.', 'dynamic-elementor-acf-repeater' ) ), 400 );
		}

		$decoded  = json_decode( $raw_settings, true );
		$settings = $this->sanitize_settings( is_array( $decoded ) ? $decoded : array() );
		if ( empty( $settings['earluna_enable_context_inspector'] ) || 'yes' !== $settings['earluna_enable_context_inspector'] ) {
			wp_send_json_error( array( 'message' => __( 'Enable the Context Inspector for this widget first.', 'dynamic-elementor-acf-repeater' ) ), 400 );
		}

		$editor_context_set = $this->set_editor_document_context( $settings, $document_id );
		try {
			$snapshot = $this->build_snapshot( $settings );
		} finally {
			if ( $editor_context_set ) {
				ContextResolver::clear_request_context();
			}
		}

		wp_send_json_success( $snapshot );
	}

	/**
	 * Restore the page being edited while admin-ajax.php has no queried object.
	 *
	 * Explicit, Options, user, term, and queried-object settings remain entirely
	 * under the widget's own context controls.
	 *
	 * @param array<string, mixed> $settings    Sanitized widget settings.
	 * @param int                  $document_id Elementor document ID.
	 * @return bool Whether request-local context was installed.
	 */
	private function set_editor_document_context( array $settings, $document_id ) {
		$requested = isset( $settings[ ContextResolver::SETTING_TYPE ] ) ? sanitize_key( $settings[ ContextResolver::SETTING_TYPE ] ) : 'auto';
		if ( ! in_array( $requested, array( 'auto', 'current_post' ), true ) || ! $document_id ) {
			return false;
		}

		$post = get_post( $document_id );
		if ( ! $post ) {
			return false;
		}

		/* translators: %d: Elementor document post ID. */
		$label = ! empty( $post->post_title ) ? $post->post_title : sprintf( __( 'Post #%d', 'dynamic-elementor-acf-repeater' ), $document_id );
		ContextResolver::set_request_context(
			array(
				'acf_context_type'      => 'post',
				'acf_context_id'        => $document_id,
				'acf_context_object_id' => $document_id,
				'acf_context_label'     => $label,
			)
		);

		return true;
	}

	/**
	 * Build one metadata-only snapshot. No field values or signed tokens leave PHP.
	 *
	 * @param array<string, mixed> $settings Loop widget settings.
	 * @return array<string, mixed>
	 */
	public function build_snapshot( $settings ) {
		$settings  = $this->sanitize_settings( is_array( $settings ) ? $settings : array() );
		$provider  = $this->provider_details();
		$state     = $this->provider->inspect_row_source_settings( $settings );
		$context   = isset( $state['active_context'] ) && is_array( $state['active_context'] ) ? $state['active_context'] : array();
		$source    = $this->source_details( isset( $settings['acf_repeater_field'] ) ? $settings['acf_repeater_field'] : '' );
		$all_posts = isset( $state['mode'] ) && 'all_posts' === $state['mode'];
		$summary   = array(
			$this->item( __( 'Field provider', 'dynamic-elementor-acf-repeater' ), $provider['value'], $provider['status'] ),
			$this->item(
				__( 'Resolved context', 'dynamic-elementor-acf-repeater' ),
				$all_posts ? __( 'Per queried post · resolved while Elementor runs the query', 'dynamic-elementor-acf-repeater' ) : $this->format_context( $context ),
				$all_posts ? 'muted' : ( empty( $context['type'] ) || 'none' === $context['type'] ? 'error' : 'ok' )
			),
			$this->item( __( 'Row source', 'dynamic-elementor-acf-repeater' ), $source['value'], $source['status'] ),
			$this->item( __( 'Rows discovered', 'dynamic-elementor-acf-repeater' ), $this->format_row_count( $state ), $this->row_count_status( $state ) ),
			$this->item( __( 'Preview row', 'dynamic-elementor-acf-repeater' ), $this->format_preview_row( $state ), empty( $state['sample'] ) ? 'muted' : 'ok' ),
		);
		$groups    = array(
			array(
				'title' => __( 'Resolved now', 'dynamic-elementor-acf-repeater' ),
				'items' => $summary,
			),
		);

		if ( $this->premium ) {
			$groups[] = array(
				'title' => __( 'Pro pipeline', 'dynamic-elementor-acf-repeater' ),
				'items' => $this->premium_items( $settings, $state, $source ),
			);
		}

		return array(
			'edition' => $this->premium ? 'pro' : 'free',
			'groups'  => $groups,
			'notices' => $this->notices( $settings, $state, $context, $source ),
		);
	}

	/**
	 * Allow only settings needed to explain the row pipeline.
	 *
	 * @param array<string, mixed> $settings Raw settings.
	 * @return array<string, mixed>
	 */
	private function sanitize_settings( array $settings ) {
		$allowed = array(
			'_skin',
			'use_acf_repeater',
			'acf_repeater_field',
			'query_current_post_only',
			ContextResolver::SETTING_TYPE,
			ContextResolver::SETTING_EXPLICIT_ID,
			'use_acf_relationship',
			'earluna_relationship_field',
			'earluna_enable_elementor_filter',
			'earluna_elementor_filter_taxonomy',
			'earluna_elementor_filter_field',
			'earluna_enable_custom_filter',
			'earluna_custom_filter_taxonomy',
			'earluna_use_repeater_taxonomy',
			'earluna_repeater_taxonomy_field',
			'earluna_enable_row_query',
			'earluna_row_search_fields',
			'earluna_row_sort_options',
			'earluna_row_range_filters',
			'earluna_row_layout_filter',
			'earluna_row_query_url_state',
			'earluna_flexible_unmapped_behavior',
			'pagination_type',
			'posts_per_page',
			'earluna_enable_lightbox',
			'earluna_enable_context_inspector',
		);
		$clean   = array();

		foreach ( $settings as $key => $value ) {
			$key = sanitize_key( $key );
			if ( ! in_array( $key, $allowed, true ) && 0 !== strpos( $key, 'earluna_flexible_template_' ) ) {
				continue;
			}
			$clean[ $key ] = $this->sanitize_setting_value( $value, 0 );
		}

		return $clean;
	}

	private function sanitize_setting_value( $value, $depth ) {
		if ( $depth > 3 ) {
			return '';
		}
		if ( is_array( $value ) ) {
			$clean = array();
			foreach ( array_slice( $value, 0, 100, true ) as $key => $item ) {
				$clean[ is_int( $key ) ? $key : sanitize_key( $key ) ] = $this->sanitize_setting_value( $item, $depth + 1 );
			}
			return $clean;
		}
		if ( is_bool( $value ) || is_int( $value ) || is_float( $value ) ) {
			return $value;
		}

		return sanitize_text_field( (string) $value );
	}

	private function provider_details() {
		if ( ! function_exists( 'get_field' ) || ! function_exists( 'acf_get_field_groups' ) ) {
			return array(
				'value'  => __( 'No compatible ACF/SCF provider loaded', 'dynamic-elementor-acf-repeater' ),
				'status' => 'error',
			);
		}

		$basename = defined( 'ACF_BASENAME' ) ? (string) ACF_BASENAME : '';
		if ( false !== strpos( $basename, 'secure-custom-fields/' ) ) {
			$name = __( 'Secure Custom Fields', 'dynamic-elementor-acf-repeater' );
		} elseif ( false !== strpos( $basename, 'advanced-custom-fields-pro/' ) ) {
			$name = __( 'Advanced Custom Fields Pro', 'dynamic-elementor-acf-repeater' );
		} elseif ( function_exists( 'acf_get_setting' ) ) {
			$name = (string) acf_get_setting( 'name' );
		} else {
			$name = __( 'ACF-compatible field provider', 'dynamic-elementor-acf-repeater' );
		}

		$version = defined( 'ACF_VERSION' ) ? (string) ACF_VERSION : '';
		return array(
			'value'  => trim( $name . ( $version ? ' ' . $version : '' ) ),
			'status' => 'ok',
		);
	}

	private function source_details( $selector ) {
		$selector = (string) $selector;
		if ( '' === $selector ) {
			return array(
				'value'   => __( 'No row source selected', 'dynamic-elementor-acf-repeater' ),
				'status'  => 'warning',
				'path'    => array(),
				'layouts' => array(),
				'source'  => null,
			);
		}

		$options = $this->provider->get_acf_repeater_fields();
		$label   = isset( $options[ $selector ] ) ? (string) $options[ $selector ] : $selector;
		$type    = __( 'Repeater', 'dynamic-elementor-acf-repeater' );
		$path    = array();
		$layouts = array();
		$source  = null;
		$field   = null;

		if ( $this->premium && method_exists( $this->provider, 'get_row_source_registry' ) ) {
			$registry = $this->provider->get_row_source_registry();
			$source   = $registry->get_source( $selector );
			if ( $source ) {
				$type    = 'flexible_content' === $source['type'] ? __( 'Flexible Content', 'dynamic-elementor-acf-repeater' ) : __( 'Nested Repeater', 'dynamic-elementor-acf-repeater' );
				$path    = ! empty( $source['path'] ) ? array_column( $source['path'], 'key' ) : array();
				$layouts = ! empty( $source['layouts'] ) && is_array( $source['layouts'] ) ? $source['layouts'] : array();
			}
		}

		if ( ! $source ) {
			$field = $this->find_field( $selector );
			if ( $field ) {
				$label = isset( $field['label'] ) ? (string) $field['label'] : $label;
				$path  = ! empty( $field['_earluna_path'] ) ? $field['_earluna_path'] : array();
			}
		}

		return array(
			'value'   => sprintf( '%1$s · %2$s', $label, $type ),
			'status'  => isset( $options[ $selector ] ) || $source || $field ? 'ok' : 'warning',
			'path'    => $path,
			'layouts' => $layouts,
			'source'  => $source,
		);
	}

	private function find_field( $selector ) {
		if ( ! function_exists( 'acf_get_field_groups' ) || ! function_exists( 'acf_get_fields' ) ) {
			return null;
		}
		foreach ( (array) acf_get_field_groups() as $group ) {
			$found = $this->walk_fields_for_selector( (array) acf_get_fields( $group ), $selector, array(), '' );
			if ( $found ) {
				return $found;
			}
		}

		return null;
	}

	private function walk_fields_for_selector( array $fields, $selector, array $path, $legacy_parent ) {
		foreach ( $fields as $field ) {
			if ( ! is_array( $field ) ) {
				continue;
			}
			$key         = isset( $field['key'] ) ? sanitize_key( $field['key'] ) : '';
			$name        = isset( $field['name'] ) ? sanitize_key( $field['name'] ) : '';
			$current     = array_merge( $path, $key ? array( $key ) : array() );
			$legacy_name = $legacy_parent && $name ? $legacy_parent . '_' . $name : $name;
			if ( $selector === $key || $selector === $name || $selector === $legacy_name ) {
				$field['_earluna_path'] = $current;
				return $field;
			}

			$children = isset( $field['sub_fields'] ) && is_array( $field['sub_fields'] ) ? $field['sub_fields'] : array();
			$found    = $children ? $this->walk_fields_for_selector( $children, $selector, $current, $legacy_name ) : null;
			if ( $found ) {
				return $found;
			}

			foreach ( isset( $field['layouts'] ) && is_array( $field['layouts'] ) ? $field['layouts'] : array() as $layout ) {
				$layout_fields = isset( $layout['sub_fields'] ) && is_array( $layout['sub_fields'] ) ? $layout['sub_fields'] : array();
				$found         = $this->walk_fields_for_selector( $layout_fields, $selector, $current, $legacy_name );
				if ( $found ) {
					return $found;
				}
			}
		}

		return null;
	}

	private function premium_items( array $settings, array $state, array $source ) {
		$path  = ! empty( $source['path'] ) ? implode( ' → ', $source['path'] ) : __( 'Top-level or legacy name-based source', 'dynamic-elementor-acf-repeater' );
		$items = array(
			$this->item( __( 'Stable field path', 'dynamic-elementor-acf-repeater' ), $path, ! empty( $source['path'] ) ? 'ok' : 'muted' ),
			$this->item( __( 'Layout templates', 'dynamic-elementor-acf-repeater' ), $this->format_layout_mappings( $settings, $source ), empty( $source['layouts'] ) ? 'muted' : 'ok' ),
		);

		if ( method_exists( $this->provider, 'inspect_relationship_settings' ) ) {
			$relationship = $this->provider->inspect_relationship_settings( $settings );
			if ( $relationship ) {
				$relationship_path = ! empty( $relationship['path'] ) ? implode( ' → ', $relationship['path'] ) : __( 'Legacy selector', 'dynamic-elementor-acf-repeater' );
				$items[]           = $this->item( __( 'Relationship path', 'dynamic-elementor-acf-repeater' ), $relationship['label'] . ' · ' . $relationship_path, 'ok' );
				/* translators: %d: resolved Relationship/Post Object post count. */
				$items[] = $this->item( __( 'Relationship posts', 'dynamic-elementor-acf-repeater' ), sprintf( _n( '%d post resolved', '%d posts resolved', $relationship['count'], 'dynamic-elementor-acf-repeater' ), $relationship['count'] ), $relationship['count'] ? 'ok' : 'warning' );
			}
		}

		$search_count = isset( $settings['earluna_row_search_fields'] ) && is_array( $settings['earluna_row_search_fields'] ) ? count( array_filter( $settings['earluna_row_search_fields'] ) ) : 0;
		$sort_count   = isset( $settings['earluna_row_sort_options'] ) && is_array( $settings['earluna_row_sort_options'] ) ? count( $settings['earluna_row_sort_options'] ) : 0;
		$range_count  = isset( $settings['earluna_row_range_filters'] ) && is_array( $settings['earluna_row_range_filters'] ) ? count( $settings['earluna_row_range_filters'] ) : 0;
		$row_enabled  = ! empty( $settings['earluna_enable_row_query'] ) && 'yes' === $settings['earluna_enable_row_query'];
		if ( $row_enabled ) {
			/* translators: 1: searchable field count, 2: sort option count, 3: range filter count, 4: layout-filter state, 5: URL-state setting. */
			$row_value = sprintf( __( 'Enabled · search %1$d · sort %2$d · ranges %3$d · layout %4$s · URL %5$s', 'dynamic-elementor-acf-repeater' ), $search_count, $sort_count, $range_count, $this->on_off( ! empty( $settings['earluna_row_layout_filter'] ) && 'yes' === $settings['earluna_row_layout_filter'] ), $this->on_off( ! empty( $settings['earluna_row_query_url_state'] ) && 'yes' === $settings['earluna_row_query_url_state'] ) );
		} else {
			$row_value = __( 'Disabled', 'dynamic-elementor-acf-repeater' );
		}
		$items[] = $this->item( __( 'Row search and sorting', 'dynamic-elementor-acf-repeater' ), $row_value, $row_enabled ? 'ok' : 'muted' );

		$native_filter = ! empty( $settings['earluna_enable_elementor_filter'] ) && 'yes' === $settings['earluna_enable_elementor_filter'];
		$custom_filter = ! empty( $settings['earluna_enable_custom_filter'] ) && 'yes' === $settings['earluna_enable_custom_filter'];
		/* translators: 1: Elementor native filter state, 2: plugin filter state. */
		$filter_value = sprintf( __( 'Elementor native: %1$s · plugin filter: %2$s', 'dynamic-elementor-acf-repeater' ), $this->on_off( $native_filter ), $this->on_off( $custom_filter ) );
		$items[]      = $this->item( __( 'Filtering', 'dynamic-elementor-acf-repeater' ), $filter_value, $native_filter || $custom_filter ? 'ok' : 'muted' );

		$items[] = $this->item( __( 'Pagination', 'dynamic-elementor-acf-repeater' ), $this->format_pagination( $settings ), 'muted' );
		$items[] = $this->item(
			__( 'Public refresh security', 'dynamic-elementor-acf-repeater' ),
			$native_filter || $custom_filter || $row_enabled
				? __( 'Signed, expiring, and bound to this widget at render time', 'dynamic-elementor-acf-repeater' )
				: __( 'Not needed by the current settings', 'dynamic-elementor-acf-repeater' ),
			$native_filter || $custom_filter || $row_enabled ? 'ok' : 'muted'
		);

		return $items;
	}

	private function format_layout_mappings( array $settings, array $source ) {
		if ( empty( $source['layouts'] ) || ! is_array( $source['layouts'] ) || ! method_exists( $this->provider, 'get_row_source_registry' ) ) {
			return __( 'Not a Flexible Content source', 'dynamic-elementor-acf-repeater' );
		}

		$selector = isset( $settings['acf_repeater_field'] ) ? (string) $settings['acf_repeater_field'] : '';
		$registry = $this->provider->get_row_source_registry();
		$mapped   = array();
		foreach ( $source['layouts'] as $layout_name => $layout_label ) {
			$control_id  = $registry::mapping_control_id( $selector, $layout_name );
			$template_id = isset( $settings[ $control_id ] ) ? absint( $settings[ $control_id ] ) : 0;
			if ( ! $template_id ) {
				/* translators: %s: Flexible Content layout label. */
				$mapped[] = sprintf( __( '%s → unmapped', 'dynamic-elementor-acf-repeater' ), $layout_label );
				continue;
			}
			$template = get_post( $template_id );
			/* translators: %d: Elementor Loop template ID. */
			$title    = $template && ! empty( $template->post_title ) ? $template->post_title : sprintf( __( 'Template #%d', 'dynamic-elementor-acf-repeater' ), $template_id );
			$mapped[] = sprintf( '%1$s → %2$s', $layout_label, $title );
		}
		$behavior = isset( $settings['earluna_flexible_unmapped_behavior'] ) && 'skip' === $settings['earluna_flexible_unmapped_behavior']
			? __( 'skip unmapped rows', 'dynamic-elementor-acf-repeater' )
			: __( 'use the default Loop template', 'dynamic-elementor-acf-repeater' );

		return implode( '; ', $mapped ) . ' · ' . $behavior;
	}

	private function format_context( array $context ) {
		if ( empty( $context['type'] ) || 'none' === $context['type'] ) {
			return ! empty( $context['reason'] ) ? (string) $context['reason'] : __( 'Unresolved', 'dynamic-elementor-acf-repeater' );
		}
		$type_labels = array(
			'post'    => __( 'Post', 'dynamic-elementor-acf-repeater' ),
			'user'    => __( 'User', 'dynamic-elementor-acf-repeater' ),
			'term'    => __( 'Term', 'dynamic-elementor-acf-repeater' ),
			'options' => __( 'Options', 'dynamic-elementor-acf-repeater' ),
		);
		$type        = isset( $type_labels[ $context['type'] ] ) ? $type_labels[ $context['type'] ] : ucfirst( (string) $context['type'] );
		$label       = isset( $context['label'] ) ? (string) $context['label'] : (string) $context['acf_object_id'];
		$id          = isset( $context['acf_object_id'] ) ? (string) $context['acf_object_id'] : '';

		return sprintf( '%1$s · %2$s%3$s', $type, $label, $id ? ' (' . $id . ')' : '' );
	}

	private function format_row_count( array $state ) {
		if ( isset( $state['mode'] ) && 'all_posts' === $state['mode'] ) {
			return __( 'Calculated while Elementor runs the all-posts query', 'dynamic-elementor-acf-repeater' );
		}
		$count = isset( $state['row_count'] ) ? (int) $state['row_count'] : 0;

		/* translators: %d: resolved row count. */
		return sprintf( _n( '%d row', '%d rows', $count, 'dynamic-elementor-acf-repeater' ), $count );
	}

	private function row_count_status( array $state ) {
		if ( isset( $state['mode'] ) && 'all_posts' === $state['mode'] ) {
			return 'muted';
		}

		return ! empty( $state['row_count'] ) ? 'ok' : 'warning';
	}

	private function format_preview_row( array $state ) {
		if ( empty( $state['sample'] ) || ! is_array( $state['sample'] ) ) {
			return __( 'No representative row is available', 'dynamic-elementor-acf-repeater' );
		}
		$sample = $state['sample'];
		/* translators: %d: one-based preview row number. */
		$value = sprintf( __( 'Row %d', 'dynamic-elementor-acf-repeater' ), (int) $sample['index'] + 1 );
		if ( ! empty( $sample['layout'] ) ) {
			/* translators: %s: Flexible Content layout name. */
			$value .= ' · ' . sprintf( __( 'layout %s', 'dynamic-elementor-acf-repeater' ), $sample['layout'] );
		}
		if ( ! empty( $sample['schema_selector'] ) ) {
			/* translators: %s: internal row schema selector. */
			$value .= ' · ' . sprintf( __( 'schema %s', 'dynamic-elementor-acf-repeater' ), $sample['schema_selector'] );
		}

		return $value;
	}

	private function format_pagination( array $settings ) {
		$type     = isset( $settings['pagination_type'] ) ? (string) $settings['pagination_type'] : '';
		$labels   = array(
			''                      => __( 'None', 'dynamic-elementor-acf-repeater' ),
			'numbers'               => __( 'Numbers', 'dynamic-elementor-acf-repeater' ),
			'prev_next'             => __( 'Previous / Next', 'dynamic-elementor-acf-repeater' ),
			'numbers_and_prev_next' => __( 'Numbers + Previous / Next', 'dynamic-elementor-acf-repeater' ),
			'load_more_on_click'    => __( 'Load More', 'dynamic-elementor-acf-repeater' ),
			'infinite_scroll'       => __( 'Infinite Scroll', 'dynamic-elementor-acf-repeater' ),
		);
		$label    = isset( $labels[ $type ] ) ? $labels[ $type ] : $type;
		$per_page = isset( $settings['posts_per_page'] ) ? (int) $settings['posts_per_page'] : 0;

		/* translators: 1: Elementor pagination type, 2: rows per page. */
		return $per_page ? sprintf( __( '%1$s · %2$d rows per page', 'dynamic-elementor-acf-repeater' ), $label, $per_page ) : $label;
	}

	private function notices( array $settings, array $state, array $context, array $source ) {
		$notices = array();
		if ( empty( $settings['use_acf_repeater'] ) || 'yes' !== $settings['use_acf_repeater'] ) {
			$notices[] = $this->notice( 'warning', __( 'ACF Rows are disabled for this widget.', 'dynamic-elementor-acf-repeater' ) );
		}
		if ( 'error' === $source['status'] || 'warning' === $source['status'] ) {
			$notices[] = $this->notice( 'warning', __( 'Select a valid row source in the Query section.', 'dynamic-elementor-acf-repeater' ) );
		}
		if ( 'all_posts' !== $state['mode'] && ( empty( $context['type'] ) || 'none' === $context['type'] ) ) {
			$notices[] = $this->notice( 'error', ! empty( $context['reason'] ) ? $context['reason'] : __( 'The ACF object context could not be resolved.', 'dynamic-elementor-acf-repeater' ) );
		}
		if ( ! empty( $state['fallback'] ) ) {
			$notices[] = $this->notice( 'info', __( 'The selected automatic context had no rows, so the Options page fallback is active.', 'dynamic-elementor-acf-repeater' ) );
		} elseif ( isset( $state['row_count'] ) && 0 === $state['row_count'] && 'direct' === $state['mode'] ) {
			$notices[] = $this->notice( 'warning', __( 'The resolved object contains no rows for this source.', 'dynamic-elementor-acf-repeater' ) );
		}

		return $notices;
	}

	private function item( $label, $value, $status ) {
		return array(
			'label'  => (string) $label,
			'value'  => (string) $value,
			'status' => sanitize_key( $status ),
		);
	}

	private function notice( $level, $message ) {
		return array(
			'level'   => sanitize_key( $level ),
			'message' => (string) $message,
		);
	}

	private function on_off( $value ) {
		return $value ? __( 'on', 'dynamic-elementor-acf-repeater' ) : __( 'off', 'dynamic-elementor-acf-repeater' );
	}
}
