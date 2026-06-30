<?php

/**
 * Plugin Name: Dynamic Elementor ACF Repeater
 * Description: Allows ACF repeater field values to be used in Elementor Loop Grids via Dynamic Tags.
 * Version: 1.0.91
 * Author:      WP Luna
 * Author URI:  https://wpluna.com
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: dynamic-elementor-acf-repeater
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Elementor tested up to: 3.32.2
 * Elementor Pro tested up to: 3.32.1
 * ACF PRO tested up to: 6.5.1
 * 
 *   */
if ( !defined( 'ABSPATH' ) ) {
    exit;
    // Exit if accessed directly.
}
use DynamicElementorAcfRepeater\MasterMind;
use DynamicElementorAcfRepeater\Controls\LightboxRepeaterVisibilityControl;
use DynamicElementorAcfRepeater\Controls\RepeaterFieldSelector;
use DynamicElementorAcfRepeater\AdminSettingsNotices;
if ( function_exists( 'earluna_fs' ) ) {
    earluna_fs()->set_basename( false, __FILE__ );
} else {
    // DO NOT REMOVE THIS IF, IT IS ESSENTIAL FOR THE `function_exists` CALL ABOVE TO PROPERLY WORK.
    if ( !function_exists( 'earluna_fs' ) ) {
        // Create a helper function for easy SDK access.
        function earluna_fs() {
            global $earluna_fs;
            if ( !isset( $earluna_fs ) ) {
                // Include Freemius SDK.
                require_once dirname( __FILE__ ) . '/vendor/freemius/start.php';
                $earluna_fs = fs_dynamic_init( array(
                    'id'             => '16334',
                    'slug'           => 'dynamic-elementor-acf-repeater',
                    'premium_slug'   => 'dynamic-elementor-acf-repeater-pro',
                    'type'           => 'plugin',
                    'public_key'     => 'pk_817f4334c0540a21ab45656b1a128',
                    'is_premium'     => false,
                    'premium_suffix' => 'PRO',
                    'has_addons'     => false,
                    'has_paid_plans' => true,
                    'trial'          => array(
                        'days'               => 3,
                        'is_require_payment' => true,
                    ),
                    'menu'           => array(
                        'slug'    => 'dynamic-elementor-acf-repeater',
                        'contact' => false,
                        'support' => false,
                        'parent'  => array(
                            'slug' => 'wp-luna',
                        ),
                    ),
                    'is_live'        => true,
                ) );
            }
            return $earluna_fs;
        }

        // Init Freemius.
        earluna_fs();
        // Signal that SDK was initiated.
        do_action( 'earluna_fs_loaded' );
    }
    define( 'DYNAMIC_ELEMENTOR_ACF_REPEATER_VERSION', '1.0.91' );
    define( 'DYNAMIC_ELEMENTOR_ACF_REPEATER_MINIMUM_ELEMENTOR_VERSION', '3.5.0' );
    define( 'DYNAMIC_ELEMENTOR_ACF_REPEATER_MINIMUM_PHP_VERSION', '7.4' );
    define( 'DYNAMIC_ELEMENTOR_ACF_REPEATER_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
    define( 'DYNAMIC_ELEMENTOR_ACF_REPEATER_PLUGIN_FILE', __FILE__ );
    require_once __DIR__ . '/includes/MasterMind.php';
    require_once __DIR__ . '/admin/AdminSettingsNotices.php';
    /**
     * Translators: %1$s is the feature text, %2$s is the plugin name, %3$s is the upgrade URL.
     */
    function earluna_get_upgrade_notice(  $feature_text = ''  ) {
        $notice = sprintf(
            /* translators: %1$s: feature text, %2$s: plugin name, %3$s: upgrade URL */
            __( '%1$s available in the PRO version of %2$s. <a target="_blank" href="%3$s">Upgrade Now!</a>', 'dynamic-elementor-acf-repeater' ),
            $feature_text,
            'Dynamic Elementor ACF Repeater',
            earluna_fs()->get_upgrade_url()
        );
        return '<div class="ear-pro-notice">' . $notice . '</div>';
    }

    class Dynamic_Elementor_ACF_Repeater {
        private static $_instance = null;

        private $mastermind;

        public static function instance() {
            if ( is_null( self::$_instance ) ) {
                self::$_instance = new self();
            }
            return self::$_instance;
        }

        function plugin_name() {
            return 'Dynamic Elementor ACF Repeater';
        }

        public function get_upgrade_notice() {
            return sprintf( 
                /* translators: %1$s: plugin name, %2$s: upgrade URL */
                __( 'available in the PRO version of %1$s. <a target="_blank" href="%2$s">Upgrade Now!</a>', 'dynamic-elementor-acf-repeater' ),
                $this->plugin_name(),
                earluna_fs()->get_upgrade_url()
             );
        }

        private function __construct() {
            add_action( 'plugins_loaded', [$this, 'init_plugin'] );
            $this->mastermind = MasterMind::instance();
            AdminSettingsNotices::init();
            add_action( 'wp_enqueue_scripts', [$this, 'enqueue_licensing_helper'] );
            add_action( 'admin_enqueue_scripts', [$this, 'enqueue_licensing_helper'] );
            add_action( 'elementor/editor/before_enqueue_scripts', [$this, 'enqueue_editor_scripts'] );
        }

        public function enqueue_licensing_helper() {
            // Register a minimal script handle for our licensing data
            wp_register_script( 'ear-licensing-helper', false );
            wp_enqueue_script( 'ear-licensing-helper' );
            wp_add_inline_script( 'ear-licensing-helper', '(function(){
                    window.earModule = window.earModule || {};
                    window.earModule.canUsePremiumCode = ' . wp_json_encode( earluna_fs()->can_use_premium_code() ) . ';
                })();' );
        }

        public function enqueue_editor_scripts() {
            $upgrade_url = earluna_fs()->get_upgrade_url();
            $acf_repeater_text = new \DynamicElementorAcfRepeater\DynamicTags\AcfRepeaterText();
            $pro_fields = $acf_repeater_text->get_pro_fields();
            $shared_data = array(
                'upgradeUrl'        => $upgrade_url,
                'premiumFields'     => $pro_fields,
                'canUsePremiumCode' => earluna_fs()->can_use_premium_code(),
                'pluginName'        => $this->plugin_name(),
            );
            wp_enqueue_script(
                'ear-editor-js',
                plugins_url( 'assets/js/editor.js', __FILE__ ),
                ['elementor-editor'],
                DYNAMIC_ELEMENTOR_ACF_REPEATER_VERSION,
                true
            );
            wp_localize_script( 'ear-editor-js', 'earSharedData', $shared_data );
            // Add the licensing helper inline
            $this->enqueue_licensing_helper();
        }

        public function init_plugin() {
            if ( $this->check_requirements() ) {
                add_action( 'wp_enqueue_scripts', [$this, 'enqueue_scripts'], 999 );
                add_action( 'elementor/frontend/after_enqueue_scripts', [$this, 'enqueue_scripts'], 999 );
                add_action( 'elementor/editor/after_enqueue_scripts', [$this, 'enqueue_scripts'], 999 );
                add_action( 'elementor_pro/init', [$this, 'init_elementor_dependent_features'], 20 );
                add_action( 'elementor/controls/register', [$this, 'init_controls'] );
            }
        }

        public function init_controls() {
            // Ensure dependencies are loaded first
            if ( !class_exists( '\\DynamicElementorAcfRepeater\\Controls\\LightboxRepeaterVisibilityControl' ) ) {
                require_once DYNAMIC_ELEMENTOR_ACF_REPEATER_PLUGIN_PATH . 'includes/Controls/LightboxRepeaterVisibilityControl.php';
            }
            new LightboxRepeaterVisibilityControl();
        }

        public function enqueue_scripts() {
            $elementor = ( class_exists( '\\Elementor\\Plugin' ) ? \Elementor\Plugin::instance() : null );
            $is_edit_mode = ( $elementor && isset( $elementor->editor ) && method_exists( $elementor->editor, 'is_edit_mode' ) ? $elementor->editor->is_edit_mode() : false );
            $is_preview_mode = ( $elementor && isset( $elementor->preview ) && method_exists( $elementor->preview, 'is_preview_mode' ) ? $elementor->preview->is_preview_mode() : false );
            // Shared
            wp_enqueue_style(
                'dynamic-elementor-acf-repeater',
                plugins_url( 'assets/css/elementor-acf-repeater.css', __FILE__ ),
                [],
                DYNAMIC_ELEMENTOR_ACF_REPEATER_VERSION
            );
            if ( $is_edit_mode || $is_preview_mode ) {
                wp_enqueue_script(
                    'ear-control-updater',
                    plugins_url( 'assets/js/control-updater.js', __FILE__ ),
                    ['elementor-editor', 'jquery'],
                    DYNAMIC_ELEMENTOR_ACF_REPEATER_VERSION,
                    true
                );
                wp_enqueue_script(
                    'ear-tag-change-detector',
                    plugins_url( 'assets/js/tag-change-detector.js', __FILE__ ),
                    ['jquery', 'elementor-editor'],
                    DYNAMIC_ELEMENTOR_ACF_REPEATER_VERSION,
                    true
                );
            }
            // Shared
            if ( $is_edit_mode || $is_preview_mode ) {
                $upgrade_url = earluna_fs()->get_upgrade_url();
                $acf_repeater_text = new \DynamicElementorAcfRepeater\DynamicTags\AcfRepeaterText();
                $pro_fields = $acf_repeater_text->get_pro_fields();
                $shared_data = array(
                    'upgradeUrl'    => $upgrade_url,
                    'premiumFields' => $pro_fields,
                    'pluginName'    => $this->plugin_name(),
                );
                wp_enqueue_script( 'ear-editor-js' );
                wp_localize_script( 'ear-editor-js', 'earSharedData', $shared_data );
            }
        }

        private function check_requirements() {
            if ( !class_exists( 'ACF' ) || !did_action( 'elementor/loaded' ) ) {
                return false;
            }
            $elementor = ( class_exists( '\\Elementor\\Plugin' ) ? \Elementor\Plugin::instance() : null );
            $elementor_version = ( $elementor && method_exists( $elementor, 'get_version' ) ? $elementor->get_version() : (( \defined( 'ELEMENTOR_VERSION' ) ? constant( 'ELEMENTOR_VERSION' ) : '0.0.0' )) );
            if ( !version_compare( $elementor_version, DYNAMIC_ELEMENTOR_ACF_REPEATER_MINIMUM_ELEMENTOR_VERSION, '>=' ) ) {
                return false;
            }
            if ( version_compare( PHP_VERSION, DYNAMIC_ELEMENTOR_ACF_REPEATER_MINIMUM_PHP_VERSION, '<' ) ) {
                return false;
            }
            return true;
        }

        public function init_elementor_dependent_features() {
            if ( did_action( 'elementor/loaded' ) ) {
                $this->mastermind->initialize();
                RepeaterFieldSelector::instance();
            }
        }

    }

    // Initialize the plugin
    Dynamic_Elementor_ACF_Repeater::instance();
}