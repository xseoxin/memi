/**
 * Frontend JavaScript for Side Tiles Menu
 * Pure JavaScript - no jQuery
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
		const container = document.querySelector('.side-tiles-menu-container');
		if (!container) return;

		initVisibility(container);
		initAnimations(container);
		initClickTracking(container);
		initKeyboardNavigation(container);
		initGA4Tracking();
	}

	/**
	 * Initialize visibility based on breakpoints
	 */
	function initVisibility(container) {
		const data = window.sideTilesMenuData || {};
		const desktopEnabled = data.desktopEnabled !== false;
		const tabletEnabled = data.tabletEnabled !== false;
		const mobileEnabled = data.mobileEnabled !== false;
		const desktopBreakpoint = parseInt(data.desktopBreakpoint) || 1024;
		const tabletBreakpoint = parseInt(data.tabletBreakpoint) || 768;

		function checkVisibility() {
			const width = window.innerWidth;
			let shouldShow = true;

			if (width >= desktopBreakpoint && !desktopEnabled) {
				shouldShow = false;
			} else if (width >= tabletBreakpoint && width < desktopBreakpoint && !tabletEnabled) {
				shouldShow = false;
			} else if (width < tabletBreakpoint && !mobileEnabled) {
				shouldShow = false;
			}

			container.style.display = shouldShow ? 'flex' : 'none';
		}

		checkVisibility();

		// Throttled resize handler
		let resizeTimeout;
		window.addEventListener('resize', function () {
			clearTimeout(resizeTimeout);
			resizeTimeout = setTimeout(checkVisibility, 150);
		});
	}

	/**
	 * Initialize animations
	 */
	function initAnimations(container) {
		const data = window.sideTilesMenuData || {};
		const animationType = data.animationType || 'slide';
		const animationDuration = parseInt(data.animationDuration) || 300;
		const animationDelay = parseInt(data.animationDelay) || 0;
		const tiles = container.querySelectorAll('.side-tile');

		if (animationType === 'none' || tiles.length === 0) {
			return;
		}

		// Check for reduced motion preference
		const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
		if (prefersReducedMotion) {
			return;
		}

		// Get position for slide animation
		const containerStyle = window.getComputedStyle(container);
		const position = containerStyle.right === '0px' ? 'right' : 'left';

		tiles.forEach(function (tile, index) {
			// Add animation class
			tile.classList.add('animate-' + animationType);

			if (position === 'left') {
				tile.classList.add('position-left');
			}

			// Set transition
			tile.style.transition = 'all ' + animationDuration + 'ms ease';

			// Trigger animation with delay
			setTimeout(function () {
				tile.classList.add('animated');
			}, index * animationDelay + 50);
		});
	}

	/**
	 * Initialize click tracking
	 */
	function initClickTracking(container) {
		const tiles = container.querySelectorAll('.side-tile:not(.side-tile-no-link)');

		tiles.forEach(function (tile) {
			tile.addEventListener('click', function (e) {
				const tileId = this.dataset.tileId;

				if (tileId) {
					trackClick(tileId);
				}
			});
		});
	}

	/**
	 * Track click via AJAX
	 */
	function trackClick(tileId) {
		const data = window.sideTilesMenuData || {};

		if (!data.ajaxUrl || !data.nonce) {
			return;
		}

		const formData = new FormData();
		formData.append('action', 'side_tiles_track_click');
		formData.append('nonce', data.nonce);
		formData.append('tile_id', tileId);

		fetch(data.ajaxUrl, {
			method: 'POST',
			body: formData
		}).catch(function (error) {
			console.error('Error tracking click:', error);
		});
	}

	/**
	 * Initialize keyboard navigation
	 */
	function initKeyboardNavigation(container) {
		const tiles = Array.from(container.querySelectorAll('.side-tile:not(.side-tile-no-link)'));

		if (tiles.length === 0) return;

		tiles.forEach(function (tile, index) {
			tile.addEventListener('keydown', function (e) {
				let targetIndex = -1;

				switch (e.key) {
					case 'ArrowUp':
						e.preventDefault();
						targetIndex = index - 1;
						break;
					case 'ArrowDown':
						e.preventDefault();
						targetIndex = index + 1;
						break;
					case 'Home':
						e.preventDefault();
						targetIndex = 0;
						break;
					case 'End':
						e.preventDefault();
						targetIndex = tiles.length - 1;
						break;
					case 'Enter':
					case ' ':
						// Let default behavior handle the click
						// But also trigger our click tracking
						trackClick(tile.dataset.tileId);
						break;
					default:
						return;
				}

				if (targetIndex >= 0 && targetIndex < tiles.length) {
					tiles[targetIndex].focus();
				}
			});
		});
	}

	/**
	 * Initialize Google Analytics 4 tracking
	 */
	function initGA4Tracking() {
		const data = window.sideTilesMenuData || {};

		if (!data.ga4Enabled || !data.ga4MeasurementId) {
			return;
		}

		// Check if gtag is available
		if (typeof gtag === 'undefined') {
			return;
		}

		const tiles = document.querySelectorAll('.side-tile:not(.side-tile-no-link)');

		tiles.forEach(function (tile) {
			tile.addEventListener('click', function (e) {
				const tileId = this.dataset.tileId;
				const tileTitle = this.getAttribute('title') || this.getAttribute('aria-label') || 'Unknown';
				const linkUrl = this.getAttribute('href') || '';

				gtag('event', 'side_tile_click', {
					tile_id: tileId,
					tile_title: tileTitle,
					link_url: linkUrl,
					event_category: 'Side Tiles Menu',
					event_label: tileTitle
				});
			});
		});
	}

	/**
	 * Lazy load images (if needed)
	 */
	function initLazyLoading() {
		const images = document.querySelectorAll('.side-tile img[loading="lazy"]');

		if ('IntersectionObserver' in window) {
			const imageObserver = new IntersectionObserver(function (entries, observer) {
				entries.forEach(function (entry) {
					if (entry.isIntersecting) {
						const img = entry.target;
						img.src = img.dataset.src || img.src;
						observer.unobserve(img);
					}
				});
			});

			images.forEach(function (img) {
				imageObserver.observe(img);
			});
		}
	}

	// Initialize lazy loading
	initLazyLoading();

})();
