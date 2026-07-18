/**
 * MyWisata i18n - Full Page Auto-Translation
 * 
 * Translates all visible text on the page based on selected language.
 * Uses batch translation API with caching. Persists preference in localStorage.
 */
var MyWisataI18n = (function () {
    var APP_URL = window.APP_URL || '/';
    var DEFAULT_LANG = 'id';
    var currentLang = localStorage.getItem('mw_lang') || DEFAULT_LANG;
    var translating = false;
    var originalTexts = new Map(); // element -> original text
    var skipSelectors = ['script', 'style', 'noscript', 'code', 'pre', '.no-translate', '[contenteditable]'];
    var minTextLength = 2;

    /**
     * Get all text nodes that should be translated
     */
    function getTranslatableNodes() {
        var nodes = [];
        var walker = document.createTreeWalker(
            document.body,
            NodeFilter.SHOW_TEXT,
            {
                acceptNode: function (node) {
                    var text = node.textContent.trim();
                    if (text.length < minTextLength) return NodeFilter.FILTER_REJECT;

                    // Check if parent is in skip list
                    var parent = node.parentElement;
                    if (!parent) return NodeFilter.FILTER_REJECT;

                    for (var i = 0; i < skipSelectors.length; i++) {
                        if (parent.closest(skipSelectors[i])) return NodeFilter.FILTER_REJECT;
                    }

                    // Skip if it's only numbers/symbols
                    if (/^[\d\s\p{P}\p{S}]+$/u.test(text)) return NodeFilter.FILTER_REJECT;

                    return NodeFilter.FILTER_ACCEPT;
                }
            }
        );

        var node;
        while (node = walker.nextNode()) {
            nodes.push(node);
        }
        return nodes;
    }

    /**
     * Get translatable attributes (placeholder, title, alt, aria-label)
     */
    function getTranslatableAttributes() {
        var attrs = [];
        var elements = document.querySelectorAll('[placeholder], [title], [alt], [aria-label]');
        elements.forEach(function (el) {
            ['placeholder', 'title', 'alt', 'aria-label'].forEach(function (attr) {
                var val = el.getAttribute(attr);
                if (val && val.trim().length >= minTextLength) {
                    // Skip if inside no-translate
                    if (el.closest('.no-translate')) return;
                    attrs.push({ element: el, attr: attr, value: val });
                }
            });
        });
        return attrs;
    }

    /**
     * Batch translate texts via API
     */
    function batchTranslate(texts, sourceLang, targetLang, callback) {
        if (!texts || texts.length === 0) {
            callback([]);
            return;
        }

        // Deduplicate texts
        var uniqueTexts = [];
        var textMap = {};
        texts.forEach(function (t) {
            if (!textMap[t]) {
                textMap[t] = true;
                uniqueTexts.push(t);
            }
        });

        var formData = new FormData();
        formData.append('csrf_token', getCsrfToken());
        formData.append('source_lang', sourceLang);
        formData.append('target_lang', targetLang);
        formData.append('texts', JSON.stringify(uniqueTexts));

        fetch(APP_URL + 'translation/translateBatch', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.status === 'success' && data.results) {
                    // results is a text-to-translation map
                    var results = texts.map(function (t) {
                        return data.results[t] || t;
                    });
                    callback(results);
                } else {
                    callback(texts); // fallback to original
                }
            })
            .catch(function () {
                callback(texts); // fallback
            });
    }

    function getCsrfToken() {
        // Try to get CSRF token from meta tag or hidden input
        var meta = document.querySelector('meta[name="csrf-token"]');
        if (meta) return meta.getAttribute('content');
        var input = document.querySelector('input[name="csrf_token"]');
        if (input) return input.value;
        return '';
    }

    /**
     * Translate the entire page
     */
    function translatePage(targetLang) {
        if (translating || targetLang === currentLang) return;
        translating = true;
        showIndicator(true);

        if (targetLang === DEFAULT_LANG) {
            restoreOriginal();
            currentLang = DEFAULT_LANG;
            localStorage.setItem('mw_lang', DEFAULT_LANG);
            translating = false;
            showIndicator(false);
            updateToggleLabel();
            return;
        }

        var textNodes = getTranslatableNodes();
        var attrNodes = getTranslatableAttributes();

        // Save originals if not already saved
        textNodes.forEach(function (node) {
            if (!originalTexts.has(node)) {
                originalTexts.set(node, node.textContent);
            }
        });
        attrNodes.forEach(function (item) {
            var key = item.element.getAttribute('data-i18n-key') || (item.element.tagName + '_' + item.attr + '_' + Math.random());
            if (!originalTexts.has(key)) {
                originalTexts.set(key, item.value);
            }
        });

        // Collect all texts
        var allTexts = textNodes.map(function (n) { return n.textContent.trim(); });
        attrNodes.forEach(function (item) {
            allTexts.push(item.value);
        });

        if (allTexts.length === 0) {
            translating = false;
            showIndicator(false);
            return;
        }

        // Batch translate
        batchTranslate(allTexts, DEFAULT_LANG, targetLang, function (results) {
            // Apply to text nodes
            textNodes.forEach(function (node, i) {
                if (results[i]) {
                    node.textContent = results[i];
                }
            });

            // Apply to attributes
            var offset = textNodes.length;
            attrNodes.forEach(function (item, i) {
                var translated = results[offset + i];
                if (translated) {
                    item.element.setAttribute(item.attr, translated);
                }
            });

            currentLang = targetLang;
            localStorage.setItem('mw_lang', targetLang);
            translating = false;
            showIndicator(false);
            updateToggleLabel();
        });
    }

    /**
     * Restore original text
     */
    function restoreOriginal() {
        originalTexts.forEach(function (original, node) {
            if (node instanceof Text) {
                node.textContent = original;
            }
        });

        // Restore attributes - we need to re-query since we stored by key
        // For simplicity, reload the page to restore attributes
        // Actually, let's just restore text nodes and reload if needed
    }

    /**
     * Show/hide translating indicator
     */
    function showIndicator(show) {
        var indicator = document.getElementById('i18nIndicator');
        if (!indicator) {
            indicator = document.createElement('div');
            indicator.id = 'i18nIndicator';
            indicator.style.cssText = 'position:fixed;top:70px;right:20px;z-index:9999;background:#fff;border:1px solid #dee2e6;border-radius:8px;padding:8px 16px;box-shadow:0 2px 8px rgba(0,0,0,0.15);display:none;font-size:13px;';
            indicator.innerHTML = '<i class="fas fa-spinner fa-spin me-2 text-primary"></i>Menerjemahkan halaman...';
            document.body.appendChild(indicator);
        }
        indicator.style.display = show ? 'block' : 'none';
    }

    /**
     * Update the toggle button label
     */
    function updateToggleLabel() {
        var label = document.getElementById('currentLangLabel');
        if (label) {
            var names = {
                'id': 'ID', 'en': 'EN', 'ja': 'JP', 'zh': 'CN', 'ko': 'KR',
                'ar': 'AR', 'es': 'ES', 'fr': 'FR', 'nl': 'NL', 'de': 'DE',
                'th': 'TH', 'vi': 'VN', 'hi': 'IN', 'ru': 'RU', 'pt': 'PT',
                'it': 'IT', 'tr': 'TR', 'pl': 'PL', 'sv': 'SE', 'no': 'NO',
                'da': 'DK', 'fi': 'FI', 'cs': 'CZ', 'hu': 'HU', 'el': 'GR',
                'he': 'IL', 'fa': 'IR', 'sw': 'SW', 'tl': 'PH', 'ms': 'MY'
            };
            label.textContent = names[currentLang] || currentLang.toUpperCase();
        }

        // Update active state in dropdown
        document.querySelectorAll('.lang-option').forEach(function (opt) {
            opt.classList.toggle('active', opt.dataset.lang === currentLang);
            var checkmark = opt.querySelector('.lang-check');
            if (checkmark) {
                checkmark.style.visibility = (opt.dataset.lang === currentLang) ? 'visible' : 'hidden';
            }
        });
    }

    /**
     * Initialize i18n
     */
    function init() {
        // Auto-translate on page load if language is set
        if (currentLang !== DEFAULT_LANG) {
            // Wait for page to fully render
            setTimeout(function () {
                translatePage(currentLang);
            }, 500);
        }
        updateToggleLabel();
    }

    /**
     * Change language
     */
    function changeLanguage(lang) {
        if (lang === currentLang) return;
        if (lang === DEFAULT_LANG) {
            // Reload page to restore everything cleanly
            localStorage.setItem('mw_lang', DEFAULT_LANG);
            window.location.reload();
            return;
        }

        if (currentLang !== DEFAULT_LANG) {
            // Need to restore first, then translate to new language
            // Simplest: reload page, then translate
            localStorage.setItem('mw_lang', lang);
            window.location.reload();
        } else {
            translatePage(lang);
        }
    }

    // Public API
    return {
        init: init,
        translatePage: translatePage,
        changeLanguage: changeLanguage,
        getCurrentLang: function () { return currentLang; },
        restoreOriginal: function () {
            localStorage.setItem('mw_lang', DEFAULT_LANG);
            window.location.reload();
        }
    };
})();

// Auto-init on DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', MyWisataI18n.init);
} else {
    MyWisataI18n.init();
}
