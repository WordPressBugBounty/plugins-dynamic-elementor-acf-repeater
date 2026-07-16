<?php

namespace DynamicElementorAcfRepeater;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class AdminSettingsNotices {

	public static function init() {
		add_action( 'admin_notices', array( self::class, 'display_admin_notices' ) );
		add_action( 'admin_enqueue_scripts', array( self::class, 'enqueue_plugin_update_notice_styles' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( DYNAMIC_ELEMENTOR_ACF_REPEATER_PLUGIN_FILE ), array( self::class, 'add_plugin_action_links' ) );
		add_action( 'admin_menu', array( self::class, 'add_menu_page' ), 20 );
		add_action( 'admin_menu', array( self::class, 'remove_main_menu_page' ), 99 );
	}

	public static function enqueue_plugin_update_notice_styles( $hook_suffix ) {
		if ( 'plugins.php' !== $hook_suffix ) {
			return;
		}

		wp_enqueue_style(
			'dynamic-elementor-acf-repeater-admin',
			plugins_url( 'assets/css/admin.css', DYNAMIC_ELEMENTOR_ACF_REPEATER_PLUGIN_FILE ),
			array(),
			DYNAMIC_ELEMENTOR_ACF_REPEATER_VERSION
		);
	}

	public static function display_admin_notices() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( ! class_exists( 'ACF' ) ) {
			self::admin_notice_acf_missing();
		}

		if ( ! did_action( 'elementor/loaded' ) ) {
			self::admin_notice_missing_main_plugin();
		}

		if ( did_action( 'elementor/loaded' ) && ! class_exists( '\\ElementorPro\\Plugin' ) ) {
			self::admin_notice_elementor_pro_missing();
		}

		if ( class_exists( 'ACF' ) && ! class_exists( 'acf_field_repeater' ) ) {
			self::admin_notice_acf_pro_missing();
		}

		if ( earluna_fs() instanceof \Earluna_Freemius_Unavailable ) {
			self::admin_notice_freemius_missing();
		}

		if ( defined( 'ELEMENTOR_VERSION' ) && version_compare( ELEMENTOR_VERSION, DYNAMIC_ELEMENTOR_ACF_REPEATER_MINIMUM_ELEMENTOR_VERSION, '<' ) ) {
			self::admin_notice_minimum_elementor_version();
		}

		if ( version_compare( PHP_VERSION, DYNAMIC_ELEMENTOR_ACF_REPEATER_MINIMUM_PHP_VERSION, '<' ) ) {
			self::admin_notice_minimum_php_version();
		}

		if ( function_exists( 'earluna_fs' ) && earluna_fs()->is_not_paying() && ! earluna_fs()->is_trial() ) {
			self::admin_notice_upgrade();
		}
	}

	public static function admin_notice_acf_missing() {
		/* translators: %s: Plugin name */
		$message = __( 'Dynamic Elementor ACF Repeater requires Secure Custom Fields or Advanced Custom Fields Pro to be installed and active.', 'dynamic-elementor-acf-repeater' );
		self::render_admin_notice( $message, 'error' );
	}

	public static function admin_notice_acf_pro_missing() {
		$message = __( 'Dynamic Elementor ACF Repeater is active, but repeater fields require Secure Custom Fields or Advanced Custom Fields Pro.', 'dynamic-elementor-acf-repeater' );
		self::render_admin_notice( $message, 'warning' );
	}

	public static function admin_notice_elementor_pro_missing() {
		$message = __( 'Dynamic Elementor ACF Repeater is active, but Loop Grid functionality requires Elementor Pro 3.8 or newer.', 'dynamic-elementor-acf-repeater' );
		self::render_admin_notice( $message, 'warning' );
	}

	public static function admin_notice_freemius_missing() {
		$message = __( 'Dynamic Elementor ACF Repeater could not load its licensing SDK. Premium features are disabled; reinstall the plugin package to restore the bundled SDK.', 'dynamic-elementor-acf-repeater' );
		self::render_admin_notice( $message, 'error' );
	}

	public static function admin_notice_missing_main_plugin() {
		if ( self::is_activation_request() ) {
			return;
		}

		$message = sprintf(
			/* translators: %1$s: Plugin name, %2$s: Required plugin name */
			esc_html__( '"%1$s" requires "%2$s" to be installed and activated.', 'dynamic-elementor-acf-repeater' ),
			'<strong>' . esc_html__( 'Dynamic Elementor ACF Repeater', 'dynamic-elementor-acf-repeater' ) . '</strong>',
			'<strong>' . esc_html__( 'Elementor', 'dynamic-elementor-acf-repeater' ) . '</strong>'
		);

		self::render_admin_notice( $message, 'warning' );
	}

