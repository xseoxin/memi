/**
 * Gutenberg Block for Side Tiles Menu
 */

(function () {
	'use strict';

	const { registerBlockType } = wp.blocks;
	const { InspectorControls } = wp.blockEditor || wp.editor;
	const { PanelBody, SelectControl } = wp.components;
	const { __ } = wp.i18n;
	const { createElement: el } = wp.element;

	registerBlockType('side-tiles-menu/tiles', {
		title: __('Side Tiles Menu', 'side-tiles-menu'),
		description: __('Wyświetl kafelki bocznego menu', 'side-tiles-menu'),
		icon: 'grid-view',
		category: 'widgets',
		attributes: {
			tileId: {
				type: 'string',
				default: ''
			}
		},

		edit: function (props) {
			const { attributes, setAttributes } = props;
			const { tileId } = attributes;

			// Get tiles data
			const tiles = window.sideTilesMenuBlock?.tiles || [];

			// Create options for select
			const options = [
				{ label: __('Wszystkie kafelki', 'side-tiles-menu'), value: '' }
			];

			tiles.forEach(function (tile) {
				options.push({
					label: tile.title || tile.id,
					value: tile.id
				});
			});

			function onChangeTileId(newTileId) {
				setAttributes({ tileId: newTileId });
			}

			return el(
				'div',
				{ className: props.className },
				el(
					InspectorControls,
					{},
					el(
						PanelBody,
						{
							title: __('Ustawienia kafelków', 'side-tiles-menu'),
							initialOpen: true
						},
						el(SelectControl, {
							label: __('Wybierz kafelek', 'side-tiles-menu'),
							value: tileId,
							options: options,
							onChange: onChangeTileId
						})
					)
				),
				el(
					'div',
					{
						style: {
							padding: '20px',
							background: '#f0f0f1',
							border: '1px solid #ddd',
							borderRadius: '4px',
							textAlign: 'center'
						}
					},
					el('div', {
						className: 'dashicons dashicons-grid-view',
						style: {
							fontSize: '40px',
							width: '40px',
							height: '40px'
						}
					}),
					el(
						'p',
						{ style: { margin: '10px 0 0 0' } },
						tileId
							? __('Wyświetlanie pojedynczego kafelka', 'side-tiles-menu')
							: __('Wyświetlanie wszystkich kafelków', 'side-tiles-menu')
					)
				)
			);
		},

		save: function () {
			// Rendered via PHP
			return null;
		}
	});

})();
