/**
 * Admin Settings Page JavaScript
 *
 * Handles tab switching and AJAX save.
 *
 * @package FS_Product_Catalog
 */

(function() {
	'use strict';

	const config = window.fsProductSettings || {};

	/**
	 * Tabs Module
	 */
	const Tabs = {
		init: function() {
			const tabs = document.querySelectorAll('.fs-settings-tabs__tab');
			if (!tabs.length) return;

			tabs.forEach(function(tab) {
				tab.addEventListener('click', function() {
					Tabs.switchTab(this.dataset.tab, tabs);
				});
			});
		},

		switchTab: function(tabId, tabs) {
			// Update tab buttons.
			tabs.forEach(function(tab) {
				tab.classList.toggle('is-active', tab.dataset.tab === tabId);
			});

			// Update panels.
			var panels = document.querySelectorAll('.fs-settings-panel');
			panels.forEach(function(panel) {
				panel.classList.toggle('is-active', panel.dataset.panel === tabId);
			});
		}
	};

	/**
	 * Save Module
	 */
	const Save = {
		isSaving: false,

		init: function() {
			var saveBtn = document.getElementById('fs-settings-save');
			if (!saveBtn) return;

			saveBtn.addEventListener('click', function() {
				Save.save();
			});

			// Ctrl+S / Cmd+S shortcut.
			document.addEventListener('keydown', function(e) {
				if ((e.ctrlKey || e.metaKey) && e.key === 's') {
					e.preventDefault();
					Save.save();
				}
			});
		},

		save: function() {
			if (this.isSaving) return;
			this.isSaving = true;

			var saveBtn = document.getElementById('fs-settings-save');
			var btnText = saveBtn.querySelector('.fs-settings-btn__text');
			var originalText = btnText.textContent;
			btnText.textContent = config.i18n.saving;
			saveBtn.disabled = true;
			saveBtn.classList.add('is-saving');

			// Gather all settings.
			var settings = {};
			var inputs = document.querySelectorAll('[name^="fs_settings["]');

			inputs.forEach(function(input) {
				var key = input.name.replace('fs_settings[', '').replace(']', '');

				if (input.type === 'checkbox') {
					settings[key] = input.checked ? '1' : '';
				} else {
					settings[key] = input.value;
				}
			});

			// Build form data.
			var formData = new URLSearchParams();
			formData.append('action', 'fs_product_save_settings');
			formData.append('nonce', config.nonce);

			Object.keys(settings).forEach(function(key) {
				formData.append('settings[' + key + ']', settings[key]);
			});

			// AJAX request.
			fetch(config.ajaxUrl, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
				},
				body: formData
			})
			.then(function(response) { return response.json(); })
			.then(function(result) {
				if (result.success) {
					Save.showToast(config.i18n.saved, 'success');
				} else {
					Save.showToast(config.i18n.error, 'error');
				}
			})
			.catch(function() {
				Save.showToast(config.i18n.error, 'error');
			})
			.finally(function() {
				btnText.textContent = originalText;
				saveBtn.disabled = false;
				saveBtn.classList.remove('is-saving');
				Save.isSaving = false;
			});
		},

		showToast: function(message, type) {
			var toast = document.getElementById('fs-settings-toast');
			if (!toast) return;

			toast.textContent = message;
			toast.className = 'fs-settings-toast fs-settings-toast--' + type;
			toast.hidden = false;

			setTimeout(function() {
				toast.hidden = true;
			}, 3000);
		}
	};

	/**
	 * Initialize on DOM ready.
	 */
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', function() {
			Tabs.init();
			Save.init();
		});
	} else {
		Tabs.init();
		Save.init();
	}

})();
