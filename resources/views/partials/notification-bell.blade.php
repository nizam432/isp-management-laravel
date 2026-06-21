{{-- resources/views/partials/notification-bell.blade.php --}}
{{-- এই partial টা main layout (resources/views/layouts/app.blade.php) এর topbar/navbar এ
     @include('partials.notification-bell') দিয়ে বসান, profile/email এর পাশে --}}

<style>
.notif-bell-wrap { position: relative; display: flex; align-items: center; list-style: none; }
.notif-bell-btn {
    background: none; border: none; position: relative; font-size: 1.1rem;
    color: #6b7280; padding: 6px 10px; cursor: pointer;
}
.notif-bell-btn:hover { color: #1f2937; }
.notif-badge {
    position: absolute; top: 0; right: 2px; background: #dc2626; color: #fff;
    font-size: .62rem; font-weight: 700; border-radius: 50%;
    min-width: 16px; height: 16px; display: flex; align-items: center;
    justify-content: center; padding: 0 3px; display: none;
}
.notif-dropdown {
    position: absolute; right: 0; top: 100%; margin-top: 8px;
    width: 340px; max-height: 420px; background: #fff; border-radius: 10px;
    box-shadow: 0 10px 30px rgba(0,0,0,.15); z-index: 1050; display: none;
    overflow: hidden;
}
.notif-dropdown.show { display: block; }
.notif-dropdown-header {
    padding: 12px 16px; border-bottom: 1px solid #f0f0f0;
    display: flex; justify-content: space-between; align-items: center;
}
.notif-dropdown-header h6 { margin: 0; font-weight: 700; font-size: .9rem; }
.notif-dropdown-header a { font-size: .75rem; color: #2563eb; text-decoration: none; cursor: pointer; }
.notif-list { max-height: 320px; overflow-y: auto; }
.notif-item {
    display: flex; gap: 10px; padding: 10px 16px; border-bottom: 1px solid #f7f7f7;
    cursor: pointer; transition: background .15s;
}
.notif-item:hover { background: #f8fafc; }
.notif-item.unread { background: #eff6ff; }
.notif-item .notif-icon {
    width: 34px; height: 34px; border-radius: 50%; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center; font-size: .8rem; color: #fff;
}
.notif-item .notif-body { flex: 1; min-width: 0; }
.notif-item .notif-title { font-size: .8rem; font-weight: 700; color: #1f2937; margin: 0; }
.notif-item .notif-message { font-size: .76rem; color: #6b7280; margin: 2px 0 0; line-height: 1.3; }
.notif-item .notif-time { font-size: .68rem; color: #9ca3af; margin-top: 4px; }
.notif-empty { padding: 40px 20px; text-align: center; color: #9ca3af; font-size: .85rem; }
</style>

<li class="notif-bell-wrap nav-item" id="notifBellWrap">
    <button class="notif-bell-btn" id="notifBellBtn">
        <i class="fas fa-bell"></i>
        <span class="notif-badge" id="notifBadge">0</span>
    </button>

    <div class="notif-dropdown" id="notifDropdown">
        <div class="notif-dropdown-header">
            <h6>Notifications</h6>
            <a id="markAllReadBtn">Mark all read</a>
        </div>
        <div class="notif-list" id="notifList">
            <div class="notif-empty">Loading...</div>
        </div>
    </div>
</li>

<script>
(function () {
    const POLL_INTERVAL = 25000; // ২৫ সেকেন্ড

    function colorHex(color) {
        return {
            primary: '#2563eb', success: '#16a34a', warning: '#d97706',
            danger: '#dc2626', info: '#0891b2',
        }[color] || '#2563eb';
    }

    function fetchUnreadCount() {
        fetch("{{ route('notifications.unread-count') }}")
            .then(r => r.json())
            .then(data => {
                const badge = document.getElementById('notifBadge');
                if (data.count > 0) {
                    badge.textContent = data.count > 99 ? '99+' : data.count;
                    badge.style.display = 'flex';
                } else {
                    badge.style.display = 'none';
                }
            })
            .catch(() => {});
    }

    function fetchNotificationList() {
        const list = document.getElementById('notifList');
        list.innerHTML = '<div class="notif-empty">Loading...</div>';

        fetch("{{ route('notifications.index') }}", { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(data => {
                if (!data.notifications || data.notifications.length === 0) {
                    list.innerHTML = '<div class="notif-empty"><i class="fas fa-bell-slash mb-2" style="font-size:1.5rem;display:block"></i>No notifications yet.</div>';
                    return;
                }

                list.innerHTML = data.notifications.map(n => `
                    <div class="notif-item ${n.is_read ? '' : 'unread'}" data-id="${n.id}" data-url="${n.url || ''}">
                        <div class="notif-icon" style="background:${colorHex(n.color)}">
                            <i class="fas ${n.icon || 'fa-bell'}"></i>
                        </div>
                        <div class="notif-body">
                            <p class="notif-title">${n.title}</p>
                            <p class="notif-message">${n.message}</p>
                            <p class="notif-time">${n.time_ago}</p>
                        </div>
                    </div>
                `).join('');
            })
            .catch(() => {
                list.innerHTML = '<div class="notif-empty">Failed to load notifications.</div>';
            });
    }

    document.getElementById('notifBellBtn').addEventListener('click', function (e) {
        e.stopPropagation();
        const dropdown = document.getElementById('notifDropdown');
        const willShow = !dropdown.classList.contains('show');
        dropdown.classList.toggle('show');
        if (willShow) fetchNotificationList();
    });

    document.addEventListener('click', function (e) {
        const wrap = document.getElementById('notifBellWrap');
        if (!wrap.contains(e.target)) {
            document.getElementById('notifDropdown').classList.remove('show');
        }
    });

    document.getElementById('notifList').addEventListener('click', function (e) {
        const item = e.target.closest('.notif-item');
        if (!item) return;

        const id  = item.dataset.id;
        const url = item.dataset.url;

        fetch(`/notifications/${id}/read`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'Content-Type': 'application/json',
            },
        }).then(() => {
            fetchUnreadCount();
            if (url) window.location.href = url;
        });
    });

    document.getElementById('markAllReadBtn').addEventListener('click', function (e) {
        e.stopPropagation();
        fetch("{{ route('notifications.read-all') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'Content-Type': 'application/json',
            },
        }).then(() => {
            fetchUnreadCount();
            fetchNotificationList();
        });
    });

    // ── Polling চালু ──────────────────────────────────
    fetchUnreadCount();
    setInterval(fetchUnreadCount, POLL_INTERVAL);
})();
</script>
