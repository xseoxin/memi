/**
 * Admin JavaScript for Side Tiles Menu
 */

(function () {
	'use strict';

	// Wait for DOM to be ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}

	function init() {
		initTabs();
		initColorPickers();
		initConditionalFields();
		initTileManagement();
		initImportExport();
		initLivePreview();
	}

	/**
	 * Initialize tabs
	 */
	function initTabs() {
		const tabLinks = document.querySelectorAll('.nav-tab');
		const tabContents = document.querySelectorAll('.tab-content');

		tabLinks.forEach(function (link) {
			link.addEventListener('click', function (e) {
				e.preventDefault();

				// Remove active class from all tabs and contents
				tabLinks.forEach(function (tab) {
					tab.classList.remove('nav-tab-active');
				});
				tabContents.forEach(function (content) {
					content.classList.remove('tab-content-active');
				});

				// Add active class to clicked tab
				link.classList.add('nav-tab-active');

				// Show corresponding content
				const targetId = link.getAttribute('href').substring(1);
				const targetContent = document.getElementById(targetId);
				if (targetContent) {
					targetContent.classList.add('tab-content-active');
				}
			});
		});
	}

	/**
	 * Initialize color pickers
	 */
	function initColorPickers() {
		if (typeof jQuery !== 'undefined' && jQuery.fn.wpColorPicker) {
			jQuery('.color-picker').wpColorPicker({
				change: function () {
					updateLivePreview();
				}
			});
		}
	}

	/**
	 * Initialize conditional fields
	 */
	function initConditionalFields() {
		// Tile shape -> border radius
		const tileShapeSelect = document.getElementById('tile-shape-select');
		const borderRadiusRow = document.querySelector('.border-radius-row');

		if (tileShapeSelect && borderRadiusRow) {
			tileShapeSelect.addEventListener('change', function () {
				borderRadiusRow.style.display = this.value === 'rounded' ? 'table-row' : 'none';
			});
		}

		// Gradient -> gradient settings
		const useGradientCheckbox = document.getElementById('use-gradient-checkbox');
		const gradientSettings = document.querySelectorAll('.gradient-settings');

		if (useGradientCheckbox) {
			useGradientCheckbox.addEventListener('change', function () {
				gradientSettings.forEach(function (row) {
					row.style.display = this.checked ? 'table-row' : 'none';
				}.bind(this));
			});
		}

		// Shadow -> shadow settings
		const shadowEnabledCheckbox = document.getElementById('shadow-enabled-checkbox');
		const shadowSettings = document.querySelectorAll('.shadow-settings');

		if (shadowEnabledCheckbox) {
			shadowEnabledCheckbox.addEventListener('change', function () {
				shadowSettings.forEach(function (row) {
					row.style.display = this.checked ? 'table-row' : 'none';
				}.bind(this));
			});
		}

		// GA4 -> GA4 settings
		const ga4EnabledCheckbox = document.getElementById('ga4-enabled-checkbox');
		const ga4Settings = document.querySelectorAll('.ga4-settings');

		if (ga4EnabledCheckbox) {
			ga4EnabledCheckbox.addEventListener('change', function () {
				ga4Settings.forEach(function (row) {
					row.style.display = this.checked ? 'table-row' : 'none';
				}.bind(this));
			});
		}
	}

	/**
	 * Initialize tile management
	 */
	function initTileManagement() {
		const addNewTileBtn = document.getElementById('add-new-tile');
		const modal = document.getElementById('tile-editor-modal');
		const closeBtn = modal ? modal.querySelector('.side-tiles-modal-close') : null;
		const cancelBtn = document.getElementById('cancel-tile-edit');
		const tileForm = document.getElementById('tile-editor-form');

		// Add new tile
		if (addNewTileBtn) {
			addNewTileBtn.addEventListener('click', function () {
				openTileEditor();
			});
		}

		// Close modal
		if (closeBtn) {
			closeBtn.addEventListener('click', function () {
				closeTileEditor();
			});
		}

		if (cancelBtn) {
			cancelBtn.addEventListener('click', function () {
				closeTileEditor();
			});
		}

		// Close on outside click
		if (modal) {
			window.addEventListener('click', function (e) {
				if (e.target === modal) {
					closeTileEditor();
				}
			});
		}

		// Content type radio buttons
		const contentTypeRadios = document.querySelectorAll('input[name="content_type"]');
		contentTypeRadios.forEach(function (radio) {
			radio.addEventListener('change', function () {
				updateContentFields(this.value);
			});
		});

		// Image upload button
		const uploadImageBtn = document.querySelector('.upload-image-button');
		if (uploadImageBtn && typeof wp !== 'undefined' && wp.media) {
			uploadImageBtn.addEventListener('click', function (e) {
				e.preventDefault();

				const frame = wp.media({
					title: 'Wybierz obraz',
					button: { text: 'Użyj obrazu' },
					multiple: false
				});

				frame.on('select', function () {
					const attachment = frame.state().get('selection').first().toJSON();
					document.getElementById('tile-image').value = attachment.url;
				});

				frame.open();
			});
		}

		// Save tile form
		if (tileForm) {
			tileForm.addEventListener('submit', function (e) {
				e.preventDefault();
				saveTile();
			});
		}

		// Edit and delete buttons (delegated)
		document.addEventListener('click', function (e) {
			if (e.target.classList.contains('edit-tile')) {
				const tileId = e.target.dataset.tileId;
				editTile(tileId);
			}

			if (e.target.classList.contains('delete-tile')) {
				const tileId = e.target.dataset.tileId;
				if (confirm(sideTilesMenu.strings.confirmDelete)) {
					deleteTile(tileId);
				}
			}
		});
	}

	/**
	 * Open tile editor
	 */
	function openTileEditor(tile) {
		const modal = document.getElementById('tile-editor-modal');
		const form = document.getElementById('tile-editor-form');
		const title = document.getElementById('tile-editor-title');

		if (!modal || !form) return;

		// Reset form
		form.reset();

		if (tile) {
			// Edit mode
			title.textContent = 'Edytuj kafelek';
			document.getElementById('tile-id').value = tile.id || '';
			document.getElementById('tile-title').value = tile.title || '';
			document.getElementById('tile-link').value = tile.link_url || '';
			document.getElementById('tile-target').value = tile.link_target || '_self';
			document.getElementById('tile-order').value = tile.order || 0;
			document.getElementById('tile-aria-label').value = tile.aria_label || '';

			// Content type
			const contentType = tile.content_type || 'svg';
			document.querySelector('input[name="content_type"][value="' + contentType + '"]').checked = true;
			updateContentFields(contentType);

			if (contentType === 'svg') {
				document.getElementById('tile-svg').value = tile.svg_code || '';
			} else if (contentType === 'image') {
				document.getElementById('tile-image').value = tile.image_url || '';
			} else if (contentType === 'text') {
				document.getElementById('tile-text').value = tile.text_content || '';
			}
		} else {
			// Add mode
			title.textContent = 'Dodaj nowy kafelek';
			document.getElementById('tile-id').value = '';
			updateContentFields('svg');
		}

		modal.style.display = 'block';
	}

	/**
	 * Close tile editor
	 */
	function closeTileEditor() {
		const modal = document.getElementById('tile-editor-modal');
		if (modal) {
			modal.style.display = 'none';
		}
	}

	/**
	 * Update content fields based on type
	 */
	function updateContentFields(type) {
		const allFields = document.querySelectorAll('.content-field');
		allFields.forEach(function (field) {
			field.style.display = 'none';
		});

		const activeField = document.querySelector('.' + type + '-field');
		if (activeField) {
			activeField.style.display = 'table-row';
		}
	}

	/**
	 * Save tile
	 */
	function saveTile() {
		console.log('Side Tiles Menu: saveTile() called');

		const formData = new FormData(document.getElementById('tile-editor-form'));
		formData.append('action', 'side_tiles_save_tile');
		// Note: nonce is already in the form via wp_nonce_field() as 'side_tiles_nonce'

		// Debug: log form data
		console.log('Side Tiles Menu: Form data being sent:');
		for (let pair of formData.entries()) {
			console.log(pair[0] + ': ' + pair[1]);
		}

		fetch(sideTilesMenu.ajaxUrl, {
			method: 'POST',
			body: formData
		})
			.then(function (response) {
				console.log('Side Tiles Menu: Response status:', response.status);
				return response.json();
			})
			.then(function (data) {
				console.log('Side Tiles Menu: Response data:', data);

				if (data.success) {
					showNotice(sideTilesMenu.strings.saveSuccess, 'success');
					closeTileEditor();
					// Reload page to show updated tile
					setTimeout(function () {
						window.location.reload();
					}, 1000);
				} else {
					let errorMsg = data.data && data.data.message ? data.data.message : sideTilesMenu.strings.saveError;
					if (data.data && data.data.debug) {
						console.error('Side Tiles Menu: Debug info:', data.data.debug);
						errorMsg += ' (Debug: ' + data.data.debug + ')';
					}
					showNotice(errorMsg, 'error');
				}
			})
			.catch(function (error) {
				console.error('Side Tiles Menu: Fetch error:', error);
				showNotice(sideTilesMenu.strings.saveError + ' - Sprawdź konsolę przeglądarki.', 'error');
			});
	}

	/**
	 * Edit tile
	 */
	function editTile(tileId) {
		// Get tile data from row
		const row = document.querySelector('tr[data-tile-id="' + tileId + '"]');
		if (!row) return;

		// Fetch tile data via AJAX or extract from page
		// For simplicity, we'll reload the page data
		// In production, you might want to store tile data in data attributes
		fetch(sideTilesMenu.ajaxUrl + '?action=side_tiles_get_tile&tile_id=' + tileId + '&nonce=' + sideTilesMenu.nonce)
			.then(function (response) {
				return response.json();
			})
			.then(function (data) {
				if (data.success) {
					openTileEditor(data.data.tile);
				}
			})
			.catch(function (error) {
				console.error('Error:', error);
				// Fallback: just open empty editor
				openTileEditor({ id: tileId });
			});
	}

	/**
	 * Delete tile
	 */
	function deleteTile(tileId) {
		const formData = new FormData();
		formData.append('action', 'side_tiles_delete_tile');
		formData.append('nonce', sideTilesMenu.nonce);
		formData.append('tile_id', tileId);

		fetch(sideTilesMenu.ajaxUrl, {
			method: 'POST',
			body: formData
		})
			.then(function (response) {
				return response.json();
			})
			.then(function (data) {
				if (data.success) {
					showNotice(sideTilesMenu.strings.deleteSuccess, 'success');
					// Remove row
					const row = document.querySelector('tr[data-tile-id="' + tileId + '"]');
					if (row) {
						row.remove();
					}
				} else {
					showNotice(data.data.message || sideTilesMenu.strings.deleteError, 'error');
				}
			})
			.catch(function (error) {
				console.error('Error:', error);
				showNotice(sideTilesMenu.strings.deleteError, 'error');
			});
	}

	/**
	 * Initialize import/export
	 */
	function initImportExport() {
		const exportBtn = document.getElementById('side-tiles-export');
		const importBtn = document.getElementById('side-tiles-import');
		const importFile = document.getElementById('side-tiles-import-file');

		if (exportBtn) {
			exportBtn.addEventListener('click', function () {
				exportSettings();
			});
		}

		if (importBtn) {
			importBtn.addEventListener('click', function () {
				importFile.click();
			});
		}

		if (importFile) {
			importFile.addEventListener('change', function (e) {
				const file = e.target.files[0];
				if (file) {
					importSettings(file);
				}
			});
		}
	}

	/**
	 * Export settings
	 */
	function exportSettings() {
		const formData = new FormData();
		formData.append('action', 'side_tiles_export_settings');
		formData.append('nonce', sideTilesMenu.nonce);

		fetch(sideTilesMenu.ajaxUrl, {
			method: 'POST',
			body: formData
		})
			.then(function (response) {
				return response.json();
			})
			.then(function (data) {
				if (data.success) {
					const json = JSON.stringify(data.data.data, null, 2);
					const blob = new Blob([json], { type: 'application/json' });
					const url = URL.createObjectURL(blob);
					const a = document.createElement('a');
					a.href = url;
					a.download = data.data.filename;
					document.body.appendChild(a);
					a.click();
					document.body.removeChild(a);
					URL.revokeObjectURL(url);
					showNotice(sideTilesMenu.strings.exportSuccess, 'success');
				}
			})
			.catch(function (error) {
				console.error('Error:', error);
			});
	}

	/**
	 * Import settings
	 */
	function importSettings(file) {
		const reader = new FileReader();

		reader.onload = function (e) {
			const formData = new FormData();
			formData.append('action', 'side_tiles_import_settings');
			formData.append('nonce', sideTilesMenu.nonce);
			formData.append('json_data', e.target.result);

			fetch(sideTilesMenu.ajaxUrl, {
				method: 'POST',
				body: formData
			})
				.then(function (response) {
					return response.json();
				})
				.then(function (data) {
					if (data.success) {
						showNotice(sideTilesMenu.strings.importSuccess, 'success');
						setTimeout(function () {
							window.location.reload();
						}, 1000);
					} else {
						showNotice(data.data.message || sideTilesMenu.strings.importError, 'error');
					}
				})
				.catch(function (error) {
					console.error('Error:', error);
					showNotice(sideTilesMenu.strings.importError, 'error');
				});
		};

		reader.readAsText(file);
	}

	/**
	 * Initialize live preview
	 */
	function initLivePreview() {
		// Listen to form changes
		const form = document.querySelector('.side-tiles-menu-settings form');
		if (form) {
			form.addEventListener('input', function () {
				updateLivePreview();
			});
			form.addEventListener('change', function () {
				updateLivePreview();
			});
		}

		// Initial preview
		updateLivePreview();
	}

	/**
	 * Update live preview
	 */
	function updateLivePreview() {
		// This is a simplified preview
		// In production, you might want to fetch and render actual tiles
		const preview = document.getElementById('side-tiles-live-preview');
		if (!preview) return;

		// Get form values
		const position = getFormValue('position', 'right');
		const tileWidth = getFormValue('tile_width', '60') + getFormValue('tile_width_unit', 'px');
		const tileHeight = getFormValue('tile_height', '60') + getFormValue('tile_height_unit', 'px');
		const bgColor = getFormValue('bg_color', '#333333');
		const textColor = getFormValue('text_color', '#ffffff');
		const borderRadius = getFormValue('border_radius', '8') + 'px';
		const tileShape = getFormValue('tile_shape', 'rounded');

		let finalBorderRadius = '0';
		if (tileShape === 'circle') {
			finalBorderRadius = '50%';
		} else if (tileShape === 'rounded') {
			finalBorderRadius = borderRadius;
		}

		// Create sample tiles
		preview.innerHTML = '';
		preview.style.right = position === 'right' ? '0' : 'auto';
		preview.style.left = position === 'left' ? '0' : 'auto';
		preview.style.display = 'flex';
		preview.style.flexDirection = 'column';
		preview.style.gap = '10px';

		for (let i = 0; i < 3; i++) {
			const tile = document.createElement('div');
			tile.style.width = tileWidth;
			tile.style.height = tileHeight;
			tile.style.backgroundColor = bgColor;
			tile.style.color = textColor;
			tile.style.borderRadius = finalBorderRadius;
			tile.style.display = 'flex';
			tile.style.alignItems = 'center';
			tile.style.justifyContent = 'center';
			tile.textContent = (i + 1);
			preview.appendChild(tile);
		}
	}

	/**
	 * Get form value
	 */
	function getFormValue(name, defaultValue) {
		const input = document.querySelector('[name*="[' + name + ']"]');
		if (!input) return defaultValue;

		if (input.type === 'checkbox') {
			return input.checked;
		}

		return input.value || defaultValue;
	}

	/**
	 * Show notice
	 */
	function showNotice(message, type) {
		const notice = document.createElement('div');
		notice.className = 'notice notice-' + type + ' is-dismissible';
		notice.innerHTML = '<p>' + message + '</p>';

		const h1 = document.querySelector('.wrap h1');
		if (h1) {
			h1.parentNode.insertBefore(notice, h1.nextSibling);

			// Auto dismiss after 5 seconds
			setTimeout(function () {
				notice.remove();
			}, 5000);
		}
	}

})();
