<?php
namespace VamtamElementor\Widgets\Call_To_Action;

use ElementorPro\Modules\CallToAction\Widgets\Call_To_Action as Elementor_Call_To_Action;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Is Pro Widget.
if ( ! \VamtamElementorIntregration::is_elementor_pro_active() ) {
	return;
}

// Theme preferences.
if ( ! \Vamtam_Elementor_Utils::is_widget_mod_active( 'call-to-action' ) ) {
	return;
}

if ( vamtam_theme_supports( 'call-to-action--underline-anim' ) ) {
	function add_button_style_section_controls( $controls_manager, $widget ) {
		$global_default = \Vamtam_Elementor_Utils::get_theme_global_widget_option( 'underline_anim_default' );
		// Use Underline Anim.
		$widget->add_control(
			'vamtam_underline_anim',
			[
				'label' => __( 'Use Underline Animation', 'vamtam-elementor-integration' ),
				'type' => $controls_manager::SWITCHER,
				'prefix_class' => 'vamtam-has-',
				'return_value' => 'underline-anim',
				'default' => empty( $global_default ) ? '' : 'underline-anim',
				'render_type' => 'template',
			]
		);
		// Width
		$widget->add_control(
			'vamtam_underline_width',
			[
				'label' => __( 'Width', 'vamtam-elementor-integration' ),
				'type' => $controls_manager::SLIDER,
				'range' => [
					'px' => [
						'max' => 10,
						'min' => 1,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-button' => '--vamtam-underline-width: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'vamtam_underline_anim!' => '',
				]
			]
		);
		// Spacing
		$widget->add_control(
			'vamtam_underline_spacing',
			[
				'label' => __( 'Spacing', 'vamtam-elementor-integration' ),
				'type' => $controls_manager::SLIDER,
				'range' => [
					'px' => [
						'max' => 50,
						'min' => 0,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-button' => '--vamtam-underline-spacing: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'vamtam_underline_anim!' => '',
				]
			]
		);
		// Underline Color.
		$widget->add_control(
			'vamtam_underline_bg_color',
			[
				'label' => __( 'Underline Color', 'vamtam-elementor-integration' ),
				'type' => $controls_manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .elementor-button' => '--vamtam-underline-bg-color: {{VALUE}};',
				],
				'condition' => [
					'vamtam_underline_anim!' => '',
				]
			]
		);
	}
	// Style - Button section
	function button_style_before_section_end( $widget, $args ) {
		$controls_manager = \Elementor\Plugin::instance()->controls_manager;
		add_button_style_section_controls( $controls_manager, $widget );
	}
	add_action( 'elementor/element/call-to-action/button_style/before_section_end', __NAMESPACE__ . '\button_style_before_section_end', 10, 2 );

	// Vamtam_Widget_Call_To_Action.
	function widgets_registered() {

		// Is Pro Widget.
		if ( ! \VamtamElementorIntregration::is_elementor_pro_active() ) {
			return;
		}

		if ( ! class_exists( '\ElementorPro\Modules\CallToAction\Widgets\Call_To_Action' ) ) {
			return; // Elementor's autoloader acts weird sometimes.
		}

		class Vamtam_Widget_Call_To_Action extends Elementor_Call_To_Action {
			public $extra_depended_scripts = [
				'vamtam-call-to-action',
			];

			// Extend constructor.
			public function __construct($data = [], $args = null) {
				parent::__construct($data, $args);

				$this->register_assets();

				$this->add_extra_script_depends();
			}

			// Register the assets the widget depends on.
			public function register_assets() {
				$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

				wp_register_script(
					'vamtam-call-to-action',
					VAMTAM_ELEMENTOR_INT_URL . '/assets/js/widgets/call-to-action/vamtam-call-to-action' . $suffix . '.js',
					[
						'elementor-frontend'
					],
					\VamtamElementorIntregration::PLUGIN_VERSION,
					true
				);
			}

			// Assets the widget depends upon.
			public function add_extra_script_depends() {
				// Scripts
				foreach ( $this->extra_depended_scripts as $script ) {
					$this->add_script_depends( $script );
				}
			}
		}

		// Replace current divider widget with our extended version.
		$widgets_manager = \Elementor\Plugin::instance()->widgets_manager;
		$widgets_manager->unregister( 'call-to-action' );
		$widgets_manager->register( new Vamtam_Widget_Call_To_Action );
	}
	add_action( \Vamtam_Elementor_Utils::get_widgets_registration_hook(), __NAMESPACE__ . '\widgets_registered', 100 );
}
