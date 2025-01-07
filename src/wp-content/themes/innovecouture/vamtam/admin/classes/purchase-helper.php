<?php

/**
 * Purchase Helper
 *
 * @package vamtam/innovecouture
 */
/**
 * class VamtamPurchaseHelper
 */
class VamtamPurchaseHelper extends VamtamAjax {

	public static $storage_path;

	/**
	 * Hook ajax actions
	 */
	public function __construct() {
		parent::__construct();

		add_filter( 'admin_body_class', array( __CLASS__, 'vamtam_admin_body_class' ) );
		add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ), 9 );
		add_action( 'admin_menu', array( __CLASS__, 'admin_menu_1'), 11 );
		add_action( 'admin_menu', array( __CLASS__, 'admin_menu_2' ), 22 ); // after "Help"

		add_action( 'after_setup_theme', array( __CLASS__, 'after_setup_theme' ) );
		add_action( 'admin_init', array( __CLASS__, 'admin_init' ) );
		add_action( 'admin_init', array( __CLASS__, 'admin_early_init' ), 5 );
		add_action( 'admin_notices', array( __CLASS__, 'notice_early' ), 5 ); // after TGMPA registers its notices, but before printing
		add_action( 'admin_notices', array( __CLASS__, 'services_notice' ), 6 );

		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_scripts' ) );

		add_filter( 'tgmpa_update_bulk_plugins_complete_actions', array( __CLASS__, 'tgmpa_plugins_complete_actions' ), 10, 2 );
	}

	public static function vamtam_admin_body_class( $classes )
	{
		// Adds a class to the body tag to hint for pending verification.
		if ( ! Version_Checker::is_valid_purchase_code() ) {
			$classes .= ' vamtam-not-verified';
		}
		return $classes;
	}

	public static function notice_early() {
		$screen = get_current_screen();
		if ( ! self::is_theme_setup_page() && $screen->id !== 'plugins' ) {
			remove_action( 'admin_notices', array( $GLOBALS['tgmpa'], 'notices' ), 10 );
		}

		$valid_key = Version_Checker::is_valid_purchase_code();

		$is_updates_page = $screen->id === 'update-core';

		if ( ! $valid_key && ! $is_updates_page ) {
			VamtamFramework::license_register();
		}
	}

	private static function server_tests() {
		$timeout = (int) ini_get( 'max_execution_time' );
		$memory  = ini_get( 'memory_limit' );
		$memoryB = str_replace( array( 'G', 'M', 'K' ), array( '000000000', '000000', '000' ), $memory );

		$tests = array(
			array(
				'name'  => esc_html__( 'PHP Version', 'innovecouture' ),
				'test'  => version_compare( phpversion(), '5.5', '<' ),
				'value' => phpversion(),
				'desc'  => esc_html__( 'While this theme works with all PHP versions supported by WordPress Core, PHP versions 5.5 and older are no longer maintained by their developers. Consider switching your server to PHP 5.6 or newer.', 'innovecouture' ),
			),
			array(
				'name'  => esc_html__( 'PHP Time Limit', 'innovecouture' ),
				'test'  => $timeout > 0 && $timeout < 30,
				'value' => $timeout,
				'desc'  => esc_html__( 'The PHP time limit should be at least 30 seconds. Note that in some configurations your server (Apache/nginx) may have a separate time limit. Please consult with your hosting provider if you get a time out while importing the demo content.', 'innovecouture' ),
			),
			array(
				'name'  => esc_html__( 'PHP Memory Limit', 'innovecouture' ),
				'test'  => (int) $memory > 0 && $memoryB < 96 * 1024 * 1024,
				'value' => $memory,
				'desc'  => esc_html__( 'You need a minimum of 96MB memory to use the theme and the bundled plugins. For non-US English websites you need a minimum of 128MB in order to accomodate the translation features which are otherwise disabled.', 'innovecouture' ),
			),
			array(
				'name'  => esc_html__( 'PHP ZipArchive Extension', 'innovecouture' ),
				'test'  => ! class_exists( 'ZipArchive' ),
				'value' => '',
				'desc'  => esc_html__( 'ZipArchive is a requirement for importing the demo sliders.', 'innovecouture' ),
			),
		);

		$fail = 0;

		foreach ( $tests as $test ) {
			$fail += (int) $test['test'];
		}

		return array(
			'fail'  => $fail,
			'tests' => $tests,
		);
	}

	private static function is_theme_setup_page() {
		return isset( $_GET['page'] ) && in_array( $_GET['page'], array( 'vamtam_theme_setup' ) );
	}

	public static function admin_scripts() {
		$theme_version = VamtamFramework::get_version();

		wp_register_script( 'vamtam-check-license', VAMTAM_ADMIN_ASSETS_URI . 'js/check-license.js', array( 'jquery' ), $theme_version, true );
		wp_register_script( 'vamtam-import-buttons', VAMTAM_ADMIN_ASSETS_URI . 'js/import-buttons.js', array( 'jquery' ), $theme_version, true );
	}

	public static function tgmpa_plugins_complete_actions( $update_actions, $plugin_info ) {
		if ( isset( $update_actions['dashboard'] ) ) {
			$update_actions['dashboard'] = sprintf(
				esc_html__( 'All plugins installed and activated successfully. %1$s', 'innovecouture' ),
				'<a href="' . esc_url( admin_url( 'admin.php?page=vamtam_theme_setup_import_content' ) ) . '" class="button button-primary">' . esc_html__( 'Continue with theme setup.', 'innovecouture' ) . '</a>'
			);

			$update_actions['dashboard'] .= '
                <script>
                    window.scroll( 0, 10000000 );
                </script>
            ';
		}

		return $update_actions;
	}

	public static function admin_menu() {
		add_menu_page( esc_html__( 'VamTam', 'innovecouture' ), esc_html__( 'VamTam', 'innovecouture' ), 'edit_theme_options', 'vamtam_theme_setup', array( __CLASS__, 'page' ), '', 2 );
		add_submenu_page( 'vamtam_theme_setup', esc_html__( 'Dashboard', 'innovecouture' ), esc_html__( 'Dashboard', 'innovecouture' ), 'edit_theme_options', 'vamtam_theme_setup', array( __CLASS__, 'page' ) );
		remove_submenu_page('vamtam_theme_setup','vamtam_theme_setup');
		add_submenu_page( 'vamtam_theme_setup', esc_html__( 'Dashboard', 'innovecouture' ), esc_html__( 'Dashboard', 'innovecouture' ), 'edit_theme_options', 'vamtam_theme_setup', array( __CLASS__, 'page' ) );
	}

	public static function admin_menu_1() {
		//Called with a lower priority so 'Installed Plugins' menu item has been registered (tgmpa).
		add_submenu_page( 'vamtam_theme_setup', esc_html__( 'Import Demo Content', 'innovecouture' ), esc_html__( 'Import Demo Content', 'innovecouture' ), 'edit_theme_options', 'vamtam_theme_setup_import_content', array( __CLASS__, 'vamtam_theme_setup_import_content' ) );
	}

	public static function admin_menu_2() {
		add_submenu_page(
			'vamtam_theme_setup',
			esc_html__( 'Services', 'innovecouture' ),
			esc_html__( 'Services', 'innovecouture' ) .
			'<span id="vamtam-premium-services">' .
			'<?xml version="1.0" encoding="UTF-8"?>
			<svg viewBox="0 0 576 512" xmlns="http://www.w3.org/2000/svg">
			<path d="m309 106c11.4-7 19-19.7 19-34 0-22.1-17.9-40-40-40s-40 17.9-40 40c0 14.4 7.6 27 19 34l-57.3 114.6c-9.1 18.2-32.7 23.4-48.6 10.7l-89.1-71.3c5-6.7 8-15 8-24 0-22.1-17.9-40-40-40s-40 17.9-40 40 17.9 40 40 40h0.7l45.7 251.4c5.5 30.4 32 52.6 63 52.6h277.2c30.9 0 57.4-22.1 63-52.6l45.7-251.4h0.7c22.1 0 40-17.9 40-40s-17.9-40-40-40-40 17.9-40 40c0 9 3 17.3 8 24l-89.1 71.3c-15.9 12.7-39.5 7.5-48.6-10.7l-57.3-114.6z"/>
			</svg>' . __( 'Premium', 'innovecouture' ) .
			'</span>',
			'edit_theme_options',
			'vamtam_theme_services',
			array( __CLASS__, 'services_menu_item' )
		);
	}

	public static function services_menu_item() {
		wp_redirect( 'https://vamtam.com/services/' );
		exit;
	}

	public static function services_notice() {
		if ( get_transient( 'vamtam_dismissed_services_notice' ) ) {
			return;
		}

		$is_updates_page = get_current_screen()->id === 'update-core';

		if ( ! $is_updates_page ) {
			return;
		}

		?>
		<div class="vamtam-ts-notice">
				<div class="vamtam-services-notice notice cta is-dismissible">
					<div class="vamtam-notice-aside">
						<div class="vamtam-notice-icon-wrapper">
							<img id="vamtam-logo" src="<?php echo esc_attr( VAMTAM_ADMIN_ASSETS_URI . 'images/vamtam-logo.png' ); ?>"></img>
						</div>
					</div>
					<div class="vamtam-notice-content">
						<h3><?php echo __( 'Make updates easy with our Premium Service', 'innovecouture' ); ?></h3>
						<p><?php echo __( 'Enjoy hassle-free updates with our Premium Services. Keep your software up-to-date effortlessly.', 'innovecouture' ); ?></p>
						<p>
							<a class="button btn-cta" target="_blank" href="https://vamtam.com/services/">Get Premium Service</a>
						</p>
					</div>
				</div>
			</div>
		<?php
	}

	public static function registration_warning() {
		?>
		<div class="vamtam-notice-wrap">
			<div class="vamtam-notice">
				<p>
					<?php echo esc_html__( 'Please activate your license to get theme updates, premium support, and access to demo content.', 'innovecouture' ); ?>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=vamtam_theme_setup' ) ); ?>">
						<?php echo esc_html__( 'Register Now', 'innovecouture' ); ?>
					</a>
				</p>
			</div>
		</div>
		<?php
	}

	public static function vamtam_theme_setup_import_content() {
		wp_enqueue_script( 'vamtam-check-license' );
		$valid_key = Version_Checker::is_valid_purchase_code();
		?>
		<div id="vamtam-ts-import-content" class="vamtam-ts">
			<div id="vamtam-ts-side">
				<?php self::dashboard_navigation(); ?>
			</div>
			<div id="vamtam-ts-main">
				<?php if ( $valid_key ) : ?>
					<?php self::import_buttons() ?>
				<?php else : ?>
					<?php self::registration_warning(); ?>
				<?php endif ?>
			</div>
		</div>
		<?php
	}

	public static function after_setup_theme() {
		if ( self::is_theme_setup_page() ) {
			add_filter( 'heartbeat_settings', [ __CLASS__, 'heartbeat_settings' ] );
		}

		add_filter( 'fs_redirect_on_activation_ajax-search-for-woocommerce', '__return_false', 100 );
	}

	public static function admin_early_init() {
		add_filter( 'woocommerce_enable_setup_wizard', '__return_false' );
		add_filter( 'woocommerce_prevent_automatic_wizard_redirect', '__return_true' );

		if ( class_exists( 'Elementor\Plugin' ) ) {
			remove_action( 'admin_init', [ Elementor\Plugin::instance()->admin, 'maybe_redirect_to_getting_started' ] );
		}

		if ( get_transient( '_fp_activation_redirect' ) ) {
			delete_transient( '_fp_activation_redirect' );
		}

		if ( get_transient( '_booked_welcome_screen_activation_redirect' ) ) {
			delete_transient( '_booked_welcome_screen_activation_redirect' );
		}

		if ( get_option( 'sbi_plugin_do_activation_redirect', false ) ) {
			remove_action( 'admin_init', 'sbi_activation_plugin_redirect' );
			delete_option( 'sbi_plugin_do_activation_redirect' );
		}

		add_filter( 'fs_redirect_on_activation_ajax-search-for-woocommerce', '__return_false', 100 );
	}

	public static function admin_init() {
		$purchase_code_option_id = VamtamFramework::get_purchase_code_option_key();

		add_settings_section(
			'vamtam_purchase_settings_section',
			'',
			array( __CLASS__, 'settings_section' ),
			'vamtam_theme_setup'
		);
		add_settings_field(
			$purchase_code_option_id,
			esc_html__( 'Enter your purchase code from ThemeForest to receive theme updates and support.', 'innovecouture' ),
			array( __CLASS__, 'purchase_key' ),
			'vamtam_theme_setup',
			'vamtam_purchase_settings_section',
			array(
				$purchase_code_option_id,
			)
		);

		register_setting(
			'vamtam_theme_setup',
			$purchase_code_option_id,
			array( __CLASS__, 'sanitize_license_key' )
		);
	}

	public static function sanitize_license_key( $value ) {
		return preg_replace( '/[^-\w\d]/', '', $value );
	}

	public static function settings_section() {
	}

	public static function heartbeat_settings( $settings ) {
		$settings['interval'] = 15;
		return $settings;
	}

	public static function page() {
		wp_enqueue_script( 'vamtam-check-license' );

		$status = self::server_tests();
		$theme_name = ucfirst( wp_get_theme()->get_template() );
		$theme_version = VamtamFramework::get_version();
		$valid_key = Version_Checker::is_valid_purchase_code();

		?>
		<h2></h2>

		<div id="vamtam-ts-homepage" class="vamtam-ts">
			<div id="vamtam-ts-side">
				<?php self::dashboard_navigation(); ?>
			</div>
			<div id="vamtam-ts-main">
				<?php do_action( 'vamtam_theme_setup_notices' ); ?>
				<div id="vamtam-ts-dash-register">
					<div id="vamtam-ts-register-product">
						<?php
							if ( defined( 'ENVATO_HOSTED_SITE' ) ) :
								esc_html_e( 'All done.', 'innovecouture' );
							else :
						?>
							<form id="vamtam-register-form" method="post" action="options.php" autocomplete="off">
								<?php if ( $valid_key ) : ?>
									<div id="vamtam-verified-code">
										<p>
											<?php  esc_html_e( 'Thanks for the verification!', 'innovecouture' ) ?>
											<br />
											<?php echo esc_html( sprintf( __( 'You can now enjoy %s and build great websites.', 'innovecouture' ) , $theme_name ) ); ?>
										</p>
									</div>
								<?php else : ?>
									<svg id="vamtam-envato-logo" viewBox="0 0 178 34">
										<path d="M45.64 6.939c-7.58 0-13.08 5.64-13.08 13.4 0 7.76 5.44 13.29 13.34 13.29a12.75 12.75 0 0 0 9.61-3.79 2.81 2.81 0 0 0 .83-1.83 2.14 2.14 0 0 0-2.24-2.19 2.59 2.59 0 0 0-1.83.83 8.75 8.75 0 0 1-6.37 2.67 7.9 7.9 0 0 1-8-7.38H55c1.86 0 2.77-.87 2.77-2.66a9.61 9.61 0 0 0-.11-1.66c-.92-6.71-5.42-10.68-12.02-10.68zm0 4.16c4.11 0 6.75 2.62 6.91 6.84H37.89a7.64 7.64 0 0 1 7.75-6.84zm28.48-4.16a9.3 9.3 0 0 0-8.19 4.73v-1.66c0-2.63-2-2.76-2.45-2.76a2.44 2.44 0 0 0-2.48 2.76v20.49a2.62 2.62 0 1 0 5.22 0v-11c0-4.78 2.71-8.13 6.59-8.13s5.6 2.47 5.6 7.55v11.58a2.62 2.62 0 1 0 5.21 0v-13.23c-.02-4.99-2.51-10.33-9.5-10.33zm33.08.27a2.72 2.72 0 0 0-2.6 2.08l-7.14 17.94-7.08-17.94a2.76 2.76 0 0 0-2.65-2.08 2.56 2.56 0 0 0-2.61 2.5 3.56 3.56 0 0 0 .33 1.47l8.2 19.36c1 2.34 2.58 2.83 3.76 2.83 1.18 0 2.78-.49 3.76-2.82l8.26-19.47a3.86 3.86 0 0 0 .32-1.43 2.44 2.44 0 0 0-2.55-2.44zm15.16-.27a14.9 14.9 0 0 0-8.74 2.61 2.39 2.39 0 0 0-1.17 2.06 2 2 0 0 0 2 2.08 2.84 2.84 0 0 0 1.55-.55 10.25 10.25 0 0 1 5.86-1.94c3.85 0 6.06 2 6.06 5.38v.57c-8.65 0-17.45 1.06-17.45 8.59 0 5.42 4.63 7.84 9.22 7.84a9.72 9.72 0 0 0 8.44-4.19v1.32a2.4 2.4 0 0 0 2.45 2.66 2.35 2.35 0 0 0 2.42-2.66v-13.81c0-6.24-4-9.96-10.64-9.96zm4.5 14.07H128v1.2c0 4.4-2.79 7.23-7.12 7.23-1.18 0-5-.27-5-3.79-.06-4.18 6.24-4.64 10.98-4.64zm19.68-9.07a2.11 2.11 0 0 0 2.4-2.13 2.13 2.13 0 0 0-2.4-2.18h-4.69v-4.75a2.6 2.6 0 1 0-5.17 0v22.54c0 5.2 2.57 8 7.42 8a8.2 8.2 0 0 0 3.29-.6 2.34 2.34 0 0 0 1.44-2.06 2 2 0 0 0-2.08-2.08 3.92 3.92 0 0 0-.93.16 4.34 4.34 0 0 1-1.08.16c-2 0-2.89-1.29-2.89-4.06v-13h4.69zm17.24-5c-7.88 0-13.61 5.59-13.61 13.29a13.29 13.29 0 0 0 3.91 9.63 13.75 13.75 0 0 0 9.7 3.77c7.79 0 13.67-5.76 13.67-13.4 0-7.64-5.75-13.29-13.67-13.29zm0 22.27c-5.41 0-8.23-4.51-8.23-9 0-6.13 4.27-8.92 8.23-8.92 3.96 0 8.22 2.81 8.22 8.94 0 6.13-4.25 8.98-8.22 8.98z" fill="#141614"></path><path d="M23.64 4.569C19.45-.341 5.89 9.169 6 21.429a.58.58 0 0 1-.57.57.58.58 0 0 1-.49-.28 13.13 13.13 0 0 1-.52-9.65.53.53 0 0 0-.9-.52A13 13 0 0 0 0 20.439a13 13 0 0 0 13.15 13.15c18.5-.42 14.23-24.64 10.49-29.02z" fill="#80B341"></path>
									</svg>
								<?php endif ?>
							<?php
								settings_fields( 'vamtam_theme_setup' );
								do_settings_sections( 'vamtam_theme_setup' );
							?>
							</form>
						<?php endif; ?>
					</div>
				</div>
				<div id="vamtam-check-license-disclaimer">
					<h5><?php esc_html_e( 'Licensing Terms', 'innovecouture' ); ?></h5>
					<p>
						<?php esc_html_e( 'You need to register a separate license for each domain on which you will use the theme. A single license is limited to a single domain/application. For more information, please refer to these articles - ', 'innovecouture' ); ?>
						<a href="http://themeforest.net/licenses" target="_blank">
							<?php esc_html_e( 'Licensing Terms Envato Market', 'innovecouture' ); ?>
						</a>,
						<a href="https://elements.envato.com/license-terms" target="_blank">
							<?php esc_html_e( 'Licensing Terms Envato Elements', 'innovecouture' ); ?>
						</a>
						.
					</p>
				</div>
				<?php if ( current_user_can( 'switch_themes' ) ) : ?>
					<?php if ( ! defined( 'ENVATO_HOSTED_SITE' ) ) : ?>
						<div id="vamtam-server-tests">
							<h3>
								<?php if ( $status['fail'] > 0 ) : ?>
									<?php esc_html_e( 'System Status', 'innovecouture' ) ?>
									<?php $fail = $status['fail']; ?>
									<small><?php printf( esc_html( _n( '(%d potential issue)', '(%d potential issues)', $fail, 'innovecouture' ) ), $fail ) ?></small>
								<?php endif ?>
							</h3>
						</div>
					<?php endif ?>
				<?php endif ?>
			</div>
		</div>
		<?php
	}

	public static function dashboard_navigation() {
		$theme_name       = str_replace( 'VAMTAM-', '', strtoupper( wp_get_theme()->get_template() ) );
		$theme_version    = VamtamFramework::get_version();
		$valid_key        = Version_Checker::is_valid_purchase_code();
		$plugin_status    = VamtamPluginManager::get_required_plugins_status();
		$content_imported = ! ! get_option( 'vamtam_last_import_map', false );

		$routes = [
			'vamtam_theme_setup',
			'tgmpa-install-plugins',
			'vamtam_theme_setup_import_content',
			'vamtam_theme_help',
		];

		$cur_route = get_current_screen()->id;
		?>
		<nav id="vamtam-ts-nav-menu">
			<div id="vamtam-theme-title">
				<span id="vamtam-ts-greeter"><?php esc_html_e( 'WELCOME TO', 'innovecouture' ); ?></span>
				<span id="vamtam-ts-greeter-title"><?php echo esc_html( $theme_name ); ?></span>
				<span id="vamtam-ts-greeter-ver"><?php echo sprintf( esc_html__( 'VER. %s', 'innovecouture' ), $theme_version ); ?></span>
			</div>
			<ul>
				<li class="<?php echo esc_attr( $cur_route === 'toplevel_page_' . $routes[0] ? 'is-active' : '' ); ?>" >
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $routes[0] ) ); ?>">
						<svg class="ts-icon" xmlns="http://www.w3.org/2000/svg" width="21" height="30" viewBox="0 0 21 30"><path fill-rule="evenodd" d="M2.4 11.3l-.1.1V6.5C2.5 2.7 5.5 0 9.6 0h.2c2.3 0 5 0 7.1 2.1 2.3 2.2 2 5.8 1.9 8.8v.6c-.8-.8-1.6-1.4-2.5-2V6.8l-.1-.1a3.2 3.2 0 0 0 0-.3L16 6v-.2l-.1-.3v-.2h-.1V5a4.3 4.3 0 0 0-.3-.5 1.7 1.7 0 0 0-.2-.3.7.7 0 0 0-.1-.1l-.2-.2c-1.4-1.4-2.7-1.4-5.3-1.4h-.2C6.9 2.5 5 4 4.9 6.5v3.1l-.6.3-.1.1-1 .6-.1.2H3l-.6.5zM10.5 30A10.5 10.5 0 0 1 0 19.9a9 9 0 0 1 2.5-6.4 11.4 11.4 0 0 1 8.3-3.7c1.3 0 2.6.3 3.9.8A10.5 10.5 0 0 1 21 20c.1 5.3-4.7 10-10.5 10.1zm0-12.3c-.9 0-1.6.7-1.6 1.6 0 .5.3 1 .8 1.3v1.9h1.6v-1.9c.5-.2.9-.8.9-1.3 0-1-.8-1.6-1.7-1.6z"/></svg>
						<span><?php echo esc_html__( 'Register' , 'innovecouture' ); ?></span>
						<span class="vamtam-step-status <?php echo esc_attr( $valid_key ? 'success' : 'error' ); ?>"></span>
					</a>
				</li>
				<?php $tgmpa_instance 	= call_user_func( array( get_class( $GLOBALS['tgmpa'] ), 'get_instance' ) ); ?>
				<?php if ( isset( $tgmpa_instance ) && isset( $tgmpa_instance->page_hook ) ) : ?>
					<li class="<?php echo esc_attr( $cur_route === 'vamtam_page_' . $routes[1] ? 'is-active' : '' ); ?>" >
						<a <?php echo esc_attr( ! $valid_key ? 'class=disabled' : '' ); ?> href="<?php echo esc_url( admin_url( 'admin.php?page=' . $routes[1] ) ); ?>">
							<span class="ts-icon dashicons dashicons-admin-plugins"></span>
							<span><?php echo esc_html__( 'Install Plugins' , 'innovecouture' ); ?></span>
							<span class="vamtam-step-status <?php echo esc_attr( $valid_key ? $plugin_status : 'error' ); ?>"></span>
						</a>
					</li>
				<?php endif ?>
				<li class="<?php echo esc_attr( $cur_route === 'vamtam_page_' . $routes[2] ? 'is-active' : '' ); ?>" >
					<a <?php echo esc_attr( ! $valid_key || $plugin_status !== 'success' ? 'class=disabled' : '' ); ?> href="<?php echo esc_url( admin_url( 'admin.php?page=' . $routes[2] ) ); ?>">
						<svg class="ts-icon" xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 30 30"><path fill-rule="evenodd" d="M25.6 25.6A15 15 0 0 0 4.4 4.4l2 2a12.2 12.2 0 1 1 0 17.2l-2 2a15 15 0 0 0 21.2 0zM0 13.7v2.8h16.7l-4.2 4.2 2 2 7.6-7.6-7.6-7.5-2 2 4.2 4.1H0z"/></svg>
						<span><?php echo esc_html__( 'Import Demo' , 'innovecouture' ); ?></span>
						<span class="vamtam-step-status <?php echo esc_attr( $valid_key && $content_imported ? 'success' : 'error' ); ?>"></span>
					</a>
				</li>
				<li>
					<a id="vamtam-hs-btn" class="<?php echo esc_attr( $cur_route === 'vamtam_page_' . $routes[3] ? 'is-active' : ''); ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=' . $routes[3] ) ); ?>">
						<svg class="ts-icon" width="30" height="30" viewBox="0 0 640 512" xmlns="http://www.w3.org/2000/svg"><path d="M208 352c114.9 0 208-78.8 208-176S322.9 0 208 0S0 78.8 0 176c0 38.6 14.7 74.3 39.6 103.4c-3.5 9.4-8.7 17.7-14.2 24.7c-4.8 6.2-9.7 11-13.3 14.3c-1.8 1.6-3.3 2.9-4.3 3.7c-.5 .4-.9 .7-1.1 .8l-.2 .2s0 0 0 0s0 0 0 0C1 327.2-1.4 334.4 .8 340.9S9.1 352 16 352c21.8 0 43.8-5.6 62.1-12.5c9.2-3.5 17.8-7.4 25.2-11.4C134.1 343.3 169.8 352 208 352zM448 176c0 112.3-99.1 196.9-216.5 207C255.8 457.4 336.4 512 432 512c38.2 0 73.9-8.7 104.7-23.9c7.5 4 16 7.9 25.2 11.4c18.3 6.9 40.3 12.5 62.1 12.5c6.9 0 13.1-4.5 15.2-11.1c2.1-6.6-.2-13.8-5.8-17.9c0 0 0 0 0 0s0 0 0 0l-.2-.2c-.2-.2-.6-.4-1.1-.8c-1-.8-2.5-2-4.3-3.7c-3.6-3.3-8.5-8.1-13.3-14.3c-5.5-7-10.7-15.4-14.2-24.7c24.9-29 39.6-64.7 39.6-103.4c0-92.8-84.9-168.9-192.6-175.5c.4 5.1 .6 10.3 .6 15.5z"/></svg>
						<span><?php echo esc_html__( 'Help & Support' , 'innovecouture' ); ?></span>
					</a>
				</li>
				<li>
					<a href="<?php echo esc_url( 'https://vamtam.com/services/' ); ?>" target="_blank">
						<svg class="ts-icon" xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 576 512"><path d="m309 106c11.4-7 19-19.7 19-34 0-22.1-17.9-40-40-40s-40 17.9-40 40c0 14.4 7.6 27 19 34l-57.3 114.6c-9.1 18.2-32.7 23.4-48.6 10.7l-89.1-71.3c5-6.7 8-15 8-24 0-22.1-17.9-40-40-40s-40 17.9-40 40 17.9 40 40 40h0.7l45.7 251.4c5.5 30.4 32 52.6 63 52.6h277.2c30.9 0 57.4-22.1 63-52.6l45.7-251.4h0.7c22.1 0 40-17.9 40-40s-17.9-40-40-40-40 17.9-40 40c0 9 3 17.3 8 24l-89.1 71.3c-15.9 12.7-39.5 7.5-48.6-10.7l-57.3-114.6z"/></svg>
						<span><?php echo esc_html__( 'Premium Services' , 'innovecouture' ); ?></span>
					</a>
				</li>
			</ul>
			<div id="vamtam-menu-logo">
			<a href="https://vamtam.com" target="_blank" rel="noopener noreferrer">
				<svg viewBox="0 0 113 24" xmlns="http://www.w3.org/2000/svg"><path d="m20.602 1.2318c0.23665-0.51665 0.89715-0.52363 1.1179-0.038075 0.19214 0.42275 2.1699 4.774 4.1605 9.1531l0.33157 0.72945c1.9865 4.3702 3.8872 8.5518 3.9292 8.6441 0.22551 0.49608 0.89348 0.68976 1.2103-0.0072232 0.3128-0.68817 8.1933-17.945 8.3972-18.475 0.18572-0.48261 0.86997-0.55839 1.1107-0.037218 0.17604 0.3813 2.1863 4.7837 4.3113 9.4432l0.51824 1.1365c2.3052 5.0557 4.5815 10.055 4.6563 10.243 0.51603 1.2944-1.2931 1.8869-1.7721 0.82541-0.34035-0.754-1.8245-4.0201-3.3989-7.483l-0.31576-0.69452-0.3163-0.69565c-1.843-4.0533-3.6176-7.9543-3.6507-8.0263-0.25587-0.55533-0.92568-0.51028-1.1708-0.0044074-0.20653 0.42628-1.7911 3.9076-3.4547 7.5715l-0.50013 1.1018c-1.7774 3.9165-3.4968 7.714-3.5814 7.9063-0.45519 1.0348-2.1988 1.5308-2.9405-0.10088-1.0618-2.3359-7.3794-16.235-7.5168-16.537-0.19711-0.43364-0.8832-0.50367-1.1213 0.022772-0.26995 0.59659-1.9783 4.3824-1.9783 4.3824-0.60835 1.1448-2.3296 0.28256-1.7303-0.90352 1.0268-2.0324 3.6167-7.9636 3.7048-8.1562zm-20.068 0.1545c-0.52583-1.1907 1.237-1.9893 1.7642-0.79872 0.12341 0.27852 8.4909 18.649 8.7376 19.192 0.25098 0.55203 0.90376 0.54921 1.1449 0.031219 0.13896-0.29836 2.6265-5.8242 2.9352-6.4701 0.10884-0.22759 0.27583-0.43474 0.63014-0.43474h6.4128c1.249 0 1.4335 1.945-0.02069 1.9451h-4.5712c-0.94478 0-1.3379 0.69723-1.5179 1.0932-0.17997 0.39593-2.0475 4.6722-2.9047 6.4729-0.62757 1.3181-2.3503 1.5078-3.0612-0.05595-0.098598-0.21689-2.2165-4.8585-4.4366-9.7292l-0.34209-0.75057c-2.2255-4.8831-4.4547-9.78-4.7705-10.495zm57.406 6.9133h1.56l2.148 6.78h0.024l2.196-6.78h1.524l-2.928 8.568h-1.668zm10.936 0h1.596l3.3 8.568h-1.608l-0.804-2.268h-3.42l-0.804 2.268h-1.548zm-0.528 5.16h2.616l-1.284-3.684h-0.036zm7.216-5.16h2.112l2.364 6.708h0.024l2.304-6.708h2.088v8.568h-1.428v-6.612h-0.024l-2.376 6.612h-1.236l-2.376-6.612h-0.024v6.612h-1.428zm17.812 0v1.296h-2.724v7.272h-1.5v-7.272h-2.712v-1.296zm4.78 0 3.3 8.568h-1.608l-0.804-2.268h-3.42l-0.804 2.268h-1.548l3.288-8.568zm-0.792 1.476h-0.036l-1.296 3.684h2.616zm5.884-1.476h2.112l2.364 6.708h0.024l2.304-6.708h2.088v8.568h-1.428v-6.612h-0.024l-2.376 6.612h-1.236l-2.376-6.612h-0.024v6.612h-1.428z"/></svg>
			</a>
			</div>
		</nav>
		<?php
	}

	public static function import_buttons() {
		wp_enqueue_script( 'vamtam-import-buttons' );

		wp_localize_script( 'vamtam-import-buttons', 'vamtamImportButtonsVars', array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'vamtam_attachment_progress' )
		));

		include_once ABSPATH . 'wp-admin/includes/plugin.php';

		$content_allowed = defined( 'ELEMENTOR_PRO__FILE__' );

		$content_imported = ! ! get_option( 'vamtam_last_import_map', false );

		$messages = array(
			'success-msg' => esc_html__( 'Imported.', 'innovecouture' ),
			'error-msg  ' => esc_html__( 'Failed to import. Please <a href="{fullimport}" target="_blank">click here</a> in order to see the full error message.', 'innovecouture' ),
		);

		$import_tests = array(
			array(
				'test'   => defined( 'ELEMENTOR_PRO__FILE__' ),
				'title'  => esc_html__( 'Posts, Pages and Site Layout', 'innovecouture' ),
				'failed' => wp_kses( __( "This theme requires Elementor Pro. If you don't have Elementor Pro, please <a href='https://be.elementor.com/visit/?bta=13981&nci=5383' target='_blank'>download it here</a>. Install and activate it, and then proceed with importing the demo content. If you have any issues with the importer please <a href='https://elementor.support.vamtam.com/support/solutions/articles/245218-vamtam-elementor-themes-how-to-install-the-theme-via-the-admin-panel-' target='_blank'>read this article</a> or reach out to us using <a href='https://vamtam.com/contact-us/' target='_blank'>the form on this page</a>.", 'innovecouture' ), 'vamtam-a-span' ),
			),
		);

		$will_import = array();

		foreach ( $import_tests as $test ) {
			if ( ! $test['test'] ) {
				$will_import[] = '<li><div class="vamtam-message">' . $test['failed'] . '</div></li>';
			}
		}

		$attachments_todo   = get_option( 'vamtam_import_attachments_todo', [ 'attachments' => '' ] )['attachments'];
		$total_attachements = is_countable( $attachments_todo ) ? count( $attachments_todo ) : 0;

		$img_progress = $total_attachements > 0 && class_exists( 'Vamtam_Importers_E' ) && is_callable( [ 'Vamtam_Importers_E', 'get_attachment_progress' ] ) ?
			Vamtam_Importers_E::get_attachment_progress( $total_attachements )['text'] :
			esc_html__( 'checking...', 'innovecouture' );

		$import_disabled_msg = empty( $will_import ) ? '' : '<div id="vamtam-recommended-plugins-notice" class="visible wide"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="50"><path fill-rule="evenodd" d="M7 33.3a5.4 5.4 0 01-5.4-5L0 5.8A5.1 5.1 0 011.1 2c.2-.2.5-.3.8-.1.2.2.3.5.1.8a4.1 4.1 0 00-.9 2.8l1.6 22.7a4.3 4.3 0 005 3.9c.3 0 .6.1.7.4 0 .3-.2.6-.5.7H7zm4.7-3.6h-.1a.6.6 0 01-.4-.7v-.7L13 5.6v-.3c0-2.3-2-4.2-4.3-4.2h-2A.6.6 0 016 .6c0-.4.3-.6.6-.6h2A5.4 5.4 0 0114 5.7l-1.6 22.7-.1.9c-.1.2-.3.4-.6.4zM7 50a6.2 6.2 0 01-6.2-6.1A6.2 6.2 0 1113 42.2c0 .3-.1.6-.4.7-.3 0-.6-.1-.7-.4a5.1 5.1 0 00-10 1.4 5 5 0 005.1 5 5 5 0 005-5c0-.3.3-.6.7-.6.3 0 .5.3.5.6 0 3.4-2.8 6.1-6.2 6.1z"/></svg><ul>' . implode( '<br>', $will_import ) . '</ul></div>';

		$buttons = array(
			array(
				'label'          => esc_html__( 'Dummy Content Import', 'innovecouture' ),
				'id'             => 'content-import-button',
				'description'    => esc_html__( 'You are advised to use this importer only on new WordPress sites.', 'innovecouture' ),
				'button_title'   => esc_html__( 'Import', 'innovecouture' ),
				'href'           => $content_allowed ? wp_nonce_url( admin_url( 'admin.php?import=wpv&step=2' ), 'vamtam-import' ) : 'javascript:void( 0 )',
				'type'           => 'button',
				'class'          => $content_allowed ? 'button-primary vamtam-import-button' : 'disabled',
				'data'           => array_merge( $messages, [
					'content-imported' => $content_imported,
					'success-msg'      => sprintf( esc_html__( 'Main content imported. Image import progress: <span class="vamtam-image-import-progress">%s</span>.', 'innovecouture' ), $img_progress ),
					'fail-msg'         => esc_html__( 'Failed to import. We recommend that you contact your hosting provider for advice, as solving this issue is often specific to each server.', 'innovecouture' ),
					'timeout-msg'      => esc_html__( 'Failed to import. This is most likely caused by a timeout. Please contact your hosting provider for advice as to how you can increase the time limit on your server.', 'innovecouture' ),
				] ),
				'additional_msg' => $import_disabled_msg . wp_kses( sprintf( __( '<p class="vamtam-description">Please make sure to <a href="%s" target="_blank">backup</a> any existing content that you need as it will be removed by the import procedure (affects Posts, Pages and Menus).</p><p class="vamtam-description">We recommend that you use the <a href="%s" target="_blank">Post Name permalink structure</a></p><p class="vamtam-description">Images will be downloaded in the background after the main import is complete. Depending on your server, this may take several minutes.<br> In the meantime you may notice that some images are not visible.', 'innovecouture' ), esc_url( admin_url( 'plugin-install.php?tab=plugin-information&plugin=updraftplus&TB_iframe=true&width=772&height=921' ) ), esc_url( admin_url( 'options-permalink.php' ) ) ), 'vamtam-admin' ),
				'disabled_msg_plain' => '',
			),
		);

		echo '<div class="main-content">';

		foreach ( $buttons as $button ) {
			self::render_button( $button );
		}

		echo '</div>';
	}

	public static function render_button( $button ) {
		echo '<div class="vamtam-box-wrap">';
		echo '<header><h3>' . esc_html( $button['label'] ) . '</h3></header>';

		$data = array();

		if ( isset( $button['data'] ) ) {
			foreach ( $button['data'] as $attr_name => $attr_value ) {
				$data[] = 'data-' . sanitize_title_with_dashes( $attr_name ) . '="' . esc_attr( $attr_value ) . '"';
			}
		}

		$data = implode( ' ', $data );

		echo '<div class="content">';

		if ( strpos( $button['class'], 'disabled' ) !== false ) {
			if ( isset( $button['disabled_msg'] ) ) {
				$href = isset( $button['disabled_msg_href'] ) ? $button['disabled_msg_href'] : admin_url( 'admin.php?page=tgmpa-install-plugins&plugin_status=required' );
				echo '<p class="vamtam-description">';
				if ( $href !== 'nolink' ) {
					echo '<a href="' . esc_html( $href ) . '">' . wp_kses_data( $button['disabled_msg'] ) . '</a>';
				} else {
					echo wp_kses_data( $button['disabled_msg'] );
				}
				echo '</p>';
			}

			if ( isset( $button['disabled_msg_plain'] ) ) {
				echo '<p class="vamtam-description">' . wp_kses_data( $button['disabled_msg_plain'] ) . '</p>';
			}
		} else {
			if ( isset( $button['description'] ) ) {
				echo '<p class="vamtam-description">' . wp_kses_data( $button['description'] ) . '</p>';
			}
			if ( isset( $button['warning'] ) ) {
				echo '<p class="vamtam-description warning">' . $button['warning'] . '</p>'; // xss ok
			}
		}

		if ( isset( $button['additional_msg'] ) ) {
			echo '<p class="vamtam-description">' . $button['additional_msg'] . '</p>'; // xss ok
		}

		echo '<div class="import-btn-wrap">';
		echo '<a href="' . ( isset( $button['href'] ) ? esc_attr( $button['href'] ) : '#' ) . '" id="' . esc_attr( $button['id'] ) . '" title="' . esc_attr( $button['button_title'] ) . '" class="button-primary vamtam-ts-button ' . esc_attr( $button['class'] ) . '" ' . $data . '>' . esc_html( $button['button_title'] ) . '</a>'; // xss ok - $data escaped above
		echo '</div>';
		echo '</div>';
		echo '</div>';
	}

	public static function purchase_key( $args ) {
		$valid_key = Version_Checker::is_valid_purchase_code();
		$option_value = get_option( $args[0] );
		$placeholder = __( 'XXXXXX-XXX-XXXX-XXXX-XXXXXXXX', 'innovecouture' );
		$plugin_status = VamtamPluginManager::get_required_plugins_status();
		$content_imported = ! ! get_option( 'vamtam_last_import_map', false );


		$button_data = '';

		$data = array(
			'nonce'     => wp_create_nonce( 'vamtam-check-license' ),
		);

		if ( ! defined( 'ENVATO_HOSTED_SITE' ) ) {
			echo '<div id="vamtam-check-license-result"></div>';
		}
		echo '<div class="vamtam-licence-wrap">';
		if ( $valid_key ) {
			echo '<span id="vamtam-license-result"';
			echo 'class="valid">';
			echo '<svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 30 30"><path fill-rule="evenodd" d="M30 15a15 15 0 1 1-30 0 15 15 0 0 1 30 0zm-2.7-4.4L15.7 22.3a1 1 0 0 1-1.4 0L7 13.7a1 1 0 0 1 1.4-1.3l6.6 7.7L26.5 8.7a13 13 0 1 0 .8 1.9z"/></svg>';
			esc_html_e( 'Valid', 'innovecouture' );
			echo '</span>';
		} else {
			echo '<span id="vamtam-license-result-wrap">';
			echo '<span class="valid">';
			echo '<svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 30 30"><path fill-rule="evenodd" d="M30 15a15 15 0 1 1-30 0 15 15 0 0 1 30 0zm-2.7-4.4L15.7 22.3a1 1 0 0 1-1.4 0L7 13.7a1 1 0 0 1 1.4-1.3l6.6 7.7L26.5 8.7a13 13 0 1 0 .8 1.9z"/></svg>';
			esc_html_e( 'Valid', 'innovecouture' );
			echo '</span>';
			echo '<span class="invalid">';
			echo '<span class="dashicons dashicons-no-alt"></span>';
			esc_html_e( 'Invalid', 'innovecouture' );
			echo '</span>';
			echo '</span>';
		}
		echo '<input type="text" id="vamtam-envato-license-key" name="' . esc_attr( $args[0] ) . '" value="' . ( $valid_key && vamtam_sanitize_bool( $option_value ) ? esc_attr( $option_value ) : '' ) . '" size="64" ' . ( defined( 'SUBSCRIPTION_CODE' ) ? 'disabled' : '' ) . 'placeholder="' . $placeholder . '"' . '/>';
		if ( $valid_key ) {
			echo '<button id="vamtam-check-license" class="button button-primary unregister" data-nonce="'. esc_attr( $data['nonce'] ) .'">';
			echo '<svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 20 20"><path fill="white" d="M15.6 3.1h-4.1V1.5c0-.4-.2-.7-.4-1-.3-.2-.6-.3-1-.3H6.9c-.4 0-.7.1-1 .4-.2.2-.4.5-.4 1V3H1.4l-.5.2-.1.4.1.4c.2.2.3.2.5.2h.8L3.5 18c0 .3.2.5.5.8.2.2.5.3.8.3h7.4a1.2 1.2 0 0 0 1.2-1.2l1.4-13.7h.8c.2 0 .3 0 .5-.2l.1-.4-.1-.4a.6.6 0 0 0-.5-.2zM6.7 1.5v-.1h3.6V3H6.8V1.5zm7 2.8L12.2 18v.1H4.7L3.3 4.2h10.2z"/></svg>';
			echo '</button>';
		}
		echo '</div>';

		if ( ! defined( 'ENVATO_HOSTED_SITE' ) ) {
			echo '<span style="display: block">';

			if ( ! $valid_key ) {
				echo '<div id="vamtam-envato-elements-cb-wrap">';
				echo '<input type="checkbox" id="vamtam-envato-elements-cb" name="vamtam_envato_elements" />';
				echo '<label for="vamtam-envato-elements-cb">' . __( 'I downloaded the theme from Envato Elements.', 'innovecouture' ) . '</label>';
				echo '</div>';

				echo '<p id="vamtam-code-help">' . wp_kses( sprintf( __( ' <a href="%s" target="_blank">Where can I find my Item Purchase Code?</a>', 'innovecouture' ), 'https://help.market.envato.com/hc/en-us/articles/202822600-Where-Is-My-Purchase-Code-' ), 'vamtam-a-span' ) . '</p>';

				echo '<p id="vamtam-code-help-elements" class="hidden">';
				echo wp_kses( sprintf( __( ' <a href="%s" target="_blank">Follow this link to generate a new Envato Elements Token.</a>', 'innovecouture' ), esc_url( 'https://api.extensions.envato.com/extensions/begin_activation'
					. '?extension_id=' . md5( get_site_url() )
					. '&extension_type=envato-wordpress'
					. '&extension_description=' . wp_get_theme()->get( 'Name' ) . ' (' . get_home_url() . ')'
					) ), 'vamtam-a-span' );
				echo '</p>';

				echo '<button id="vamtam-check-license" class="button button-primary" ';

				foreach ( $data as $key => $value ) {
					echo ' data-' . $key . '="' . esc_attr( $value ) . '"';
				}

				echo '>' . esc_html__( 'Register', 'innovecouture' );
				echo '</button>';
			} else if ( $plugin_status !== 'success' ) {
				echo '<a id="vamtam-plugin-step" class="button-primary vamtam-ts-button" href="' . esc_url( admin_url( 'admin.php?page=tgmpa-install-plugins' ) ) . '">';
				echo esc_html__( 'Continue to required plguins', 'innovecouture' );
				echo '</a>';
			} elseif ( ! $content_imported ) {
				echo '<a id="vamtam-import-step" class="button-primary vamtam-ts-button" href="' . esc_url( admin_url( 'admin.php?page=vamtam_theme_setup_import_content' ) ) . '">';
				echo esc_html__( 'Continue to demo import', 'innovecouture' );
				echo '</a>';
			}

			echo '</span>';
		}
	}
}
