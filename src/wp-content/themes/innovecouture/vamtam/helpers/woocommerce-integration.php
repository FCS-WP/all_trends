<?php

/**
 * WooCommerce-related functions and filters
 *
 * @package vamtam/innovecouture
 */

if ( vamtam_has_woocommerce() || apply_filters( 'vamtam_force_dropdown_cart', false ) ) {
	/**
	 * Retrieve page ids - used for myaccount, edit_address, shop, cart, checkout, pay, view_order, terms. returns -1 if no page is found.
	 *
	 * @param string $page Page slug.
	 * @return int
	 */
	function vamtam_wc_get_page_id( $page ) {
		if ( 'pay' === $page || 'thanks' === $page ) {
			wc_deprecated_argument( __FUNCTION__, '2.1', 'The "pay" and "thanks" pages are no-longer used - an endpoint is added to the checkout instead. To get a valid link use the WC_Order::get_checkout_payment_url() or WC_Order::get_checkout_order_received_url() methods instead.' );

			$page = 'checkout';
		}
		if ( 'change_password' === $page || 'edit_address' === $page || 'lost_password' === $page ) {
			wc_deprecated_argument( __FUNCTION__, '2.1', 'The "change_password", "edit_address" and "lost_password" pages are no-longer used - an endpoint is added to the my-account instead. To get a valid link use the wc_customer_edit_account_url() function instead.' );

			$page = 'myaccount';
		}

		$page = apply_filters( 'woocommerce_get_' . $page . '_page_id', get_option( 'woocommerce_' . $page . '_page_id' ) );

		return $page ? absint( $page ) : -1;
	}

	/**
	 * Retrieve page permalink.
	 *
	 * @param string      $page page slug.
	 * @param string|bool $fallback Fallback URL if page is not set. Defaults to home URL. @since 3.4.0.
	 * @return string
	 */
	function vamtam_wc_get_page_permalink( $page, $fallback = null ) {
		$page_id   = vamtam_wc_get_page_id( $page );
		$permalink = 0 < $page_id ? get_permalink( $page_id ) : '';

		if ( ! $permalink ) {
			$permalink = is_null( $fallback ) ? get_home_url() : $fallback;
		}

		return apply_filters( 'woocommerce_get_' . $page . '_page_permalink', $permalink );
	}

	function vamtam_wc_get_cart_url() {
		return apply_filters( 'woocommerce_get_cart_url', vamtam_wc_get_page_permalink( 'cart' ) );
	}

	function vamtam_woocommerce_cart_dropdown() {
		get_template_part( 'templates/cart-dropdown' );
	}
	add_action( 'vamtam_header_cart', 'vamtam_woocommerce_cart_dropdown' );

	if ( ! function_exists( 'vamtam_is_wc_archive' ) ) {
		function vamtam_is_wc_archive() {
			$is_shop_sub_page = strpos( $_SERVER['REQUEST_URI'], '/shop/' ) === 0;
			return is_shop() || is_product_taxonomy() || $is_shop_sub_page;
		}
	}

	if ( ! vamtam_has_woocommerce() ) {
		// shim for the cart fragments script
		function vamtam_wc_cart_fragments_shim() {
			wp_localize_script( 'vamtam-all', 'wc_cart_fragments_params', [
				'ajax_url'        => admin_url( 'admin-ajax.php' ),
				'wc_ajax_url'     => esc_url_raw( apply_filters( 'woocommerce_ajax_get_endpoint', add_query_arg( 'wc-ajax', '%%endpoint%%', remove_query_arg( array( 'remove_item', 'add-to-cart', 'added-to-cart', 'order_again', '_wpnonce' ), home_url( '/', 'relative' ) ) ), '%%endpoint%%' ) ),
				'cart_hash_key'   => apply_filters( 'woocommerce_cart_hash_key', 'wc_cart_hash_' . md5( get_current_blog_id() . '_' . get_site_url( get_current_blog_id(), '/' ) . get_template() ) ),
				'fragment_name'   => apply_filters( 'woocommerce_cart_fragment_name', 'wc_fragments_' . md5( get_current_blog_id() . '_' . get_site_url( get_current_blog_id(), '/' ) . get_template() ) ),
				'request_timeout' => 5000,
				'jspath'          => plugins_url( 'woocommerce/assets/js/frontend/cart-fragments.min.js' ),
				'csspath'         => plugins_url( 'woocommerce/assets/css/woocommerce.css' ),
			] );
		}
		add_action( 'wp_enqueue_scripts', 'vamtam_wc_cart_fragments_shim', 9999 );
	}
}

// TODO: Turn this into a class for better readability.

// Only use our pagination for fallback.
if ( ! vamtam_extra_features() ) {
	// We may want to add a separate master toggle for this.
	if ( ! function_exists( 'woocommerce_pagination' ) ) {
		// replace the default pagination with ours
		function woocommerce_pagination() {
			$query = null;

			$base = esc_url_raw( add_query_arg( 'product-page', '%#%', false ) );
			$format = '?product-page=%#%';

			if ( ! wc_get_loop_prop( 'is_shortcode' ) ) {
				$format = '';
				$base   = esc_url_raw( str_replace( 999999999, '%#%', remove_query_arg( 'add-to-cart', get_pagenum_link( 999999999, false ) ) ) );
			}

			if ( isset( $GLOBALS['woocommerce_loop'] ) ) {
				$query = (object)[
					'max_num_pages' => wc_get_loop_prop( 'total_pages' ),
					'query_vars'    => [
						'paged' => wc_get_loop_prop( 'current_page' )
					],
				];
			}

			echo VamtamTemplates::pagination_list( $query, $format, $base ); // xss ok
		}
	}
}

