<?php

/**
 * Plugin Name: Dynamic Elementor ACF Repeater
 * Description: Allows ACF repeater field values to be used in Elementor Loop Grids via Dynamic Tags.
 * Version: 1.2.0
 * Author:      WP Luna
 * Author URI:  https://wpluna.com
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: dynamic-elementor-acf-repeater
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Requires Plugins: elementor, advanced-custom-fields
 * Elementor tested up to: 4.1.5
 * Elementor Pro tested up to: 4.1.3
 * ACF PRO tested up to: 6.3.12
 *
 */
if ( !defined( 'ABSPATH' ) ) {
    exit;
    // Exit if accessed directly.
}
use DynamicElementorAcfRepeater\MasterMind;
use DynamicElementorAcfRepeater\Controls\LightboxRepeaterVisibilityControl;
use DynamicElementorAcfRepeater\Controls\RepeaterFieldSelector;
use DynamicElementorAcfRepeater\AdminSettingsNotices;
define( 'DYNAMIC_ELEMENTOR_ACF_REPEATER_VERSION', '1.2.0' );
define( 'DYNAMIC_ELEMENTOR_ACF_REPEATER_MINIMUM_ELEMENTOR_VERSION', '3.8.0' );
define( 'DYNAMIC_ELEMENTOR_ACF_REPEATER_MINIMUM_ELEMENTOR_PRO_VERSION', '3.8.0' );
define( 'DYNAMIC_ELEMENTOR_ACF_REPEATER_MINIMUM_PHP_VERSION', '7.4' );
define( 'DYNAMIC_ELEMENTOR_ACF_REPEATER_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'DYNAMIC_ELEMENTOR_ACF_REPEATER_PLUGIN_FILE', __FILE__ );
require_once __DIR__ . '/includes/Support/FreemiusUnavailable.php';
if ( !function_exists( 'earluna_fs' ) ) {
    function earluna_fs() {
        global $earluna_fs;
        if ( !isset( $earluna_fs ) ) {
            $sdk_file = __DIR__ . '/vendor/freemius/start.php';
            if ( !function_exists( 'fs_dynamic_init' ) && is_readable( $sdk_file ) ) {
                require_once $sdk_file;
            }
            if ( function_exists( 'fs_dynamic_init' ) ) {
                try {
                    $earluna_fs = fs_dynamic_init( array(
                        'id'               => '16334',
                        'slug'             => 'dynamic-elementor-acf-repeater',
                        'premium_slug'     => 'dynamic-elementor-acf-repeater-pro',
                        'type'             => 'plugin',
                        'public_key'       => 'pk_817f4334c0540a21ab45656b1a128',
                        'is_premium'       => false,
                        'premium_suffix'   => 'PRO',
                        'has_addons'       => false,
                        'has_paid_plans'   => true,
                        'trial'            => array(
                            'days'               => 3,
                            'is_require_payment' => true,
                        ),
                        'menu'             => array(
                            'slug'    => 'dynamic-elementor-acf-repeater',
                            'contact' => false,
                            'support' => false,
                            'parent'  => array(
                                'slug' => 'wp-luna',
                            ),
                        ),
                        'is_live'          => true,
                        'is_org_compliant' => true,
                    ) );
                } catch ( \Throwable $throwable ) {
                    $earluna_fs = null;
                }
            }
            if ( !is_object( $earluna_fs ) ) {
                $earluna_fs = new Earluna_Freemius_Unavailable();
            }
        }
        return $earluna_fs;
    }

}
if ( !function_exists( 'earluna_can_use_premium_code' ) ) {
    /**
     * Whether both the entitlement and the packaged premium source are present.
     *
     * Freemius removes premium directories from free builds. Checking the
     * artifact boundary prevents a migrated license or partial package from
     * turning a missing premium file into a fatal error.
     */
    function earluna_can_use_premium_code() {
        $required_paths = array(
            'includes/Controls/ProControls/LoopGridControlsBasePro.php',
            'includes/Controls/ProControls/LoopGridLightboxControls.php',
            'includes/LoopGrid/ProFeatures/LoopGridFilter.php',
            'includes/LoopGrid/ProFeatures/LoopGridProviderPro.php'
        );
        foreach ( $required_paths as $relative_path ) {
            if ( !is_readable( DYNAMIC_ELEMENTOR_ACF_REPEATER_PLUGIN_PATH . $relative_path ) ) {
                return false;
            }
        }
        return earluna_fs()->can_use_premium_code__premium_only();
    }

}
if ( !function_exists( 'earluna_uninstall_site_data' ) ) {
    /**
     * Remove settings and legacy metadata owned by this plugin on one site.
     */
    function earluna_uninstall_site_data() {
        global $wpdb;
        delete_option( 'earluna_upgrade_notice_count' );
        delete_post_meta_by_key( 'earluna_loop_repeater_field' );
        // Version 1.0.x wrote per-widget flags that were never consumed. The key
        // suffix is dynamic, so the core exact-key helper cannot remove these rows.
        // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Uninstall-only cleanup of plugin-owned wildcard metadata.
        $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE %s", $wpdb->esc_like( 'widget_has_acf_repeater_tag_' ) . '%' ) );
        // phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    }

}
if ( !function_exists( 'earluna_after_uninstall' ) ) {
    /**
     * Remove plugin-owned data after Freemius completes its uninstall event.
     */
    function earluna_after_uninstall() {
        if ( is_multisite() ) {
            $site_ids = get_sites( array(
                'fields' => 'ids',
                'number' => 0,
            ) );
            foreach ( $site_ids as $site_id ) {
                switch_to_blog( $site_id );
                earluna_uninstall_site_data();
                restore_current_blog();
            }
            return;
        }
        earluna_uninstall_site_data();
    }

}
earluna_fs();
earluna_fs()->set_basename( false, __FILE__ );
earluna_fs()->add_action( 'after_uninstall', 'earluna_after_uninstall' );
do_action( 'earluna_fs_loaded' );
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

    private $bootstrapped = false;

    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function plugin_name() {
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
        $this->mastermind = MasterMind::instance();
        AdminSettingsNotices::init();
        add_action( 'admin_enqueue_scripts', array($this, 'enqueue_licensing_helper') );
        // Some ACF-compatible plugins load after Elementor. Retry once all active
        // plugin files are available instead of permanently missing initialization.
        add_action( 'plugins_loaded', array($this, 'init_plugin'), 20 );
        $this->init_plugin();
    }

    public function enqueue_licensing_helper() {
        // Register a minimal script handle for our licensing data
        wp_register_script(
            'ear-licensing-helper',
            false,
            array(),
            DYNAMIC_ELEMENTOR_ACF_REPEATER_VERSION,
            true
        );
        wp_enqueue_script( 'ear-licensing-helper' );
        wp_add_inline_script( 'ear-licensing-helper', '(function(){
                    window.earModule = window.earModule || {};
                    window.earModule.canUsePremiumCode = ' . wp_json_encode( earluna_can_use_premium_code() ) . ';
                })();' );
    }

    public function enqueue_editor_scripts() {
        if ( !class_exists( '\\DynamicElementorAcfRepeater\\DynamicTags\\AcfRepeaterText' ) ) {
            return;
        }
        $upgrade_url = earluna_fs()->get_upgrade_url();
        $acf_repeater_text = new \DynamicElementorAcfRepeater\DynamicTags\AcfRepeaterText();
        $pro_fields = $acf_repeater_text->get_pro_fields();
        $shared_data = array(
            'upgradeUrl'        => $upgrade_url,
            'premiumFields'     => $pro_fields,
            'canUsePremiumCode' => earluna_can_use_premium_code(),
            'pluginName'        => $this->plugin_name(),
        );
        wp_enqueue_script(
            'ear-editor-js',
            plugins_url( 'assets/js/editor.js', __FILE__ ),
            array('elementor-editor'),
            DYNAMIC_ELEMENTOR_ACF_REPEATER_VERSION,
            true
        );
        wp_localize_script( 'ear-editor-js', 'earSharedData', $shared_data );
        // Add the licensing helper inline
        $this->enqueue_licensing_helper();
    }

    public function init_plugin() {
        if ( $this->bootstrapped ) {
            return;
        }
        if ( !did_action( 'elementor/loaded' ) ) {
            add_action( 'elementor/loaded', array($this, 'init_plugin'), 20 );
            return;
        }
        if ( $this->check_requirements() ) {
            $this->bootstrapped = true;
            add_action( 'wp_enqueue_scripts', array($this, 'register_assets'), 5 );
            add_action( 'elementor/editor/before_enqueue_scripts', array($this, 'enqueue_editor_assets') );
            add_action( 'elementor/preview/enqueue_styles', array($this, 'enqueue_preview_styles') );
            add_action( 'elementor/preview/enqueue_scripts', array($this, 'enqueue_preview_assets') );
            add_action( 'elementor/controls/register', array($this, 'init_controls') );
            if ( did_action( 'elementor_pro/init' ) ) {
                $this->init_elementor_dependent_features();
            } else {
                add_action( 'elementor_pro/init', array($this, 'init_elementor_dependent_features'), 20 );
            }
        }
    }

    public function init_controls() {
        // Ensure dependencies are loaded first
        if ( !class_exists( '\\DynamicElementorAcfRepeater\\Controls\\LightboxRepeaterVisibilityControl' ) ) {
            require_once DYNAMIC_ELEMENTOR_ACF_REPEATER_PLUGIN_PATH . 'includes/Controls/LightboxRepeaterVisibilityControl.php';
        }
        new LightboxRepeaterVisibilityControl();
    }

    public function register_assets() {
        wp_register_style(
            'dynamic-elementor-acf-repeater',
            plugins_url( 'assets/css/elementor-acf-repeater.css', __FILE__ ),
            array(),
            DYNAMIC_ELEMENTOR_ACF_REPEATER_VERSION
        );
        if ( earluna_can_use_premium_code() ) {
            wp_register_script(
                'ear-filter-updater',
                plugins_url( 'assets/js/pro/filter-updater.js', __FILE__ ),
                array('jquery'),
                DYNAMIC_ELEMENTOR_ACF_REPEATER_VERSION,
                true
            );
            wp_register_script(
                'ear-lightbox-provider',
                plugins_url( 'assets/js/pro/lightbox/lightbox-provider.js', __FILE__ ),
                array('jquery'),
                DYNAMIC_ELEMENTOR_ACF_REPEATER_VERSION,
                true
            );
            wp_register_script(
                'ear-virtual-lightbox',
                plugins_url( 'assets/js/pro/lightbox/virtual-lightbox.js', __FILE__ ),
                array('jquery', 'ear-lightbox-provider'),
                DYNAMIC_ELEMENTOR_ACF_REPEATER_VERSION,
                true
            );
        }
    }

    public function enqueue_editor_assets() {
        $this->register_assets();
        wp_enqueue_style( 'dynamic-elementor-acf-repeater' );
        wp_enqueue_script(
            'ear-control-updater',
            plugins_url( 'assets/js/control-updater.js', __FILE__ ),
            array('elementor-editor', 'jquery'),
            DYNAMIC_ELEMENTOR_ACF_REPEATER_VERSION,
            true
        );
        wp_enqueue_script(
            'ear-tag-change-detector',
            plugins_url( 'assets/js/tag-change-detector.js', __FILE__ ),
            array('jquery', 'elementor-editor'),
            DYNAMIC_ELEMENTOR_ACF_REPEATER_VERSION,
            true
        );
        $this->enqueue_editor_scripts();
    }

    /**
     * Load interactive widget assets inside Elementor's preview iframe.
     *
     * Editor widget renders can arrive over AJAX after the iframe has printed its
     * footer scripts. Enqueuing from render_content is therefore too late for the
     * editor even though it remains sufficient on normal frontend requests.
     */
    public function enqueue_preview_assets() {
        if ( !earluna_can_use_premium_code() ) {
            return;
        }
        $this->enqueue_lightbox_assets();
        $this->enqueue_filter_assets();
    }

    /**
     * Style editor-only diagnostics in both Free and Pro preview iframes.
     */
    public function enqueue_preview_styles() {
        $this->register_assets();
        wp_enqueue_style( 'dynamic-elementor-acf-repeater' );
    }

    public function enqueue_filter_assets() {
        $this->register_assets();
        wp_enqueue_style( 'dynamic-elementor-acf-repeater' );
        wp_enqueue_script( 'ear-filter-updater' );
        wp_localize_script( 'ear-filter-updater', 'earFilterConfig', array(
            'restUrl'       => esc_url_raw( rest_url( 'elementor-acf-repeater/v1/filter-loop-grid' ) ),
            'restNonce'     => ( is_user_logged_in() ? wp_create_nonce( 'wp_rest' ) : '' ),
            'requestFailed' => __( 'The filter could not be updated. Please try again.', 'dynamic-elementor-acf-repeater' ),
        ) );
    }

    public function enqueue_lightbox_assets() {
        $this->register_assets();
        wp_enqueue_style( 'dynamic-elementor-acf-repeater' );
        wp_enqueue_script( 'ear-lightbox-provider' );
        wp_enqueue_script( 'ear-virtual-lightbox' );
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
        if ( did_action( 'elementor/loaded' ) && class_exists( '\\ElementorPro\\Plugin' ) && defined( 'ELEMENTOR_PRO_VERSION' ) && version_compare( ELEMENTOR_PRO_VERSION, DYNAMIC_ELEMENTOR_ACF_REPEATER_MINIMUM_ELEMENTOR_PRO_VERSION, '>=' ) ) {
            $this->mastermind->initialize();
            RepeaterFieldSelector::instance();
        }
    }

}

// Initialize the plugin
Dynamic_Elementor_ACF_Repeater::instance();