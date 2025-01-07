<?php
namespace VamtamElementor\Widgets\MenuCart;

// Extending the Menu Cart widget.

// Is WC Widget.
if ( ! vamtam_has_woocommerce() ) {
	return;
}

// Is Pro Widget.
if ( ! \VamtamElementorIntregration::is_elementor_pro_active() ) {
	return;
}

// Theme Settings.
if ( ! \Vamtam_Elementor_Utils::is_widget_mod_active( 'woocommerce-menu-cart' ) ) {
	return;
}

function render_content( $content, $widget ) {
	if ( 'woocommerce-menu-cart' === $widget->get_name() ) {
		$settings            = $widget->get_settings();
		$show_close_cart_btn = ! empty( $settings[ 'close_cart_button_show' ] );
		$close_cart_btn_icon = isset( $settings[ 'close_cart_icon_svg' ] ) ? $settings[ 'close_cart_icon_svg' ] : [];
		$close_btn_el        = '';
		$regex               = '/<div class="elementor-menu-cart__close-button(-custom)?">.*?<\/div>/s';
		$force_show          = ! \VamtamElementorBridge::elementor_pro_is_v3_12_or_greater(); // before pro v3.12.0 there was no way to hide or change the close icon.

		preg_match_all( $regex, $content, $matches );

		// Remove current close button (we add it in header below).
		$content = preg_replace( $regex, '', $content );

		if ( $show_close_cart_btn || $force_show ) {
			if ( empty( $close_cart_btn_icon[ 'value' ] ) || $force_show ) { // empty value == default icon (we replace with theme default)
		$close_cart_icon = '<svg class="font-h4 vamtam-close vamtam-close-cart" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" version="1.1"><path d="M10 8.586l-7.071-7.071-1.414 1.414 7.071 7.071-7.071 7.071 1.414 1.414 7.071-7.071 7.071 7.071 1.414-1.414-7.071-7.071 7.071-7.071-1.414-1.414-7.071 7.071z"></path></svg>';

		if ( vamtam_theme_supports( 'woocommerce-menu-cart--close-cart-theme-icon' ) ) {
			$close_cart_icon = '<i class="vamtam-close vamtam-close-cart vamtamtheme- vamtam-theme-close"></i>';
		}

				$close_btn_el = '<div class="elementor-menu-cart__close-button">' . $close_cart_icon . '</div>';
			} else {
				$close_btn_el = ! empty( $matches[0] ) ? $matches[0][0] : '';
			}
		}

		// Inject cart header.
		$header  = '<div class="vamtam-elementor-menu-cart__header">
						<span class="font-h4 label">' . esc_html__( 'Cart', 'vamtam-elementor-integration' ) . '</span>
						<span class="font-h4 item-count">(' . esc_html( WC()->cart->get_cart_contents_count() ) . ')</span>
						' . $close_btn_el . '
					</div>';
		$content = str_replace( '<div class="widget_shopping_cart_content', $header . '<div class="widget_shopping_cart_content', $content );
	}
	return $content;
}
// Called frontend & editor (editor after element loses focus).
add_filter( 'elementor/widget/render_content', __NAMESPACE__ . '\render_content', 10, 2 );

