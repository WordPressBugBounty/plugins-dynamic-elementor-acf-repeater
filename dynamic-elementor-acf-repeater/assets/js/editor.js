(function($, window) {
    'use strict';

    const debounce = (func, wait) => {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    };

    const canUsePremiumCode = () => {
        return window.earModule && window.earModule.canUsePremiumCode;
    };

    const stylePremiumOptions = () => {
        const acfRepeaterDropdown = document.querySelector('.elementor-control-repeater_field select');
        
        if (acfRepeaterDropdown) {
            const presentProFieldTypes = new Set();
            const isPremium = canUsePremiumCode();
            
            acfRepeaterDropdown.querySelectorAll('option').forEach(option => {
                const [fieldLabel, fieldType] = option.textContent.split('|').map(s => s.trim());
                const isPro = option.value.endsWith('__pro') || fieldType === 'url';


                option.textContent = `${fieldLabel} | ${fieldType}`;
                
                if (isPro) {
                    if (!isPremium) {
                        option.style.color = '#999';
                        option.style.fontStyle = 'italic';
                        option.disabled = true;
                        if (fieldType === 'url' && !option.value.endsWith('__pro')) {
                            option.value += '__pro';
                        }
                        presentProFieldTypes.add(fieldType);
                    } else {
                        option.style.color = '';
                        option.style.fontStyle = '';
                        option.disabled = false;
                        if (fieldType === 'url' && option.value.endsWith('__pro')) {
                            option.value = option.value.replace('__pro', '');
                        }
                    }
                } else {
                    option.disabled = false;
                    option.style.color = '';
                    option.style.fontStyle = '';
                }
            });

            // Only show upgrade message for non-premium users
            if (!isPremium && presentProFieldTypes.size > 0) {
                const controlContent = acfRepeaterDropdown.closest('.elementor-control-content');
                if (controlContent && !controlContent.nextElementSibling?.classList.contains('ear-pro-notice')) {
                    const fieldLabel = controlContent.closest('.elementor-control')?.querySelector('.elementor-control-title')?.textContent.trim() || 'ACF Repeater';
                    
                    let proFieldsText = '';
                    const fieldTypeArray = Array.from(presentProFieldTypes);
                    if (fieldTypeArray.length === 1) {
                        proFieldsText = `(${fieldTypeArray[0]} and more)`;
                    } else {
                        proFieldsText = `(${fieldTypeArray[0]}, ${fieldTypeArray[1]}, and more)`;
                    }
                    
                    const noticeDiv = document.createElement('div');
                    noticeDiv.className = 'ear-pro-notice';
                    

                    noticeDiv.innerHTML = `All ${fieldLabel} tags ${proFieldsText} are available in the PRO version of ${earSharedData.pluginName}. <a href="${window.earSharedData.upgradeUrl}" target="_blank" style="color: #4a90e2;">Upgrade Now!</a>`;
                    controlContent.parentNode.insertBefore(noticeDiv, controlContent.nextSibling);
                }
            } else {
                // Remove the upgrade notice if it exists for premium users
                const existingNotice = document.querySelector('.ear-pro-notice');
                if (existingNotice) {
                    existingNotice.remove();
                }
            }
        } else {
            const observer = new MutationObserver((mutations, obs) => {
                const dropdown = document.querySelector('.elementor-control-repeater_field select');
                if (dropdown) {
                    stylePremiumOptions();
                    obs.disconnect();
                }
            });

            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
        }
    }

    const styleLightboxVisibilityOptions = () => {
        const lightboxVisibilitySelect = document.querySelector('.elementor-control-earluna_lightbox_visibility select');
        
        if (lightboxVisibilitySelect) {
            const isPremium = canUsePremiumCode();
            
            if (!lightboxVisibilitySelect.querySelector('option[value="hide"]')) {
                const hideOption = document.createElement('option');
                hideOption.value = 'hide';
                hideOption.textContent = 'Hide in Lightbox';
                lightboxVisibilitySelect.appendChild(hideOption);
            }
            if (!lightboxVisibilitySelect.querySelector('option[value="show"]')) {
                const showOption = document.createElement('option');
                showOption.value = 'show';
                showOption.textContent = 'Show Only in Lightbox';
                lightboxVisibilitySelect.appendChild(showOption);
            }

            lightboxVisibilitySelect.querySelectorAll('option').forEach(option => {
                if (option.value === 'hide' || option.value === 'show') {
                    if (!isPremium) {
                        option.style.color = '#999';
                        option.style.fontStyle = 'italic';
                        option.disabled = true;
                        if (!option.textContent.includes('(PRO)')) {
                            option.textContent += ' (PRO)';
                        }
                    } else {
                        option.disabled = false;
                        option.style.color = '';
                        option.style.fontStyle = '';
                        option.textContent = option.textContent.replace(' (PRO)', '');
                    }
                }
            });

            if (!isPremium) {
                const controlContent = lightboxVisibilitySelect.closest('.elementor-control-content');
                if (controlContent && !controlContent.nextElementSibling?.classList.contains('ear-pro-notice')) {
                    const noticeDiv = document.createElement('div');
                    noticeDiv.className = 'elementor-control ear-pro-notice';
                    noticeDiv.innerHTML = `Lightbox and Lightbox Visibility options are available in the PRO version of ${earSharedData.pluginName}. <a href="${window.earSharedData.upgradeUrl}" target="_blank" style="color: #4a90e2;">Upgrade Now!</a>`;
                    controlContent.parentNode.insertBefore(noticeDiv, controlContent.nextSibling);
                }
            }
        }
    };

    const initElementorHandlers = () => {
        if (window.elementor && elementor.config.document.type === 'loop-item') {
            elementor.hooks.addAction('panel/open_editor/widget', function(panel, model, view) {
                const applyStyles = debounce(() => {
                    stylePremiumOptions();
                    styleLightboxVisibilityOptions();
                }, 250); 

                applyStyles();

                const observer = new MutationObserver((mutations) => {
                    applyStyles();
                });

                observer.observe(panel.content.currentView.$el[0], {
                    childList: true,
                    subtree: true,
                    attributes: true,
                    attributeFilter: ['class']
                });

                panel.on('destroy', () => observer.disconnect());
            });
        }
    }

    $(document).ready(function() {
        if (window.elementor) {
            elementor.on('panel:init', function() {
                if (elementor.config.document.type === 'loop-item') {
                    initElementorHandlers();
                }
            });
        } else {
            $(window).on('elementor:init', function() {
                elementor.on('panel:init', function() {
                    if (elementor.config.document.type === 'loop-item') {
                        initElementorHandlers();
                    }
                });
            });
        }
    });

})(jQuery, window);