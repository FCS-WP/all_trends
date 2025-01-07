<?php
namespace VamtamElementor\Widgets\Login;

use ElementorPro\Modules\Forms\Widgets\Login as Elementor_Login;
use ElementorPro\Plugin;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Typography;

// Extending the Login widget.

// Is Pro Widget.
if ( ! \VamtamElementorIntregration::is_elementor_pro_active() ) {
	return;
}

// Theme preferences.
if ( ! \Vamtam_Elementor_Utils::is_widget_mod_active( 'login' ) ) {
	return;
}

if ( vamtam_theme_supports( 'login--extra-login-controls' ) ) {

	function add_content_extra_controls( $controls_manager, $widget ) {
		$widget->start_controls_section(
			'section_vamtam_login_content',
			[
				'label' => __( 'Login Content', 'vamtam-elementor-integration' ),
			]
		);

		// Show Logged Out Content in Editor.
		$widget->add_control(
			'show_editor_logged_out_status',
			[
				'label' => __( 'Show Logged Out status in Editor', 'vamtam-elementor-integration' ),
				'type' => $controls_manager::SWITCHER,
				'separator' => 'after',
				'default' => 'yes',
			]
		);

		// Title
		$widget->add_control(
			'login_title',
			[
				'label' => esc_html__( 'Title', 'vamtam-elementor-integration' ),
				'type' => $controls_manager::TEXT,
				'dynamic' => [
					'active' => true,
				],
				'placeholder' => esc_html__( 'Enter your title', 'vamtam-elementor-integration' ),
				'default' => esc_html__( 'Login', 'vamtam-elementor-integration' ),
			]
		);

		// Tag
		$widget->add_control(
			'login_title_tag',
			[
				'label' => esc_html__( 'HTML Tag', 'vamtam-elementor-integration' ),
				'type' => $controls_manager::SELECT,
				'options' => [
					'h1' => 'H1',
					'h2' => 'H2',
					'h3' => 'H3',
					'h4' => 'H4',
					'h5' => 'H5',
					'h6' => 'H6',
					'div' => 'div',
					'span' => 'span',
					'p' => 'p',
				],
				'default' => 'h3',
			]
		);

		// Align
		$widget->add_responsive_control(
			'login_title_align',
			[
				'label' => esc_html__( 'Alignment', 'vamtam-elementor-integration' ),
				'type' => $controls_manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => esc_html__( 'Left', 'vamtam-elementor-integration' ),
						'icon' => 'eicon-text-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'vamtam-elementor-integration' ),
						'icon' => 'eicon-text-align-center',
					],
					'right' => [
						'title' => esc_html__( 'Right', 'vamtam-elementor-integration' ),
						'icon' => 'eicon-text-align-right',
					],
					'justify' => [
						'title' => esc_html__( 'Justified', 'vamtam-elementor-integration' ),
						'icon' => 'eicon-text-align-justify',
					],
				],
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .vamtam-login-title' => 'text-align: {{VALUE}};',
				],
			]
		);

		// Spacing.
		$widget->add_responsive_control(
			'login_title_spacing',
			[
				'label' => __( 'Spacing', 'vamtam-elementor-integration' ),
				'type' => $controls_manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default' => [
                    'size' => 20,
                ],
				'selectors' => [
					'{{WRAPPER}} .vamtam-login-title' => 'margin-bottom: {{SIZE}}{{UNIT}}',
				],
				'separator' => 'after',
			]
		);

		// Text
		$widget->add_control(
			'login_text',
			[
				'label' => esc_html__( 'Text', 'vamtam-elementor-integration' ),
				'type' => $controls_manager::TEXTAREA,
				'placeholder' => esc_html__( 'Enter your text', 'vamtam-elementor-integration' ),
				'default' => esc_html__( 'Add Your Text Here', 'vamtam-elementor-integration' ),
			]
		);

		// Align
		$widget->add_responsive_control(
			'login_text_align',
			[
				'label' => esc_html__( 'Alignment', 'vamtam-elementor-integration' ),
				'type' => $controls_manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => esc_html__( 'Left', 'vamtam-elementor-integration' ),
						'icon' => 'eicon-text-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'vamtam-elementor-integration' ),
						'icon' => 'eicon-text-align-center',
					],
					'right' => [
						'title' => esc_html__( 'Right', 'vamtam-elementor-integration' ),
						'icon' => 'eicon-text-align-right',
					],
					'justify' => [
						'title' => esc_html__( 'Justified', 'vamtam-elementor-integration' ),
						'icon' => 'eicon-text-align-justify',
					],
				],
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .vamtam-login-text' => 'text-align: {{VALUE}};',
				],
			]
		);

		// Spacing.
		$widget->add_responsive_control(
			'login_text_spacing',
			[
				'label' => __( 'Spacing', 'vamtam-elementor-integration' ),
				'type' => $controls_manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default' => [
                    'size' => 20,
                ],
				'selectors' => [
					'{{WRAPPER}} .vamtam-login-text' => 'margin-bottom: {{SIZE}}{{UNIT}}',
				],
			]
		);

		$widget->end_controls_section();

		$widget->start_controls_section(
			'section_vamtam_logged_in_content',
			[
				'label' => __( 'Logged In Content', 'vamtam-elementor-integration' ),
			]
		);

		// Use default logged in title.
		$widget->add_control(
			'use_default_logged_in_title',
			[
				'label' => __( 'Use Default Title', 'vamtam-elementor-integration' ),
				'type' => $controls_manager::SWITCHER,
				'default' => 'yes',
				'render_type' => 'template',
			]
		);

		// Title
		$widget->add_control(
			'logged_in_title',
			[
				'label' => esc_html__( 'Title', 'vamtam-elementor-integration' ),
				'type' => $controls_manager::TEXT,
				'dynamic' => [
					'active' => true,
				],
				'placeholder' => esc_html__( 'Enter your title', 'vamtam-elementor-integration' ),
				'default' => esc_html__( 'Add Your Heading Text Here', 'vamtam-elementor-integration' ),
				'condition' => [
					'use_default_logged_in_title!' => 'yes',
				]
			]
		);

		// Tag
		$widget->add_control(
			'logged_in_title_tag',
			[
				'label' => esc_html__( 'HTML Tag', 'vamtam-elementor-integration' ),
				'type' => $controls_manager::SELECT,
				'options' => [
					'h1' => 'H1',
					'h2' => 'H2',
					'h3' => 'H3',
					'h4' => 'H4',
					'h5' => 'H5',
					'h6' => 'H6',
					'div' => 'div',
					'span' => 'span',
					'p' => 'p',
				],
				'default' => 'h3',
			]
		);

		// Align
		$widget->add_responsive_control(
			'logged_in_title_align',
			[
				'label' => esc_html__( 'Alignment', 'vamtam-elementor-integration' ),
				'type' => $controls_manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => esc_html__( 'Left', 'vamtam-elementor-integration' ),
						'icon' => 'eicon-text-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'vamtam-elementor-integration' ),
						'icon' => 'eicon-text-align-center',
					],
					'right' => [
						'title' => esc_html__( 'Right', 'vamtam-elementor-integration' ),
						'icon' => 'eicon-text-align-right',
					],
					'justify' => [
						'title' => esc_html__( 'Justified', 'vamtam-elementor-integration' ),
						'icon' => 'eicon-text-align-justify',
					],
				],
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .vamtam-logged-in-title' => 'text-align: {{VALUE}};',
				],
			]
		);

		// Spacing.
		$widget->add_responsive_control(
			'logged_in_title_spacing',
			[
				'label' => __( 'Spacing', 'vamtam-elementor-integration' ),
				'type' => $controls_manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default' => [
                    'size' => 50,
                ],
				'selectors' => [
					'{{WRAPPER}} .vamtam-logged-in-title' => 'margin-bottom: {{SIZE}}{{UNIT}}',
				],
				'separator' => 'after',
			]
		);

		// Text
		$widget->add_control(
			'logged_in_text',
			[
				'label' => esc_html__( 'Text', 'vamtam-elementor-integration' ),
				'type' => $controls_manager::TEXTAREA,
				'placeholder' => esc_html__( 'Enter your text', 'vamtam-elementor-integration' ),
				'default' => esc_html__( 'Add Your Text Here', 'vamtam-elementor-integration' ),
			]
		);

		// Align
		$widget->add_responsive_control(
			'logged_in_text_align',
			[
				'label' => esc_html__( 'Alignment', 'vamtam-elementor-integration' ),
				'type' => $controls_manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => esc_html__( 'Left', 'vamtam-elementor-integration' ),
						'icon' => 'eicon-text-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'vamtam-elementor-integration' ),
						'icon' => 'eicon-text-align-center',
					],
					'right' => [
						'title' => esc_html__( 'Right', 'vamtam-elementor-integration' ),
						'icon' => 'eicon-text-align-right',
					],
					'justify' => [
						'title' => esc_html__( 'Justified', 'vamtam-elementor-integration' ),
						'icon' => 'eicon-text-align-justify',
					],
				],
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .vamtam-logged-in-text' => 'text-align: {{VALUE}};',
				],
			]
		);

		// Spacing.
		$widget->add_responsive_control(
			'logged_in_text_spacing',
			[
				'label' => __( 'Spacing', 'vamtam-elementor-integration' ),
				'type' => $controls_manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default' => [
                    'size' => 50,
                ],
				'selectors' => [
					'{{WRAPPER}} .vamtam-logged-in-text' => 'margin-bottom: {{SIZE}}{{UNIT}}',
				],
				'separator' => 'after',
			]
		);

		// Show CTA button.
		$widget->add_control(
			'show_logged_in_btn',
			[
				'label' => __( 'Show Button', 'vamtam-elementor-integration' ),
				'type' => $controls_manager::SWITCHER,
				'default' => 'yes',
			]
		);

		// CTA Btn Text
		$widget->add_control(
			'logged_in_button_text',
			[
				'label' => esc_html__( 'Text', 'vamtam-elementor-integration' ),
				'type' => $controls_manager::TEXT,
				'placeholder' => esc_html__( 'Enter your text', 'vamtam-elementor-integration' ),
				'default' => esc_html__( 'Add Your Text Here', 'vamtam-elementor-integration' ),
				'condition' => [
					'show_logged_in_btn!' => '',
				],
			]
		);

		// CTA Btn URL.
		$widget->add_control(
			'logged_in_button_url',
			[
				'type' => $controls_manager::URL,
				'show_label' => false,
				'options' => false,
				'separator' => false,
				'placeholder' => __( 'https://your-link.com', 'vamtam-elementor-integration' ),
				'condition' => [
					'show_logged_in_btn!' => '',
				],
			]
		);

		// Spacing.
		$widget->add_responsive_control(
			'logged_in_btn_spacing',
			[
				'label' => __( 'Spacing', 'vamtam-elementor-integration' ),
				'type' => $controls_manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default' => [
                    'size' => 50,
                ],
				'selectors' => [
					'{{WRAPPER}} .vamtam-logged-in-btn' => 'margin-bottom: {{SIZE}}{{UNIT}}',
				],
				'condition' => [
					'show_logged_in_btn!' => '',
				],
			]
		);

		$widget->end_controls_section();
	}

	function inject_register_extra_controls( $controls_manager, $widget ) {
		if ( get_option( 'users_can_register' ) ) {
			// Register
			$widget->start_injection( [
				'of' => 'show_register',
			] );

			// Custom Register URL.
			$widget->add_control(
				'use_custom_register',
				[
					'label' => __( 'Custom Register URL', 'vamtam-elementor-integration' ),
					'type' => $controls_manager::SWITCHER,
					'condition' => [
						'show_register' => 'yes',
					],
				]
			);

			// Custom Register URL.
			$widget->add_control(
				'custom_register_url',
				[
					'type' => $controls_manager::URL,
					'show_label' => false,
					'options' => false,
					'separator' => false,
					'placeholder' => __( 'https://your-link.com', 'vamtam-elementor-integration' ),
					'condition' => [
						'use_custom_register' => 'yes',
					],
				]
			);
			$widget->end_injection();
		}
	}

	// Content - Button section (After).
	function section_button_content_after_section_end( $widget, $args ) {
		$controls_manager = \Elementor\Plugin::instance()->controls_manager;
		add_content_extra_controls( $controls_manager, $widget );
	}
	add_action( 'elementor/element/login/section_button_content/after_section_end', __NAMESPACE__ . '\section_button_content_after_section_end', 10, 2 );

	// Content - Additional Options section (Before).
	function section_login_content_before_section_end( $widget, $args ) {
		$controls_manager = \Elementor\Plugin::instance()->controls_manager;
		inject_register_extra_controls( $controls_manager, $widget );
	}
	add_action( 'elementor/element/login/section_login_content/before_section_end', __NAMESPACE__ . '\section_login_content_before_section_end', 10, 2 );

	function add_style_extra_controls( $controls_manager, $widget ) {
		$widget->start_controls_section(
			'section_vamtam_style_login_content',
			[
				'label' => __( 'Login Content', 'vamtam-elementor-integration' ),
				'tab' => $controls_manager::TAB_STYLE,
			]
		);

		// Login Title Color
		$widget->add_control(
			'login_title_color',
			[
				'label' => __( 'Title Color', 'vamtam-elementor-integration' ),
				'type' => $controls_manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-widget-container .vamtam-login-title' => 'color: {{VALUE}};',
				],
			]
		);

		// Login Title Typography
		$widget->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'login_title_typography',
				'selector' => '{{WRAPPER}} .elementor-widget-container .vamtam-login-title',
			]
		);

		// Login Text Color
		$widget->add_control(
			'login_text_color',
			[
				'label' => __( 'Text Color', 'vamtam-elementor-integration' ),
				'type' => $controls_manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-widget-container .vamtam-login-text' => 'color: {{VALUE}};',
				],
				'separator' => 'before',
			]
		);

		// Login Text Typography
		$widget->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'login_text_typography',
				'selector' => '{{WRAPPER}} .elementor-widget-container .vamtam-login-text',
				'separator' => 'after',
			]
		);

		$widget->end_controls_section();

		$widget->start_controls_section(
			'section_vamtam_style_logged_in_content',
			[
				'label' => __( 'Logged In Content', 'vamtam-elementor-integration' ),
				'tab' => $controls_manager::TAB_STYLE,
			]
		);

		// Logged In Title Color
		$widget->add_control(
			'logged_in_title_color',
			[
				'label' => __( 'Title Color', 'vamtam-elementor-integration' ),
				'type' => $controls_manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-widget-container .vamtam-logged-in-title' => 'color: {{VALUE}};',
				],
			]
		);

		// Logged In Title Typography
		$widget->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'logged_in_title_typography',
				'selector' => '{{WRAPPER}} .elementor-widget-container .vamtam-logged-in-title',
				'separator' => 'after',
			]
		);

		// Logged In Text Color
		$widget->add_control(
			'logged_in_text_color',
			[
				'label' => __( 'Text Color', 'vamtam-elementor-integration' ),
				'type' => $controls_manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-widget-container .vamtam-logged-in-text' => 'color: {{VALUE}};',
				],
				'separator' => 'before',
			]
		);

		// Logged In Text Typography
		$widget->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'logged_in_text_typography',
				'selector' => '{{WRAPPER}} .elementor-widget-container .vamtam-logged-in-text',
				'separator' => 'after',
			]
		);

		$widget->end_controls_section();
	}

	// Increase specificity of button selectors so the kit's link ones dont override the local ones.
	function update_button_style_controls( $controls_manager, $widget ) {
		// Button Color.
		\Vamtam_Elementor_Utils::add_control_options( $controls_manager, $widget, 'button_text_color', [
			'selectors' => [
				'{{WRAPPER}} a.elementor-button' => 'color: {{VALUE}};',
			]
		] );

		// Button Typography.
		\Vamtam_Elementor_Utils::add_control_options( $controls_manager, $widget, 'button_typography', [
			'selector' => '{{WRAPPER}} a.elementor-button',
		], \Elementor\Group_Control_Typography::get_type() );

		// Button Hover Color.
		\Vamtam_Elementor_Utils::add_control_options( $controls_manager, $widget, 'button_hover_color', [
			'selectors' => [
				'{{WRAPPER}} a.elementor-button:hover' => 'color: {{VALUE}};',
			]
		] );
	}

	// Style - Button section (Before).
	function section_button_style_before_section_end( $widget, $args ) {
		$controls_manager = \Elementor\Plugin::instance()->controls_manager;
		update_button_style_controls( $controls_manager, $widget );
	}
	add_action( 'elementor/element/login/section_button_style/before_section_end', __NAMESPACE__ . '\section_button_style_before_section_end', 10, 2 );

	// Style - Button section (After).
	function section_style_message_after_section_end( $widget, $args ) {
		$controls_manager = \Elementor\Plugin::instance()->controls_manager;
		add_style_extra_controls( $controls_manager, $widget );
	}
	add_action( 'elementor/element/login/section_style_message/after_section_end', __NAMESPACE__ . '\section_style_message_after_section_end', 10, 2 );

}