function add_controls_style_tab_products_section( $controls_manager, $widget ) {
	// Product Title Color.
	$widget->start_injection( [
		'of' => 'heading_product_title_style',
		'at' => 'before',
	] );
	// Products Spacing.
	\Vamtam_Elementor_Utils::remove_control( $controls_manager, $widget, 'divider_gap' ); // This option would interfere with the spacing.
	$widget->add_control(
		'products_spacing',
		[
			'label' => __( 'Spacing', 'vamtam-elementor-integration' ),
			'type' => $controls_manager::SLIDER,
			'range' => [
				'px' => [
					'min' => 0,
					'max' => 50,
				],
			],
			'selectors' => [
				'{{WRAPPER}} .elementor-menu-cart__container .elementor-menu-cart__main .elementor-menu-cart__product' => 'padding-top: {{SIZE}}{{UNIT}};padding-bottom: {{SIZE}}{{UNIT}}',
			],
		]
	);

	if ( ! \VamtamElementorBridge::elementor_is_v3_or_greater() ) {
		$widget->end_injection();
		// Product Title Color.
		$widget->start_injection( [
			'of' => 'heading_product_title_style',
		] );
		$widget->start_controls_tabs( 'product_title_color_tabs' );
		// Normal
		$widget->start_controls_tab(
			'product_title_color_normal',
			[
				'label' => __( 'Normal', 'vamtam-elementor-integration' ),
			]
		);
		// We have to remove and re-add existing controls so they can be properly inserted into the tabs.
		// Product Title Color.
		$widget->remove_control( 'product_title_color' );
		$widget->add_control(
			'product_title_color',
			[
				'label' => __( 'Color', 'vamtam-elementor-integration' ),
				'type' => $controls_manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-menu-cart__product-name, {{WRAPPER}} .elementor-menu-cart__product-name a' => 'color: {{VALUE}}',

				],
			]
		);
		$widget->end_controls_tab();
		// Hover
		$widget->start_controls_tab(
			'product_title_color_hover_tab',
			[
				'label' => __( 'Hover', 'vamtam-elementor-integration' ),
			]
		);
		// Product Title Hover Color.
		$widget->add_control(
			'product_title_color_hover',
			[
				'label' => __( 'Color', 'vamtam-elementor-integration' ),
				'type' => $controls_manager::COLOR,
				'selectors' => [
					implode( ',', [
						'{{WRAPPER}} .elementor-menu-cart__product-name:hover',
						'{{WRAPPER}} .elementor-menu-cart__product-name a:hover',
						'{{WRAPPER}} .elementor-menu-cart__product-remove:hover svg.vamtam-close',
						'{{WRAPPER}} .elementor-menu-cart__product-remove a:hover svg.vamtam-close',
					] ) => 'color: {{VALUE}}',
				],
			]
		);
		$widget->end_controls_tab();
		$widget->end_controls_tabs();
	}

	$widget->end_injection();
}