if ( vamtam_has_woocommerce() ) {
	// we have woocommerce

	remove_action( 'woocommerce_checkout_terms_and_conditions', 'wc_terms_and_conditions_page_content', 30 );
	add_action( 'woocommerce_checkout_terms_and_conditions', 'vamtam_wc_terms_and_conditions_page_content', 30 );

	function vamtam_wc_terms_and_conditions_page_content() {
		$terms_page_id = wc_terms_and_conditions_page_id();

		if ( ! $terms_page_id ) {
			return;
		}

		$page = get_post( $terms_page_id );

		$content = preg_replace( '@<(script|style)[^>]*?>.*?</\\1>@si', '', $page->post_content );

		if ( $page && 'publish' === $page->post_status && $page->post_content && ! has_shortcode( $page->post_content, 'woocommerce_checkout' ) ) {
			echo '<div class="woocommerce-terms-and-conditions" style="display: none; max-height: 200px; overflow: auto;">' . wc_format_content( wp_kses_post( $content ) ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	add_filter( 'wcml_load_multi_currency_in_ajax', '__return_true', 1000 );

	// Include WC theme funcionality based on site settings.
	function vamtam_check_include_wc_hooks() {
		// General purpose. (Always enabled).
		vamtam_wc_general_hooks();

		if ( vamtam_extra_features() ) {
			if ( VamtamElementorBridge::is_elementor_active() && \Vamtam_Elementor_Utils::is_widget_mod_active( 'woocommerce-cart' ) ) {
				add_action( 'woocommerce_before_cart', 'vamtam_woocommerce_before_cart', 100 );
			}
		}

		if ( vamtam_extra_features() ) {
			if ( VamtamElementorBridge::is_elementor_active() && \Vamtam_Elementor_Utils::is_widget_mod_active( 'woocommerce-cart' ) ) {
				add_action( 'woocommerce_before_cart', 'vamtam_woocommerce_before_cart', 100 );
			}
		}
	}

	if ( is_admin() ) {
		// Editor.
		add_action( 'init', 'vamtam_check_include_wc_hooks', 10 );
	} else {
		// Frontend.
		add_action( 'wp', 'vamtam_check_include_wc_hooks', 10 );
	}

	function vamtam_woocommerce_before_cart() {
		echo '<h5>';
		esc_html_e( 'My Bag', 'innovecouture' );
		echo '</h5>';
	}

	function vamtam_wc_general_hooks() {
		// remove the WooCommerce breadcrumbs
		remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20,0 );

		// remove the WooCommerve sidebars
		remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );

		function vamtam_woocommerce_body_class( $class ) {
			if ( is_cart() || is_checkout() || is_account_page() ) {
				$class[] = 'woocommerce';
			}

			return $class;
		}
		add_action( 'body_class', 'vamtam_woocommerce_body_class' );

		add_filter( 'woocommerce_product_description_heading', '__return_false' );
		add_filter( 'woocommerce_show_page_title', '__return_false' );

		if ( defined( 'WOOSW_VERSION' ) && ! function_exists( 'vamtam_print_woosw_button' ) ) {
			function vamtam_woosw_button_add_loading_class( $html ) {
				wp_enqueue_style( 'elementor-icons-fa-solid' );
				wp_enqueue_style( 'elementor-icons-fa-regular' );

				return str_replace( 'class="', 'class="vamtam-loading ', $html );
			}

			if ( ! wp_doing_ajax() ) {
				add_filter( 'woosw_button_html', 'vamtam_woosw_button_add_loading_class' );
			}

			function vamtam_print_woosw_buttons_ajax() {
				$result = [];

				foreach( $_POST['id'] as $id ) {
					$id = intval( $id );

					$result[ $id ] = do_shortcode( '[woosw id="' . $id . '"]' );
				}

				echo json_encode( $result );

				exit;
			}

			add_action( 'wp_ajax_nopriv_vamtam_get_woosw_buttons', 'vamtam_print_woosw_buttons_ajax' );
			add_action( 'wp_ajax_vamtam_get_woosw_buttons', 'vamtam_print_woosw_buttons_ajax' );

			function vamtam_print_woosw_button() {
				echo do_shortcode( '[woosw]' );
			}

			add_action( 'woocommerce_after_add_to_cart_button', 'vamtam_print_woosw_button' );

			function vamtam_paypal_button_widget_content( $widget_content, $widget ) {
				if ( 'paypal-button' === $widget->get_name() ) {
					return $widget_content . do_shortcode( '[woosw]' );
				}

				return $widget_content;

			}
			add_filter( 'elementor/widget/render_content', 'vamtam_paypal_button_widget_content', 10, 2 );

			// Confirm that all wishlist products exist
			add_filter( 'option_woosw_list_' . WPCleverWoosw::get_key(), 'vamtam_wishlist_fix' );
			function vamtam_wishlist_fix( $products ) {
				$option_name = 'woosw_list_' . WPCleverWoosw::get_key();

				if ( is_array( $products ) && count( $products ) > 0 ) {
					foreach ( $products as $product_id => $product_data ) {
						if ( get_post( $product_id ) === null ) {
							unset( $products[ $product_id ]);
						}
					}

					remove_filter( current_filter(), 'vamtam_wishlist_fix' );

					update_option( $option_name, $products );
				}

				return $products;
			}

		}

		function vamtam_woosw_after_items( $key, $products ) {
			if ( !! $products ) {
				echo '<div class="vamtam-empty-wishlist-notice">';
				echo '<div class="woosw-content-mid-notice">';

				$str = '';

				if ( ! empty( WPCleverWoosw::$localization[ 'empty_message' ] ) ) {
					$str = WPCleverWoosw::$localization[ 'empty_message' ];
				} else {
					$str = esc_html__( 'There are no products on the wishlist!', 'innovecouture' );
				}

				echo apply_filters( 'woosw_localization_empty_message', $str );

				echo '</div>';
			}

			echo '
				<svg class="vamtam-empty-wishlist-icon" width="96" height="96" viewBox="0 0 96 96" xmlns="http://www.w3.org/2000/svg">
					<g fill="none" fill-rule="evenodd">
					<path d="M0 0h96v96H0z"/>
					<path d="M48 11.25c7.04 0 12.75 5.7 12.75 12.75v7.25h9.6a8.75 8.75 0 0 1 8.66 10.08l-5.02 32.6a12.75 12.75 0 0 1-12.6 10.82H33.96c-6.29 0-11.64-4.6-12.6-10.81l-5.02-32.61A8.75 8.75 0 0 1 25 31.25h10.25V24c0-7.04 5.7-12.75 12.75-12.75Zm22.36 21.5h-9.61V44a.75.75 0 1 1-1.5 0V32.75h-22.5V44a.75.75 0 1 1-1.5 0V32.75H25a7.25 7.25 0 0 0-7.17 8.35l5.02 32.61a11.25 11.25 0 0 0 11.12 9.54h27.41c5.56 0 10.28-4.05 11.12-9.54l5.02-32.6a7.25 7.25 0 0 0-7.16-8.36ZM47.42 53.02l.08.09.09-.09a6.8 6.8 0 0 1 5.63-1.86l.26.04a6.8 6.8 0 0 1 3.6 11.53l-.04.04-9.01 8.93a.75.75 0 0 1-.97.07l-.09-.07-9.06-8.97a6.8 6.8 0 1 1 9.5-9.7Zm-5.66-.38a5.3 5.3 0 0 0-2.8 9.02l8.54 8.45 8.53-8.45.04-.04.14-.13a5.3 5.3 0 0 0 1.24-4.94l-.07-.24a5.3 5.3 0 0 0-9.28-1.6c-.3.4-.9.4-1.2 0a5.3 5.3 0 0 0-5.14-2.07ZM48 12.75A11.25 11.25 0 0 0 36.75 24v7.25h22.5V24c0-6.21-5.04-11.25-11.25-11.25Z" fill="#000" fill-rule="nonzero"/>
					<path d="M33 47h29v29H33z"/>
					</g>
				</svg>
			';
			echo '<p class="vamtam-look-for-heart">';
			esc_html_e( 'Look for the heart to save favorites while you shop.', 'innovecouture' );
			echo '</p>';
			echo '<form><button class="vamtam-start-shopping" formaction="' . esc_attr( get_permalink( wc_get_page_id( 'shop' ) ) ) . '">' . esc_html__( 'Start Shopping', 'innovecouture' ) . '</button></form>';

			if ( !! $products ) {
				echo '</div>';
			}
		}

		add_action( 'woosw_after_items', 'vamtam_woosw_after_items', 10, 2 );

		if ( apply_filters( 'vamtam_woosw_use_defaults', true ) ) {
			$woosw_hardcoded = [
				'woosw_button_action' => 'no',
				'woosw_button_action_added' => 'page',
				'woosw_button_class' => '',
				'woosw_button_position_archive' => '0',
				'woosw_button_position_single' => '0',
				'woosw_button_text' => '',
				'woosw_button_text_added' => '',
				'woosw_button_type' => 'button',
				'woosw_color' => '#5fbd74',
				'woosw_continue_url' => '',
				'woosw_empty_button' => 'yes',
				'woosw_link' => 'yes',
				'woosw_menu_action' => 'open_page',
				'woosw_menus' => '',
				'woosw_page_copy' => 'no',
				'woosw_page_icon' => 'no',
				'woosw_page_items' => array (
					0 => 'facebook',
					1 => 'twitter',
					2 => 'pinterest',
					3 => 'mail',
				),
				'woosw_page_share' => 'no',
				'woosw_perfect_scrollbar' => 'yes',
				'woosw_show_note' => 'no',
			];

			foreach ( $woosw_hardcoded as $option_name => $option_value ) {
				add_filter( "option_{$option_name}", function ( $value, $option ) use ( $woosw_hardcoded ) {
					return $woosw_hardcoded[ $option ];
				}, 10, 2 );
			}
		}

		function vamtam_woocommerce_product_thumbnails() {
			wp_enqueue_script( 'vamtam-wc-gallery' );
		}
		add_action( 'woocommerce_product_thumbnails', 'vamtam_woocommerce_product_thumbnails' );

		// Cart quantity override.
		function vamtam_woocommerce_cart_item_quantity( $content, $cart_item_key, $cart_item ) {
			if ( VamtamElementorBridge::is_elementor_active() ) {
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
				$select_cart_attrs = 'name="cart[' . esc_attr( $cart_item_key ) . '][qty]" value="' . esc_attr( $cart_item['quantity'] ) . '" title="' . esc_attr__( 'Qty', 'innovecouture' ) . '" min="0" max="' . esc_attr( $_product->get_max_purchase_quantity() ) . '"';
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
				$content = preg_replace( '/<span class="quantity">(\d+)/', '<span class="quantity">' .$select, $content );
				// Remove the "x" symbol.
				$content = str_replace( ' &times; ', '', $content );
			} else {
				$content = $select;
			}

			return $content;
		}

		add_filter( 'woocommerce_cart_item_quantity', 'vamtam_woocommerce_cart_item_quantity', 10, 3 );

		function vamtam_woocommerce_cart_item_remove_link( $content, $cart_item_key ) {
			$needle = '</a>'; // Default is for menu-cart.

			if ( is_cart() ) {
				$needle = '&times;</a>'; // Cart page, no menu-cart.
			}

			// Inject our close icon.
			$content = str_replace(
				$needle,
				'<i class="vamtam-remove-product"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 17 19" width="17px"><g fill="#BFBDBB" fill-rule="nonzero"><path d="M16.227 3.03h-5l-.433-1.87c-.086-.37-.394-.63-.749-.63h-3.09c-.355 0-.664.26-.75.632L5.771 3.03H.773c-.427 0-.773.373-.773.833v.834c0 .46.346.833.773.833h15.454c.427 0 .773-.373.773-.833v-.834c0-.46-.346-.833-.773-.833ZM15 6.53H3v9.75a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 15 16.28V6.53Z"/></g></svg></i></a>',
				$content );

			return $content;
		}
		add_filter( 'woocommerce_cart_item_remove_link', 'vamtam_woocommerce_cart_item_remove_link', 10, 2 );

		// WC Form Fields filtering (works in conjuction with vamtam_woocommerce_form_field())
		function vamtam_woocommerce_form_field_args( $args, $key, $value ) {
			if ( VamtamElementorBridge::is_elementor_active() ) {
				if ( $args['type'] === 'select' || $args['type'] === 'country' || $args['type'] === 'state' ) {
					$args['input_class'][] = 'elementor-field-textual';
					$args['class'][] = 'elementor-field-group';
				}
			}
			return $args;
		}
		add_filter( 'woocommerce_form_field_args', 'vamtam_woocommerce_form_field_args', 10, 3 );

		// WC Form Fields filtering (works in conjuction with vamtam_woocommerce_form_field_args())
		function vamtam_woocommerce_form_field( $field, $key, $args, $value ) {
			if ( VamtamElementorBridge::is_elementor_active() ) {
				if ( $args['type'] === 'select' || $args['type'] === 'country' || $args['type'] === 'state' ) {
					$field = str_replace( 'woocommerce-input-wrapper', 'woocommerce-input-wrapper elementor-select-wrapper', $field );
				}
			}
			return $field;
		}
		add_filter( 'woocommerce_form_field', 'vamtam_woocommerce_form_field', 10, 4 );

		// WC's smallscreen br aligned with site's mobile br.
		function vamtam_woocommerce_style_smallscreen_breakpoint( $px ) {
			$small_breakpoint = VamtamElementorBridge::get_site_breakpoints( 'md' );
			$new_br        = ( $small_breakpoint - 1 ) . 'px';
			return $new_br;
		}
		add_filter( 'woocommerce_style_smallscreen_breakpoint' ,'vamtam_woocommerce_style_smallscreen_breakpoint', 10, 1 );

		// Advanced AJAX Product Filters for WooCommerce integration with Elementor Loop
		if (
			isset( $GLOBALS['wp_filter']['woocommerce_shortcode_products_query'] ) &&
			isset( $GLOBALS['wp_filter']['woocommerce_shortcode_products_query']['900000'] )
		) {
			// find the callback for the WC shortcode filter and add it to Elementor's query args filter
			foreach ( $GLOBALS['wp_filter']['woocommerce_shortcode_products_query']['900000'] as $cb ) {
				if ( is_array( $cb['function'] ) && is_object( $cb['function'][0] ) && get_class( $cb['function'][0] ) === 'BeRocket_url_parse_page' ) {
					add_filter( 'elementor/query/query_args', $cb['function'], 900000, 1 );
					break;
				}
			}
		}

		if ( vamtam_extra_features() &&  VamtamElementorBridge::is_elementor_active() ) {
			// Custom sorting for products.
			if ( ! function_exists( 'vamtam_products_ordering' ) && \Vamtam_Elementor_Utils::is_wc_mod_active( 'wc_custom_products_ordering' ) ) {
				function vamtam_products_ordering() {
					if ( ! isset( $GLOBALS['vamtam_has_catalog_ordering_sc'] ) ) {
						if ( ! wc_get_loop_prop( 'is_paginated' ) || ! woocommerce_products_will_display() ) {
							return;
						}
					}

					// Elementor doesnt allow products ordering on the front page.
					if ( is_front_page() ) {
						return;
					}

					$catalog_orderby_options = apply_filters( 'vamtam_products_filter_order_by', array(
						'menu_order' => __( 'Default', 'innovecouture' ),
						'popularity' => __( 'Popularity', 'innovecouture' ),
						'rating'     => __( 'Average rating', 'innovecouture' ),
						'date'       => __( 'Newest', 'innovecouture' ),
						'price'      => __( 'Price (low to high)', 'innovecouture' ),
						'price-desc' => __( 'Price (high to low)', 'innovecouture' ),
					) );

					$orderby    = ! empty( $_GET['orderby'] ) ? $_GET['orderby'] : '';
					$order_html = $order_current = '';
					$query_args = [];

					// Add current request query args to $query_args.
					if ( ! empty( $_GET ) ) {
						foreach ( $_GET as $key => $value ) {
							$query_args[ $key ] = esc_attr( $value );
						}
					}

					foreach ( $catalog_orderby_options as $id => $name ) {
						// Add orderby to $query_args and create the href url for each sorting option.
						$query_args['orderby'] = esc_attr( $id );

						$url = add_query_arg( $query_args );

						$css_class = '';
						if ( $orderby == $id ) {
							$css_class     = 'active';
							$order_current = $name;
						}

						$order_html .= sprintf(
							'<li><a href="%s" class="woocommerce-ordering__link %s">%s</a></li>',
							esc_url( $url ),
							esc_attr( $css_class ),
							esc_html( $name )
						);
					}

					?>
					<div class="woocommerce-ordering">
						<span class="woocommerce-ordering__button">
							<span class="woocommerce-ordering__button-label">
								<?php
									echo __( 'Sort by:', 'innovecouture' );
									?>
									<span>
										<?php echo ! empty( $orderby ) ? $order_current : esc_html__( 'Default', 'innovecouture' ); ?>
									</span>
									<?php
								?>
							</span>
								<?php
									\Elementor\Icons_Manager::render_icon( [
										'value' => 'vamtamtheme- vamtam-theme-arrow-down' ,
										'library' => 'theme-icons'
									], [ 'aria-hidden' => 'true' ] );
								?>
							</span>
						<ul class="woocommerce-ordering__submenu">
							<?php echo wp_kses_post( $order_html ); ?>
						</ul>
					</div>
					<?php
				}
			}

			// Product "New" badge.
			if ( \Vamtam_Elementor_Utils::is_wc_mod_active( 'wc_products_new_badge' ) ) {
				// Add field in the product's custom fields (advanced tab).
				function vamtam_woocommerce_product_custom_fields() {
					global $woocommerce, $post;
					echo '<div class="vamtam-wc-product-custom-field">';
					// Is New custom field.
					woocommerce_wp_checkbox(
						array(
							'id'          => '_vamtam_product_is_new',
							'label'       => __( 'New Product', 'innovecouture' ),
							'description' => __( 'Products marked as new will display the "New" badge.', 'innovecouture' ),
						)
					);
					echo '</div>';
				}
				add_action( 'woocommerce_product_options_advanced', 'vamtam_woocommerce_product_custom_fields' );

				// Save custom field's value in the db.
				function vamtam_woocommerce_product_custom_fields_save( $post_id ) {
					$_vamtam_product_is_new = $_POST['_vamtam_product_is_new'];
					update_post_meta( $post_id, '_vamtam_product_is_new', esc_attr( $_vamtam_product_is_new ) );
				}
				add_action( 'woocommerce_process_product_meta', 'vamtam_woocommerce_product_custom_fields_save' );

				// Display the new badge on product single/archive page.
				function vamtam_show_product_new_badge() {
					global $post, $product;

					if ( ! $post || empty( $post ) || ! $product || empty( $product ) ) {
						return;
					}

					$_vamtam_product_is_new = $product->get_meta( '_vamtam_product_is_new' );
					?>
					<?php if ( ! empty( $_vamtam_product_is_new ) ) : ?>

						<?php
							$classes =  'vamtam-new';
							if ( $product->is_on_sale() ) {
								$classes .= ' vamtam-onsale';
							}

							echo apply_filters( 'vamtam_wc_new_badge', '<span class="' . esc_attr( $classes ) .'">' . esc_html__( 'New!', 'innovecouture' ) . '</span>', $post, $product ); ?>

						<?php
					endif;
				}
				// Loop.
				add_action( 'woocommerce_before_shop_loop_item_title', 'vamtam_show_product_new_badge', 9 );
				// Single.
				add_action( 'woocommerce_before_single_product_summary', 'vamtam_show_product_new_badge', 9 );
				// Single Elementor - For elementor template this is handled in product-images widget render().
				add_action( 'vamtam_display_product_new_badge', 'vamtam_show_product_new_badge', 10 );

				// New Badge Shortcode.
				function vamtam_product_new_badge_shortcode() {
					if ( ! \Elementor\Plugin::$instance->editor->is_edit_mode() && ! \Elementor\Plugin::$instance->preview->is_preview_mode() ) {
						vamtam_show_product_new_badge();
					}
				}
				add_shortcode('vamtam_product_new_badge', 'vamtam_product_new_badge_shortcode');
			}

			// Products Gallery in Loop.
			if ( \Vamtam_Elementor_Utils::is_wc_mod_active( 'wc_products_gallery_in_loop' ) ) {
				function vamtam_open_thumb_wrapper() {
					global $product;
					$product_gallery = $product->get_gallery_image_ids();

					if ( ! empty( $product_gallery ) ) {
						echo '<div class="vamtam-thumb-wrapper">'; // Open thumb-wrapper
					}
				}
				add_action( 'woocommerce_before_shop_loop_item_title', 'vamtam_open_thumb_wrapper', 9 );
				// Include the gallery on product loop.
				function vamtam_product_loop_gallery() {
					global $product;
					$product_gallery = $product->get_gallery_image_ids();
					$featured_image_id = get_post_thumbnail_id( $product->get_id() );

					if ( ! empty( $product_gallery ) ) {
						?>
						<div class="vamtam-product-gallery swiper">
							<div class="vamtam-gallery-wrapper swiper-wrapper">
								<?php
								$product_gallery = $product->get_gallery_image_ids();

								foreach ($product_gallery as $image_id) :
									?>
									<div class="swiper-slide">
										<?php echo wp_get_attachment_image( $image_id, 'woocommerce_thumbnail', false, [ 'class' => 'vamtam-gallery-img' ] ); ?>
									</div>
									<?php
								endforeach;
								// Display the featured image as the last slide of the gallery.
								if ( $featured_image_id ) {
									?>
									<div class="swiper-slide">
										<?php echo wp_get_attachment_image( $featured_image_id, 'woocommerce_thumbnail', false, [ 'class' => 'vamtam-gallery-img' ] ); ?>
									</div>
									<?php
								}
								?>
							</div>
							<div class="swiper-button-prev"></div>
							<div class="swiper-button-next"></div>
						</div>
						<?php
					}
				}
				add_action( 'woocommerce_before_shop_loop_item_title', 'vamtam_product_loop_gallery', 9 );
				function vamtam_close_thumb_wrapper() {
					global $product;
					$product_gallery = $product->get_gallery_image_ids();

					if ( ! empty( $product_gallery ) ) {
						echo '</div>'; // Close thumb-wrapper
					}
				}
				add_action( 'woocommerce_before_shop_loop_item_title', 'vamtam_close_thumb_wrapper', 11 );
			}

			/**
			 * Vamtam WC Results Count - Works with templates & wc-products.
			 * Output the result count text (Showing x - x of x results).
			 */
			function vamtam_woocommerce_result_count_shortcode() {
				if ( ! \Elementor\Plugin::$instance->editor->is_edit_mode() && ! \Elementor\Plugin::$instance->preview->is_preview_mode() ) {
					if ( vamtam_is_wc_archive() ) {
						if ( isset( $GLOBALS['vamtam_wc_result_count_sc_query_data'] ) ) {
							// Custom products loop (template).
							$args = $GLOBALS[ 'vamtam_wc_result_count_sc_query_data' ];

							wc_get_template( 'loop/result-count.php', $args );

							unset( $GLOBALS[ 'vamtam_wc_result_count_sc_query_data' ] );
						} else {
							// Normal WC products loop in shop.
							woocommerce_result_count();
						}
					}
				}
			}
			add_shortcode('vamtam_woocommerce_result_count', 'vamtam_woocommerce_result_count_shortcode');

			// Vamtam WC Catalog Ordering - Works with templates & wc-products.
			function vamtam_woocommerce_catalog_ordering_shortcode() {
				if ( ! \Elementor\Plugin::$instance->editor->is_edit_mode() && ! \Elementor\Plugin::$instance->preview->is_preview_mode() ) {
					if ( vamtam_is_wc_archive() ) {
						$GLOBALS['vamtam_has_catalog_ordering_sc'] = true;
						if ( function_exists( 'vamtam_products_ordering' ) && \Vamtam_Elementor_Utils::is_wc_mod_active( 'wc_custom_products_ordering' ) ) {
							vamtam_products_ordering();
						} else {
							woocommerce_catalog_ordering();
						}
					}
				}
			}
			add_shortcode('vamtam_woocommerce_catalog_ordering', 'vamtam_woocommerce_catalog_ordering_shortcode');

			function vamtam_elementor_shop_page_custom_products_query_args( $query_args ) {
				if ( ! vamtam_is_wc_archive() ) {
					return $query_args;
				}

				$GLOBALS['vamtam_has_custom_products_loop'] = true;

				if ( isset( $GLOBALS['vamtam_has_catalog_ordering_sc'] ) ) {
					// Add ordering args to custom products loop (in shop), compatible with the catalog ordering shortcode

					$default_orderby = wc_get_loop_prop( 'is_search' ) ? 'relevance' : apply_filters( 'woocommerce_default_catalog_orderby', get_option( 'woocommerce_default_catalog_orderby', '' ) );
					// phpcs:disable WordPress.Security.NonceVerification.Recommended
					$orderby = isset( $_GET['orderby'] ) ? wc_clean( wp_unslash( $_GET['orderby'] ) ) : $default_orderby;
					$order   = '';

					if ( $orderby === 'price-desc' ) {
						$orderby = 'price';
						$order   = 'DESC';
					}

					$ordering_args = WC()->query->get_catalog_ordering_args( $orderby, $order );

					$query_args['orderby'] = $ordering_args['orderby'];
					$query_args['order']   = $ordering_args['order'];

					unset( $GLOBALS['vamtam_has_catalog_ordering_sc'] );
				}

				return $query_args;
			}
			add_filter( 'elementor/query/query_args', 'vamtam_elementor_shop_page_custom_products_query_args', 10, 2 );
			add_filter( 'elementor/query/get_query_args/current_query', 'vamtam_elementor_shop_page_custom_products_query_args', 10 );


			function vamtam_elementor_shop_page_custom_products_query_results( $query, $widget ) {
				if ( ! vamtam_is_wc_archive() || ! isset( $GLOBALS['vamtam_has_custom_products_loop'] ) ) {
					return;
				}

				if ( strpos( $widget->get_name(), "loop-" ) === false ) {
					return;
				}

				// Store the data we need for displaying the vamtam_woocommerce_result_count shortcode.
				// Note: The results count shortcode is/should be rendered after the loop-grid/carousel widget, so the query is available.
				$GLOBALS[ 'vamtam_wc_result_count_sc_query_data' ] = [
					'total'    => $query->found_posts,
					'per_page' => $query->query_vars[ 'posts_per_page' ],
					'current'  => max( 1, $query->query_vars[ 'paged' ] ),
				];

				unset( $GLOBALS['vamtam_has_custom_products_loop'] );
			}
			add_action( 'elementor/query/query_results', 'vamtam_elementor_shop_page_custom_products_query_results', 10, 2 );

			if ( \Vamtam_Elementor_Utils::is_widget_mod_active( 'woocommerce-product-images' ) ) {
				function vamtam_woocommerce_single_product_carousel_options( $args ) {
					$args['animation'] = 'fade';
					return $args;
				}

				add_filter(	'woocommerce_single_product_carousel_options', 'vamtam_woocommerce_single_product_carousel_options' );
			}

			// Sale badge shortcode.
			function vamtam_product_sale_badge_shortcode($atts) {
				global $product;

				if ( ! $product || empty( $product ) ) {
					return;
				}

				if ( ! \Elementor\Plugin::$instance->editor->is_edit_mode() && ! \Elementor\Plugin::$instance->preview->is_preview_mode() ) {
					wc_get_template( 'loop/sale-flash.php' );
				}
			}
			add_shortcode('vamtam_product_sale_badge', 'vamtam_product_sale_badge_shortcode');
		}
	}

	if ( vamtam_extra_features() ) {
		// Shipping Progress Bar.
		function vamtam_shipping_progress_bar() {
			class Vamtam_WC_Shipping_Progress_Bar {
				protected static $is_mod_active;
				protected static $show_on_minicart;
				protected static $show_on_cart_page;
				protected static $goal_amount;
				protected static $include_taxes;
				protected static $initial_msg;
				protected static $success_msg;
				protected static $normal_color;
				protected static $success_color;

				public function __construct() {
					add_action( 'elementor/init', [ __CLASS__, 'construct' ] );
				}

				public static function construct() {
					self::$is_mod_active     = ! empty( \Vamtam_Elementor_Utils::is_wc_mod_active( 'shipping_progress_bar_theme_mod' ) );
					self::$show_on_minicart  = self::$is_mod_active ? ! empty( \Vamtam_Elementor_Utils::get_theme_site_settings( "vamtam_theme_wc_spb_show_on_mini_cart" ) ) : false;
					self::$show_on_cart_page = self::$is_mod_active ? ! empty( \Vamtam_Elementor_Utils::get_theme_site_settings( "vamtam_theme_wc_spb_show_on_cart_page" ) ) : false;
					self::$goal_amount       = self::$is_mod_active ? \Vamtam_Elementor_Utils::get_theme_site_settings( "vamtam_theme_wc_spb_goal_amount" ) : 0;
					self::$include_taxes     = self::$is_mod_active ? ! empty( \Vamtam_Elementor_Utils::get_theme_site_settings( "vamtam_theme_wc_spb_include_taxes" ) ) : false;
					self::$initial_msg       = self::$is_mod_active ? \Vamtam_Elementor_Utils::get_theme_site_settings( "vamtam_theme_wc_spb_initial_msg" ) : '';
					self::$success_msg       = self::$is_mod_active ? \Vamtam_Elementor_Utils::get_theme_site_settings( "vamtam_theme_wc_spb_success_msg" ) : '';
					self::$normal_color      = self::$is_mod_active ? \Vamtam_Elementor_Utils::get_theme_site_settings( "vamtam_theme_wc_spb_normal_color" ) : '';
					self::$success_color     = self::$is_mod_active ? \Vamtam_Elementor_Utils::get_theme_site_settings( "vamtam_theme_wc_spb_success_color" ) : '';
					self::handle();
				}

				private static function handle() {
					if ( ! self::$is_mod_active ) {
						return;
					}

					// Register Shortcode.
					add_shortcode ( 'missing_amount', [ __CLASS__, 'vamtam_spb_amount_left_shortcode' ] );

					// Mini cart.
					if ( self::$show_on_minicart ) {
						add_filter( 'woocommerce_add_to_cart_fragments', [ __CLASS__, 'vamtam_progress_bar_atc_fragments' ] );
						add_action( 'woocommerce_mini_cart_contents', [ __CLASS__, 'vamtam_progress_bar_content' ], 9999999 );
						// add_action( 'woocommerce_widget_shopping_cart_before_buttons', [ __CLASS__, 'vamtam_progress_bar_content' ] );
					}

					// Cart page.
					if ( self::$show_on_cart_page ) {
						add_filter( 'woocommerce_add_to_cart_fragments', [ __CLASS__, 'vamtam_progress_bar_atc_fragments' ] );
						add_action( 'woocommerce_before_cart', [ __CLASS__, 'vamtam_progress_bar_placeholder' ] );
					}
				}

				/*
					Progress bar shortcode amount left:
					[missing_amount]
				*/

				public static function vamtam_spb_amount_left_shortcode() {
					$subtotal = WC()->cart->get_subtotal();
					$tax      = WC()->cart->get_subtotal_tax();
					$current  = $subtotal + $tax;

					add_filter( 'woocommerce_price_trim_zeros', '__return_true' );

					$amount_left = wc_price( self::$goal_amount - $current );

					remove_filter( 'woocommerce_price_trim_zeros', '__return_true' );

					return $amount_left;
				}

				public static function vamtam_progress_bar_atc_fragments( $fragments ) {
					$fragments['.vamtam-free-shipping-progress-bar'] = self::vamtam_progress_bar_fragments();
					return $fragments;
				}

				public static function vamtam_progress_bar_fragments() {
					ob_start();

					self::vamtam_progress_bar_content();

					$output = ob_get_clean();

					return $output;
				}


				/* Placeholder, to be updated with cart fragments */
				public static function vamtam_progress_bar_placeholder() {
					?>
					<div class="vamtam-free-shipping-progress-bar bar-placeholder"></div>
					<?php
				}


				public static function vamtam_progress_bar_content() {
					$is_cart_page = is_cart();

					if ( current_action() === 'woocommerce_widget_shopping_cart_before_buttons' ) {
						$g = 1;
					}

					$subtotal = WC()->cart->get_subtotal();
					$percent  = 100;

					if (  self::$include_taxes ) {
						$tax      = WC()->cart->get_subtotal_tax();
						$subtotal = $subtotal + $tax;
					}

					if ( $subtotal < self::$goal_amount ) {
						$percent = floor( ( $subtotal / self::$goal_amount ) * 100 );
					}

					?>

					<?php if ( ! $is_cart_page ) : // On menu cart we close the contents <div> here and let WC close ours, cause we dont have a better hook and Elementor is already overriding the mini-cart template.?>
					</div>
					<?php endif; ?>

					<div class="vamtam-free-shipping-progress-bar" data-progress="<?php echo esc_attr( $percent ); ?>">
						<div class="message">
							<?php
								if ( $percent == 100 ) {
									echo do_shortcode( wp_kses_post( self::$success_msg ) );
								} else {
									echo do_shortcode( wp_kses_post( self::$initial_msg ) );
								}
							?>
						</div>
						<div class="rail">
							<span class="status <?php echo ( $percent >= 100 ) ?  'success' : ''; ?>" style="min-width:<?php echo esc_attr( $percent ); ?>%;">
								<span class="indicator"></span>
								<span class="progress-percent"><?php echo esc_html( $percent ); ?>%</span>
							</span>
							<span class="left"></span>
						</div>
					<?php if ( $is_cart_page ) : ?>
					</div>
					<?php endif; ?>

				<?php
				}

			}
			new Vamtam_WC_Shipping_Progress_Bar();
		}
		vamtam_shipping_progress_bar();
	}

	function vamtam_wc_single_product_ajax_hooks() {
		// Handles ajax add to cart calls.
		function woocommerce_ajax_add_to_cart() {
			new Vamtam_WC_Ajax_Add_To_Cart_Handler();
		}
		// Ajax hooks for add to cart on single products.
		add_action( 'wp_ajax_woocommerce_ajax_add_to_cart', 'woocommerce_ajax_add_to_cart' );
		add_action( 'wp_ajax_nopriv_woocommerce_ajax_add_to_cart', 'woocommerce_ajax_add_to_cart' );


		class Vamtam_WC_Ajax_Add_To_Cart_Handler {
			protected static $product_id;
			protected static $quantity;

			public function __construct() {
				self::$product_id = apply_filters( 'woocommerce_add_to_cart_product_id', absint( $_POST['product_id'] ) );
				self::$quantity   = empty( $_POST['quantity'] ) ? 1 : wc_stock_amount( $_POST['quantity'] );
				self::handle();
			}

			private static function handle() {
				$product_id        = self::$product_id;
				$quantity          = self::$quantity;
				$variation_id      = absint( $_POST['variation_id'] );
				$passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity );
				$product_status    = get_post_status( $product_id );
				$is_valid          = $passed_validation && 'publish' === $product_status;
				$product_added     = false;

				// Don't manually add Bookables as they are already added by WC Bokkings.
				if ( $_POST['is_wc_booking'] && function_exists( 'is_wc_booking_product' ) ) {
					$product       = wc_get_product( $product_id );
					$product_added = is_wc_booking_product( $product );
					if ( $product_added ) {
						$is_valid = true;
					}
				}

				if ( ! $product_added ) {
					if ( isset( $_POST['is_grouped'] ) ) {
						// Grouped products.
						$product_added = self::handle_grouped_products();
					} elseif ( isset( $_POST['is_variable'] ) ) {
						// Variable products
						$product_added = self::handle_variable_products();
					} else {
						// Simple products
						// Add product to cart.
				if ( $is_valid ) {
					$product_added = WC()->cart->add_to_cart( $product_id, $quantity, $variation_id );
				}
					}
				}

				if ( $is_valid && $product_added ) {
					do_action( 'woocommerce_ajax_added_to_cart', $product_id );

					if ( 'yes' === get_option( 'woocommerce_cart_redirect_after_add' ) ) {
						wc_add_to_cart_message( array( $product_id => $quantity ), true );

						// User has enabled redirect to cart on successful addition.
						if ( 0 === wc_notice_count( 'error' ) ) {
							$data = array(
								'redirect_to_cart' => true,
							);

							// Clear notices so they don't show up after redirect.
							wc_clear_notices();

							echo wp_send_json( $data );

							wp_die();
						}
					} else {
						// Adding the notice in the response so it can be outputted right away (woocommerce.js).
						add_filter( 'woocommerce_add_to_cart_fragments', [ __CLASS__, 'vamtam_woocommerce_add_to_cart_fragments' ] );
					}

					// Clear noticed so they don't show up on refresh.
					wc_clear_notices();

					WC_AJAX::get_refreshed_fragments();
				} else {
					$data = array(
						'error' => true,
						'product_url' => apply_filters( 'woocommerce_cart_redirect_after_error', get_permalink( $product_id ), $product_id ),
						'notice' => '<div class="' . esc_attr( 'woocommerce-error vamtam-notice-error' ) . '" role="alert"><span class="vamtam-wc-msg">' . wc_kses_notice( end( wc_get_notices( 'error' ) )['notice'] ) . '</span><span class="vamtam-close-notice-btn" /></div>',
					);

					// Clear noticed so they don't show up on refresh.
					wc_clear_notices();

					echo wp_send_json( $data );
				}

				wp_die();
			}

			public static function handle_grouped_products() {
				$items             = isset( $_POST['products'] ) && is_array( $_POST['products'] ) ? $_POST['products'] : [];
				$added_to_cart     = [];
				$was_added_to_cart = false;

				if ( ! empty( $items ) ) {
					$quantity_set = false;

					foreach ( $items as $item => $quantity ) {
						if ( $quantity <= 0 ) {
							continue;
						}
						$quantity_set = true;

						// Add to cart validation.
						$passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $item, $quantity );

						// Suppress total recalculation until finished.
						remove_action( 'woocommerce_add_to_cart', array( WC()->cart, 'calculate_totals' ), 20, 0 );

						if ( $passed_validation && false !== WC()->cart->add_to_cart( $item, $quantity ) ) {
							$was_added_to_cart      = true;
							$added_to_cart[ $item ] = $quantity;
						}

						add_action( 'woocommerce_add_to_cart', array( WC()->cart, 'calculate_totals' ), 20, 0 );
					}

					if ( ! $was_added_to_cart && ! $quantity_set ) {
						wc_add_notice( __( 'Please choose the quantity of items you wish to add to your cart.', 'innovecouture' ), 'error' );
					} elseif ( $was_added_to_cart ) {
						WC()->cart->calculate_totals();
						return true;
					}
				}

				return false;
			}

			public static function handle_variable_products() {
				$product_id   = self::$product_id;
				$variation_id = empty( $_REQUEST['variation_id'] ) ? '' : absint( wp_unslash( $_REQUEST['variation_id'] ) );  // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$quantity     = empty( $_REQUEST['quantity'] ) ? 1 : wc_stock_amount( wp_unslash( $_REQUEST['quantity'] ) );  // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$variations   = array();

				$product      = wc_get_product( $product_id );

				foreach ( $_REQUEST as $key => $value ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					if ( 'attribute_' !== substr( $key, 0, 10 ) ) {
						continue;
					}

					$variations[ sanitize_title( wp_unslash( $key ) ) ] = wp_unslash( $value );
				}

				$passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity, $variation_id, $variations );

				if ( ! $passed_validation ) {
					return false;
				}

				// Prevent parent variable product from being added to cart.
				if ( empty( $variation_id ) && $product && $product->is_type( 'variable' ) ) {
					/* translators: 1: product link, 2: product name */
					wc_add_notice( sprintf( __( 'Please choose product options by visiting <a href="%1$s" title="%2$s">%2$s</a>.', 'innovecouture' ), esc_url( get_permalink( $product_id ) ), esc_html( $product->get_name() ) ), 'error' );

					return false;
				}

				return WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variations );
			}

			public static function vamtam_woocommerce_add_to_cart_fragments( $fragments ) {
				remove_filter( 'woocommerce_add_to_cart_fragments', [ __CLASS__, 'vamtam_woocommerce_add_to_cart_fragments' ] );
				$fragments['notice'] = '<div class="' . esc_attr( 'woocommerce-message' ) . '" role="alert"><span class="vamtam-wc-msg">' . wc_add_to_cart_message( array( self::$product_id => self::$quantity ), true, true ) . '</span><span class="vamtam-close-notice-btn" /></div>';
				return $fragments;
			}
		}
	}
	// Ajax hooks must be included early.
	if ( vamtam_extra_features() ) {
		vamtam_wc_single_product_ajax_hooks();
	}
}
