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
		add_filter( 'elementor/widget/render_content', array( $this, 'inject_context_diagnostics' ), 9, 2 );
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
			$repeater_data = $source_id ? get_field( $repeater_field, $source_id ) : null;

			if ( ( ! is_array( $repeater_data ) || empty( $repeater_data ) ) && 'auto' === $requested && 'options' !== $source_id ) {
				$source_id     = 'options';
				$source_label  = __( 'Options page (automatic fallback)', 'dynamic-elementor-acf-repeater' );
				$repeater_data = get_field( $repeater_field, $source_id );
			}

			if ( ! is_array( $repeater_data ) || empty( $repeater_data ) ) {
				return array();
			}

			$source_post   = is_numeric( $source_id ) ? get_post( $source_id ) : null;
			$virtual_posts = $this->create_virtual_posts( $repeater_data, $source_id, $source_label, $repeater_field, $source_post );
			return $this->prepare_virtual_posts_for_output( $virtual_posts, $query );
		}

		$virtual_posts = array();

		// If no posts provided, create a dummy post for options page data
		if ( empty( $posts ) ) {
			$repeater_data = get_field( $repeater_field, 'options' );

			if ( $repeater_data && is_array( $repeater_data ) ) {
				$virtual_posts = $this->create_virtual_posts( $repeater_data, 'options', __( 'Options page', 'dynamic-elementor-acf-repeater' ), $repeater_field );

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
	private function create_virtual_posts( $repeater_data, $source_id, $source_label, $repeater_field, $source_post = null ) {
		$virtual_posts = array();
		$source_label  = $source_label ? $source_label : (string) $source_id;

		foreach ( $repeater_data as $index => $row ) {
			$virtual_post              = new \stdClass();
			$virtual_post->ID          = VirtualRowContext::register( $source_id, $index, $repeater_field, $source_label );
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

			$virtual_posts[] = $virtual_post;
		}

		return $virtual_posts;
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

	/**
	 * Return the normalized editor diagnostic for one Loop widget.
	 *
	 * @param array<string, mixed> $settings Elementor widget settings.
	 * @return array<string, int|string>
	 */
	public function get_context_diagnostics( $settings ) {
		$field        = isset( $settings['acf_repeater_field'] ) ? $settings['acf_repeater_field'] : '';
		$current_only = isset( $settings['query_current_post_only'] ) ? $settings['query_current_post_only'] : 'yes';
		$requested    = isset( $settings[ ContextResolver::SETTING_TYPE ] ) ? sanitize_key( $settings[ ContextResolver::SETTING_TYPE ] ) : 'auto';

		if ( 'auto' === $requested && 'yes' !== $current_only ) {
			$post_type = isset( $settings['post_query_post_type'] ) ? sanitize_key( $settings['post_query_post_type'] ) : 'post';
			return array(
				'requested'     => 'auto',
				'type'          => 'query',
				'acf_object_id' => 'query:' . $post_type,
				'object_id'     => 0,
				'label'         => __( 'All queried objects', 'dynamic-elementor-acf-repeater' ) . ' (' . $post_type . ')',
				'reason'        => __( 'Rows are aggregated from the widget query instead of one ACF object.', 'dynamic-elementor-acf-repeater' ),
				'field'         => (string) $field,
				'row_count'     => 0,
				'status'        => $field ? 'aggregate' : 'missing_field',
			);
		}

		return $this->get_context_resolver()->diagnose( $field, $settings );
	}

	/**
	 * Add a compact live resolver report above Loop widgets in Elementor preview.
	 */
	public function inject_context_diagnostics( $content, $widget ) {
		if ( ! $this->is_elementor_editor_preview() || ! in_array( $widget->get_name(), array( 'loop-grid', 'loop-carousel' ), true ) ) {
			return $content;
		}

		$settings = $widget->get_settings_for_display();
		if ( ! isset( $settings['use_acf_repeater'] ) || 'yes' !== $settings['use_acf_repeater'] ) {
			return $content;
		}

		$diagnostic = $this->get_context_diagnostics( $settings );
		$status     = isset( $diagnostic['status'] ) ? sanitize_key( $diagnostic['status'] ) : 'missing_context';
		if ( 'aggregate' === $status ) {
			$diagnostic['row_count'] = $this->count_rendered_loop_items( $content );
		}
		$status_text = in_array( $status, array( 'ready', 'aggregate' ), true )
			/* translators: %d: Number of resolved repeater rows. */
			? sprintf( __( '%d rows resolved', 'dynamic-elementor-acf-repeater' ), absint( $diagnostic['row_count'] ) )
			: ( 'empty' === $status ? __( 'No rows found', 'dynamic-elementor-acf-repeater' ) : __( 'Context incomplete', 'dynamic-elementor-acf-repeater' ) );

		$notice  = '<details class="ear-context-diagnostic ear-context-diagnostic--' . esc_attr( $status ) . '">';
		$notice .= '<summary aria-label="' . esc_attr__( 'Show ACF repeater context diagnostic', 'dynamic-elementor-acf-repeater' ) . '"><span class="ear-context-diagnostic__status">' . esc_html( $status_text ) . '</span></summary>';
		$notice .= '<div class="ear-context-diagnostic__details">';
		$notice .= '<span><strong>' . esc_html__( 'Source:', 'dynamic-elementor-acf-repeater' ) . '</strong> ' . esc_html( $diagnostic['label'] ) . ' <code>' . esc_html( (string) $diagnostic['acf_object_id'] ) . '</code></span>';
		$notice .= '<span><strong>' . esc_html__( 'Field:', 'dynamic-elementor-acf-repeater' ) . '</strong> <code>' . esc_html( (string) $diagnostic['field'] ) . '</code></span>';
		if ( 'ready' !== $status && ! empty( $diagnostic['reason'] ) ) {
			$notice .= '<span class="ear-context-diagnostic__reason">' . esc_html( $diagnostic['reason'] ) . '</span>';
		}
		$notice .= '</div></details>';

		return $notice . $content;
	}

	private function count_rendered_loop_items( $content ) {
		if ( class_exists( '\\WP_HTML_Tag_Processor' ) ) {
			$processor = new \WP_HTML_Tag_Processor( $content );
			$count     = 0;
			while ( $processor->next_tag( array( 'class_name' => 'e-loop-item' ) ) ) {
				++$count;
			}
			return $count;
		}

		return preg_match_all( '/class=(?:"[^"]*\\be-loop-item\\b[^"]*"|\'[^\']*\\be-loop-item\\b[^\']*\')/i', $content, $matches );
	}

	private function is_elementor_editor_preview() {
		if ( ! class_exists( '\\Elementor\\Plugin' ) ) {
			return false;
		}

		try {
			$elementor  = \Elementor\Plugin::instance();
			$is_editor  = isset( $elementor->editor ) && method_exists( $elementor->editor, 'is_edit_mode' ) && $elementor->editor->is_edit_mode();
			$is_preview = isset( $elementor->preview ) && method_exists( $elementor->preview, 'is_preview_mode' ) && $elementor->preview->is_preview_mode();
			return $is_editor || $is_preview;
		} catch ( \Throwable $throwable ) {
			return false;
		}
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