function update_controls_style_tab_products_section( $controls_manager, $widget ) {
	if ( \VamtamElementorBridge::elementor_is_v3_or_greater() ) {
		// Product Quantity Color.
		\Vamtam_Elementor_Utils::add_control_options( $controls_manager, $widget, 'product_quantity_color', [
			'selectors' => [
				'{{WRAPPER}} .elementor-menu-cart__container .elementor-menu-cart__product-price.product-price .quantity .vamtam-quantity select' => 'color: {{_RESET_}}',
				'{{WRAPPER}} .elementor-menu-cart__container .elementor-menu-cart__product-price.product-price .quantity .vamtam-quantity svg' => 'color: {{_RESET_}}',
			]
		] );
		// Product Quantity Typography.
		\Vamtam_Elementor_Utils::add_control_options( $controls_manager, $widget, 'product_quantity_typography', [
			'selector' => [
				'{{WRAPPER}} .elementor-menu-cart__product-price.product-price .quantity .vamtam-quantity select,' .
				'{{WRAPPER}} .elementor-menu-cart__product-price.product-price .quantity .vamtam-quantity select option' ,
				]
			],
			\Elementor\Group_Control_Typography::get_type()
		);
		// Product Price Color.
		\Vamtam_Elementor_Utils::add_control_options( $controls_manager, $widget, 'product_price_color', [
			'selectors' => [
				'{{WRAPPER}} .elementor-menu-cart__product-price.product-price .quantity .amount' => 'color: {{_RESET_}}',
			]
		] );
		// Product Price Typography.
		\Vamtam_Elementor_Utils::add_control_options( $controls_manager, $widget, 'product_price_typography', [
			'selector' => [
				'{{WRAPPER}} .elementor-menu-cart__product-price.product-price .quantity .amount',
				]
			],
			\Elementor\Group_Control_Typography::get_type()
		);

		// This is for Gift Card product title.
		// Product Title Color.
		\Vamtam_Elementor_Utils::add_control_options( $controls_manager, $widget, 'product_title_color', [
			'selectors' => [
				'{{WRAPPER}} .elementor-menu-cart__product-name' => '{{_RESET_}}',
			]
		] );
		// This is for Gift Card product title.
		// Product Title Hover Color.
		\Vamtam_Elementor_Utils::add_control_options( $controls_manager, $widget, 'product_title_hover_color', [
			'selectors' => [
				'{{WRAPPER}} .elementor-menu-cart__product-name:hover' => '{{_RESET_}}',
			]
		] );
	} else {
		// Product Title Typography.
		\Vamtam_Elementor_Utils::add_control_options( $controls_manager, $widget, 'product_title_typography', [
			'separator' => 'before',
			],
			\Elementor\Group_Control_Typography::get_type()
		);
		// Product Title Typography Font Size.
		\Vamtam_Elementor_Utils::add_control_options( $controls_manager, $widget, 'product_title_typography_font_size', [
			'selectors' => [
				'{{WRAPPER}} .product-price .vamtam-quantity > select' => '{{_RESET_}}',
			]
		] );
		// Product Title Typography Line Height.
		\Vamtam_Elementor_Utils::add_control_options( $controls_manager, $widget, 'product_title_typography_line_height', [
			'selectors' => [
				'{{WRAPPER}} .product-remove a' => '{{_RESET_}}',
			]
		] );
		// Product Title Color.
		\Vamtam_Elementor_Utils::add_control_options( $controls_manager, $widget, 'product_title_color', [
			'selectors' => [
				implode( ',', [
					'{{WRAPPER}} .product-price .quantity .vamtam-quantity > select',
					'{{WRAPPER}} .product-price .quantity .vamtam-quantity > svg',
				] ) => '{{_RESET_}}',
			]
		] );
		// Product Price Color.
		\Vamtam_Elementor_Utils::add_control_options( $controls_manager, $widget, 'product_price_color', [
			'selectors' => [
				'{{WRAPPER}} .elementor-menu-cart__product-price.product-price .quantity .amount' => 'color: {{_RESET_}}',
			]
		] );
		// Product Price Typography.
		\Vamtam_Elementor_Utils::add_control_options( $controls_manager, $widget, 'product_price_typography', [
			'selector' => [
				'{{WRAPPER}} .elementor-menu-cart__product-price.product-price .quantity .amount,' .
				'{{WRAPPER}} .elementor-menu-cart__product-price.product-price .quantity .vamtam-quantity select,' .
				'{{WRAPPER}} .elementor-menu-cart__product-price.product-price .quantity .vamtam-quantity select option' ,
				]
			],
			\Elementor\Group_Control_Typography::get_type()
		);
		// Divider Width.
		\Vamtam_Elementor_Utils::replace_control_options( $controls_manager, $widget, 'divider_width', [
			'selectors' => [
				implode( ',', [
					'{{WRAPPER}} .elementor-menu-cart__container .elementor-menu-cart__main .elementor-menu-cart__product:not(:last-of-type)',
					'{{WRAPPER}} .elementor-menu-cart__container .elementor-menu-cart__main .elementor-menu-cart__products',
					'{{WRAPPER}} .elementor-menu-cart__container .elementor-menu-cart__main .elementor-menu-cart__subtotal',
					'{{WRAPPER}} .elementor-menu-cart__container .elementor-menu-cart__main .product-price::before',
				] ) => '{{_RESET_}}',
			]
		] );
		// Divider Style.
		\Vamtam_Elementor_Utils::replace_control_options( $controls_manager, $widget, 'divider_style', [
			'selectors' => [
				implode( ',', [
					'{{WRAPPER}} .elementor-menu-cart__container .elementor-menu-cart__main .elementor-menu-cart__product',
					'{{WRAPPER}} .elementor-menu-cart__container .elementor-menu-cart__main .elementor-menu-cart__products',
					'{{WRAPPER}} .elementor-menu-cart__container .elementor-menu-cart__main .elementor-menu-cart__subtotal',
					'{{WRAPPER}} .elementor-menu-cart__container .elementor-menu-cart__main .product-price::before',
				] ) => '{{_RESET_}}',
			]
		] );
		// Divider Color.
		\Vamtam_Elementor_Utils::add_control_options( $controls_manager, $widget, 'divider_color', [
			'selectors' => [
				implode( ',', [
					'{{WRAPPER}} .elementor-menu-cart__container .elementor-menu-cart__main .elementor-menu-cart__product',
					'{{WRAPPER}} .elementor-menu-cart__container .elementor-menu-cart__main .elementor-menu-cart__products',
					'{{WRAPPER}} .elementor-menu-cart__container .elementor-menu-cart__main .elementor-menu-cart__subtotal',
					'{{WRAPPER}} .elementor-menu-cart__container .elementor-menu-cart__main .product-price::before',
				] ) => '{{_RESET_}}',
			]
		] );
	}
}

