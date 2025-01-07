<?php
namespace VamtamElementor\Widgets\LoopGrid;

use \ElementorPro\Modules\LoopBuilder\Widgets\Loop_Grid as Elementor_Loop_Grid;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Is Pro Widget.
if ( ! \VamtamElementorIntregration::is_elementor_pro_active() ) {
	return;
}

// Theme preferences.
if ( ! \Vamtam_Elementor_Utils::is_widget_mod_active( 'loop-grid' ) ) {
	return;
}

if ( vamtam_theme_supports( 'loop-grid--swiper-dep' ) ) {
	// Vamtam_Widget_Loop_Grid.
	function widgets_registered() {

		// Is Pro Widget.
		if ( ! \VamtamElementorIntregration::is_elementor_pro_active() ) {
			return;
		}

		if ( ! class_exists( '\ElementorPro\Modules\LoopBuilder\Widgets\Loop_Grid' ) ) {
			return; // Elementor's autoloader acts weird sometimes.
		}

		class Vamtam_Widget_Loop_Grid extends Elementor_Loop_Grid {

			public function get_script_depends(): array {
				return array_merge( parent::get_script_depends(), [ 'swiper' ] );
			}

			public function get_style_depends(): array {
				return array_merge( parent::get_style_depends(), [ 'e-swiper' ] );
			}
		}

		// Replace current tabs widget with our extended version.
		$widgets_manager = \Elementor\Plugin::instance()->widgets_manager;
		$widgets_manager->unregister( 'loop-grid' );
		$widgets_manager->register( new Vamtam_Widget_Loop_Grid );
	}
	add_action( \Vamtam_Elementor_Utils::get_widgets_registration_hook(), __NAMESPACE__ . '\widgets_registered', 100 );
}
