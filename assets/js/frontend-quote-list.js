/**
 * Frontend Quote List JavaScript
 *
 * Manages the quote list feature: localStorage CRUD, panel UI,
 * floating trigger badge, form page integration.
 *
 * @package FS_Product_Catalog
 */

(function() {
	'use strict';

	var config = (window.fsProductCatalog && window.fsProductCatalog.quoteList) || {};

	if (!config.enabled) return;

	/**
	 * QuoteList Module
	 */
	var QuoteList = {
		STORAGE_KEY: 'fs_quote_list',

		// ─── Core ───────────────────────────────────────────────

		init: function() {
			this.bindAddButtons();
			this.bindTriggerButton();
			this.bindPanelEvents();
			this.renderBadge();
			this.checkFormPage();
			this.checkSuccessPage();
		},

		getItems: function() {
			try {
				var data = localStorage.getItem(this.STORAGE_KEY);
				return data ? JSON.parse(data) : [];
			} catch (e) {
				return [];
			}
		},

		setItems: function(items) {
			try {
				localStorage.setItem(this.STORAGE_KEY, JSON.stringify(items));
			} catch (e) {
				// localStorage full or unavailable
			}
		},

		addItem: function(productData) {
			var items = this.getItems();

			// Check if already in list
			var existing = null;
			for (var i = 0; i < items.length; i++) {
				if (items[i].id === productData.id) {
					existing = i;
					break;
				}
			}

			if (existing !== null) {
				// Increment qty by the added quantity
				var moq = parseInt(items[existing].moq, 10) || 1;
				items[existing].qty = Math.max(items[existing].qty + (productData.qty || 1), moq);
			} else {
				// Check max items
				if (items.length >= (config.maxItems || 50)) {
					this.showToast(config.i18n.maxReached || 'Maximum items reached.');
					return false;
				}
				items.push(productData);
			}

			this.setItems(items);
			this.renderBadge();
			this.renderPanel();
			return true;
		},

		removeItem: function(productId) {
			var items = this.getItems();
			items = items.filter(function(item) {
				return item.id !== productId;
			});
			this.setItems(items);
			this.renderBadge();
			this.renderPanel();
			this.refreshFormTable();
			this.updateAddButtonStates();
		},

		updateQty: function(productId, qty) {
			var items = this.getItems();
			for (var i = 0; i < items.length; i++) {
				if (items[i].id === productId) {
					var moq = parseInt(items[i].moq, 10) || 1;
					items[i].qty = Math.max(qty, moq);
					break;
				}
			}
			this.setItems(items);
			this.renderBadge();
			this.syncFormField();
		},

		clearAll: function() {
			this.setItems([]);
			this.renderBadge();
			this.renderPanel();
			this.updateAddButtonStates();
		},

		getCount: function() {
			return this.getItems().length;
		},

		// ─── UI: Badge ──────────────────────────────────────────

		renderBadge: function() {
			var badge = document.getElementById('fs-quote-list-badge');
			var trigger = document.getElementById('fs-quote-list-trigger');
			var count = this.getCount();

			if (badge) {
				badge.textContent = count;
				badge.style.display = count > 0 ? '' : 'none';
			}
			if (trigger) {
				trigger.style.display = count > 0 ? '' : 'none';
			}

			// Update add button states
			this.updateAddButtonStates();
		},

		// ─── UI: Panel ──────────────────────────────────────────

		openPanel: function() {
			var panel = document.getElementById('fs-quote-list-panel');
			var overlay = document.getElementById('fs-quote-list-overlay');
			if (panel) {
				panel.classList.add('is-open');
				panel.setAttribute('aria-hidden', 'false');
			}
			if (overlay) {
				overlay.classList.add('is-visible');
				overlay.setAttribute('aria-hidden', 'false');
			}
			document.body.style.overflow = 'hidden';
			this.renderPanel();
		},

		closePanel: function() {
			var panel = document.getElementById('fs-quote-list-panel');
			var overlay = document.getElementById('fs-quote-list-overlay');
			if (panel) {
				panel.classList.remove('is-open');
				panel.setAttribute('aria-hidden', 'true');
			}
			if (overlay) {
				overlay.classList.remove('is-visible');
				overlay.setAttribute('aria-hidden', 'true');
			}
			document.body.style.overflow = '';
		},

		renderPanel: function() {
			var itemsContainer = document.getElementById('fs-quote-list-items');
			var emptyContainer = document.getElementById('fs-quote-list-empty');
			var footerContainer = document.getElementById('fs-quote-list-footer');
			var headerCount = document.getElementById('fs-quote-list-header-count');

			if (!itemsContainer) return;

			var items = this.getItems();

			// Header count
			if (headerCount) {
				headerCount.textContent = items.length > 0 ? '(' + items.length + ' ' + (config.i18n.items || 'items') + ')' : '';
			}

			if (items.length === 0) {
				itemsContainer.innerHTML = '';
				if (emptyContainer) {
					emptyContainer.style.display = '';
					var emptyTitle = document.getElementById('fs-quote-list-empty-title');
					var emptyText = document.getElementById('fs-quote-list-empty-text');
					if (emptyTitle) emptyTitle.textContent = config.i18n.emptyTitle || 'Your list is empty';
					if (emptyText) emptyText.textContent = config.i18n.emptyText || 'Browse products to add items.';
				}
				if (footerContainer) footerContainer.style.display = 'none';
				return;
			}

			if (emptyContainer) emptyContainer.style.display = 'none';
			if (footerContainer) footerContainer.style.display = '';

			var html = '';
			for (var i = 0; i < items.length; i++) {
				html += this.renderPanelItem(items[i]);
			}
			itemsContainer.innerHTML = html;

			// Bind item events
			this.bindPanelItemEvents();

			// Update submit link
			var submitBtn = document.getElementById('fs-quote-list-submit');
			if (submitBtn) {
				submitBtn.href = config.formUrl || '/custom-quote/';
			}
		},

		renderPanelItem: function(item) {
			var subtitle = item.sku || item.partNo || '';
			var moq = parseInt(item.moq, 10) || 1;

			var imgHtml = item.image
				? '<img src="' + this.escAttr(item.image) + '" alt="" class="fs-quote-list-item__img" />'
				: '<span class="fs-quote-list-item__no-img"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" opacity="0.3"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg></span>';

			return '<div class="fs-quote-list-item" data-product-id="' + item.id + '">' +
				'<div class="fs-quote-list-item__image">' + imgHtml + '</div>' +
				'<div class="fs-quote-list-item__info">' +
					'<a href="' + this.escAttr(item.url) + '" class="fs-quote-list-item__name">' + this.escHtml(item.name) + '</a>' +
					(subtitle ? '<span class="fs-quote-list-item__sku">' + this.escHtml(subtitle) + '</span>' : '') +
				'</div>' +
				'<div class="fs-quote-list-item__controls">' +
					'<div class="fs-quote-list-item__qty">' +
						'<button type="button" class="fs-quote-list-qty-btn" data-action="minus" data-id="' + item.id + '" ' + (item.qty <= moq ? 'disabled' : '') + '>&minus;</button>' +
						'<input type="number" class="fs-quote-list-qty-input" data-id="' + item.id + '" value="' + item.qty + '" min="' + moq + '" max="9999" />' +
						'<button type="button" class="fs-quote-list-qty-btn" data-action="plus" data-id="' + item.id + '">&plus;</button>' +
					'</div>' +
					'<button type="button" class="fs-quote-list-item__remove" data-id="' + item.id + '" aria-label="Remove">' +
						'<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>' +
					'</button>' +
				'</div>' +
			'</div>';
		},

		bindPanelItemEvents: function() {
			var self = this;

			// Qty buttons
			document.querySelectorAll('.fs-quote-list-qty-btn').forEach(function(btn) {
				btn.addEventListener('click', function() {
					var id = parseInt(this.dataset.id, 10);
					var input = document.querySelector('.fs-quote-list-qty-input[data-id="' + id + '"]');
					if (!input) return;

					var val = parseInt(input.value, 10) || 1;
					var items = self.getItems();
					var item = items.find(function(i) { return i.id === id; });
					var moq = item ? (parseInt(item.moq, 10) || 1) : 1;

					if (this.dataset.action === 'minus' && val > moq) {
						input.value = val - 1;
					} else if (this.dataset.action === 'plus' && val < 9999) {
						input.value = val + 1;
					}

					self.updateQty(id, parseInt(input.value, 10));

					// Update minus button disabled state
					var minusBtn = document.querySelector('.fs-quote-list-qty-btn[data-action="minus"][data-id="' + id + '"]');
					if (minusBtn) minusBtn.disabled = (parseInt(input.value, 10) <= moq);
				});
			});

			// Qty input change
			document.querySelectorAll('.fs-quote-list-qty-input').forEach(function(input) {
				input.addEventListener('change', function() {
					var id = parseInt(this.dataset.id, 10);
					var val = parseInt(this.value, 10) || 1;
					var items = self.getItems();
					var item = items.find(function(i) { return i.id === id; });
					var moq = item ? (parseInt(item.moq, 10) || 1) : 1;

					if (val < moq) {
						this.value = moq;
						val = moq;
					}
					if (val > 9999) {
						this.value = 9999;
						val = 9999;
					}

					self.updateQty(id, val);
				});
			});

			// Remove buttons
			document.querySelectorAll('.fs-quote-list-item__remove').forEach(function(btn) {
				btn.addEventListener('click', function() {
					var id = parseInt(this.dataset.id, 10);
					self.removeItem(id);
				});
			});
		},

		// ─── UI: Add Buttons ────────────────────────────────────

		bindAddButtons: function() {
			var self = this;

			document.querySelectorAll('.fs-add-to-list').forEach(function(btn) {
				btn.addEventListener('click', function(e) {
					e.preventDefault();
					e.stopPropagation();

					var productId = parseInt(this.dataset.productId, 10);
					var items = self.getItems();
					var alreadyInList = false;
					for (var i = 0; i < items.length; i++) {
						if (items[i].id === productId) {
							alreadyInList = true;
							break;
						}
					}

					if (alreadyInList) {
						self.removeItem(productId);
						return;
					}

					var initialQty = parseInt(this.dataset.productMoq, 10) || 1;
					if (this.closest('.fs-product-inquiry')) {
						var qtyInput = document.getElementById('fs-inquiry-qty');
						if (qtyInput) {
							initialQty = parseInt(qtyInput.value, 10) || initialQty;
						}
					}

					var productData = {
						id: productId,
						name: this.dataset.productName || '',
						sku: this.dataset.productSku || '',
						partNo: this.dataset.productPartNo || '',
						uom: this.dataset.productUom || '',
						moq: parseInt(this.dataset.productMoq, 10) || 1,
						qty: initialQty,
						url: this.dataset.productUrl || '',
						image: this.dataset.productImage || ''
					};

					var added = self.addItem(productData);
					if (added !== false) {
						self.showAddedFeedback(this);
					}
				});
			});

			// Set initial states
			this.updateAddButtonStates();
		},

		showAddedFeedback: function(btn) {
			var textEl = btn.querySelector('.fs-add-to-list__text');
			var originalText = textEl ? textEl.textContent : '';

			btn.classList.add('is-added');
			if (textEl) textEl.textContent = config.i18n.added || 'Added ✓';

			setTimeout(function() {
				btn.classList.remove('is-added');
				btn.classList.add('is-in-list');
				if (textEl) textEl.textContent = config.i18n.inList || 'In List';
			}, 1500);
		},

		updateAddButtonStates: function() {
			var items = this.getItems();
			var ids = items.map(function(item) { return item.id; });

			document.querySelectorAll('.fs-add-to-list').forEach(function(btn) {
				var productId = parseInt(btn.dataset.productId, 10);
				var textEl = btn.querySelector('.fs-add-to-list__text');

				if (ids.indexOf(productId) !== -1) {
					btn.classList.add('is-in-list');
					if (textEl) textEl.textContent = config.i18n.inList || 'In List';
				} else {
					btn.classList.remove('is-in-list');
					if (textEl) textEl.textContent = config.i18n.addToList || 'Add to List';
				}
			});
		},

		// ─── UI: Trigger Button ─────────────────────────────────

		bindTriggerButton: function() {
			var self = this;
			var trigger = document.getElementById('fs-quote-list-trigger');
			if (trigger) {
				trigger.addEventListener('click', function() {
					self.openPanel();
				});
			}
		},

		// ─── UI: Panel Events ───────────────────────────────────

		bindPanelEvents: function() {
			var self = this;

			var closeBtn = document.getElementById('fs-quote-list-close');
			if (closeBtn) {
				closeBtn.addEventListener('click', function() {
					self.closePanel();
				});
			}

			var overlay = document.getElementById('fs-quote-list-overlay');
			if (overlay) {
				overlay.addEventListener('click', function() {
					self.closePanel();
				});
			}

			var clearBtn = document.getElementById('fs-quote-list-clear');
			if (clearBtn) {
				var footer = clearBtn.closest('.fs-quote-list-footer');
				if (footer) {
					var defaultWrap = footer.querySelector('.fs-quote-list-footer__default');
					var confirmWrap = footer.querySelector('.fs-quote-list-footer__confirm');

					if (!defaultWrap || !confirmWrap) {
						defaultWrap = document.createElement('div');
						defaultWrap.className = 'fs-quote-list-footer__default';
						while (footer.firstChild) {
							defaultWrap.appendChild(footer.firstChild);
						}
						footer.appendChild(defaultWrap);

						confirmWrap = document.createElement('div');
						confirmWrap.className = 'fs-quote-list-footer__confirm';
						confirmWrap.style.display = 'none';
						confirmWrap.innerHTML = 
							'<span class="fs-quote-list-footer__confirm-text">' + (config.i18n.clearConfirmText || 'Clear all items?') + '</span>' +
							'<div class="fs-quote-list-footer__confirm-actions">' +
								'<button type="button" class="fs-quote-list-footer__confirm-btn fs-quote-list-footer__confirm-btn--yes">' + (config.i18n.yes || 'Yes') + '</button>' +
								'<button type="button" class="fs-quote-list-footer__confirm-btn fs-quote-list-footer__confirm-btn--no">' + (config.i18n.cancel || 'Cancel') + '</button>' +
							'</div>';
						footer.appendChild(confirmWrap);

						var yesBtn = confirmWrap.querySelector('.fs-quote-list-footer__confirm-btn--yes');
						var noBtn = confirmWrap.querySelector('.fs-quote-list-footer__confirm-btn--no');

						if (yesBtn) {
							yesBtn.addEventListener('click', function() {
								self.setItems([]);
								self.renderBadge();
								self.renderPanel();
								self.updateAddButtonStates();
								defaultWrap.style.display = '';
								confirmWrap.style.display = 'none';
							});
						}

						if (noBtn) {
							noBtn.addEventListener('click', function() {
								defaultWrap.style.display = '';
								confirmWrap.style.display = 'none';
							});
						}
					}

					clearBtn.addEventListener('click', function(e) {
						e.preventDefault();
						defaultWrap.style.display = 'none';
						confirmWrap.style.display = '';
					});
				}
			}

			// Escape key
			document.addEventListener('keydown', function(e) {
				if (e.key === 'Escape') {
					var panel = document.getElementById('fs-quote-list-panel');
					if (panel && panel.classList.contains('is-open')) {
						self.closePanel();
					}
				}
			});
		},

		// ─── Form Page Integration ──────────────────────────────

		checkFormPage: function() {
			var fieldEl = document.querySelector(config.fieldSelector || '#fs-products-field');
			var tableEl = document.querySelector(config.tableSelector || '#fs-products-table');

			if (!fieldEl && !tableEl) return;

			var items = this.getItems();

			if (tableEl) {
				this.renderFormTable(tableEl, items);
			}

			if (fieldEl) {
				this.injectFormField(fieldEl, items);
			}
		},

		renderFormTable: function(container, items) {
			if (items.length === 0) {
				container.innerHTML = '<div class="fs-quote-list-form-empty">' +
					'<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" opacity="0.3">' +
					'<path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>' +
					'</svg>' +
					'<p class="fs-quote-list-form-empty__title">' + (config.i18n.noProducts || 'No products selected') + '</p>' +
					'<p class="fs-quote-list-form-empty__text">' + (config.i18n.emptyText || 'Browse products to add items.') + '</p>' +
					'<a href="' + (config.i18n.browseLink || '/product/') + '" class="fs-quote-list-form-empty__link">' +
					'Browse Products</a>' +
					'</div>';
				return;
			}

			var html = '<div class="fs-quote-list-form-cart">';

			// Header
			html += '<div class="fs-quote-list-form-cart__header">' +
				'<span class="fs-quote-list-form-cart__title">Selected Products</span>' +
				'<span class="fs-quote-list-form-cart__count">' + items.length + ' ' + (config.i18n.items || 'items') + '</span>' +
				'</div>';

			// Items — reuse the same renderPanelItem markup
			html += '<div class="fs-quote-list-form-cart__items">';
			for (var i = 0; i < items.length; i++) {
				html += this.renderPanelItem(items[i]);
			}
			html += '</div>';

			// Footer with clear button & confirm wrappers
			html += '<div class="fs-quote-list-form-cart__footer">' +
				'<div class="fs-quote-list-form-cart__default">' +
					'<button type="button" class="fs-quote-list-form-clear" id="fs-quote-list-form-clear">' + (config.i18n.clearAll || 'Clear All') + '</button>' +
				'</div>' +
				'<div class="fs-quote-list-form-cart__confirm" style="display:none">' +
					'<span class="fs-quote-list-form-cart__confirm-text">' + (config.i18n.clearConfirmText || 'Clear all items?') + '</span>' +
					'<div class="fs-quote-list-form-cart__confirm-actions">' +
						'<button type="button" class="fs-quote-list-form-confirm-btn fs-quote-list-form-confirm-btn--yes">' + (config.i18n.yes || 'Yes') + '</button>' +
						'<button type="button" class="fs-quote-list-form-confirm-btn fs-quote-list-form-confirm-btn--no">' + (config.i18n.cancel || 'Cancel') + '</button>' +
					'</div>' +
				'</div>' +
				'</div>';

			html += '</div>';
			container.innerHTML = html;

			// Bind qty/remove events (reuse panel item event binding)
			this.bindPanelItemEvents();

			// Bind clear button
			var self = this;
			var clearBtn = document.getElementById('fs-quote-list-form-clear');
			if (clearBtn) {
				var footer = clearBtn.closest('.fs-quote-list-form-cart__footer');
				if (footer) {
					var defaultWrap = footer.querySelector('.fs-quote-list-form-cart__default');
					var confirmWrap = footer.querySelector('.fs-quote-list-form-cart__confirm');

					clearBtn.addEventListener('click', function(e) {
						e.preventDefault();
						if (defaultWrap && confirmWrap) {
							defaultWrap.style.display = 'none';
							confirmWrap.style.display = '';
						}
					});

					var yesBtn = footer.querySelector('.fs-quote-list-form-confirm-btn--yes');
					var noBtn = footer.querySelector('.fs-quote-list-form-confirm-btn--no');

					if (yesBtn) {
						yesBtn.addEventListener('click', function() {
							self.setItems([]);
							self.renderBadge();
							self.updateAddButtonStates();
							self.renderFormTable(container, []);
							self.syncFormField();
						});
					}

					if (noBtn) {
						noBtn.addEventListener('click', function() {
							if (defaultWrap && confirmWrap) {
								defaultWrap.style.display = '';
								confirmWrap.style.display = 'none';
							}
						});
					}
				}
			}

			// Override the default qty/remove handlers to also sync the form field.
			// The panel events already update localStorage; we just need to re-render
			// the table and re-inject the hidden field after each change.
			self._formTableContainer = container;
		},

		injectFormField: function(fieldEl, items) {
			if (items.length === 0) {
				fieldEl.value = '';
				return;
			}

			var template = config.template || '{name} | SKU: {sku} | Part No: {part_no} | Qty: {qty}';
			var lines = [];

			for (var i = 0; i < items.length; i++) {
				var line = this.applyTemplate(template, items[i]);
				if (line) {
					lines.push(line);
				}
			}

			fieldEl.value = lines.join('\n');
		},

		/**
		 * Sync the hidden form field with current localStorage items.
		 * Called after qty/remove changes from the form table cart.
		 */
		syncFormField: function() {
			var fieldEl = document.querySelector(config.fieldSelector || '#fs-products-field');
			if (fieldEl) {
				this.injectFormField(fieldEl, this.getItems());
			}
		},

		/**
		 * Re-render the form table cart and sync the hidden field.
		 * Called after item removal from the form table.
		 */
		refreshFormTable: function() {
			if (this._formTableContainer) {
				this.renderFormTable(this._formTableContainer, this.getItems());
			}
			this.syncFormField();
		},

		applyTemplate: function(template, item) {
			var siteUrl = window.location.origin;
			var fullUrl = item.url;
			if (fullUrl && fullUrl.indexOf('http') !== 0) {
				fullUrl = siteUrl + fullUrl;
			}

			var replacements = {
				'{name}': item.name || '',
				'{sku}': item.sku || '',
				'{part_no}': item.partNo || '',
				'{qty}': item.qty || 1,
				'{url}': fullUrl || '',
				'{id}': item.id || '',
				'{uom}': item.uom || ''
			};

			var result = template;
			for (var tag in replacements) {
				result = result.replace(new RegExp(tag.replace(/[{}]/g, '\\$&'), 'g'), replacements[tag]);
			}

			// Remove pipe-separated segments where the value is empty
			// Handles patterns like "| SKU:  |" or "| Part No:  |" or trailing "| SKU: "
			// Split by pipe, filter out segments that end with ": " (label with no value)
			var segments = result.split('|').map(function(s) { return s.trim(); });
			segments = segments.filter(function(s) {
				// Remove empty segments
				if (!s) return false;
				// Remove segments that are just a label with no value (e.g. "SKU: " or "Part No: ")
				if (/^[^:]+:\s*$/.test(s)) return false;
				return true;
			});
			result = segments.join(' | ').trim();

			return result;
		},

		// ─── Success Page Detection ─────────────────────────────

		checkSuccessPage: function() {
			if (!config.successUrl) return;

			var currentPath = window.location.pathname;
			var successPath = config.successUrl;

			// Normalize paths
			if (successPath.indexOf('http') === 0) {
				try {
					successPath = new URL(successPath).pathname;
				} catch (e) {
					return;
				}
			}

			// Remove trailing slashes for comparison
			currentPath = currentPath.replace(/\/+$/, '');
			successPath = successPath.replace(/\/+$/, '');

			if (currentPath === successPath) {
				this.setItems([]);
				this.renderBadge();
			}
		},

		// ─── Utilities ──────────────────────────────────────────

		escHtml: function(str) {
			var div = document.createElement('div');
			div.textContent = str;
			return div.innerHTML;
		},

		escAttr: function(str) {
			return String(str)
				.replace(/&/g, '&amp;')
				.replace(/"/g, '&quot;')
				.replace(/'/g, '&#39;')
				.replace(/</g, '&lt;')
				.replace(/>/g, '&gt;');
		},

		showToast: function(message) {
			// Simple toast notification
			var existing = document.querySelector('.fs-quote-list-toast');
			if (existing) existing.remove();

			var toast = document.createElement('div');
			toast.className = 'fs-quote-list-toast';
			toast.textContent = message;
			document.body.appendChild(toast);

			setTimeout(function() {
				toast.classList.add('is-visible');
			}, 10);

			setTimeout(function() {
				toast.classList.remove('is-visible');
				setTimeout(function() { toast.remove(); }, 300);
			}, 3000);
		}
	};

	/**
	 * Initialize on DOM ready
	 */
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', function() {
			QuoteList.init();
		});
	} else {
		QuoteList.init();
	}

})();