function update_menu_icon_section_controls( $controls_manager, $widget ) {
	if ( vamtam_theme_supports( 'woocommerce-menu-cart--theme-cart-icon' ) ) {
		// Icon.
		\Vamtam_Elementor_Utils::add_control_options( $controls_manager, $widget, 'icon', [
			'options' => [
				'vamtam-theme' => esc_html__( 'Theme Default', 'vamtam-elementor-integration' ),
			],
		] );
		\Vamtam_Elementor_Utils::replace_control_options( $controls_manager, $widget, 'icon', [
			'default' => 'vamtam-theme',
		] );
	}

	// Hide Emtpy.
	\Vamtam_Elementor_Utils::replace_control_options( $controls_manager, $widget, 'hide_empty_indicator', [
		'condition' => null,
	] );
}

function add_controls_content_tab_section( $controls_manager, $widget ) {
	$widget->add_control(
		'hide_on_wc_cart_checkout',
		[
			'label' => __( 'Hide on Cart/Checkout', 'vamtam-elementor-integration' ),
			'description' => __( 'Hides the menu-card widget on WC\'s Cart & Checkout pages.', 'vamtam-elementor-integration' ),
			'type' => $controls_manager::SWITCHER,
			'prefix_class' => 'vamtam-has-',
			'return_value' => 'hide-cart-checkout',
			'default' => 'hide-cart-checkout',
		]
	);
}

// Content - Menu Icon Section
function section_menu_icon_content_before_section_end( $widget, $args ) {
	$controls_manager = \Elementor\Plugin::instance()->controls_manager;
	add_controls_content_tab_section( $controls_manager, $widget );
	update_menu_icon_section_controls( $controls_manager, $widget );
}
add_action( 'elementor/element/woocommerce-menu-cart/section_menu_icon_content/before_section_end', __NAMESPACE__ . '\section_menu_icon_content_before_section_end', 10, 2 );

function update_cart_section_controls( $controls_manager, $widget ) {
	if ( \VamtamElementorBridge::elementor_pro_is_v3_12_or_greater() ) {
		// Close Icon.
		\Vamtam_Elementor_Utils::add_control_options( $controls_manager, $widget, 'close_cart_button_show', [
			'render_type' => 'template',
		] );

		// Custom Icon.
		\Vamtam_Elementor_Utils::add_control_options( $controls_manager, $widget, 'close_cart_icon_svg', [
			'render_type' => 'template',
		] );
	}
}
// Content - Cart Section
function section_cart_before_section_end( $widget, $args ) {
	$controls_manager = \Elementor\Plugin::instance()->controls_manager;
	update_cart_section_controls( $controls_manager, $widget );
}
add_action( 'elementor/element/woocommerce-menu-cart/section_cart/before_section_end', __NAMESPACE__ . '\section_cart_before_section_end', 10, 2 );

// Style - Products Section
function section_product_tabs_style_before_section_end( $widget, $args ) {
	$controls_manager = \Elementor\Plugin::instance()->controls_manager;
	add_controls_style_tab_products_section( $controls_manager, $widget );
	update_controls_style_tab_products_section( $controls_manager, $widget );
}
add_action( 'elementor/element/woocommerce-menu-cart/section_product_tabs_style/before_section_end', __NAMESPACE__ . '\section_product_tabs_style_before_section_end', 10, 2 );

