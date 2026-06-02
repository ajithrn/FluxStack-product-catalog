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
	 * Conditional Fields Module
	 * Shows/hides fields based on another field's value.
	 */
	const ConditionalFields = {
		init: function() {
			var fields = document.querySelectorAll('.fs-settings-field--conditional');
			if (!fields.length) return;

			var self = this;
			fields.forEach(function(field) {
				var dependsOn = field.dataset.dependsOn;
				var showWhen = (field.dataset.showWhen || '').split(',');
				var source = document.getElementById(dependsOn);

				if (!source) return;

				// Initial state.
				self.toggle(field, source, showWhen);

				// Listen for changes.
				source.addEventListener('change', function() {
					self.toggle(field, source, showWhen);
				});
			});
		},

		toggle: function(field, source, showWhen) {
			if (showWhen.indexOf(source.value) !== -1) {
				field.style.display = '';
			} else {
				field.style.display = 'none';
			}
		}
	};

	/**
	 * GF Field Picker Module
	 * Loads form fields via AJAX when a Gravity Forms form is selected.
	 */
	const GFFieldPicker = {
		init: function() {
			var formSelect = document.getElementById('fs-gf-form-select');
			var fieldSelect = document.getElementById('fs-gf-field-select');
			if (!formSelect || !fieldSelect) return;

			var self = this;

			// Load fields for the initially selected form (if any).
			if (formSelect.value) {
				self.loadFields(formSelect.value, fieldSelect);
			}

			// Reload fields when form selection changes.
			formSelect.addEventListener('change', function() {
				self.loadFields(this.value, fieldSelect);
			});
		},

		loadFields: function(formId, fieldSelect) {
			if (!formId) {
				fieldSelect.innerHTML = '<option value="">— Select a field —</option>';
				return;
			}

			fieldSelect.innerHTML = '<option value="">Loading…</option>';
			fieldSelect.disabled = true;

			var formData = new URLSearchParams();
			formData.append('action', 'fs_gf_get_form_fields');
			formData.append('nonce', config.nonce);
			formData.append('form_id', formId);

			fetch(config.ajaxUrl, {
				method: 'POST',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
				body: formData
			})
			.then(function(r) { return r.json(); })
			.then(function(result) {
				fieldSelect.innerHTML = '<option value="">— Select a field —</option>';
				if (result.success && result.data && result.data.fields) {
					result.data.fields.forEach(function(field) {
						var opt = document.createElement('option');
						opt.value = field.id;
						opt.textContent = field.label + ' (' + field.type + ')';
						fieldSelect.appendChild(opt);
					});

					// Restore previously saved value.
					var savedValue = fieldSelect.dataset.savedValue;
					if (savedValue) {
						fieldSelect.value = savedValue;
					}
				}
			})
			.catch(function() {
				fieldSelect.innerHTML = '<option value="">Error loading fields</option>';
			})
			.finally(function() {
				fieldSelect.disabled = false;
			});
		}
	};

	/**
	 * Initialize on DOM ready.
	 */
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', function() {
			Tabs.init();
			Save.init();
			ConditionalFields.init();
			GFFieldPicker.init();
		});
	} else {
		Tabs.init();
		Save.init();
		ConditionalFields.init();
		GFFieldPicker.init();
	}

})();
