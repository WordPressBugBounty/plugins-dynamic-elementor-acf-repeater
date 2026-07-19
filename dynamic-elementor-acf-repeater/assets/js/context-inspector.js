(function ($, window) {
    'use strict';

    const config = window.earSharedData && window.earSharedData.inspector;
    if (!config || !config.ajaxUrl || !config.nonce) {
        return;
    }

    const supportedWidgets = new Set(['loop-grid', 'loop-carousel']);
    const allowedSettings = new Set([
        '_skin',
        'use_acf_repeater',
        'acf_repeater_field',
        'query_current_post_only',
        'earluna_context_type',
        'earluna_context_id',
        'use_acf_relationship',
        'earluna_relationship_field',
        'earluna_enable_elementor_filter',
        'earluna_elementor_filter_taxonomy',
        'earluna_elementor_filter_field',
        'earluna_enable_custom_filter',
        'earluna_custom_filter_taxonomy',
        'earluna_use_repeater_taxonomy',
        'earluna_repeater_taxonomy_field',
        'earluna_enable_row_query',
        'earluna_row_search_fields',
        'earluna_row_sort_options',
        'earluna_row_range_filters',
        'earluna_row_layout_filter',
        'earluna_row_query_url_state',
        'earluna_flexible_unmapped_behavior',
        'pagination_type',
        'posts_per_page',
        'earluna_enable_lightbox',
        'earluna_enable_context_inspector'
    ]);
    let teardownActive = function () {};
    let registered = false;

    const debounce = (callback, wait) => {
        let timeout;
        return function () {
            const args = arguments;
            clearTimeout(timeout);
            timeout = setTimeout(function () {
                callback.apply(null, args);
            }, wait);
        };
    };

    const element = (tag, className, text) => {
        const node = document.createElement(tag);
        if (className) {
            node.className = className;
        }
        if (typeof text === 'string') {
            node.textContent = text;
        }
        return node;
    };

    const editorSettings = (settingsModel) => {
        const source = settingsModel && typeof settingsModel.toJSON === 'function'
            ? settingsModel.toJSON()
            : {};
        const settings = {};

        Object.keys(source).forEach((key) => {
            if (allowedSettings.has(key) || key.indexOf('earluna_flexible_template_') === 0) {
                settings[key] = source[key];
            }
        });

        return settings;
    };

    const renderError = (target, message) => {
        target.replaceChildren(element('p', 'ear-context-inspector__error', message));
        target.dataset.earInspectorReady = 'yes';
    };

    const renderSnapshot = (target, snapshot) => {
        const fragment = document.createDocumentFragment();

        (snapshot.groups || []).forEach((group) => {
            const section = element('section', 'ear-context-inspector__group');
            section.appendChild(element('h4', 'ear-context-inspector__heading', group.title || ''));
            const list = element('dl', 'ear-context-inspector__list');

            (group.items || []).forEach((item) => {
                const row = element('div', 'ear-context-inspector__item ear-context-inspector__item--' + (item.status || 'muted'));
                const term = element('dt', 'ear-context-inspector__label');
                const dot = element('span', 'ear-context-inspector__status');
                dot.setAttribute('aria-hidden', 'true');
                term.appendChild(dot);
                term.appendChild(document.createTextNode(item.label || ''));
                row.appendChild(term);
                row.appendChild(element('dd', 'ear-context-inspector__value', item.value || ''));
                list.appendChild(row);
            });

            section.appendChild(list);
            fragment.appendChild(section);
        });

        (snapshot.notices || []).forEach((notice) => {
            fragment.appendChild(element('p', 'ear-context-inspector__notice ear-context-inspector__notice--' + (notice.level || 'info'), notice.message || ''));
        });

        target.replaceChildren(fragment);
        target.dataset.earInspectorReady = 'yes';
    };

    const bindInspector = (panel, model) => {
        teardownActive();

        if (!model || !supportedWidgets.has(model.get('widgetType'))) {
            return;
        }

        const settingsModel = model.get('settings');
        const panelElement = document.querySelector('#elementor-panel');
        if (!settingsModel || !panelElement) {
            return;
        }

        let requestController = null;
        let lastFingerprint = '';
        let disposed = false;

        const resolveTarget = (attempt) => {
            if (disposed || settingsModel.get('earluna_enable_context_inspector') !== 'yes') {
                return;
            }

            const target = panelElement.querySelector('[data-ear-context-inspector]');
            if (!target) {
                if (attempt < 8) {
                    setTimeout(function () {
                        resolveTarget(attempt + 1);
                    }, 100);
                }
                return;
            }

            const settings = editorSettings(settingsModel);
            const fingerprint = JSON.stringify(settings);
            if (fingerprint === lastFingerprint && target.dataset.earInspectorReady === 'yes') {
                return;
            }
            lastFingerprint = fingerprint;

            if (requestController) {
                requestController.abort();
            }
            requestController = new AbortController();
            target.dataset.earInspectorReady = 'no';
            target.replaceChildren(element('p', 'ear-context-inspector__loading', 'Resolving the current widget context…'));

            const body = new URLSearchParams();
            body.set('action', config.action);
            body.set('nonce', config.nonce);
            body.set('document_id', window.elementor && elementor.config.document ? elementor.config.document.id : '0');
            body.set('widget_id', model.get('id') || '');
            body.set('settings', JSON.stringify(settings));

            window.fetch(config.ajaxUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
                body: body.toString(),
                signal: requestController.signal
            }).then((response) => response.json()).then((response) => {
                if (!response || !response.success || !response.data) {
                    const message = response && response.data && response.data.message
                        ? response.data.message
                        : 'The current widget context could not be inspected.';
                    renderError(target, message);
                    return;
                }
                renderSnapshot(target, response.data);
            }).catch((error) => {
                if (error && error.name === 'AbortError') {
                    return;
                }
                renderError(target, 'The current widget context could not be inspected.');
            });
        };

        const schedule = debounce(function () {
            resolveTarget(0);
        }, 180);
        const settingsChanged = function () {
            lastFingerprint = '';
            schedule();
        };
        const panelClicked = function () {
            schedule();
        };

        settingsModel.on('change', settingsChanged);
        panelElement.addEventListener('click', panelClicked, true);
        schedule();

        teardownActive = function () {
            disposed = true;
            settingsModel.off('change', settingsChanged);
            panelElement.removeEventListener('click', panelClicked, true);
            if (requestController) {
                requestController.abort();
            }
        };
    };

    const register = () => {
        if (registered || !window.elementor || !elementor.hooks) {
            return;
        }
        registered = true;
        elementor.hooks.addAction('panel/open_editor/widget', bindInspector);
    };

    register();
    $(window).on('elementor:init', register);
})(jQuery, window);
