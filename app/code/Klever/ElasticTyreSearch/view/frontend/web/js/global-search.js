/**
 * Klever Global Search — tab UI (Hyva/vanilla JS version)
 * Panel is static (in-flow), injected below the search form inside the header.
 * Left: vertical category tabs. Right: results list for active tab.
 */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {

        if (window._kleverGlobalSearchInitialized) return;
        window._kleverGlobalSearchInitialized = true;

        var cfg       = window.KleverGlobalSearch || {};
        var searchUrl = cfg.searchUrl || '/elastyresearch/ajax/search';

        var popup = document.querySelector('.search-full-tyre-by-size-head');
        if (!popup) return;

        var input = document.getElementById('tyre-size-search-full-input-head');
        if (!input) return;

        // ── Build panel (injected once, after the tyre-size form container) ───
        var TABS = [
            { key: 'tyresizes', label: 'Tyre Sizes'  },
            { key: 'products',  label: 'Products'    },
            { key: 'brands',    label: 'Brands'      },
            { key: 'vehicles',  label: 'Vehicles'    },
            { key: 'blogs',     label: 'Blog Posts'  },
            { key: 'cms',       label: 'Pages'       }
        ];

        var tabsHtml = '';
        var panesHtml = '';
        TABS.forEach(function (t, i) {
            tabsHtml  += '<button class="klever-gsp-tab' + (i === 0 ? ' active' : '') + '" data-tab="' + t.key + '">'
                       +   '<span class="klever-gsp-tab-label">' + t.label + '</span>'
                       +   '<span class="klever-gsp-tab-count"></span>'
                       + '</button>';
            panesHtml += '<div class="klever-gsp-pane' + (i === 0 ? ' active' : '') + '" data-pane="' + t.key + '">'
                       +   '<ul></ul>'
                       + '</div>';
        });

        var panel = document.createElement('div');
        panel.id = 'klever-global-search-panel';
        panel.style.display = 'none';
        panel.innerHTML = '<div class="klever-gsp-inner">'
            + '<div class="klever-gsp-tabs">' + tabsHtml + '</div>'
            + '<div class="klever-gsp-content">' + panesHtml + '</div>'
            + '</div>';

        // Inject after the search container
        popup.appendChild(panel);

        // ── Tab switching ─────────────────────────────────────────────────────
        panel.addEventListener('click', function (e) {
            var tabBtn = e.target.closest('.klever-gsp-tab');
            if (!tabBtn) return;
            var key = tabBtn.getAttribute('data-tab');
            panel.querySelectorAll('.klever-gsp-tab').forEach(function (t) { t.classList.remove('active'); });
            panel.querySelectorAll('.klever-gsp-pane').forEach(function (p) { p.classList.remove('active'); });
            tabBtn.classList.add('active');
            var pane = panel.querySelector('.klever-gsp-pane[data-pane="' + key + '"]');
            if (pane) pane.classList.add('active');
        });

        // ── Tyre-size detector ────────────────────────────────────────────────
        function isTyreSizeQuery(val) {
            return /^[\d\s\/\.rRcCxX]+$/.test(val.trim());
        }

        // ── Escape HTML ───────────────────────────────────────────────────────
        function esc(s) {
            var div = document.createElement('div');
            div.textContent = String(s || '');
            return div.innerHTML;
        }

        // ── Item renderers ────────────────────────────────────────────────────
        function imgTag(src, alt) {
            return src
                ? '<img src="' + esc(src) + '" alt="' + esc(alt) + '" loading="lazy">'
                : '<span class="klever-gsp-no-img"></span>';
        }

        function renderTyreSize(t) {
            return '<li><a href="' + esc(t.url) + '" class="klever-gsp-tyresize-link">'
                + '<span class="klever-gsp-tyresize-icon"></span>'
                + '<span class="klever-gsp-item-name">' + esc(t.size) + '</span>'
                + '</a></li>';
        }

        function renderProduct(p) {
            return '<li><a href="' + esc(p.url) + '">'
                + imgTag(p.image, p.name)
                + '<span class="klever-gsp-item-info">'
                +   '<span class="klever-gsp-item-name">' + esc(p.name) + '</span>'
                +   (p.price ? '<span class="klever-gsp-item-price">AED ' + esc(p.price) + '</span>' : '')
                + '</span></a></li>';
        }

        function renderBrand(b) {
            return '<li><a href="' + esc(b.url) + '">'
                + imgTag(b.image, b.name)
                + '<span class="klever-gsp-item-info"><span class="klever-gsp-item-name">' + esc(b.name) + '</span></span>'
                + '</a></li>';
        }

        function renderBlog(p) {
            return '<li><a href="' + esc(p.url) + '">'
                + imgTag(p.image, p.title)
                + '<span class="klever-gsp-item-info"><span class="klever-gsp-item-name">' + esc(p.title) + '</span></span>'
                + '</a></li>';
        }

        function renderVehicle(v) {
            return '<li><a href="' + esc(v.url) + '">'
                + imgTag(v.image, v.name)
                + '<span class="klever-gsp-item-info"><span class="klever-gsp-item-name">' + esc(v.name) + '</span></span>'
                + '</a></li>';
        }

        function renderCms(c) {
            return '<li><a href="' + esc(c.url) + '">'
                + '<span class="klever-gsp-item-info"><span class="klever-gsp-item-name">' + esc(c.title) + '</span></span>'
                + '</a></li>';
        }

        var RENDERERS = {
            tyresizes: renderTyreSize,
            products:  renderProduct,
            brands:    renderBrand,
            blogs:     renderBlog,
            vehicles:  renderVehicle,
            cms:       renderCms
        };

        // ── Populate panel with results ───────────────────────────────────────
        function showPanel(data, preferTab) {
            var firstWithResults = null;

            TABS.forEach(function (t) {
                var items  = data[t.key] || [];
                var tab    = panel.querySelector('.klever-gsp-tab[data-tab="' + t.key + '"]');
                var pane   = panel.querySelector('.klever-gsp-pane[data-pane="' + t.key + '"]');
                var count  = tab.querySelector('.klever-gsp-tab-count');
                var ul     = pane.querySelector('ul');

                count.textContent = items.length ? items.length : '';
                if (items.length === 0) {
                    tab.classList.add('klever-gsp-tab-empty');
                } else {
                    tab.classList.remove('klever-gsp-tab-empty');
                }

                if (items.length) {
                    var html = '';
                    items.forEach(function (item) {
                        html += RENDERERS[t.key](item);
                    });
                    ul.innerHTML = html;
                    if (!firstWithResults) firstWithResults = t.key;
                } else {
                    ul.innerHTML = '<li class="klever-gsp-empty-item">No results</li>';
                }
            });

            // Auto-activate preferred tab (if it has results), otherwise first with results
            var activeTab = null;
            if (preferTab && (data[preferTab] || []).length) {
                activeTab = preferTab;
            } else if (firstWithResults) {
                activeTab = firstWithResults;
            }

            if (activeTab) {
                panel.querySelectorAll('.klever-gsp-tab').forEach(function (t) { t.classList.remove('active'); });
                panel.querySelectorAll('.klever-gsp-pane').forEach(function (p) { p.classList.remove('active'); });
                var activeTabEl = panel.querySelector('.klever-gsp-tab[data-tab="' + activeTab + '"]');
                var activePaneEl = panel.querySelector('.klever-gsp-pane[data-pane="' + activeTab + '"]');
                if (activeTabEl) activeTabEl.classList.add('active');
                if (activePaneEl) activePaneEl.classList.add('active');
            }

            panel.style.display = '';
        }

        function hidePanel() {
            panel.style.display = 'none';
        }

        // ── Debounced fetch ──────────────────────────────────────────────────
        var _timer = null;

        function doSearch(query, preferTab) {
            clearTimeout(_timer);
            _timer = setTimeout(function () {
                fetch(searchUrl + '?q=' + encodeURIComponent(query), {
                    method: 'GET',
                    credentials: 'include',
                    headers: { 'Accept': 'application/json' }
                })
                .then(function (res) { return res.json(); })
                .then(function (resp) {
                    if (resp && !resp.error) {
                        showPanel(resp, preferTab);
                    } else {
                        hidePanel();
                    }
                })
                .catch(function () { hidePanel(); });
            }, 300);
        }

        // ── Input handler ─────────────────────────────────────────────────────
        input.addEventListener('input', function () {
            var val = input.value;

            if (val.length < 3) {
                hidePanel();
                return;
            }

            // For tyre-size queries, prefer the Tyre Sizes tab
            var preferTab = isTyreSizeQuery(val) ? 'tyresizes' : null;
            doSearch(val, preferTab);
        });

        // Hide when input is cleared
        input.addEventListener('input', function () {
            if (input.value.length === 0) hidePanel();
        });

        // Navigate on result click
        panel.addEventListener('click', function (e) {
            var link = e.target.closest('a');
            if (link) {
                hidePanel();
                input.value = '';
            }
        });
    });
})();