// Replaces btn section controls & aligns them with those of Button widget.
function replace_button_section_controls( $controls_manager, $widget ) {
	// Remove all section controls.
	\Vamtam_Elementor_Utils::remove_section_controls( $controls_manager, $widget, 'section_style_buttons' );

	// Layout
	$widget->add_responsive_control(
		'buttons_layout',
		[
			'label' => __( 'Layout', 'vamtam-elementor-integration' ),
			'type' => $controls_manager::SELECT,
			'options' => [
				'inline' => __( 'Inline', 'vamtam-elementor-integration' ),
				'stacked' => __( 'Stacked', 'vamtam-elementor-integration' ),
			],
			'default' => 'inline',
			'devices' => [ 'desktop', 'tablet', 'mobile' ],
			'condition' => [
				'view_cart_button_show!' => '',
				'checkout_button_show!' => '',
			],
			'selectors' => [
				'{{WRAPPER}}' => '{{VALUE}}',
			],
			'selectors_dictionary' => [
				'inline' => '--cart-footer-layout: 1fr 1fr;',
				'stacked' => '--cart-footer-layout: 1fr;',
			],
			'prefix_class' => 'elementor-menu-cart--buttons%s-',
		]
	);

	// Space Between
	$widget->add_control(
		'space_between_buttons',
		[
			'label' => __( 'Space Between', 'vamtam-elementor-integration' ),
			'type' => $controls_manager::SLIDER,
			'range' => [
				'px' => [
					'min' => 0,
					'max' => 50,
				],
			],
			'selectors' => [
				'{{WRAPPER}} .elementor-menu-cart__footer-buttons' => 'grid-column-gap: {{SIZE}}{{UNIT}}; grid-row-gap: {{SIZE}}{{UNIT}}',
			],
		]
	);

	// Padding
	$widget->add_responsive_control(
		'footer_buttons_padding',
		[
			'label' => __( 'Padding', 'vamtam-elementor-integration' ),
			'type' => $controls_manager::DIMENSIONS,
			'size_units' => [ 'px', '%' ],
			'allowed_dimensions' => 'vertical',
			'default' => [
				'top' => 20,
				'bottom' => 20,
				'unit' => 'px',
				'isLinked' => true,
			],
			'selectors' => [
				'{{WRAPPER}} .elementor-menu-cart__container .elementor-menu-cart__main .elementor-menu-cart__footer-buttons' => 'padding: {{TOP}}{{UNIT}} 0 {{BOTTOM}}{{UNIT}} 0;',
			],
		]
	);

	// Button widget aligned controls
	foreach ( [ 'view_cart', 'checkout' ] as $prefix ) {
		$class_suffix = str_replace( '_', '-', $prefix );

		$widget->add_control(
			"heading_{$prefix}_button_style",
			[
				'type' => $controls_manager::HEADING,
				'label' => ( $prefix === 'view_cart' ) ? __( 'View Cart', 'vamtam-elementor-integration' ) : __( 'Checkout', 'vamtam-elementor-integration' ),
				'separator' => 'before',
			]
		);

		$widget->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => "{$prefix}_typography",
				'selector' => "{{WRAPPER}} .elementor-button.elementor-button--{$class_suffix}",
			]
		);

		$widget->add_group_control(
			\Elementor\Group_Control_Text_Shadow::get_type(),
			[
				'name' => "{$prefix}_text_shadow",
				'selector' => "{{WRAPPER}} .elementor-button.elementor-button--{$class_suffix}",
			]
		);

		$widget->start_controls_tabs( "{$prefix}_tabs_button_style" );
		$widget->start_controls_tab(
			"{$prefix}_tab_button_normal",
			[
				'label' => __( 'Normal', 'vamtam-elementor-integration' ),
			]
		);

		$widget->add_control(
			"{$prefix}_button_text_color",
			[
				'label' => __( 'Text Color', 'vamtam-elementor-integration' ),
				'type' => $controls_manager::COLOR,
				'default' => '',
				'selectors' => [
					"{{WRAPPER}} .elementor-button.elementor-button--{$class_suffix}" => 'fill: {{VALUE}}; color: {{VALUE}};',
				],
			]
		);

		$widget->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			[
				'name' => "{$prefix}_background",
				'label' => __( 'Background', 'vamtam-elementor-integration' ),
				'types' => [ 'classic', 'gradient' ],
				'exclude' => [ 'image' ],
				'selector' => "{{WRAPPER}} .elementor-button.elementor-button--{$class_suffix}",
				'fields_options' => [
					'background' => [
						'default' => 'classic',
					],
				],
			]
		);

		$widget->end_controls_tab();
		$widget->start_controls_tab(
			"{$prefix}_tab_button_hover",
			[
				'label' => __( 'Hover', 'vamtam-elementor-integration' ),
			]
		);

		$widget->add_control(
			"{$prefix}_hover_color",
			[
				'label' => __( 'Text Color', 'vamtam-elementor-integration' ),
				'type' => $controls_manager::COLOR,
				'selectors' => [
					"{{WRAPPER}} .elementor-button.elementor-button--{$class_suffix}:hover, {{WRAPPER}} .elementor-button.elementor-button--{$class_suffix}:focus" => 'color: {{VALUE}};',
					"{{WRAPPER}} .elementor-button.elementor-button--{$class_suffix}:hover svg, {{WRAPPER}} .elementor-button.elementor-button--{$class_suffix}:focus svg" => 'fill: {{VALUE}};',
				],
			]
		);

		$widget->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			[
				'name' => "{$prefix}_button_background_hover",
				'label' => __( 'Background', 'vamtam-elementor-integration' ),
				'types' => [ 'classic', 'gradient' ],
				'exclude' => [ 'image' ],
				'selector' => "{{WRAPPER}} .elementor-button.elementor-button--{$class_suffix}:hover, {{WRAPPER}} .elementor-button.elementor-button--{$class_suffix}:focus",
				'fields_options' => [
					'background' => [
						'default' => 'classic',
					],
				],
			]
		);

		$widget->add_control(
			"{$prefix}_button_hover_border_color",
			[
				'label' => __( 'Border Color', 'vamtam-elementor-integration' ),
				'type' => $controls_manager::COLOR,
				'condition' => [
					"{$prefix}_border_border!" => '',
				],
				'selectors' => [
					"{{WRAPPER}} .elementor-button.elementor-button--{$class_suffix}:hover, {{WRAPPER}} .elementor-button.elementor-button--{$class_suffix}:focus" => 'border-color: {{VALUE}};',
				],
			]
		);

		// TODO: If we need hover_anims, we need widget class extension.
		// $widget->add_control(
		// 	"{$prefix}_hover_animation",
		// 	[
		// 		'label' => __( 'Hover Animation', 'vamtam-elementor-integration' ),
		// 		'type' => $controls_manager::HOVER_ANIMATION,
		// 	]
		// );

		$widget->end_controls_tab();
		$widget->end_controls_tabs();

		$widget->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => "{$prefix}_border",
				'selector' => "{{WRAPPER}} .elementor-button.elementor-button--{$class_suffix}",
				'separator' => 'before',
			]
		);

		$widget->add_control(
			"{$prefix}_border_radius",
			[
				'label' => __( 'Border Radius', 'vamtam-elementor-integration' ),
				'type' => $controls_manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					"{{WRAPPER}} .elementor-button.elementor-button--{$class_suffix}" => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$widget->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			[
				'name' => "{$prefix}_button_box_shadow",
				'selector' => "{{WRAPPER}} .elementor-button.elementor-button--{$class_suffix}",
			]
		);

		$widget->add_responsive_control(
			"{$prefix}_text_padding",
			[
				'label' => __( 'Padding', 'vamtam-elementor-integration' ),
				'type' => $controls_manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .elementor-button.elementor-button--view-cart' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'separator' => 'before',
			]
		);
	}
}

