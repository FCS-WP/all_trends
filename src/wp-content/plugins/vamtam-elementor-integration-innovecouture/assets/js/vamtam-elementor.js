// Contains logic related to elementor.
( function( $, undefined ) {
	"use strict";

	$( function() {

		var VAMTAM_ELEMENTOR = {
			init: function () {
				this.removeGrowScaleAnims.init();
				this.disableRemovalOfCustomThemeFontsAndColors.init();
			},
			// Removes grow-scale anims (select options) for all widgets except image.
			removeGrowScaleAnims: {
				init: function () {
					let selectedWidget = '';

					function removeImageAnims() {
						[ '', '_tablet', '_mobile' ].forEach( device => {
							const optGroupSelector = `#elementor-panel select[data-setting="_animation${device}"] optgroup[label="Vamtam"], #elementor-panel select[data-setting="animation${device}"] optgroup[label="Vamtam"]`,
								$animsVamtamOptGroup = $( optGroupSelector ),
								$imageGrowScaleAnims = $animsVamtamOptGroup.find( 'option[value*="imageGrowWithScale"' );

							// Remove the options.
							$.each( $imageGrowScaleAnims, function ( i, opt ) {
								$( opt ).remove();
							} );

							// If Vamtam optgroup is empty, remove it.
							if( $animsVamtamOptGroup.children(':visible').length == 0 ) {
								$animsVamtamOptGroup.remove();
							}
						} );
					}

					// Widgets.
					elementor.hooks.addAction( 'panel/open_editor/widget', function( panel, model, view ) {
						// Update selected widget.
						selectedWidget = model.elType || model.attributes.widgetType;
					} );
					// Columns.
					elementor.hooks.addAction( 'panel/open_editor/column', function( panel, model, view ) {
						// Update selected widget.
						selectedWidget = model.elType || model.attributes.elType;
					} );
					// Sections.
					elementor.hooks.addAction( 'panel/open_editor/section', function( panel, model, view ) {
						// Update selected widget.
						selectedWidget = model.elType || model.attributes.elType;
					} );

					const docClickHandler = ( e ) => {
						// We dont remove for Image widget.
						if ( selectedWidget === 'image' ) {
							return;
						}
						// Advanced Tab.
						if ( ! $( 'body' ).hasClass( 'e-route-panel-editor-advanced' ) ) {
							return;
						}
						// Isnide Motion Effects section.
						if ( e.target.closest( '.elementor-control-section_effects' ) ) {
							setTimeout( () => {
								removeImageAnims();
							}, 10 );
						}
					};

					const panel = document.getElementById( 'elementor-panel' );
					panel.addEventListener( 'click', docClickHandler, { passive: true, capture: true } ); // we need capture phase here.
				}
			},
			disableRemovalOfCustomThemeFontsAndColors: {
				init: function () {
					elementor.hooks.addAction( 'panel/global/tab/before-show', function( tab ) {
						const tabId = tab.id;
						if ( tabId !== 'global-colors' && tabId !== 'global-typography' ) {
							return;
						}

						const themeCustomFontIds = VAMTAM_ELEMENTOR_STRINGS.themeCustomFontIds;
						const themeCustomColorIds = VAMTAM_ELEMENTOR_STRINGS.themeCustomColorIds;

						setTimeout(() => {
							const idsToCheck = tabId === 'global-colors' ? themeCustomColorIds : themeCustomFontIds;

							idsToCheck.forEach( id => {
								// Find the input with the value of the id.
								const $input = $( `.elementor-control-_id input[value="${id}"]` );

								// Get row controls for repeater field.
								const $row = $input.closest( '.elementor-repeater-row-controls' );

								// Get the delete button.
								const $deleteBtn = $row.find( '.elementor-repeater-tool-remove' );

								// Add disabled class.
								$deleteBtn.addClass( 'elementor-repeater-tool-remove--disabled' );
								$deleteBtn.removeClass( 'elementor-repeater-tool-remove' );

								// Change the icon.
								$deleteBtn.find( 'i' ).attr( 'class', 'eicon-disable-trash-o' );

								// Update the tipsy popover text for these delete buttons.
								$deleteBtn.data('tipsy')['options'].title = function () {
									const title = tabId === 'global-colors' ? VAMTAM_ELEMENTOR_STRINGS.customThemeColorRemoveText : VAMTAM_ELEMENTOR_STRINGS.customThemeFontRemoveText;
									return title;
								}
							} );
						}, 250);
					} );
				}
			},
		}

		$( window ).on( 'load', function() {
			VAMTAM_ELEMENTOR.init();
		} );
	});
})( jQuery );
