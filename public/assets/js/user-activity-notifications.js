/**
 * Actividad reciente: badge de no leídos, lista y marcar como visto al abrir.
 */
(function () {
    var toggle = document.getElementById('activityNotifToggle');
    if (!toggle) {
        return;
    }

    var listEl = document.getElementById('activityNotifList');
    var badge = document.getElementById('activityNotifBadge');
    var headerCount = document.getElementById('activityNotifHeaderCount');
    var recentUrl = toggle.getAttribute('data-activity-url');
    var unreadUrl = toggle.getAttribute('data-unread-url');
    var markSeenUrl = toggle.getAttribute('data-mark-seen-url');
    var activityScope = toggle.getAttribute('data-activity-scope') || 'personal';

    if (!listEl || !recentUrl || !unreadUrl || !markSeenUrl) {
        return;
    }

    var loading = false;

    function csrfToken() {
        var m = document.querySelector('meta[name="csrf-token"]');
        return m ? m.getAttribute('content') : '';
    }

    function refreshBadge() {
        fetch(unreadUrl, {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
            .then(function (r) {
                if (!r.ok) {
                    throw new Error('unread');
                }
                return r.json();
            })
            .then(function (data) {
                var n = typeof data.count === 'number' ? data.count : 0;
                if (!badge) {
                    return;
                }
                if (n > 0) {
                    badge.textContent = data.display != null ? data.display : String(Math.min(n, 99));
                    badge.classList.remove('d-none');
                } else {
                    badge.classList.add('d-none');
                }
            })
            .catch(function () {});
    }

    function iconClassForMethod(method) {
        var m = (method || '').toUpperCase();
        if (m === 'POST') {
            return 'ti ti-plus';
        }
        if (m === 'PUT' || m === 'PATCH') {
            return 'ti ti-pencil';
        }
        if (m === 'DELETE') {
            return 'ti ti-trash';
        }
        return 'ti ti-activity';
    }

    function splitActivityLabel(label) {
        if (!label || typeof label !== 'string') {
            return { kind: '', detail: '' };
        }
        var sep = ' · ';
        var i = label.indexOf(sep);
        if (i === -1) {
            return { kind: '', detail: label.trim() };
        }
        return {
            kind: label.slice(0, i).trim(),
            detail: label.slice(i + sep.length).trim(),
        };
    }

    function render(items) {
        listEl.innerHTML = '';
        if (!items || !items.length) {
            var empty = document.createElement('li');
            empty.className = 'px-3 py-4 text-center text-muted small';
            empty.textContent =
                'Aún no hay movimientos. Se registran al guardar, actualizar o eliminar datos.';
            listEl.appendChild(empty);
            return;
        }

        var unreadShown = 0;
        items.forEach(function (row) {
            var li = document.createElement('li');
            li.className = 'activity-notif-item border-bottom';
            if (row.unread) {
                li.classList.add('activity-notif-item--unread');
                unreadShown += 1;
            }

            var parts = splitActivityLabel(row.label);
            var iconI = document.createElement('i');
            iconI.className = iconClassForMethod(row.method);

            var iconWrap = document.createElement('span');
            iconWrap.className = 'activity-notif-item-icon';
            iconWrap.appendChild(iconI);

            var textCol = document.createElement('div');
            textCol.className = 'flex-grow-1 min-w-0';

            if (parts.kind) {
                var kindEl = document.createElement('div');
                kindEl.className = 'activity-notif-type-badge';
                kindEl.textContent = parts.kind;
                textCol.appendChild(kindEl);
            }

            var titleEl = document.createElement('div');
            titleEl.className = 'activity-notif-item-title';
            titleEl.textContent =
                (activityScope === 'global' && row.actor_name ? row.actor_name + ': ' : '') +
                (parts.detail || parts.kind || row.label || '');
            textCol.appendChild(titleEl);

            var meta = document.createElement('div');
            meta.className = 'activity-notif-item-time';
            meta.textContent = row.created_at_human || '';

            textCol.appendChild(meta);

            var rowWrap = document.createElement('div');
            rowWrap.className = 'activity-notif-item-row px-3 py-2';
            rowWrap.appendChild(iconWrap);
            rowWrap.appendChild(textCol);

            var inner = document.createElement('div');
            inner.appendChild(rowWrap);
            li.appendChild(inner);
            listEl.appendChild(li);
        });

        if (headerCount) {
            if (unreadShown > 0) {
                headerCount.textContent = unreadShown + ' nuevo' + (unreadShown === 1 ? '' : 's');
                headerCount.classList.remove('d-none');
            } else {
                headerCount.classList.add('d-none');
            }
        }
    }

    function stripUnreadVisual() {
        listEl.querySelectorAll('.activity-notif-item--unread').forEach(function (el) {
            el.classList.remove('activity-notif-item--unread');
        });
        if (headerCount) {
            headerCount.classList.add('d-none');
        }
    }

    function markSeen() {
        return fetch(markSeenUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: '{}',
        }).then(function (r) {
            if (!r.ok) {
                throw new Error('mark-seen');
            }
            return r.json();
        });
    }

    var dropdownRoot = toggle.closest('.dropdown');

    if (dropdownRoot) {
        dropdownRoot.addEventListener('hidden.bs.dropdown', function () {
            loading = false;
        });
    }

    function loadList() {
        if (loading) {
            return;
        }
        loading = true;
        listEl.innerHTML =
            '<li class="px-3 py-4 text-center text-muted small">Cargando…</li>';

        fetch(recentUrl, {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
            .then(function (r) {
                if (!r.ok) {
                    throw new Error('recent');
                }
                return r.json();
            })
            .then(function (data) {
                render(data.items || []);
                return markSeen();
            })
            .then(function () {
                stripUnreadVisual();
                refreshBadge();
            })
            .catch(function () {
                listEl.innerHTML =
                    '<li class="px-3 py-4 text-center text-danger small">No se pudo cargar la actividad.</li>';
            })
            .finally(function () {
                loading = false;
            });
    }

    toggle.addEventListener('shown.bs.dropdown', function () {
        loadList();
    });

    function initActivityDropdown() {
        if (typeof bootstrap !== 'undefined' && bootstrap.Dropdown && toggle) {
            try {
                if (typeof bootstrap.Dropdown.getOrCreateInstance === 'function') {
                    bootstrap.Dropdown.getOrCreateInstance(toggle, { autoClose: 'outside' });
                }
            } catch (e) {
                /* noop */
            }
        }
    }

    function bootBadgePolling() {
        refreshBadge();
        setInterval(refreshBadge, 45000);
    }

    function runOnReady() {
        initActivityDropdown();
        bootBadgePolling();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', runOnReady);
    } else {
        runOnReady();
    }

    document.addEventListener('visibilitychange', function () {
        if (document.visibilityState === 'visible') {
            refreshBadge();
        }
    });
})();
