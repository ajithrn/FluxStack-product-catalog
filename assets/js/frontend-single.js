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
	 * Initialize on DOM ready
	 */
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', function() {
			Gallery.init();
			Tabs.init();
		});
	} else {
		Gallery.init();
		Tabs.init();
	}

})();
