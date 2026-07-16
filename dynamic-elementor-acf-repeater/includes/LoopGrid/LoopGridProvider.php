<?php

namespace DynamicElementorAcfRepeater\LoopGrid;

use DynamicElementorAcfRepeater\Controls\LoopGridControlsBase;
use DynamicElementorAcfRepeater\MasterMind;
use DynamicElementorAcfRepeater\Support\VirtualRowContext;

class LoopGridProvider {
	protected static $instance = null;
	protected $mastermind;
	protected $controls;

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
		$this->mastermind = \DynamicElementorAcfRepeater\MasterMind::instance();
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

		$virtual_posts = array();

		// If no posts provided, create a dummy post for options page data
		if ( empty( $posts ) ) {
			$repeater_data = get_field( $repeater_field, 'options' );

			if ( $repeater_data && is_array( $repeater_data ) ) {

				// Create virtual posts from options page data
				foreach ( $repeater_data as $index => $row ) {
					$virtual_post              = new \stdClass();
					$virtual_post->ID          = VirtualRowContext::register( 'options', $index, $repeater_field );
					$virtual_post->post_parent = 0;
					$virtual_post->post_title  = 'Options - ' . $repeater_field . ' ' . ( $index + 1 );
					$virtual_post->post_status = 'publish';
					$virtual_post->post_type   = 'options';
					$virtual_post->filter      = 'raw';

					// Add our custom data
					$virtual_post->acf_repeater_data      = $row;
					$virtual_post->acf_repeater_source_id = 'options';
					$virtual_post->acf_repeater_field     = $repeater_field;
					$virtual_post->earluna_loop_index     = $index;

					$virtual_posts[] = $virtual_post;
				}

				return $this->prepare_virtual_posts_for_output( $virtual_posts, $query );
			}
		}

		foreach ( $posts as $post ) {
			$repeater_data = get_field( $repeater_field, $post->ID );

			// Fallback to global Options page if no data on the post itself
			if ( ! $repeater_data || ! is_array( $repeater_data ) ) {
				$repeater_data = get_field( $repeater_field, 'options' );
				// If still empty, skip this post
				if ( ! $repeater_data || ! is_array( $repeater_data ) ) {
					continue;
				}
			}

			foreach ( $repeater_data as $index => $row ) {
				$virtual_post              = new \stdClass();
				$virtual_post->ID          = VirtualRowContext::register( $post->ID, $index, $repeater_field );
				$virtual_post->post_parent = $post->ID;
				$virtual_post->post_title  = $post->post_title . ' - ' . $repeater_field . ' ' . ( $index + 1 );
				$virtual_post->post_status = 'publish';
				$virtual_post->post_type   = $post->post_type;
				$virtual_post->filter      = 'raw';

				// Add our custom data
				$virtual_post->acf_repeater_data      = $row;
				$virtual_post->acf_repeater_source_id = $post->ID;
				$virtual_post->acf_repeater_field     = $repeater_field;
				$virtual_post->earluna_loop_index     = $index;

				$virtual_posts[] = $virtual_post;
			}
		}

		return $this->prepare_virtual_posts_for_output( $virtual_posts, $query );
	}

	/**
	 * Premium taxonomy filters run after row expansion. Defer pagination in
	 * that case so matching rows on later unfiltered pages are not discarded.
	 */
	private function prepare_virtual_posts_for_output( $virtual_posts, $query ) {
		if ( ! empty( $query->get( 'earluna_filter_terms' ) ) && ! empty( $query->get( 'earluna_repeater_taxonomy_field' ) ) ) {
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

			// Add our virtual posts flags
			$query_args['earluna_virtual_posts'] = 1;
			$query_args['acf_repeater_field']    = $settings['acf_repeater_field'];
			$current_only                        = isset( $settings['query_current_post_only'] ) ? $settings['query_current_post_only'] : 'yes';

			// Store in query vars so other filters can read it later.
			$query_args['query_current_post_only'] = $current_only;

			// Only modify query if we want current post
			$current_id = get_the_ID();
			if ( ! empty( $query_args['post__not_in'] ) && is_array( $query_args['post__not_in'] ) ) {
					// Elementor excludes the current post by default in some Loop contexts.
					// Remove only that one exclusion, and only for this plugin-owned query.
					// phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in -- Existing Elementor exclusions are preserved except for the required source object.
					$query_args['post__not_in'] = array_values( array_diff( array_map( 'absint', $query_args['post__not_in'] ), array( $current_id ) ) );
			}

			if ( $current_only === 'yes' ) {
				$query_args['post__in'] = array( $current_id );

				// WordPress paginates source posts before the_posts runs. Preserve
				// Elementor's requested row page, then always fetch the one source
				// object so virtual rows can be paginated after expansion.
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
			$post    = $context && 'options' !== $context['source_id'] ? get_post( $context['source_id'] ) : null;
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
