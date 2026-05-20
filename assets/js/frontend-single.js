/**
 * Frontend Single Product JavaScript
 * 
 * Handles gallery lightbox and specification tabs
 * 
 * @package FS_Product_Catalog
 */

(function() {
	'use strict';

	/**
	 * Gallery Module
	 */
	const Gallery = {
		lightbox: null,
		images: [],
		currentIndex: 0,

		/**
		 * Initialize gallery
		 */
		init: function() {
			this.lightbox = document.getElementById('fs-lightbox');
			if (!this.lightbox) {
				return;
			}

			// Load gallery data
			const dataElement = this.lightbox.querySelector('.fs-gallery-data');
			if (dataElement) {
				try {
					this.images = JSON.parse(dataElement.textContent);
				} catch (e) {
					console.error('Failed to parse gallery data:', e);
					return;
				}
			}

			this.bindEvents();
		},

		/**
		 * Bind gallery events
		 */
		bindEvents: function() {
			const self = this;

			// Main image click
			const mainImage = document.querySelector('.fs-gallery-main');
			if (mainImage) {
				mainImage.addEventListener('click', function() {
					self.openLightbox(0);
				});
			}

			// Thumbnail clicks
			const thumbnails = document.querySelectorAll('.fs-gallery-thumbnail');
			thumbnails.forEach(function(thumb) {
				thumb.addEventListener('click', function(e) {
					e.preventDefault();
					const index = parseInt(this.dataset.index, 10);
					
					// Update main image
					const mainImg = document.querySelector('.fs-gallery-main-image');
					if (mainImg) {
						mainImg.src = this.dataset.fullUrl;
						mainImg.dataset.fullUrl = this.dataset.fullUrl;
					}

					// Update active thumbnail
					thumbnails.forEach(function(t) {
						t.classList.remove('active');
					});
					this.classList.add('active');

					// Update current index
					self.currentIndex = index;
				});
			});

			// Lightbox controls
			const closeBtn = this.lightbox.querySelector('.fs-lightbox-close');
			if (closeBtn) {
				closeBtn.addEventListener('click', function() {
					self.closeLightbox();
				});
			}

			const prevBtn = this.lightbox.querySelector('.fs-lightbox-prev');
			if (prevBtn) {
				prevBtn.addEventListener('click', function() {
					self.prevImage();
				});
			}

			const nextBtn = this.lightbox.querySelector('.fs-lightbox-next');
			if (nextBtn) {
				nextBtn.addEventListener('click', function() {
					self.nextImage();
				});
			}

			// Overlay click
			const overlay = this.lightbox.querySelector('.fs-lightbox-overlay');
			if (overlay) {
				overlay.addEventListener('click', function() {
					self.closeLightbox();
				});
			}

			// Keyboard navigation
			document.addEventListener('keydown', function(e) {
				if (self.lightbox.style.display === 'flex') {
					if (e.key === 'Escape') {
						self.closeLightbox();
					} else if (e.key === 'ArrowLeft') {
						self.prevImage();
					} else if (e.key === 'ArrowRight') {
						self.nextImage();
					}
				}
			});
		},

		/**
		 * Open lightbox
		 */
		openLightbox: function(index) {
			this.currentIndex = index;
			this.updateLightboxImage();
			this.lightbox.style.display = 'flex';
			document.body.style.overflow = 'hidden';
		},

		/**
		 * Close lightbox
		 */
		closeLightbox: function() {
			this.lightbox.style.display = 'none';
			document.body.style.overflow = '';
		},

		/**
		 * Show previous image
		 */
		prevImage: function() {
			this.currentIndex = (this.currentIndex - 1 + this.images.length) % this.images.length;
			this.updateLightboxImage();
		},

		/**
		 * Show next image
		 */
		nextImage: function() {
			this.currentIndex = (this.currentIndex + 1) % this.images.length;
			this.updateLightboxImage();
		},

		/**
		 * Update lightbox image
		 */
		updateLightboxImage: function() {
			const img = this.lightbox.querySelector('.fs-lightbox-image');
			const counter = this.lightbox.querySelector('.fs-lightbox-current');

			if (img && this.images[this.currentIndex]) {
				img.src = this.images[this.currentIndex].url;
			}

			if (counter) {
				counter.textContent = this.currentIndex + 1;
			}
		}
	};

	/**
	 * Tabs Module
	 */
	const Tabs = {
		/**
		 * Initialize tabs
		 */
		init: function() {
			const tabButtons = document.querySelectorAll('.fs-specs-tab-button');
			if (tabButtons.length === 0) {
				return;
			}

			this.bindEvents(tabButtons);
		},

		/**
		 * Bind tab events
		 */
		bindEvents: function(tabButtons) {
			const self = this;

			tabButtons.forEach(function(button) {
				button.addEventListener('click', function() {
					const tabIndex = this.dataset.tab;
					self.switchTab(tabIndex, tabButtons);
				});
			});
		},

		/**
		 * Switch tab
		 */
		switchTab: function(tabIndex, tabButtons) {
			// Update buttons
			tabButtons.forEach(function(button) {
				if (button.dataset.tab === tabIndex) {
					button.classList.add('active');
					button.setAttribute('aria-selected', 'true');
				} else {
					button.classList.remove('active');
					button.setAttribute('aria-selected', 'false');
				}
			});

			// Update panels
			const panels = document.querySelectorAll('.fs-specs-tab-panel');
			panels.forEach(function(panel) {
				if (panel.id === 'fs-spec-panel-' + tabIndex) {
					panel.classList.add('active');
					panel.removeAttribute('hidden');
				} else {
					panel.classList.remove('active');
					panel.setAttribute('hidden', '');
				}
			});
		}
	};

	/**
	 * Responsive Tables Module
	 * Wraps tables with a toolbar containing scroll buttons
	 */
	const ResponsiveTables = {
		scrollStep: 200,

		/**
		 * Initialize responsive tables
		 */
		init: function() {
			var self = this;
			var contentAreas = document.querySelectorAll(
				'.fs-product-content, .fs-spec-content, .fs-info-item-content'
			);

			contentAreas.forEach(function(area) {
				var tables = area.querySelectorAll('table');
				tables.forEach(function(table) {
					// Skip if already wrapped
					if (table.parentNode.classList.contains('fs-table-responsive')) {
						return;
					}

					self.wrapTable(table);
				});
			});

			// Recheck on resize
			window.addEventListener('resize', function() {
				var wrappers = document.querySelectorAll('.fs-table-responsive-wrap');
				wrappers.forEach(function(outerWrap) {
					var tableWrap = outerWrap.querySelector('.fs-table-responsive');
					self.updateState(outerWrap, tableWrap);
				});
			});
		},

		/**
		 * Wrap a table with toolbar and scroll container
		 */
		wrapTable: function(table) {
			var self = this;

			// Create outer wrapper
			var outerWrap = document.createElement('div');
			outerWrap.className = 'fs-table-responsive-wrap';

			// Create toolbar
			var toolbar = document.createElement('div');
			toolbar.className = 'fs-table-toolbar';

			var hint = document.createElement('span');
			hint.className = 'fs-table-toolbar-hint';
			hint.textContent = '← Scroll to view more →';

			var controls = document.createElement('div');
			controls.className = 'fs-table-toolbar-controls';

			var btnLeft = document.createElement('button');
			btnLeft.type = 'button';
			btnLeft.className = 'fs-table-scroll-btn fs-table-scroll-left';
			btnLeft.innerHTML = '‹';
			btnLeft.setAttribute('aria-label', 'Scroll left');

			var btnRight = document.createElement('button');
			btnRight.type = 'button';
			btnRight.className = 'fs-table-scroll-btn fs-table-scroll-right';
			btnRight.innerHTML = '›';
			btnRight.setAttribute('aria-label', 'Scroll right');

			controls.appendChild(btnLeft);
			controls.appendChild(btnRight);
			toolbar.appendChild(hint);
			toolbar.appendChild(controls);

			// Create table wrapper
			var tableWrap = document.createElement('div');
			tableWrap.className = 'fs-table-responsive';

			// Assemble
			table.parentNode.insertBefore(outerWrap, table);
			outerWrap.appendChild(toolbar);
			outerWrap.appendChild(tableWrap);
			tableWrap.appendChild(table);

			// Button click handlers
			btnLeft.addEventListener('click', function() {
				tableWrap.scrollLeft -= self.scrollStep;
			});

			btnRight.addEventListener('click', function() {
				tableWrap.scrollLeft += self.scrollStep;
			});

			// Update state on scroll
			tableWrap.addEventListener('scroll', function() {
				self.updateState(outerWrap, tableWrap);
			});

			// Initial state
			self.updateState(outerWrap, tableWrap);
		},

		/**
		 * Update scrollable state and button disabled states
		 */
		updateState: function(outerWrap, tableWrap) {
			var isScrollable = tableWrap.scrollWidth > tableWrap.clientWidth;
			var scrollLeft = tableWrap.scrollLeft;
			var maxScroll = tableWrap.scrollWidth - tableWrap.clientWidth;

			if (isScrollable) {
				outerWrap.classList.add('is-scrollable');
			} else {
				outerWrap.classList.remove('is-scrollable');
			}

			// Update button states
			var btnLeft = outerWrap.querySelector('.fs-table-scroll-left');
			var btnRight = outerWrap.querySelector('.fs-table-scroll-right');

			if (btnLeft) {
				btnLeft.disabled = scrollLeft <= 0;
			}
			if (btnRight) {
				btnRight.disabled = scrollLeft >= maxScroll - 2;
			}

			// Update fade
			if (scrollLeft >= maxScroll - 2) {
				outerWrap.classList.add('is-scrolled-end');
			} else {
				outerWrap.classList.remove('is-scrolled-end');
			}
		}
	};

	/**
	 * Initialize on DOM ready
	 */
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', function() {
			Gallery.init();
			Tabs.init();
			ResponsiveTables.init();
		});
	} else {
		Gallery.init();
		Tabs.init();
		ResponsiveTables.init();
	}

})();