// Style - Buttons section
function section_style_buttons_before_section_end( $widget, $args ) {
	$controls_manager = \Elementor\Plugin::instance()->controls_manager;
	replace_button_section_controls( $controls_manager, $widget );
}
add_action( 'elementor/element/woocommerce-menu-cart/section_style_buttons/before_section_end', __NAMESPACE__ . '\section_style_buttons_before_section_end', 10, 2 );

function update_controls_style_tab_cart_section( $controls_manager, $widget ) {
	// Subtotal Color.
	\Vamtam_Elementor_Utils::replace_control_options( $controls_manager, $widget, 'subtotal_color', [
		'selectors' => [
			'{{WRAPPER}}.elementor-widget-woocommerce-menu-cart .elementor-menu-cart__container .elementor-menu-cart__main .elementor-menu-cart__subtotal' => '{{_RESET_}}',
		]
	] );
	// Subtotal Typography.
	\Vamtam_Elementor_Utils::replace_control_options( $controls_manager, $widget, 'subtotal_typography', [
		'selector' => '{{WRAPPER}}.elementor-widget-woocommerce-menu-cart .elementor-menu-cart__container .elementor-menu-cart__main .elementor-menu-cart__subtotal',
		],
		\Elementor\Group_Control_Typography::get_type()
	);
	// Subtotal Typography Font Weight.
	\Vamtam_Elementor_Utils::replace_control_options( $controls_manager, $widget, 'subtotal_typography_font_weight', [
		'selectors' => [
			'{{WRAPPER}} .elementor-menu-cart__subtotal strong' => '{{_RESET_}}',
		]
	] );
}
// Style - Cart section
function section_cart_style_before_section_end( $widget, $args ) {
	$controls_manager = \Elementor\Plugin::instance()->controls_manager;
	update_controls_style_tab_cart_section( $controls_manager, $widget );
}
add_action( 'elementor/element/woocommerce-menu-cart/section_cart_style/before_section_end', __NAMESPACE__ . '\section_cart_style_before_section_end', 10, 2 );

