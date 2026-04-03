{{-- Notification Bell Component --}}
<div class="notification-bell" id="notificationBell" style="position: relative; cursor: pointer;">
    <i class="fa-solid fa-bell" style="font-size: 1.2rem; color: var(--fg-3, #888);"></i>
    <span class="notification-bell__badge" id="notifBadge" style="display:none;
        position:absolute; top:-6px; right:-8px; background:#E52716; color:#fff;
        font-size:0.65rem; min-width:18px; height:18px; border-radius:50%;
        display:none; align-items:center; justify-content:center; font-weight:bold;">0</span>
</div>

{{-- Dropdown --}}
<div class="notification-dropdown" id="notifDropdown" style="display:none;
    position:absolute; top:100%; right:0; width:360px; max-height:420px;
    background:var(--panel, #fff); border:1px solid var(--br, #eee); border-radius:12px;
    box-shadow:0 8px 32px rgba(0,0,0,0.12); z-index:9999; overflow:hidden;">
    <div style="padding:12px 16px; border-bottom:1px solid var(--br, #eee); display:flex; justify-content:space-between; align-items:center;">
        <strong style="font-size:0.95rem;">Уведомления</strong>
        <button id="markAllRead" style="background:none; border:none; color:var(--accent, #E52716); cursor:pointer; font-size:0.8rem;">
            Прочитать все
        </button>
    </div>
    <div id="notifList" style="overflow-y:auto; max-height:350px;">
        <div class="text-center py-4" style="color:var(--fg-3, #999); font-size:0.85rem;">
            Нет уведомлений
        </div>
    </div>
</div>

<style>
.notification-bell { position: relative; margin-right: 12px; }
.notification-bell:hover i { color: var(--accent, #E52716) !important; }
.notif-item { display:flex; gap:10px; padding:10px 16px; border-bottom:1px solid var(--br, #f0f0f0); text-decoration:none; color:inherit; transition:background 0.15s; }
.notif-item:hover { background: var(--bg-hover, #f5f5f5); }
.notif-item__icon { width:36px; height:36px; border-radius:50%; display:flex; align-items:center; justify-content:center; flex-shrink:0; font-size:0.85rem; }
.notif-item__text { font-size:0.85rem; line-height:1.3; }
.notif-item__time { font-size:0.72rem; color:var(--fg-3, #999); margin-top:2px; }
</style>

<script>
(function() {
    const bell = document.getElementById('notificationBell');
    const dropdown = document.getElementById('notifDropdown');
    const badge = document.getElementById('notifBadge');
    const list = document.getElementById('notifList');
    const markAll = document.getElementById('markAllRead');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

    let isOpen = false;

    bell.addEventListener('click', function(e) {
        e.stopPropagation();
        isOpen = !isOpen;
        dropdown.style.display = isOpen ? 'block' : 'none';
        if (isOpen) loadNotifications();
    });

    document.addEventListener('click', () => { dropdown.style.display = 'none'; isOpen = false; });
    dropdown.addEventListener('click', e => e.stopPropagation());

    markAll.addEventListener('click', async () => {
        await fetch('/notifications/read-all', { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf } });
        badge.style.display = 'none';
        list.innerHTML = '<div class="text-center py-4" style="color:#999;font-size:0.85rem;">Нет уведомлений</div>';
    });

    const iconMap = {
        'new_application': { icon: 'fa-file-circle-plus', bg: '#3B82F6' },
        'application_status': { icon: 'fa-circle-check', bg: '#22c55e' },
        'meeting_invitation': { icon: 'fa-video', bg: '#8B5CF6' },
        'meeting_reminder': { icon: 'fa-clock', bg: '#f59e0b' },
        'staff_chat': { icon: 'fa-comment', bg: '#E52716' },
    };

    async function loadNotifications() {
        try {
            const res = await fetch('/notifications');
            const data = await res.json();
            updateBadge(data.unread_count);
            renderList(data.notifications);
        } catch(e) { console.error('Notification load error:', e); }
    }

    function updateBadge(count) {
        if (count > 0) {
            badge.textContent = count > 99 ? '99+' : count;
            badge.style.display = 'flex';
        } else {
            badge.style.display = 'none';
        }
    }

    function renderList(notifications) {
        if (!notifications.length) {
            list.innerHTML = '<div class="text-center py-4" style="color:#999;font-size:0.85rem;">Нет уведомлений</div>';
            return;
        }
        list.innerHTML = notifications.map(n => {
            const conf = iconMap[n.type] || { icon: 'fa-bell', bg: '#666' };
            return `<a href="${n.url || '#'}" class="notif-item" data-id="${n.id}" onclick="markRead('${n.id}')">
                <div class="notif-item__icon" style="background:${conf.bg};color:#fff;">
                    <i class="fa-solid ${conf.icon}"></i>
                </div>
                <div>
                    <div class="notif-item__text">${escHtml(n.message || '')}</div>
                    <div class="notif-item__time">${n.created_at}</div>
                </div>
            </a>`;
        }).join('');
    }

    window.markRead = async function(id) {
        fetch(`/notifications/${id}/read`, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf } });
    };

    function escHtml(s) { const d = document.createElement('div'); d.textContent = s; return d.innerHTML; }

    // Initial load
    loadNotifications();

    // Refresh every 30s
    setInterval(loadNotifications, 30000);

    // Real-time via Echo
    if (typeof Echo !== 'undefined') {
        const userId = document.querySelector('meta[name="user-id"]')?.content;
        if (userId) {
            Echo.private(`notifications.${userId}`)
                .notification((notification) => {
                    loadNotifications();
                    // Show browser notification
                    if (Notification.permission === 'granted') {
                        new Notification(notification.message || 'Новое уведомление');
                    }
                });
        }
    }

    // Request browser notification permission
    if ('Notification' in window && Notification.permission === 'default') {
        Notification.requestPermission();
    }
})();
</script>
