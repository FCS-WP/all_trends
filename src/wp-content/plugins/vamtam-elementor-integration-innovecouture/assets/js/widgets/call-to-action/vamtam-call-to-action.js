class VamtamCallToAction extends elementorModules.frontend.handlers.Base {
	getDefaultSettings() {
		return {
			selectors: {
				btnText: '.elementor-button',
			},
		};
	}

	getDefaultElements() {
		const selectors = this.getSettings( 'selectors' );
		return {
			$btnText: this.$element.find( selectors.btnText ),
		};
	}

	onInit( ...args ) {
		super.onInit( ...args );
		this.handleCtaBtnUnderlineAnimation();
	}

	handleCtaBtnUnderlineAnimation() {
		if ( ! this.$element.hasClass( 'vamtam-has-underline-anim' ) ) {
			return;
		}

		/*
			Because on buttons the text container is using flex, all its children are forced to block-level.
			We need inline for the underline animation to work properly on multiline text so we add a new
			nested span.

			TODO: Maybe do this on server.
		*/
		const btnText = this.elements.$btnText.text();
		this.elements.$btnText.text('');
		this.elements.$btnText.append('<span class="vamtam-btn-text">' + btnText + '</span>');
	}
}


jQuery( window ).on( 'elementor/frontend/init', () => {
	if ( ! elementorFrontend.elementsHandler || ! elementorFrontend.elementsHandler.attachHandler ) {
		const addHandler = ( $element ) => {
			elementorFrontend.elementsHandler.addHandler( VamtamCallToAction, {
				$element,
			} );
		};

		elementorFrontend.hooks.addAction( 'frontend/element_ready/call-to-action.default', addHandler, 100 );
	} else {
		elementorFrontend.elementsHandler.attachHandler( 'call-to-action', VamtamCallToAction );
	}
} );