// Before render (all widgets).
function menu_cart_before_render( $widget ) {
    $widget_name = $widget->get_name();

    if ( $widget->get_name() === 'global' ) {
        $widget_name = $widget->get_original_element_instance()->get_name();
    }

    if ( 'woocommerce-menu-cart' === $widget_name ) {
		$hide_empty = ! empty( $widget->get_settings( 'hide_empty_indicator' ) );
        if ( $hide_empty && WC()->cart->get_cart_contents_count() === 0 ) {
			// Add hidden class to wrapper element.
			$widget->add_render_attribute( '_wrapper',  'class', 'hidden' );
		}
    }
}
add_action( 'elementor/frontend/widget/before_render', __NAMESPACE__ . '\menu_cart_before_render', 10, 1 );

/* WC Filters */

// Cart quantity override.
function vamtam_woocommerce_widget_cart_item_quantity( $content, $cart_item_key, $cart_item ) {
	if ( \VamtamElementorBridge::is_elementor_active() ) {
		// Elementor's filter has different args order.
		if ( ! isset( $cart_item['data'] ) && isset( $cart_item_key['data'] ) ) {
			$temp          = $cart_item_key;
			$cart_item_key = $cart_item;
			$cart_item     = $temp;
		}
	}
	$_product  = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
	$only_one_allowed  = $_product->is_sold_individually();

	// Attrs needed in cart (for variant quantities) but not menu cart.
	$select_cart_attrs = '';
	if ( ! $only_one_allowed && is_cart() ) {
		$select_cart_attrs = 'name="cart[' . esc_attr( $cart_item_key ) . '][qty]" value="' . esc_attr( $cart_item['quantity'] ) . '" title="' . esc_attr__( 'Qty', 'wpv' ) . '" min="0" max="' . esc_attr( $_product->get_max_purchase_quantity() ) . '"';
	}

	$max_product_quantity = $_product->get_stock_quantity();
	if ( ! isset( $max_product_quantity ) ) {
		if ( $_product->get_max_purchase_quantity() === -1 ) {
			// For product that don't adhere to stock_quantity, provide a default max-quantity.
			// This will be used for the number of options inside the quantity <select>.
			$max_product_quantity = apply_filters( 'vamtam_cart_item_max_quantity', 10 );
		} else {
			$max_product_quantity = $_product->get_max_purchase_quantity();
		}
	}

	// Inject select for quantity.
	$select = '<div class="vamtam-quantity"' . ( $only_one_allowed ? ' disabled ' : '' ) . '>';

	if ( vamtam_extra_features() ) {
		$select .= '<svg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 320 512\' focusable=\'false\' aria-hidden=\'true\'><path fill="currentColor" d="M143 352.3L7 216.3c-9.4-9.4-9.4-24.6 0-33.9l22.6-22.6c9.4-9.4 24.6-9.4 33.9 0l96.4 96.4 96.4-96.4c9.4-9.4 24.6-9.4 33.9 0l22.6 22.6c9.4 9.4 9.4 24.6 0 33.9l-136 136c-9.2 9.4-24.4 9.4-33.8 0z"/></svg>
				<select ' . ( $only_one_allowed ? 'disabled' : $select_cart_attrs ) . ' data-product_id="' . esc_attr( $cart_item['product_id'] ) . '" data-cart_item_key="' . esc_attr( $cart_item_key ) . '">';

		for ( $quantity = 1; $quantity <= ( $only_one_allowed ? 1 : $max_product_quantity ); $quantity++ ) {
			$select .= '<option ' . selected( $cart_item['quantity'], $quantity, false ) . "value='$quantity'>$quantity</option>";
			if ( $quantity >= $max_product_quantity ) {
				break;
			}
		}

		if ( $cart_item['quantity'] > $max_product_quantity ) {
			$select .= '<option selected value=' . $cart_item['quantity'] . '>' . $cart_item['quantity'] . '</option>';
		}

		$select .= '</select></div>';
	} else {
		$select = woocommerce_quantity_input(
			array(
				'input_name'   => "cart[{$cart_item_key}][qty]",
				'input_value'  => $cart_item['quantity'],
				'max_value'    => $_product->get_max_purchase_quantity(),
				'min_value'    => '0',
				'product_name' => $_product->get_name(),
			),
			$_product,
			false
		);
	}

	if ( vamtam_extra_features() ) {
		if ( \VamtamElementorBridge::elementor_pro_is_v3_4_or_greater() ) {
			$content = preg_replace( '/<span class="quantity"><span class="product-quantity">(\d+)/', '<span class="quantity">' .$select, $content );
			// Remove the "x" symbol and a closing </span> tag.
			$content = str_replace( ' &times;</span>', '', $content );
		} else {
			$content = preg_replace( '/<span class="quantity">(\d+)/', '<span class="quantity">' .$select, $content );
			// Remove the "x" symbol.
			$content = str_replace( ' &times; ', '', $content );
		}
	} else {
		$content = preg_replace( '#</div>#', $content, $select, 1 ) . '</div>';
	}

	return $content;
}
// Elementor menu cart widget, quantity override.
add_filter( 'woocommerce_widget_cart_item_quantity', __NAMESPACE__ . '\vamtam_woocommerce_widget_cart_item_quantity', 10, 3 );

