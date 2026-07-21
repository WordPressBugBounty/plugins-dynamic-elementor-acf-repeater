(function($) {
    'use strict';

    var DEARControlUpdater = {
        init: function() {
            this.bindEvents();
            this.initializeRepeaterField();
        },

        bindEvents: function() {
            $(document).on('change', '#elementor-panel-page-settings-controls [data-setting="earluna_loop_repeater_field"]', this.onRepeaterFieldChange.bind(this));
        },

        onRepeaterFieldChange: function(e) {
            var selectedRepeater = e.target ? $(e.target).val() : e;
            this.updateDynamicTagControls(selectedRepeater, true);
        },

        initializeRepeaterField: function() {
            var self = this;
            elementor.on('preview:loaded', function() {
                var postId = elementor.config.document.id;
                self.fetchSavedRepeaterField(postId);
            });
        },

        fetchSavedRepeaterField: function(postId) {
            
            var restUrl = wpApiSettings.root;
            
            if (!restUrl) {
                console.error('REST URL not found');
                return;
            }
        
            var fullUrl = restUrl + 'elementor-acf-repeater/v1/get-saved-repeater-field';
        
            $.ajax({
                url: fullUrl,
                method: 'GET',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
                },
                data: {
                    post_id: postId
                },
                success: function(response) {
                    if (response && response.repeater_field) {
                        DEARControlUpdater.updateDynamicTagControls({
                            key: response.repeater_field,
                            name: response.repeater_field_name || ''
                        });
                    } 
                },
                error: function(xhr, status, error) {
                    console.error('Failed to retrieve saved repeater field', status, error);
                }
            });
        },

        updateDynamicTagControls: function(selectedRepeater, refreshPreview) {
            try {
                var postId = elementor.config.document.id;
                        
                if (!selectedRepeater) {
                    return;
                }   
        
                var repeaterKey = typeof selectedRepeater === 'string' ? selectedRepeater : selectedRepeater.key;
        
                if (this.lastSelectedRepeater === repeaterKey) {
                    return;
                }
                this.lastSelectedRepeater = repeaterKey;
        
                var dynamicTags = elementor.dynamicTags.getConfig('tags');
                var tagsToUpdate = {};
                
                Object.keys(dynamicTags).forEach(function(tagName) {
                    if (tagName.startsWith('acf-repeater-')) {
                        tagsToUpdate[tagName] = dynamicTags[tagName];
                    }
                });
                
                $.ajax({
                    url: wpApiSettings.root + 'elementor-acf-repeater/v1/update-dynamic-tag-controls',
                    method: 'POST',
                    beforeSend: function(xhr) {
                        xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
                    },
                    data: {
                        post_id: postId,
                        selected_repeater: repeaterKey,
                        tags: JSON.stringify(tagsToUpdate)
                    },
                    success: function(response) {
                        if (response && response.tags) {
                            DEARControlUpdater.updateTagControls(response.tags, response.selected_repeater);
                        } else {
                            console.error('Failed to update dynamic tag controls:', response);
                        }

                        if (refreshPreview) {
                            DEARControlUpdater.refreshPreview(repeaterKey);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX error:', status, error);
                        DEARControlUpdater.lastSelectedRepeater = null;
                    }
                });
            } catch (error) {
                console.error('Error in updateDynamicTagControls:', error);
            }
        },
        
        updateTagControls: function(updatedTags, selectedRepeater) {
        
            if (!selectedRepeater) {
                console.warn('No repeater selected, skipping tag control update');
                return;
            }

            var currentTags = elementor.dynamicTags.getConfig('tags');

            if (!currentTags) {
                console.error('Unable to get current tags configuration');
                return;
            }

            Object.keys(currentTags).forEach(function(tagName) {
                if (tagName.startsWith('acf-repeater-')) {
                    if (currentTags[tagName].controls && currentTags[tagName].controls.repeater_field) {
                        currentTags[tagName].controls.repeater_field.default = selectedRepeater;
                    }
                }
            });

            Object.keys(updatedTags).forEach(function(tagName) {
                if (currentTags[tagName] && updatedTags[tagName].controls) {
                    Object.keys(updatedTags[tagName].controls).forEach(function(controlName) {
                        if (!currentTags[tagName].controls[controlName]) {
                            currentTags[tagName].controls[controlName] = {};
                        }
                        Object.assign(currentTags[tagName].controls[controlName], updatedTags[tagName].controls[controlName]);
                    });
                }
            });

            if (elementor.dynamicTags.config) {
                elementor.dynamicTags.config.tags = currentTags;
            } else if (elementor.config && elementor.config.dynamicTags) {
                elementor.config.dynamicTags.tags = currentTags;
            } else {
                console.warn('Unable to update dynamic tags configuration');
            }

            if (typeof elementor.dynamicTags.cleanCache === 'function') {
                elementor.dynamicTags.cleanCache();
            }

            elementor.channels.editor.trigger('change:dynamic');
        },

        refreshPreview: function(selectedRepeater) {
            if (typeof $e === 'undefined' || typeof $e.run !== 'function') {
                console.warn('Unable to save the Loop Item before refreshing its preview');
                return;
            }

            var currentDocument = elementor.documents && elementor.documents.getCurrent ? elementor.documents.getCurrent() : null;
            var container = currentDocument ? currentDocument.container : null;
            var settingPromise = Promise.resolve();

            if (container && container.settings && container.settings.get('earluna_loop_repeater_field') !== selectedRepeater) {
                settingPromise = $e.run('document/elements/settings', {
                    container: container,
                    settings: {
                        earluna_loop_repeater_field: selectedRepeater
                    }
                });
            }

            settingPromise.then(function() {
                return $e.run('document/save/auto', { force: true });
            }).then(function() {
                if (!elementor.reloadPreview || typeof elementor.reloadPreview !== 'function') {
                    console.warn('Unable to refresh the Loop Item preview');
                    return;
                }

                elementor.reloadPreview();

                elementor.once('preview:loaded', function() {
                    if (typeof $e.route === 'function') {
                        $e.route('panel/page-settings/settings');
                    }
                });
            }).catch(function(error) {
                console.error('Failed to save the Loop Item before refreshing its preview', error);
            });
        },
    };

    // Initialize only when we're sure we're in the Elementor editor for a loop item
    elementor.on('panel:init', function() {
        if (elementor.config.document.type === 'loop-item') {
            DEARControlUpdater.init();
        }
    });

})(jQuery);
