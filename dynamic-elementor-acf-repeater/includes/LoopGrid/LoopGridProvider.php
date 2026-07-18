<?php

namespace DynamicElementorAcfRepeater\LoopGrid;

use DynamicElementorAcfRepeater\Controls\LoopGridControlsBase;
use DynamicElementorAcfRepeater\MasterMind;
use DynamicElementorAcfRepeater\Support\ContextResolver;
use DynamicElementorAcfRepeater\Support\VirtualRowContext;

class LoopGridProvider {
	protected static $instance = null;
	protected $mastermind;
	protected $controls;
	protected $context_resolver;

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			if ( earluna_can_use_premium_code() ) {
				require_once __DIR__ . '/ProFeatures/LoopGridProviderPro.php';
				self::$instance = new LoopGridProviderPro();
			} else {
				self::$instance = new self();
			}
		}
		return self::$instance;
	}

	protected function __construct() {
		$this->mastermind       = \DynamicElementorAcfRepeater\MasterMind::instance();
		$this->context_resolver = new ContextResolver();
		$this->init_controls();
		$this->register_controls();

		// Add filter for virtual post classes
		add_filter( 'post_class', array( $this, 'add_virtual_post_classes' ), 10, 3 );
	}

	protected function init_controls() {
		if ( ! earluna_can_use_premium_code() ) {
			$this->controls = new \DynamicElementorAcfRepeater\Controls\LoopGridControlsBase( $this->mastermind, $this );
		}
	}

	protected function register_controls() {
		if ( ! earluna_can_use_premium_code() ) {
			add_action( 'elementor/element/loop-grid/section_query/after_section_start', array( $this->controls, 'register_query_controls' ), 10, 2 );
			add_action( 'elementor/element/loop-grid/section_query/after_section_end', array( $this->controls, 'register_lightbox_section' ), 10, 2 );
		}
	}

	public function add_virtual_posts( $posts, $query ) {
		// Only run this filter for our specific post type
		if ( ! isset( $query->query_vars['earluna_virtual_posts'] ) || ! $query->query_vars['earluna_virtual_posts'] ) {
			return $posts;
		}

		$repeater_field = $query->get( 'acf_repeater_field' );
		if ( ! $repeater_field ) {
			return $posts;
		}

		if ( $query->get( 'earluna_context_direct' ) ) {
			$source_id     = $query->get( 'earluna_context_id' );
			$source_label  = $query->get( 'earluna_context_label' );
			$requested     = $query->get( 'earluna_context_requested' );
			$repeater_data = $source_id ? $this->resolve_row_source_rows( $repeater_field, $source_id ) : array();

			if ( empty( $repeater_data ) && 'auto' === $requested && 'options' !== $source_id ) {
				$source_id     = 'options';
				$source_label  = __( 'Options page (automatic fallback)', 'dynamic-elementor-acf-repeater' );
				$repeater_data = $this->resolve_row_source_rows( $repeater_field, $source_id );
			}

			if ( empty( $repeater_data ) ) {
				return array();
			}

			$source_post   = is_numeric( $source_id ) ? get_post( $source_id ) : null;
			$virtual_posts = $this->create_virtual_posts( $repeater_data, $source_id, $source_label, $repeater_field, $source_post );
			return $this->prepare_virtual_posts_for_output( $virtual_posts, $query );
		}

		$virtual_posts = array();

		// If no posts provided, create a dummy post for options page data
		if ( empty( $posts ) ) {
			$repeater_data = $this->resolve_row_source_rows( $repeater_field, 'options' );

			if ( ! empty( $repeater_data ) ) {
				$virtual_posts = $this->create_virtual_posts( $repeater_data, 'options', __( 'Options page', 'dynamic-elementor-acf-repeater' ), $repeater_field );

				return $this->prepare_virtual_posts_for_output( $virtual_posts, $query );
			}
		}

		foreach ( $posts as $post ) {
			$repeater_data = $this->resolve_row_source_rows( $repeater_field, $post->ID );

			// Fallback to global Options page if no data on the post itself
			if ( empty( $repeater_data ) ) {
				$repeater_data = $this->resolve_row_source_rows( $repeater_field, 'options' );
				// If still empty, skip this post
				if ( empty( $repeater_data ) ) {
					continue;
				}
			}

			$virtual_posts = array_merge(
				$virtual_posts,
				$this->create_virtual_posts( $repeater_data, $post->ID, $post->post_title, $repeater_field, $post )
			);
		}

		return $this->prepare_virtual_posts_for_output( $virtual_posts, $query );
	}

	/**
	 * Expand one ACF object into request-local virtual posts.
	 *
	 * @param array<int, mixed> $repeater_data Repeater rows.
	 * @param int|string        $source_id     ACF-compatible object ID.
	 * @param string            $source_label  Human-readable source label.
	 * @param string            $repeater_field Repeater field name or key.
	 * @param object|null       $source_post   Source post when the context is singular.
	 * @return array<int, object>
	 */
	protected function create_virtual_posts( $repeater_data, $source_id, $source_label, $repeater_field, $source_post = null ) {
		$virtual_posts = array();
		$source_label  = $source_label ? $source_label : (string) $source_id;

		foreach ( $repeater_data as $index => $resolved_row ) {
			$row                       = isset( $resolved_row['data'] ) && is_array( $resolved_row['data'] ) ? $resolved_row['data'] : array();
			$row_path                  = isset( $resolved_row['row_path'] ) && is_array( $resolved_row['row_path'] ) ? $resolved_row['row_path'] : array();
			$row_type                  = isset( $resolved_row['row_type'] ) ? $resolved_row['row_type'] : 'repeater';
			$layout                    = isset( $resolved_row['layout'] ) ? $resolved_row['layout'] : '';
			$schema_selector           = isset( $resolved_row['schema_selector'] ) ? $resolved_row['schema_selector'] : '';
			$virtual_post              = new \stdClass();
			$virtual_post->ID          = VirtualRowContext::register( $source_id, $index, $repeater_field, $source_label, $row_path, $row_type, $layout, $schema_selector );
			$virtual_post->post_parent = is_numeric( $source_id ) ? absint( $source_id ) : 0;
			$virtual_post->post_title  = $source_label . ' - ' . $repeater_field . ' ' . ( $index + 1 );
			$virtual_post->post_status = 'publish';
			$virtual_post->post_type   = $source_post && isset( $source_post->post_type ) ? $source_post->post_type : 'earluna-repeater-row';
			$virtual_post->filter      = 'raw';

			$virtual_post->acf_repeater_data         = $row;
			$virtual_post->acf_repeater_source_id    = $source_id;
			$virtual_post->acf_repeater_source_label = $source_label;
			$virtual_post->acf_repeater_field        = $repeater_field;
			$virtual_post->earluna_loop_index        = $index;
			$virtual_post->earluna_row_path          = $row_path;
			$virtual_post->earluna_row_type          = $row_type;
			$virtual_post->earluna_flexible_layout   = $layout;
			$virtual_post->earluna_schema_selector   = $schema_selector;

			$virtual_posts[] = $virtual_post;
		}

		return $virtual_posts;
	}

	/**
	 * Normalize a classic top-level Repeater into the shared row contract.
	 *
	 * Premium overrides this method for nested paths and Flexible Content.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	protected function resolve_row_source_rows( $repeater_field, $source_id ) {
		$repeater_data = get_field( $repeater_field, $source_id );
		if ( ! is_array( $repeater_data ) ) {
			return array();
		}

		$rows = array();
		foreach ( $repeater_data as $index => $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$rows[] = array(
				'data'            => $row,
				'row_path'        => array(
					array(
						'field'  => sanitize_key( $repeater_field ),
						'index'  => absint( $index ),
						'layout' => '',
					),
				),
				'row_type'        => 'repeater',
				'layout'          => '',
				'source_selector' => $repeater_field,
				'schema_selector' => $repeater_field,
			);
		}

		return $rows;
	}

	/**
	 * Resolve rows for a widget that uses one explicit/current ACF object.
	 *
	 * Native Elementor filter widgets render independently from their target
	 * Loop Grid. This read-only bridge lets the premium adapter discover which
	 * taxonomy terms occur in that grid's rows without exposing row internals.
	 *
	 * @param array<string, mixed> $settings Loop widget settings.
	 * @return array<int, array<string, mixed>>|null Rows, or null for all-posts mode.
	 */
	public function resolve_direct_rows_for_settings( $settings ) {
		$current_only = isset( $settings['query_current_post_only'] ) ? $settings['query_current_post_only'] : 'yes';
		$requested    = isset( $settings[ ContextResolver::SETTING_TYPE ] ) ? sanitize_key( $settings[ ContextResolver::SETTING_TYPE ] ) : 'auto';
		$direct       = 'yes' === $current_only || 'auto' !== $requested;

		if ( ! $direct || empty( $settings['acf_repeater_field'] ) ) {
			return null;
		}

		$context = $this->get_context_resolver()->resolve( $settings );
		$rows    = $this->resolve_row_source_rows( $settings['acf_repeater_field'], $context['acf_object_id'] );

		if ( empty( $rows ) && 'auto' === $requested && 'options' !== $context['acf_object_id'] ) {
			$rows = $this->resolve_row_source_rows( $settings['acf_repeater_field'], 'options' );
		}

		return $rows;
	}

	/**
	 * Premium taxonomy filters run after row expansion. Defer pagination in
	 * that case so matching rows on later unfiltered pages are not discarded.
	 */
	private function prepare_virtual_posts_for_output( $virtual_posts, $query ) {
		$has_custom_filter = ! empty( $query->get( 'earluna_filter_terms' ) ) && ! empty( $query->get( 'earluna_repeater_taxonomy_field' ) );
		$has_native_filter = ! empty( $query->get( 'earluna_elementor_tax_query' ) );

		if ( $has_custom_filter || $has_native_filter ) {
			return $virtual_posts;
		}

		return $this->paginate_virtual_posts( $virtual_posts, $query );
	}

	/**
	 * Apply Elementor's row-level pagination after a current-object repeater has
	 * been expanded into virtual posts.
	 *
	 * @param array     $virtual_posts Expanded repeater rows.
	 * @param \WP_Query $query         Plugin-owned source query.
	 * @return array
	 */
	public function paginate_virtual_posts( $virtual_posts, $query ) {
		$per_page = absint( $query->get( 'earluna_rows_per_page' ) );
		if ( ! $per_page ) {
			return $virtual_posts;
		}

		$page        = max( 1, absint( $query->get( 'earluna_rows_page' ) ) );
		$base_offset = absint( $query->get( 'earluna_rows_offset' ) );
		$total       = max( 0, count( $virtual_posts ) - $base_offset );
		$offset      = $base_offset + ( ( $page - 1 ) * $per_page );
		$page_rows   = array_slice( $virtual_posts, $offset, $per_page );

		$query->found_posts   = $total;
		$query->max_num_pages = (int) ceil( $total / $per_page );

		return $page_rows;
	}

	public function filter_elementor_query_args( $query_args, $widget ) {
		$settings = $widget->get_settings();

		if ( isset( $settings['use_acf_repeater'] ) && $settings['use_acf_repeater'] === 'yes' ) {
			$current_only = isset( $settings['query_current_post_only'] ) ? $settings['query_current_post_only'] : 'yes';
			$requested    = isset( $settings[ ContextResolver::SETTING_TYPE ] ) ? sanitize_key( $settings[ ContextResolver::SETTING_TYPE ] ) : 'auto';
			$context      = $this->get_context_resolver()->resolve( $settings );
			$direct       = 'yes' === $current_only || 'auto' !== $requested;

			$query_args['earluna_virtual_posts']     = 1;
			$query_args['acf_repeater_field']        = isset( $settings['acf_repeater_field'] ) ? $settings['acf_repeater_field'] : '';
			$query_args['query_current_post_only']   = $current_only;
			$query_args['earluna_context_requested'] = $requested;
			$query_args['earluna_context_id']        = $context['acf_object_id'];
			$query_args['earluna_context_label']     = $context['label'];
			$query_args['earluna_context_direct']    = $direct ? 1 : 0;

			if ( $direct ) {
				$source_post_id = 'post' === $context['type'] ? absint( $context['object_id'] ) : 0;

				if ( $source_post_id && ! empty( $query_args['post__not_in'] ) && is_array( $query_args['post__not_in'] ) ) {
					// Elementor excludes the current post by default in some Loop contexts.
					// Remove only the resolved source, and only for this plugin-owned query.
					// phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in -- Existing Elementor exclusions are preserved except for the required source object.
					$query_args['post__not_in'] = array_values( array_diff( array_map( 'absint', $query_args['post__not_in'] ), array( $source_post_id ) ) );
				}

				// Non-post ACF objects intentionally return no source posts. The
				// the_posts filter expands their repeater directly from the context ID.
				$query_args['post__in'] = array( $source_post_id ? $source_post_id : 0 );

				// WordPress paginates source posts before the_posts runs. Preserve
				// Elementor's requested row page, then fetch at most one source post so
				// virtual rows can be paginated after expansion.
				$query_args['earluna_rows_per_page'] = isset( $query_args['posts_per_page'] ) ? absint( $query_args['posts_per_page'] ) : 0;
				$query_args['earluna_rows_page']     = isset( $query_args['paged'] ) ? max( 1, absint( $query_args['paged'] ) ) : 1;
				$query_args['earluna_rows_offset']   = isset( $query_args['offset'] ) ? absint( $query_args['offset'] ) : 0;
				$query_args['posts_per_page']        = 1;
				$query_args['paged']                 = 1;
				$query_args['offset']                = 0;
				$query_args['ignore_sticky_posts']   = true;
			}
		}

		return $query_args;
	}

	private function get_context_resolver() {
		if ( ! $this->context_resolver ) {
			$this->context_resolver = new ContextResolver();
		}

		return $this->context_resolver;
	}

	public function get_acf_repeater_fields() {
		$fields = array();

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

	private function process_fields( $fields, &$result, $parent_name = '' ) {
		foreach ( $fields as $field ) {
			if ( $field['type'] === 'repeater' ) {
				$key            = $parent_name ? $parent_name . '_' . $field['name'] : $field['name'];
				$result[ $key ] = $field['label'];
			} elseif ( $field['type'] === 'group' && ! empty( $field['sub_fields'] ) ) {
				$this->process_fields( $field['sub_fields'], $result, $field['name'] );
			}
		}
	}

	public function get_original_post_title( $post_id ) {
		if ( $post_id < 0 ) {
			$context = VirtualRowContext::get( $post_id );
			if ( $context && ! is_numeric( $context['source_id'] ) ) {
				return isset( $context['source_label'] ) ? (string) $context['source_label'] : '';
			}
			$post = $context ? get_post( $context['source_id'] ) : null;
		} else {
			$post = get_post( $post_id );
		}

		if ( ! $post ) {
			return '';
		}

		return get_the_title( $post->ID );
	}

	public function add_virtual_post_classes( $classes, $class_name, $post_id ) {
		if ( is_numeric( $post_id ) && (int) $post_id < 0 ) {
			// Add standard WordPress classes
			$classes[] = 'post-' . abs( $post_id );
			$classes[] = 'type-earluna-repeater-field-post';
			$classes[] = 'status-publish';
			$classes[] = 'hentry';
		}
		return $classes;
	}
}
