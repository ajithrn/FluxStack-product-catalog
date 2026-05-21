/**
 * Frontend Common JavaScript
 * 
 * Shared sidebar interactions: toggle, collapsible sections, show more/less.
 * Loaded on all product pages (archive + single).
 * 
 * @package FS_Product_Catalog
 */

(function() {
	'use strict';

	/**
	 * Sidebar Module
	 */
	const Sidebar = {
		init: function() {
			this.bindMainToggle();
			this.bindCollapsible();
			this.bindShowMore();
		},

		/**
		 * Main sidebar toggle (mobile: show/hide entire filter panel)
		 */
		bindMainToggle: function() {
			var header = document.querySelector('.fs-filters-header');
			if (!header || header.dataset.bound) return;
			header.dataset.bound = '1';

			header.addEventListener('click', function() {
				var wrap = this.closest('.fs-filters-wrap');
				if (wrap) wrap.classList.toggle('active');
			});
		},

		/**
		 * Collapsible section headers (click title bar to expand/collapse)
		 */
		bindCollapsible: function() {
			var titles = document.querySelectorAll('.fs-filter-title--collapsible');
			titles.forEach(function(title) {
				if (title.dataset.bound) return;
				title.dataset.bound = '1';
				title.addEventListener('click', function(e) {
					e.stopPropagation();
					var group = this.closest('.fs-filter-group');
					if (group) {
						group.classList.toggle('is-collapsed');
					}
				});
			});
		},

		/**
		 * Show more/less toggle for long filter lists
		 */
		bindShowMore: function() {
			var buttons = document.querySelectorAll('.fs-filter-show-more');
			buttons.forEach(function(button) {
				if (button.dataset.bound) return;
				button.dataset.bound = '1';
				button.addEventListener('click', function() {
					var group = this.closest('.fs-filter-group');
					if (!group) return;

					var isExpanded = group.classList.contains('is-expanded');
					group.classList.toggle('is-expanded');

					if (isExpanded) {
						var hiddenCount = this.dataset.more || 0;
						this.textContent = 'Show more (' + hiddenCount + ')';
					} else {
						this.textContent = 'Show less';
					}
				});
			});
		}
	};

	/**
	 * Initialize on DOM ready
	 */
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', function() {
			Sidebar.init();
		});
	} else {
		Sidebar.init();
	}

})();
