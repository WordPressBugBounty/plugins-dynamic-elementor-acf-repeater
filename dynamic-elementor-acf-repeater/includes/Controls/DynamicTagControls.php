<?php
namespace DynamicElementorAcfRepeater\Controls;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use DynamicElementorAcfRepeater\MasterMind;
use DynamicElementorAcfRepeater\Data\RepeaterDataTrait;
use Elementor\Controls_Manager;

require_once DYNAMIC_ELEMENTOR_ACF_REPEATER_PLUGIN_PATH . 'includes/Data/RepeaterDataTrait.php';

class DynamicTagControls {
    use RepeaterDataTrait;
    
    private static $instance = null;
    private $mastermind;

    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->mastermind = MasterMind::instance();
    }


    public function register_tags($dynamic_tags) {
        try {
            if ($this->mastermind->is_in_widgets_context() || $this->mastermind->is_all_processing_disabled()) {
                return; // Silently return if in widgets context or all processing is disabled
            }
        
            $tag_classes = \DynamicElementorAcfRepeater\DynamicTags\RepeaterTagManager::get_tag_classes_names();
            
            foreach ($tag_classes as $class) {
                $full_class_name = 'DynamicElementorAcfRepeater\\DynamicTags\\' . $class;
                if (class_exists($full_class_name)) {
                    $tag = new $full_class_name();
                    if ($tag->get_name() !== 'acf-repeater-original-title') {
                        $this->register_controls($tag);
                    }
                    $dynamic_tags->register($tag);
                }
                // Silently continue if the class doesn't exist
            }
        } catch (\Exception $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                // For admin users only
                if (current_user_can('manage_options')) {
                    add_action('admin_notices', function() use ($e) {
                        printf(
                            '<div class="notice notice-error"><p>%s</p></div>',
                            esc_html(sprintf(
                                /* translators: %s: Error message */
                                __('Dynamic Elementor ACF Repeater: Error registering tags - %s', 'dynamic-elementor-acf-repeater'),
                                $e->getMessage()
                            ))
                        );
                    });
                }
            }
        }
    }
    
        /**
     * Registers the initial control structure for ACF Repeater dynamic tags.
     * 
     * This method is called during the initial registration of dynamic tags with Elementor.
     * It sets up the basic structure of the controls that will appear in the Elementor editor
     * for each ACF Repeater dynamic tag.
     * 
     * Note: This method works in conjunction with MasterMind::get_updated_dynamic_tag_controls().
     * While this method sets up the initial structure, get_updated_dynamic_tag_controls() 
     * provides dynamic updates to the control options based on user interactions.
     * 
     * @param \Elementor\Core\DynamicTags\Tag $tag The tag instance.
     * @param string|null $selected_repeater The initially selected repeater field, if any.
     */
    public function register_controls($tag, $selected_repeater = null) {
        try {
            if ($this->mastermind->is_in_widgets_context()) {
                return;
            }
            
            $supported_fields = method_exists($tag, 'get_supported_fields') ? $tag->get_supported_fields() : [];
            $control_options = $this->get_control_options($supported_fields, $selected_repeater, $tag);
            
            if (empty($control_options)) {
                return;
            }

            $tag->start_controls_section(
                'earluna_section',
                [
                    'label' => __('ACF Repeater', 'dynamic-elementor-acf-repeater'),
                ]
            );

            $tag->add_control(
                'repeater_field',
                [
                    'label'   => esc_html__('Repeater Field', 'dynamic-elementor-acf-repeater'),
                    'type'    => \Elementor\Controls_Manager::SELECT,
                    'groups'  => $control_options,
                    'classes' => 'ear-premium-fields',
                    'frontend_available' => true,
                ]
            );

            $tag->end_controls_section();
        } catch (\Exception $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                // For admin users only
                if (current_user_can('manage_options')) {
                    add_action('admin_notices', function() use ($e) {
                        printf(
                            '<div class="notice notice-error"><p>%s</p></div>',
                            esc_html(sprintf(
                                /* translators: %s: Error message */
                                __('Dynamic Elementor ACF Repeater: Error registering controls - %s', 'dynamic-elementor-acf-repeater'),
                                $e->getMessage()
                            ))
                        );
                    });
                }
            }
        }
    }

    public function get_control_options($supported_fields, $selected_repeater = null, $tag = null) {

        
        if ($this->mastermind->is_in_widgets_context()) {
            return [];
        }
        
        if (empty($selected_repeater)) {
            return [
                [
                    'label' => __('Select Repeater Field in Page Settings', 'dynamic-elementor-acf-repeater'),
                    'options' => ['' => __('No Repeater Field Selected', 'dynamic-elementor-acf-repeater')],
                ]
            ];
        }
        
        $repeater_fields = [];
		$field = acf_get_field($selected_repeater);
		$schema = null;
		if ( earluna_can_use_premium_code() && class_exists( '\\DynamicElementorAcfRepeater\\LoopGrid\\RowSourceRegistry' ) ) {
			$schema = \DynamicElementorAcfRepeater\LoopGrid\RowSourceRegistry::instance()->get_schema( $selected_repeater );
		}
		$sub_fields = $schema && isset( $schema['sub_fields'] ) ? $schema['sub_fields'] : ( $field && 'repeater' === $field['type'] ? $field['sub_fields'] : array() );
		$schema_label = $schema && isset( $schema['option_label'] ) ? $schema['option_label'] : ( $field && isset( $field['label'] ) ? $field['label'] : '' );

		if ( ! empty( $sub_fields ) ) {
            $options = [];
            $all_supported_fields = $supported_fields;

            if ($tag !== null && method_exists($tag, 'get_supported_fields')) {
                $all_supported_fields = $tag->get_supported_fields();
            }

			foreach ($sub_fields as $sub_field) {
                $label = $sub_field['label'];
                $type = $sub_field['type'];
                $key = $sub_field['key'];
                                
                // Always include 'url' type, regardless of the tag
                if (in_array($type, $all_supported_fields) || $type === 'url') {
                    if (earluna_can_use_premium_code() || in_array($type, ['text', 'textarea', 'image', 'url'])) {
                        $options[$key] = $label . ' | ' . $type;
                    } else {
                        $options[$key . '__pro'] = $label . ' (PRO)' . ' | ' . $type;
                    }
                } 
            }
            if (!empty($options)) {
                $repeater_fields[] = [
					'label' => $schema_label,
                    'options' => $options,
                ];
            }
        }
        
        return $repeater_fields;
    }
    
}
