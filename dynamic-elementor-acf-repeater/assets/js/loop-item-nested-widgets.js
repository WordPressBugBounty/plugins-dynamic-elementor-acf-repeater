/**
 * Re-enable Loop Grid and Loop Carousel in the widget panel of a Loop Item.
 *
 * Elementor Pro's Loop document hides both widgets from its panel through
 * `panel.widgets_settings[<widget>].show_in_panel = false`. The editor merges
 * that map into the widget cache every time the widget panel is (re)built, so
 * flipping the same flags on the current document is enough: on load for a
 * saved setting, and immediately when the document switcher changes.
 *
 * Nothing here changes rendering. A Loop widget pasted or generated into a Loop
 * Item always rendered; this only makes it draggable from the panel.
 */
(function ($) {
	'use strict';

	var SETTING = 'earluna_loop_allow_nested_widgets';
	var WIDGETS = ['loop-grid', 'loop-carousel'];

	var EARNestedLoopWidgets = {
		init: function () {
			if (typeof elementor === 'undefined' || typeof elementor.on !== 'function') {
				return;
			}

			elementor.on('document:loaded', this.onDocumentLoaded.bind(this));

			// document:loaded may already have fired for the initial document when
			// this script runs after Elementor's own bootstrap.
			var current = this.getCurrentDocument();
			if (current) {
				this.onDocumentLoaded(current);
			}
		},

		getCurrentDocument: function () {
			return elementor.documents && typeof elementor.documents.getCurrent === 'function'
				? elementor.documents.getCurrent()
				: null;
		},

		isLoopDocument: function (document) {
			return !!(document && document.config && 'loop-item' === document.config.type);
		},

		isEnabled: function (document) {
			var model = document && document.container ? document.container.settings : null;
			if (model && typeof model.get === 'function') {
				return 'yes' === model.get(SETTING);
			}

			var settings = document && document.config && document.config.settings ? document.config.settings.settings : null;
			return !!(settings && 'yes' === settings[SETTING]);
		},

		onDocumentLoaded: function (document) {
			if (!this.isLoopDocument(document)) {
				return;
			}

			this.apply(document, this.isEnabled(document));

			var model = document.container ? document.container.settings : null;
			if (model && typeof model.on === 'function' && !document.earNestedLoopWidgetsBound) {
				document.earNestedLoopWidgetsBound = true;
				model.on('change:' + SETTING, function (changed) {
					EARNestedLoopWidgets.apply(document, 'yes' === changed.get(SETTING));
				});
			}
		},

		apply: function (document, enabled) {
			var panel = document.config.panel;
			if (!panel) {
				return;
			}
			if (!panel.widgets_settings) {
				panel.widgets_settings = {};
			}

			WIDGETS.forEach(function (widgetName) {
				panel.widgets_settings[widgetName] = $.extend({}, panel.widgets_settings[widgetName], {
					show_in_panel: !!enabled
				});
			});

			this.refreshElementsPanel();
		},

		// The widget panel rebuilds its list whenever it is opened, so a saved
		// change is picked up on the next visit. Refresh only when it is already
		// showing, e.g. after an undo of the setting.
		refreshElementsPanel: function () {
			if (typeof $e === 'undefined' || !$e.routes || typeof $e.routes.getCurrent !== 'function') {
				return;
			}

			var currentRoute = $e.routes.getCurrent('panel') || '';
			if (0 === currentRoute.indexOf('panel/elements')) {
				$e.routes.refreshContainer('panel');
			}
		}
	};

	$(window).on('elementor:init', function () {
		EARNestedLoopWidgets.init();
	});
})(jQuery);