	public static function admin_notice_minimum_elementor_version() {
		if ( self::is_activation_request() ) {
			return;
		}

		$message = sprintf(
			/* translators: %1$s: Plugin name, %2$s: Required plugin name, %3$s: Required version number */
			esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'dynamic-elementor-acf-repeater' ),
			'<strong>' . esc_html__( 'Dynamic Elementor ACF Repeater', 'dynamic-elementor-acf-repeater' ) . '</strong>',
			'<strong>' . esc_html__( 'Elementor', 'dynamic-elementor-acf-repeater' ) . '</strong>',
			DYNAMIC_ELEMENTOR_ACF_REPEATER_MINIMUM_ELEMENTOR_VERSION
		);

		self::render_admin_notice( $message, 'warning' );
	}

	public static function admin_notice_minimum_php_version() {
		if ( self::is_activation_request() ) {
			return;
		}

		$message = sprintf(
			/* translators: %1$s: Plugin name, %2$s: Required plugin name, %3$s: Required version number */
			esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'dynamic-elementor-acf-repeater' ),
			'<strong>' . esc_html__( 'Dynamic Elementor ACF Repeater', 'dynamic-elementor-acf-repeater' ) . '</strong>',
			'<strong>' . esc_html__( 'PHP', 'dynamic-elementor-acf-repeater' ) . '</strong>',
			DYNAMIC_ELEMENTOR_ACF_REPEATER_MINIMUM_PHP_VERSION
		);

		self::render_admin_notice( $message, 'warning' );
	}

	public static function admin_notice_upgrade() {
		$notice_count = get_option( 'earluna_upgrade_notice_count', 0 );
		$max_notices  = 3;

		if ( $notice_count >= $max_notices ) {
			return;
		}

		$message = sprintf(
			/* translators: %s: Upgrade Now button HTML */
			esc_html__( 'Upgrade to Dynamic Elementor ACF Repeater Pro for advanced features! %s', 'dynamic-elementor-acf-repeater' ),
			'<a href="' . esc_url( earluna_fs()->get_upgrade_url() ) . '" class="button button-primary">' . __( 'Upgrade Now', 'dynamic-elementor-acf-repeater' ) . '</a>'
		);

		self::render_admin_notice( $message, 'info' );

		update_option( 'earluna_upgrade_notice_count', $notice_count + 1 );
	}

	private static function render_admin_notice( $message, $type = 'info' ) {
		printf(
			'<div class="notice notice-%s is-dismissible"><p>%s</p></div>',
			esc_attr( $type ),
			wp_kses_post( $message )
		);
	}

	public static function add_plugin_action_links( $actions ) {
		if ( function_exists( 'earluna_fs' ) && ! earluna_fs()->can_use_premium_code__premium_only() ) {
			$style = 'font-weight: bold; color: #a19f00;';
			/**
			 * Translators: %1$s is the inline CSS style, %2$s is the upgrade URL, %3$s is the text for the "Go PRO" link.
			 */
			$actions['go-pro'] = sprintf(
				'<a style="%1$s" href="%2$s">%3$s</a>',
				esc_attr( $style ),
				esc_url( earluna_fs()->get_upgrade_url() ),
				esc_html__( 'Go PRO', 'dynamic-elementor-acf-repeater' )
			);
		}
		return $actions;
	}

	public static function add_menu_page() {
		// Add the top-level WP Luna menu
		add_menu_page(
			__( 'WP Luna', 'dynamic-elementor-acf-repeater' ),
			__( 'WP Luna', 'dynamic-elementor-acf-repeater' ),
			'manage_options',
			'wp-luna',
			array( self::class, 'render_settings_page' ),
			'dashicons-superhero',
			55
		);

		// Add our plugin as a submenu
		add_submenu_page(
			'wp-luna',  // Parent slug
			__( 'Dynamic Elementor ACF Repeater', 'dynamic-elementor-acf-repeater' ),
			__( 'Dynamic Elementor ACF Repeater', 'dynamic-elementor-acf-repeater' ),
			'manage_options',
			'dynamic-elementor-acf-repeater',
			array( self::class, 'render_settings_page' )
		);
	}

	public static function remove_main_menu_page() {
		// Remove the duplicate submenu item
		remove_submenu_page( 'wp-luna', 'wp-luna' );
	}

	public static function render_settings_page() {
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<?php
			// Include the getting started template
			$template_path = plugin_dir_path( __DIR__ ) . 'admin/partials/getting-started.php';
			if ( file_exists( $template_path ) ) {
				include_once $template_path;
			}
			?>
		</div>
		<?php
	}

	private static function is_activation_request() {
		if ( ! isset( $_GET['activate'], $_GET['_wpnonce'] ) ) {
			return false;
		}

		$nonce = sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) );
		return (bool) wp_verify_nonce( $nonce, 'activate-plugin_' . plugin_basename( DYNAMIC_ELEMENTOR_ACF_REPEATER_PLUGIN_FILE ) );
	}
}