// Vamtam_Widget_Login.
function widgets_registered() {
	class Vamtam_Widget_Login extends Elementor_Login {
		public $extra_depended_scripts = [
			'vamtam-login',
		];

		public function get_script_depends() {
			return [
				'vamtam-login',
			];
		}

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
				'vamtam-login',
				VAMTAM_ELEMENTOR_INT_URL . '/assets/js/widgets/login/vamtam-login' . $suffix . '.js',
				[
					'elementor-frontend'
				],
				\VamtamElementorIntregration::PLUGIN_VERSION,
				true
			);

			wp_localize_script(
				'vamtam-login', 'VamtamLoginStrings', array(
					'account' => __( 'Don\'t have an account?', 'vamtam-elementor-integration' ),
					'register' => __( 'Create account', 'vamtam-elementor-integration' ),
				)
			);
		}

		// Assets the widget depends upon.
		public function add_extra_script_depends() {
			// Scripts
			foreach ( $this->extra_depended_scripts as $script ) {
				$this->add_script_depends( $script );
			}
		}

		private function form_fields_render_attributes() {
			$settings = $this->get_settings();

			if ( ! empty( $settings['button_size'] ) ) {
				$this->add_render_attribute( 'button', 'class', 'elementor-size-' . $settings['button_size'] );
			}

			if ( $settings['button_hover_animation'] ) {
				$this->add_render_attribute( 'button', 'class', 'elementor-animation-' . $settings['button_hover_animation'] );
			}

			$this->add_render_attribute(
				[
					'wrapper' => [
						'class' => [
							'elementor-form-fields-wrapper',
						],
					],
					'field-group' => [
						'class' => [
							'elementor-field-type-text',
							'elementor-field-group',
							'elementor-column',
							'elementor-col-100',
						],
					],
					'submit-group' => [
						'class' => [
							'elementor-field-group',
							'elementor-column',
							'elementor-field-type-submit',
							'elementor-col-100',
						],
					],

					'button' => [
						'class' => [
							'elementor-button',
						],
						'name' => 'wp-submit',
					],
					'user_label' => [
						'for' => 'user',
					],
					'user_input' => [
						'type' => 'text',
						'name' => 'log',
						'id' => 'user',
						'placeholder' => $settings['user_placeholder'],
						'class' => [
							'elementor-field',
							'elementor-field-textual',
							'elementor-size-' . $settings['input_size'],
						],
					],
					'password_label' => [
						'for' => 'password',
					],
					'password_input' => [
						'type' => 'password',
						'name' => 'pwd',
						'id' => 'password',
						'placeholder' => $settings['password_placeholder'],
						'class' => [
							'elementor-field',
							'elementor-field-textual',
							'elementor-size-' . $settings['input_size'],
						],
					],
					//TODO: add unique ID
					'label_user' => [
						'for' => 'user',
						'class' => 'elementor-field-label',
					],

					'label_password' => [
						'for' => 'password',
						'class' => 'elementor-field-label',
					],
				]
			);

			if ( ! $settings['show_labels'] ) {
				$this->add_render_attribute( 'label', 'class', 'elementor-screen-only' );
			}

			$this->add_render_attribute( 'field-group', 'class', 'elementor-field-required' )
				 ->add_render_attribute( 'input', 'required', true )
				 ->add_render_attribute( 'input', 'aria-required', 'true' );

		}

		private function render_login_title( $settings ) {
			if ( '' === $settings['login_title'] ) {
				return;
			}

			$this->add_render_attribute( 'vamtam-login-title', 'class', 'vamtam-login-title elementor-heading-title' );

			$title = $settings['login_title'];

			$title_html = sprintf( '<%1$s %2$s>%3$s</%1$s>', \Elementor\Utils::validate_html_tag( $settings['login_title_tag'] ), $this->get_render_attribute_string( 'vamtam-login-title' ), $title );

			// PHPCS - the variable $title_html holds safe data.
			echo $title_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		private function render_login_text( $settings ) {
			if ( '' === $settings['login_text'] ) {
				return;
			}

			$this->add_render_attribute( 'vamtam-login-text', 'class', 'vamtam-login-text' );

			$text = $settings['login_text'];

			$text_html = sprintf( '<%1$s %2$s>%3$s</%1$s>', \Elementor\Utils::validate_html_tag( 'p' ), $this->get_render_attribute_string( 'vamtam-login-text' ), $text );

			// PHPCS - the variable $text_html holds safe data.
			echo $text_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		private function render_logged_in_title( $settings ) {
			$use_default = $settings['use_default_logged_in_title'] === 'yes';
			$has_custom  = ! $use_default && ! empty( $settings['use_default_logged_in_title'] );

			if ( ! $use_default ) {
				if ( ! $has_custom ) {
					return;
				}
			}

			$this->add_render_attribute( 'vamtam-logged-in-title', 'class', 'vamtam-logged-in-title elementor-heading-title' );

			$title = $use_default ? '<div class="vamtam-logged-in-def-title">' . ( __( 'Welcome', 'vamtam-elementor-integration' ) . '</div>' . wp_get_current_user()->display_name ) : $settings['logged_in_title'];

			$title_html = sprintf( '<%1$s %2$s>%3$s</%1$s>', \Elementor\Utils::validate_html_tag( $settings['logged_in_title_tag'] ), $this->get_render_attribute_string( 'vamtam-logged-in-title' ), $title );

			// PHPCS - the variable $title_html holds safe data.
			echo $title_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		private function render_logged_in_text( $settings ) {
			if ( '' === $settings['logged_in_text'] ) {
				return;
			}

			$this->add_render_attribute( 'vamtam-logged-in-text', 'class', 'vamtam-logged-in-text' );

			$text = $settings['logged_in_text'];

			$text_html = sprintf( '<%1$s %2$s>%3$s</%1$s>', \Elementor\Utils::validate_html_tag( 'p' ), $this->get_render_attribute_string( 'vamtam-logged-in-text' ), $text );

			// PHPCS - the variable $text_html holds safe data.
			echo $text_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		private function render_logged_in_button( $settings ) {
			if ( '' === $settings['show_logged_in_btn'] ) {
				return;
			}

			$this->add_render_attribute( 'vamtam-logged-in-btn', 'class', 'vamtam-logged-in-btn elementor-button button' );
			$this->add_render_attribute( 'vamtam-logged-in-btn-url', 'href', $settings['logged_in_button_url'] );

			$btn_text = $settings['logged_in_button_text'];

			$button_html = sprintf( '<%1$s %2$s %3$s>%4$s</%1$s>', 'a', $this->get_render_attribute_string( 'vamtam-logged-in-btn' ), $this->get_render_attribute_string( 'vamtam-logged-in-btn-url' ), $btn_text );

			// PHPCS - the variable $button_html holds safe data.
			echo $button_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		private function render_login_content( $settings ) {
			self::render_login_title( $settings );
			self::render_login_text( $settings );
		}

		private function render_logged_in_content( $settings ) {
			self::render_logged_in_title( $settings );
			self::render_logged_in_text( $settings );

			if ( $settings[ 'show_logged_in_btn' ] === 'yes' ) {
				self::render_logged_in_button( $settings );
			}
		}

		protected function render() {
			if ( ! vamtam_theme_supports( 'login--extra-login-controls' ) ) {
				parent::render();
			}

			$settings = $this->get_settings_for_display();
			$current_url = remove_query_arg( 'fake_arg' );
			$logout_redirect = $current_url;

			if ( 'yes' === $settings['redirect_after_login'] && ! empty( $settings['redirect_url']['url'] ) ) {
				$redirect_url = $settings['redirect_url']['url'];
			} else {
				$redirect_url = $current_url;
			}

			if ( 'yes' === $settings['redirect_after_logout'] && ! empty( $settings['redirect_logout_url']['url'] ) ) {
				$logout_redirect = $settings['redirect_logout_url']['url'];
			}

			if ( is_user_logged_in() ) {
				if ( Plugin::elementor()->editor->is_edit_mode() && $settings['show_editor_logged_out_status'] === 'yes' ) {
					self::render_login_content( $settings );
				} else {
					self::render_logged_in_content( $settings);

					if ( 'yes' === $settings['show_logged_in_message'] ) {
						$current_user = wp_get_current_user();
						$logout_url = Plugin::elementor()->editor->is_edit_mode() ? '#' : wp_logout_url( $logout_redirect );
						echo '<div class="elementor-login elementor-login__logged-in-message">' .
							sprintf( __( 'You are Logged in as %1$s (<a href="%2$s">Logout</a>)', 'vamtam-elementor-integration' ), $current_user->display_name, $logout_url ) .
							'</div>';
					}

					return; // Dont render form.
				}
			} else {
				self::render_login_content( $settings );
			}

			$this->form_fields_render_attributes();
			?>
			<form class="elementor-login elementor-form" method="post" action="<?php echo esc_url( site_url( 'wp-login.php', 'login_post' ) ); ?>">
				<input type="hidden" name="redirect_to" value="<?php echo esc_attr( $redirect_url ); ?>">
				<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>
					<div <?php echo $this->get_render_attribute_string( 'field-group' ); ?>>
						<?php
						if ( $settings['show_labels'] ) {
							echo '<label ' . $this->get_render_attribute_string( 'user_label' ) . '>' . $settings['user_label'] . '</label>';
						}

						echo '<input size="1" ' . $this->get_render_attribute_string( 'user_input' ) . '>';

						?>
					</div>
					<div <?php echo $this->get_render_attribute_string( 'field-group' ); ?>>
						<?php
						if ( $settings['show_labels'] ) :
							echo '<label ' . $this->get_render_attribute_string( 'password_label' ) . '>' . $settings['password_label'] . '</label>';
						endif;

						echo '<input size="1" ' . $this->get_render_attribute_string( 'password_input' ) . '>';
						?>
					</div>

					<?php if ( 'yes' === $settings['show_remember_me'] ) : ?>
						<div class="elementor-field-type-checkbox elementor-field-group elementor-column elementor-col-100 elementor-remember-me">
							<label for="elementor-login-remember-me">
								<input type="checkbox" id="elementor-login-remember-me" name="rememberme" value="forever">
								<?php echo __( 'Remember Me', 'vamtam-elementor-integration' ); ?>
							</label>
						</div>
					<?php endif; ?>

					<div <?php echo $this->get_render_attribute_string( 'submit-group' ); ?>>
						<button type="submit" <?php echo $this->get_render_attribute_string( 'button' ); ?>>
								<?php if ( ! empty( $settings['button_text'] ) ) : ?>
									<span class="elementor-button-text"><?php echo $settings['button_text']; ?></span>
								<?php endif; ?>
						</button>
					</div>

					<?php
					$show_lost_password   = 'yes' === $settings['show_lost_password'];
					$show_register        = get_option( 'users_can_register' ) && 'yes' === $settings['show_register'];
					$show_custom_register = $show_register && 'yes' === $settings['use_custom_register'];

					if ( $show_lost_password || $show_register ) : ?>
						<div class="elementor-field-group elementor-column elementor-col-100">
							<?php if ( $show_lost_password ) : ?>
								<a class="elementor-lost-password" href="<?php echo wp_lostpassword_url( $redirect_url ); ?>">
									<?php echo __( 'Lost your password?', 'vamtam-elementor-integration' ); ?>
								</a>
							<?php endif; ?>

							<?php if ( $show_register ) : ?>
								<?php if ( $show_lost_password ) : ?>
									<span class="elementor-login-separator"> | </span>
								<?php endif; ?>
								<a class="elementor-register" href="<?php echo ( $show_custom_register ) ? esc_url( $settings['custom_register_url']['url'] ) : wp_registration_url(); ?>">
									<?php echo __( 'Register', 'vamtam-elementor-integration' ); ?>
								</a>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				</div>
			</form>
			<?php
		}

		/**
		 * Render Login Form output in the editor.
		 *
		 * Written as a Backbone JavaScript template and used to generate the live preview.
		 *
		 * @since 2.9.0
		 * @access protected
		 */
		protected function content_template() {
			if ( ! vamtam_theme_supports( 'login--extra-login-controls' ) ) {
				parent::content_template();
			}
			?>
			<#
				const validateHTMLTag = function(tag) {
					var validHTMLTags = ['article', 'aside', 'div', 'footer', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'header', 'main', 'nav', 'p', 'section', 'span'];
					return validHTMLTags.includes(tag.toLowerCase()) ? tag : 'div';
				}

				function renderLoginTitle() {
					if ( ! settings.login_title ) {
						return;
					}

					var title = settings.login_title;

					view.addRenderAttribute( 'vamtam-login-title', 'class', [ 'vamtam-login-title', 'elementor-heading-title' ] );

					var titleTag = validateHTMLTag( settings.login_title_tag ),
						title_html = '<' + titleTag  + ' ' + view.getRenderAttributeString( 'vamtam-login-title' ) + '>' + title + '</' + titleTag + '>';

					print( title_html );
				}

				function renderLoginText() {
					if ( ! settings.login_text ) {
						return;
					}

					var text = settings.login_text;

					view.addRenderAttribute( 'vamtam-login-text', 'class', [ 'vamtam-login-text' ] );

					var	text_html = '<p ' + view.getRenderAttributeString( 'vamtam-login-text' ) + '>' + text + '</p>';

					print( text_html );
				}

				function renderLoggedInTitle() {
					var use_default = settings.use_default_logged_in_title,
						has_custom = ! use_default && settings.use_default_logged_in_title;

					if ( ! use_default ) {
						if ( ! has_custom ) {
							return;
						}
					}

					var title = use_default ? `<?php echo ( '<div class="vamtam-logged-in-def-title">' . __( 'Welcome', 'vamtam-elementor-integration' ) . wp_get_current_user()->display_name ); ?>` : settings.logged_in_title;

					view.addRenderAttribute( 'vamtam-logged-in-title', 'class', [ 'vamtam-logged-in-title', 'elementor-heading-title' ] );

					var titleTag = elementor.helpers.validateHTMLTag( settings.logged_in_title_tag ),
						title_html = '<' + titleTag  + ' ' + view.getRenderAttributeString( 'vamtam-logged-in-title' ) + '>' + title + '</' + titleTag + '>';

					print( title_html );
				}

				function renderLoggedInText() {
					if ( ! settings.logged_in_text ) {
						return;
					}

					var text = settings.logged_in_text;

					view.addRenderAttribute( 'vamtam-logged-in-text', 'class', [ 'vamtam-logged-in-text' ] );

					var textTag = elementor.helpers.validateHTMLTag( 'p' ),
						text_html = '<' + textTag  + ' ' + view.getRenderAttributeString( 'vamtam-logged-in-text' ) + '>' + text + '</' + textTag + '>';

					print( text_html );
				}

				function renderLoggedInButton() {
					if ( ! settings.show_logged_in_btn ) {
						return;
					}

					var btnText = settings.logged_in_button_text;

					view.addRenderAttribute( 'vamtam-logged-in-btn', 'class', [ 'vamtam-logged-in-btn elementor-button button' ] );
					view.addRenderAttribute( 'vamtam-logged-in-btn-url', 'href', [ settings.logged_in_button_url.url ] );

					var btn_html = '<a ' + view.getRenderAttributeString( 'vamtam-logged-in-btn' ) + view.getRenderAttributeString( 'vamtam-logged-in-btn-url' ) + '>' + btnText + '</a>';

					print( btn_html );
				}

				function renderLoggedInMessage() {
					if ( ! settings.show_logged_in_message ) {
						return;
					}

					var msg_html = `
						<div class="elementor-login elementor-login__logged-in-message">
							<?php echo sprintf( __( 'You are Logged in as %1$s (<a href="#">Logout</a>)', 'vamtam-elementor-integration' ), wp_get_current_user()->display_name ); ?>
						</div>`;

					print( msg_html );
				}

				function renderLoginContent() {
					renderLoginTitle();
					renderLoginText();
				}

				function renderLoggedInContent() {
					renderLoggedInTitle();
					renderLoggedInText();

					if ( settings.show_logged_in_btn ) {
						renderLoggedInButton();
					}

					renderLoggedInMessage();
				}

				if ( settings.show_editor_logged_out_status ) {
					renderLoginContent();
					#>
					<div class="elementor-login elementor-form">
						<div class="elementor-form-fields-wrapper">
							<#
								fieldGroupClasses = 'elementor-field-group elementor-column elementor-col-100 elementor-field-type-text';
							#>
							<div class="{{ fieldGroupClasses }}">
								<# if ( settings.show_labels ) { #>
									<label class="elementor-field-label" for="user" >{{{ settings.user_label }}}</label>
									<# } #>
										<input size="1" type="text" id="user" placeholder="{{ settings.user_placeholder }}" class="elementor-field elementor-field-textual elementor-size-{{ settings.input_size }}" />
							</div>
							<div class="{{ fieldGroupClasses }}">
								<# if ( settings.show_labels ) { #>
									<label class="elementor-field-label" for="password" >{{{ settings.password_label }}}</label>
									<# } #>
										<input size="1" type="password" id="password" placeholder="{{ settings.password_placeholder }}" class="elementor-field elementor-field-textual elementor-size-{{ settings.input_size }}" />
							</div>

							<# if ( settings.show_remember_me ) { #>
								<div class="elementor-field-type-checkbox elementor-field-group elementor-column elementor-col-100 elementor-remember-me">
									<label for="elementor-login-remember-me">
										<input type="checkbox" id="elementor-login-remember-me" name="rememberme" value="forever">
										<?php echo __( 'Remember Me', 'vamtam-elementor-integration' ); ?>
									</label>
								</div>
							<# } #>

							<div class="elementor-field-group elementor-column elementor-field-type-submit elementor-col-100">
								<button type="submit" class="elementor-button elementor-size-{{ settings.button_size }}">
									<# if ( settings.button_text ) { #>
										<span class="elementor-button-text">{{ settings.button_text }}</span>
									<# } #>
								</button>
							</div>

							<# if ( settings.show_lost_password || settings.show_register ) { #>
								<div class="elementor-field-group elementor-column elementor-col-100">
									<# if ( settings.show_lost_password ) { #>
										<a class="elementor-lost-password" href="<?php echo wp_lostpassword_url(); ?>">
											<?php echo __( 'Lost your password?', 'vamtam-elementor-integration' ); ?>
										</a>
									<# } #>

									<?php if ( get_option( 'users_can_register' ) ) { ?>
										<# if ( settings.show_register ) { #>
											<# if ( settings.show_lost_password ) { #>
												<span class="elementor-login-separator"> | </span>
											<# } #>
											<# if ( settings.use_custom_register ) { #>
												<a class="elementor-register" href="{{settings.custom_register_url.url}}">
													<?php echo __( 'Register', 'vamtam-elementor-integration' ); ?>
												</a>
											<# } else { #>
												<a class="elementor-register" href="<?php echo wp_registration_url(); ?>">
													<?php echo __( 'Register', 'vamtam-elementor-integration' ); ?>
												</a>
											<# } #>
										<# } #>
									<?php } ?>
								</div>
							<# } #>
						</div>
					</div>
					<#
				} else {
					renderLoggedInContent();
				} #>
			<?php
		}
	}

	// Replace current tabs widget with our extended version.
	$widgets_manager = \Elementor\Plugin::instance()->widgets_manager;
	$widgets_manager->unregister( 'login' );
	$widgets_manager->register( new Vamtam_Widget_Login );
}
add_action( \Vamtam_Elementor_Utils::get_widgets_registration_hook(), __NAMESPACE__ . '\widgets_registered', 100 );
