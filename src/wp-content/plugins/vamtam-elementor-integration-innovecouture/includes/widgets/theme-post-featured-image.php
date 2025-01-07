<?php
namespace VamtamElementor\Widgets\Post_Featured_Image;

use ElementorPro\Modules\ThemeBuilder\Widgets\Post_Featured_Image as Elementor_Post_Featured_Image;
use Elementor\Plugin;
use Elementor\Utils;
use Elementor\Group_Control_Image_Size;

// Extending the Post Featured Image widget.

// Theme preferences.
if ( ! \Vamtam_Elementor_Utils::is_widget_mod_active( 'theme-post-featured-image' ) ) {
	return;
}

if ( vamtam_theme_supports( 'theme-post-featured-image--gallery-on-hover' ) ) {

	function add_use_post_gallery_controls( $controls_manager, $widget ) {
		// Show Post Gallery.
		$widget->add_control(
			'vamtam_show_post_gallery',
			[
				'label' => esc_html__( 'Display Gallery on Hover (Products only)', 'vamtam-elementor-integration' ),
				'description' => esc_html__('Display the product\'s gallery when hovering over the featured image. Works only for products.', 'vamtam-elementor-integration'),
				'type' => $controls_manager::SWITCHER,
				'default' => '',
				'separator' => 'before',
				'prefix_class' => 'vamtam-has-',
				'return_value' => 'post-gallery',
				'render_type' => 'template'
			]
		);
	}

	// Content - Image Section.
	function section_content_image_before_section_end( $widget, $args ) {
		$controls_manager = \Elementor\Plugin::instance()->controls_manager;
		add_use_post_gallery_controls( $controls_manager, $widget );
	}
	add_action( 'elementor/element/theme-post-featured-image/section_image/before_section_end', __NAMESPACE__ . '\section_content_image_before_section_end', 10, 2 );

	// Vamtam_Widget_Post_Featured_Image.
	function widgets_registered() {
		class Vamtam_Widget_Post_Featured_Image extends Elementor_Post_Featured_Image {
			public $extra_depended_scripts = [
				'vamtam-theme-post-featured-image',
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
					'vamtam-theme-post-featured-image',
					VAMTAM_ELEMENTOR_INT_URL . '/assets/js/widgets/theme-post-featured-image/vamtam-theme-post-featured-image' . $suffix . '.js',
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

			protected function render() {
				$settings     = $this->get_settings_for_display();
				$showGallery  = vamtam_has_woocommerce() && $settings[ 'vamtam_show_post_gallery' ];
				$gallery      = null;

				if ( empty( $settings['image']['url'] ) ) {
					return;
				}

				if ( $showGallery ) {
					global $product;

					if ( $product && $product->post_type === 'product' ) {
						$product_gallery = $product->get_gallery_image_ids();

						if ( ! empty( $product_gallery ) ) {
							ob_start();
							$this->render_product_gallery( $product_gallery, $settings['image']['id'] );
							$gallery = ob_get_clean();
						}
					}
				}

				$has_caption = $this->has_caption( $settings );

				$link = $this->get_link_url( $settings );

				if ( $link ) {
					$this->add_link_attributes( 'link', $link );

					if ( Plugin::$instance->editor->is_edit_mode() ) {
						$this->add_render_attribute( 'link', [
							'class' => 'elementor-clickable',
						] );
					}

					if ( 'custom' !== $settings['link_to'] ) {
						$this->add_lightbox_data_attributes( 'link', $settings['image']['id'], $settings['open_lightbox'] );
					}
				} ?>
					<?php if ( $has_caption ) : ?>
						<figure class="wp-caption">
					<?php endif; ?>
					<?php if ( $link ) : ?>
							<a <?php $this->print_render_attribute_string( 'link' ); ?>>
					<?php endif; ?>
					<?php
						if ( $gallery ) {
							echo '<div class="vamtam-thumb-wrapper">'; // Open thumb-wrapper
							echo $gallery;
						}
					?>
					<?php Group_Control_Image_Size::print_attachment_image_html( $settings ); ?>
					<?php
						if ( $gallery ) {
							echo '</div>'; // Close thumb-wrapper
						}
					?>
					<?php if ( $link ) : ?>
						</a>
					<?php endif; ?>
					<?php if ( $has_caption ) : ?>
							<figcaption class="widget-image-caption wp-caption-text"><?php
								echo wp_kses_post( $this->get_caption( $settings ) );
							?></figcaption>
					<?php endif; ?>
					<?php if ( $has_caption ) : ?>
						</figure>
					<?php endif; ?>
				<?php
			}

			private function render_product_gallery( $product_gallery, $featured_image_id ) {
				if ( ! empty( $product_gallery ) ) {
					?>
						<div class="vamtam-product-gallery swiper">
							<div class="vamtam-gallery-wrapper swiper-wrapper">
								<?php
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

			/**
			 * Check if the current widget has caption
			 *
			 * @access private
			 * @since 2.3.0
			 *
			 * @param array $settings
			 *
			 * @return boolean
			 */
			private function has_caption( $settings ) {
				return ( ! empty( $settings['caption_source'] ) && 'none' !== $settings['caption_source'] );
			}

			/**
			 * Get the caption for current widget.
			 *
			 * @access private
			 * @since 2.3.0
			 * @param $settings
			 *
			 * @return string
			 */
			private function get_caption( $settings ) {
				$caption = '';
				if ( ! empty( $settings['caption_source'] ) ) {
					switch ( $settings['caption_source'] ) {
						case 'attachment':
							$caption = wp_get_attachment_caption( $settings['image']['id'] );
							break;
						case 'custom':
							$caption = ! Utils::is_empty( $settings['caption'] ) ? $settings['caption'] : '';
					}
				}
				return $caption;
			}
		}

		// Replace current theme-post-featured-image widget with our extended version.
		$widgets_manager = \Elementor\Plugin::instance()->widgets_manager;
		$widgets_manager->unregister( 'theme-post-featured-image' );
		$widgets_manager->register( new Vamtam_Widget_Post_Featured_Image );
	}
	add_action( \Vamtam_Elementor_Utils::get_widgets_registration_hook(), __NAMESPACE__ . '\widgets_registered', 100 );
}
