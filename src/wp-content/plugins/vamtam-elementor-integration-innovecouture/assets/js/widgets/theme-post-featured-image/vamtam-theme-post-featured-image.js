class VamtamThemePostFeaturedImage extends elementorModules.frontend.handlers.Base {

	onInit( ...args ) {
		super.onInit( ...args );
		this.handleShowProuductGallery();
	}

	handleShowProuductGallery() {
		if ( ! this.$element.hasClass( 'vamtam-has-post-gallery' ) ) {
			return;
		}

		const $swiperElements = this.$element.find( '.vamtam-product-gallery.swiper' );

		if ( ! $swiperElements.length ) {
			return;
		}

		// Loop through all products with gallery and initialize Swiper.
		$swiperElements.each( function () {
			const $swiperContainer = jQuery( this );

			new window.elementorFrontend.utils.swiper( $swiperContainer, {
				loop: true, // Infinite looping.

				// No dragging. Can be problematic with nested swipers.
				allowTouchMove: false,

				// Navigation arrows.
				navigation: {
					nextEl: '.swiper-button-next',
					prevEl: '.swiper-button-prev',
				},

				on: {
					init: function () {
						const swiper = this;

						// Go to the first slide on mouse leave.
						swiper.$el.on( 'mouseleave', () => {
							setTimeout( () => {
								swiper.slideTo( 1, 0 );
							}, 400 ); // 300ms is the transition time of the gallery's fade-in/out effect.
						} );
					},
				},
			} );
		} );
	}
}


jQuery( window ).on( 'elementor/frontend/init', () => {
	if ( ! elementorFrontend.elementsHandler || ! elementorFrontend.elementsHandler.attachHandler ) {
		const addHandler = ( $element ) => {
			elementorFrontend.elementsHandler.addHandler( VamtamThemePostFeaturedImage, {
				$element,
			} );
		};

		elementorFrontend.hooks.addAction( 'frontend/element_ready/theme-post-featured-image.default', addHandler, 100 );
	} else {
		elementorFrontend.elementsHandler.attachHandler( 'theme-post-featured-image', VamtamThemePostFeaturedImage );
	}
} );