/* Menu Cart - Ajax Actions */

// Remove product in the menu cart using ajax
function vamtam_ajax_menu_cart_product_remove() {
	if ( is_cart() ) {
		// It's cart page.
		return;
	}

	foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
		if( $cart_item['product_id'] == $_POST['product_id'] && $cart_item_key == $_POST['cart_item_key'] ) {
			WC()->cart->remove_cart_item( $cart_item_key );
		}
	}

	WC()->cart->calculate_totals();
	WC()->cart->maybe_set_cart_cookies();

	// Fragments and mini cart are returned
	\WC_AJAX::get_refreshed_fragments();
}
// Ajax hooks for product remove from menu cart.
add_action( 'wp_ajax_product_remove', __NAMESPACE__ . '\vamtam_ajax_menu_cart_product_remove' );
add_action( 'wp_ajax_nopriv_product_remove', __NAMESPACE__ . '\vamtam_ajax_menu_cart_product_remove' );

// Update product quantity from menu cart.
function vamtam_ajax_update_item_from_menu_cart() {
	if ( is_cart() ) {
		// It's cart page.
		return;
	}

	$quantity = (int) $_POST['product_quantity'];

	foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
		if( $cart_item['product_id'] == $_POST['product_id'] && $cart_item_key == $_POST['cart_item_key'] ) {
			WC()->cart->set_quantity( $cart_item_key, $quantity );
		}
	}

	WC()->cart->calculate_totals();
	WC()->cart->maybe_set_cart_cookies();

	// Fragments and mini cart are returned
	\WC_AJAX::get_refreshed_fragments();
}
// Ajax hooks for updating product quantity from menu cart.
add_action('wp_ajax_update_item_from_cart', __NAMESPACE__ . '\vamtam_ajax_update_item_from_menu_cart');
add_action('wp_ajax_nopriv_update_item_from_cart', __NAMESPACE__ . '\vamtam_ajax_update_item_from_menu_cart');
