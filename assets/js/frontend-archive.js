/**
 * Frontend Archive JavaScript
 * 
 * Handles AJAX filtering, infinite scroll, and mobile sidebar
 * 
 * @package FS_Product_Catalog
 */

(function() {
	'use strict';

	// Get localized data
	const config = window.fsProductCatalog || {};

	/**
	 * Filters Module
	 */
	const Filters = {
		isFiltering: false,
		searchTimeout: null,

		/**
		 * Initialize filters
		 */
		init: function() {
			this.bindEvents();
		},

		/**
		 * Bind filter events
		 */
		bindEvents: function() {
			const self = this;

			// Search input
			const searchInput = document.querySelector('.fs-search-input');
			if (searchInput) {
				searchInput.addEventListener('input', function() {
					clearTimeout(self.searchTimeout);
					self.searchTimeout = setTimeout(function() {
						self.applyFilters();
					}, 500);
				});
			}

			// Filter checkboxes
			const filterCheckboxes = document.querySelectorAll('.fs-filter-option input[type="checkbox"], .fs-filter-tag input[type="checkbox"]');
			filterCheckboxes.forEach(function(checkbox) {
				checkbox.addEventListener('change', function() {
					self.applyFilters();
				});
			});

			// Clear filters
			const clearBtn = document.querySelector('.fs-clear-filters');
			if (clearBtn) {
				clearBtn.addEventListener('click', function() {
					self.clearFilters();
				});
			}

			// Mobile filter toggle
			const filterToggle = document.querySelector('.fs-filters-toggle');
			if (filterToggle) {
				filterToggle.addEventListener('click', function() {
					const filtersWrap = document.querySelector('.fs-filters-wrap');
					if (filtersWrap) {
						filtersWrap.classList.toggle('active');
					}
				});
			}

			// Filter group toggles
			const groupToggles = document.querySelectorAll('.fs-filter-toggle');
			groupToggles.forEach(function(toggle) {
				toggle.addEventListener('click', function() {
					const group = this.closest('.fs-filter-group');
					if (group) {
						group.classList.toggle('active');
					}
				});
			});
		},

		/**
		 * Apply filters
		 */
		applyFilters: function() {
			if (this.isFiltering) {
				return;
			}

			this.isFiltering = true;

			const data = this.getFilterData();
			const grid = document.querySelector('.fs-product-grid');
			const resultsCount = document.querySelector('.fs-product-results-count');

			// Show loading
			if (grid) {
				grid.style.opacity = '0.5';
			}

		// AJAX request
		const formData = new URLSearchParams();
		formData.append('action', 'fs_filter_products');
		formData.append('nonce', config.nonce);
		formData.append('search', data.search);
		formData.append('paged', data.paged);
		formData.append('per_page', data.per_page);
		
		// Append arrays properly
		data.categories.forEach(function(value) {
			formData.append('categories[]', value);
		});
		data.brands.forEach(function(value) {
			formData.append('brands[]', value);
		});
		data.types.forEach(function(value) {
			formData.append('types[]', value);
		});
		data.tags.forEach(function(value) {
			formData.append('tags[]', value);
		});

		fetch(config.ajaxUrl, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
			body: formData
		})
			.then(response => response.json())
			.then(result => {
				if (result.success && grid) {
					grid.innerHTML = result.html;
					grid.style.opacity = '1';

					// Update results count
					if (resultsCount) {
						const total = result.found || 0;
						const text = total === 1 
							? config.i18n.showing + ' 1 ' + config.i18n.of + ' 1 ' + config.i18n.products
							: config.i18n.showing + ' ' + total + ' ' + config.i18n.of + ' ' + total + ' ' + config.i18n.products;
						resultsCount.innerHTML = '<span class="count">' + total + '</span> ' + config.i18n.products;
					}

					// Update load more button
					const loadMoreWrap = document.querySelector('.fs-product-load-more-wrap');
					const loadMoreBtn = document.querySelector('.fs-product-load-more');
					if (loadMoreBtn) {
						loadMoreBtn.dataset.page = '1';
						loadMoreBtn.dataset.maxPages = result.max_pages || 1;
						
						if (result.max_pages > 1) {
							loadMoreWrap.style.display = 'block';
							loadMoreBtn.style.display = 'inline-block';
						} else {
							loadMoreWrap.style.display = 'none';
						}
					}

					// Update active filters
					this.updateActiveFilters();
				}

				this.isFiltering = false;
			})
			.catch(error => {
				console.error('Filter error:', error);
				if (grid) {
					grid.style.opacity = '1';
				}
				this.isFiltering = false;
			});
		},

		/**
		 * Get filter data
		 */
		getFilterData: function() {
			const data = {
				search: '',
				categories: [],
				brands: [],
				types: [],
				tags: [],
				paged: 1,
				per_page: config.perPage || 12
			};

			// Search
			const searchInput = document.querySelector('.fs-search-input');
			if (searchInput) {
				data.search = searchInput.value;
			}

			// Categories
			const categoryCheckboxes = document.querySelectorAll('input[name="fs_category[]"]:checked');
			categoryCheckboxes.forEach(function(checkbox) {
				data.categories.push(checkbox.value);
			});

			// Brands
			const brandCheckboxes = document.querySelectorAll('input[name="fs_brand[]"]:checked');
			brandCheckboxes.forEach(function(checkbox) {
				data.brands.push(checkbox.value);
			});

			// Types
			const typeCheckboxes = document.querySelectorAll('input[name="fs_type[]"]:checked');
			typeCheckboxes.forEach(function(checkbox) {
				data.types.push(checkbox.value);
			});

			// Tags
			const tagCheckboxes = document.querySelectorAll('input[name="fs_tag[]"]:checked');
			tagCheckboxes.forEach(function(checkbox) {
				data.tags.push(checkbox.value);
			});

			return data;
		},

		/**
		 * Clear all filters
		 */
		clearFilters: function() {
			// Clear search
			const searchInput = document.querySelector('.fs-search-input');
			if (searchInput) {
				searchInput.value = '';
			}

			// Uncheck all checkboxes
			const checkboxes = document.querySelectorAll('.fs-filter-option input[type="checkbox"], .fs-filter-tag input[type="checkbox"]');
			checkboxes.forEach(function(checkbox) {
				checkbox.checked = false;
			});

			// Apply filters
			this.applyFilters();
		},

		/**
		 * Update active filters display
		 */
		updateActiveFilters: function() {
			const activeFiltersWrap = document.querySelector('.fs-active-filters');
			const activeFiltersList = document.querySelector('.fs-active-filters-list');
			
			if (!activeFiltersWrap || !activeFiltersList) {
				return;
			}

			const checkedFilters = document.querySelectorAll('.fs-filter-option input[type="checkbox"]:checked, .fs-filter-tag input[type="checkbox"]:checked');
			
			if (checkedFilters.length > 0) {
				activeFiltersWrap.style.display = 'block';
				activeFiltersList.innerHTML = '';

				checkedFilters.forEach(function(checkbox) {
					const label = checkbox.closest('label').querySelector('.fs-filter-label, .fs-tag-label');
					if (label) {
						const filterItem = document.createElement('span');
						filterItem.className = 'fs-active-filter-item';
						filterItem.innerHTML = label.textContent.trim() + ' <button type="button" class="fs-active-filter-remove" aria-label="Remove filter">×</button>';
						
						filterItem.querySelector('.fs-active-filter-remove').addEventListener('click', function() {
							checkbox.checked = false;
							Filters.applyFilters();
						});

						activeFiltersList.appendChild(filterItem);
					}
				});
			} else {
				activeFiltersWrap.style.display = 'none';
			}
		}
	};

	/**
	 * Infinite Scroll Module
	 */
	const InfiniteScroll = {
		isLoading: false,
		observer: null,

		/**
		 * Initialize infinite scroll
		 */
		init: function() {
			const loadMoreBtn = document.querySelector('.fs-product-load-more');
			if (!loadMoreBtn) {
				return;
			}

			this.bindEvents(loadMoreBtn);
			this.setupIntersectionObserver(loadMoreBtn);
		},

		/**
		 * Bind events
		 */
		bindEvents: function(loadMoreBtn) {
			const self = this;

			loadMoreBtn.addEventListener('click', function() {
				self.loadMore(this);
			});
		},

		/**
		 * Setup intersection observer for infinite scroll
		 */
		setupIntersectionObserver: function(loadMoreBtn) {
			const self = this;

			if ('IntersectionObserver' in window) {
				this.observer = new IntersectionObserver(function(entries) {
					entries.forEach(function(entry) {
						if (entry.isIntersecting && !self.isLoading) {
							const currentPage = parseInt(loadMoreBtn.dataset.page, 10);
							const maxPages = parseInt(loadMoreBtn.dataset.maxPages, 10);
							
							if (currentPage < maxPages) {
								self.loadMore(loadMoreBtn);
							}
						}
					});
				}, {
					rootMargin: '200px'
				});

				this.observer.observe(loadMoreBtn);
			}
		},

		/**
		 * Load more products
		 */
		loadMore: function(button) {
			if (this.isLoading) {
				return;
			}

			const currentPage = parseInt(button.dataset.page, 10);
			const maxPages = parseInt(button.dataset.maxPages, 10);

			if (currentPage >= maxPages) {
				button.textContent = config.i18n.noMore || 'No more products';
				button.disabled = true;
				return;
			}

			this.isLoading = true;
			const nextPage = currentPage + 1;

			// Show loading
			button.style.display = 'none';
			const loading = document.querySelector('.fs-product-loading');
			if (loading) {
				loading.style.display = 'flex';
			}

		// Get filter data
		const filterData = Filters.getFilterData();
		filterData.paged = nextPage;

		// Build form data properly
		const formData = new URLSearchParams();
		formData.append('action', 'fs_filter_products');
		formData.append('nonce', config.nonce);
		formData.append('search', filterData.search);
		formData.append('paged', filterData.paged);
		formData.append('per_page', filterData.per_page);
		
		// Append arrays properly
		filterData.categories.forEach(function(value) {
			formData.append('categories[]', value);
		});
		filterData.brands.forEach(function(value) {
			formData.append('brands[]', value);
		});
		filterData.types.forEach(function(value) {
			formData.append('types[]', value);
		});
		filterData.tags.forEach(function(value) {
			formData.append('tags[]', value);
		});

		// AJAX request
		fetch(config.ajaxUrl, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
			body: formData
		})
			.then(response => response.json())
			.then(result => {
				if (result.success) {
					const grid = document.querySelector('.fs-product-grid');
					if (grid) {
						grid.insertAdjacentHTML('beforeend', result.html);
					}

					button.dataset.page = nextPage;

					if (nextPage >= maxPages) {
						button.textContent = config.i18n.noMore || 'No more products';
						button.disabled = true;
					}
				}

				// Hide loading
				if (loading) {
					loading.style.display = 'none';
				}
				button.style.display = 'inline-block';
				this.isLoading = false;
			})
			.catch(error => {
				console.error('Load more error:', error);
				if (loading) {
					loading.style.display = 'none';
				}
				button.style.display = 'inline-block';
				this.isLoading = false;
			});
		}
	};

	/**
	 * Initialize on DOM ready
	 */
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', function() {
			Filters.init();
			InfiniteScroll.init();
		});
	} else {
		Filters.init();
		InfiniteScroll.init();
	}

})();
