<meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
<meta name="user-id" content="<?php echo e(auth()->id()); ?>">

<style>
    .notification-item {
        transition: all 0.3s ease;
        border-radius: 8px;
        margin: 2px 0;
        position: relative;
    }
    
    .notification-item:hover {
        transform: translateX(3px);
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .notification-unread h6 {
        font-weight: 700 !important;
    }
    
    .notification-unread small {
        font-weight: 600 !important;
    }
    
    .notification-unread .avatar-md svg {
        stroke-width: 3 !important;
    }
    
    .notification-read h6 {
        font-weight: 400 !important;
    }
    
    .notification-read small {
        font-weight: 400 !important;
    }
    
    .notification-read .avatar-md svg {
        stroke-width: 2 !important;
    }
    
    .dropdown-menu {
        max-height: 400px;
        overflow-y: auto;
        border-radius: 12px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.1);
    }
    
    .notification-item:first-child {
        animation: slideInFromTop 0.4s ease-out;
    }
    
    @keyframes slideInFromTop {
        0% {
            opacity: 0;
            transform: translateY(-20px);
        }
        100% {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

<li class="dropdown notification-list" style="position: relative; z-index: 1050;">
    <a class="nav-link dropdown-toggle arrow-none waves-light waves-effect" data-bs-toggle="dropdown" href="#"
        role="button" aria-haspopup="false" aria-expanded="false" id="notificationDropdown">
        <i data-feather="bell" class="text-primary fa-3x"></i>
        <span class="badge bg-danger rounded-pill noti-icon-badge" id="notificationBadge"
            style="display: none;">0</span>
    </a>
    <div class="dropdown-menu dropdown-menu-end dropdown-lg pt-0"
        style="z-index: 2000; overflow: hidden; border-radius: 12px;">
        <h6 class="dropdown-item-text font-15 m-0 py-3 border-bottom d-flex justify-content-between align-items-center">
            الإشعارات
            <span class="badge bg-primary rounded-pill" id="notificationCount">0</span>
        </h6>

        <div class="notification-menu" data-simplebar="init" id="notificationList"
            style="max-height: 300px; overflow-y: auto; overflow-x: hidden;">
            <div class="text-center py-3">
                <small class="text-muted">لا توجد إشعارات</small>
            </div>
        </div>

        <a href="javascript:void(0);" class="dropdown-item text-center text-primary" id="markAllRead">
            تحديد الكل كمقروء <i class="fi-arrow-right"></i>
        </a>
    </div>
</li>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        loadNotifications();
        setInterval(loadNotifications, 30000); // تحديث كل 30 ثانية كاحتياط
        listenForNotifications();

        document.getElementById('markAllRead').addEventListener('click', function() {
            markAllAsRead();
        });
    });

    function listenForNotifications() {
        const metaEl = document.querySelector('meta[name="user-id"]');
        const userId = metaEl ? metaEl.getAttribute('content') : null;

        if (!userId) {
            console.error('User ID meta tag not found.');
            return;
        }
        if (!window.Echo) {
            console.error('Laravel Echo is not initialized. Ensure resources/js/app.js (Echo setup) is loaded.');
            return;
        }

        // Default Laravel notifications channel uses: private-App.Models.User.{id}
        window.Echo.private(`App.Models.User.${userId}`)
            .listen('.new-notification', (e) => {
                addSingleNotification(e.notification);
            });
    }

    function loadNotifications() {
        fetch('/notifications')
            .then(response => response.json())
            .then(data => {
                updateNotificationUI(data.notifications, data.unread_count);
            })
            .catch(error => console.error('خطأ في تحميل الإشعارات:', error));
    }

    function updateNotificationUI(notifications, unreadCount) {
        const badge = document.getElementById('notificationBadge');
        const count = document.getElementById('notificationCount');
        const list = document.getElementById('notificationList');

        if (unreadCount > 0) {
            badge.style.display = 'inline';
            badge.textContent = unreadCount;
        } else {
            badge.style.display = 'none';
        }

        count.textContent = unreadCount;

        if (notifications.length === 0) {
            list.innerHTML = `
                <div class="text-center py-3">
                    <small class="text-muted">لا توجد إشعارات</small>
                </div>
            `;
            return;
        }

        let notificationHTML = '';
        notifications.forEach(notification => {
            const data = notification.data;
            const timeAgo = getTimeAgo(notification.created_at);
            const isRead = notification.read_at !== null;
            const readClass = isRead ? 'notification-read' : 'notification-unread';

            notificationHTML += `
                <a href="#" class="dropdown-item py-3 notification-item ${readClass}"
                   data-notification-id="${notification.id}">
                    <small class="float-end text-muted ps-2">${timeAgo}</small>
                    <div class="media">
                        <div class="avatar-md bg-soft-primary">
                            ${getNotificationIcon(data.icon)}
                        </div>
                        <div class="media-body align-self-center ms-2 text-truncate" dir="auto">
                            <h6 class="my-0 fw-normal text-dark">${data.title}</h6>
                            <small class="text-muted mb-0">${data.message}</small>
                        </div>
                    </div>
                </a>
            `;
        });

        list.innerHTML = notificationHTML;

        document.querySelectorAll('.notification-item').forEach(item => {
            item.addEventListener('click', function() {
                const notificationId = this.dataset.notificationId;
                markAsRead(notificationId);
            });
        });
    }

    function addSingleNotification(notification) {
        const list = document.getElementById('notificationList');
        const timeAgo = getTimeAgo(notification.created_at);

        const notificationHTML = `
            <a href="#" class="dropdown-item py-3 notification-item notification-unread"
               data-notification-id="${notification.id}">
                <small class="float-end text-muted ps-2">${timeAgo}</small>
                <div class="media">
                    <div class="avatar-md bg-soft-primary">
                        ${getNotificationIcon(notification.icon)}
                    </div>
                    <div class="media-body align-self-center ms-2 text-truncate" dir="auto">
                        <h6 class="my-0 fw-normal text-dark">${notification.title}</h6>
                        <small class="text-muted mb-0">${notification.message}</small>
                    </div>
                </div>
            </a>
        `;

        list.innerHTML = notificationHTML + list.innerHTML;

        const badge = document.getElementById('notificationBadge');
        const count = document.getElementById('notificationCount');
        let currentCount = parseInt(count.textContent) + 1;
        count.textContent = currentCount;
        badge.style.display = 'inline';
        badge.textContent = currentCount;

        const newItem = list.querySelector('.notification-item:first-child');
        newItem.addEventListener('click', function() {
            const notificationId = this.dataset.notificationId;
            markAsRead(notificationId);
        });
    }

    function getNotificationIcon(iconType) {
        const icons = {
            'shopping-cart': '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-shopping-cart align-self-center icon-xs"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>',
            'users': '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-users align-self-center icon-xs"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>',
            'check-circle': '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-circle align-self-center icon-xs"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>'
        };

        return icons[iconType] || icons['check-circle'];
    }

    function getTimeAgo(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diffInMinutes = Math.floor((now - date) / 60000);

        if (diffInMinutes < 1) return 'الآن';
        if (diffInMinutes < 60) return `منذ ${diffInMinutes} دقيقة`;
        if (diffInMinutes < 1440) return `منذ ${Math.floor(diffInMinutes / 60)} ساعة`;
        return `منذ ${Math.floor(diffInMinutes / 1440)} يوم`;
    }

    function markAsRead(notificationId) {
        fetch(`/notifications/${notificationId}/read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadNotifications();
                }
            })
            .catch(error => console.error('خطأ في تحديد الإشعار كمقروء:', error));
    }

    function markAllAsRead() {
        fetch('/notifications/read-all', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadNotifications();
                }
            })
            .catch(error => console.error('خطأ في تحديد الكل كمقروء:', error));
    }
</script>
<?php /**PATH D:\Laragon\laragon\www\massar1.02\Modules/Notifications\Resources/views/components/notifications.blade.php ENDPATH**/ ?>