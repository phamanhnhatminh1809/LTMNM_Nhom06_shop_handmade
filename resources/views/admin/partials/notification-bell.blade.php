@php
    $unreadCount = (int) ($adminUnreadNotificationsCount ?? 0);
@endphp
<div class="admin-bell-wrapper"
     data-admin-bell
     data-fetch-url="{{ route('admin.notifications.index', [], false) }}"
     data-read-all-url="{{ route('admin.notifications.readAll', [], false) }}"
     data-read-url-template="{{ route('admin.notifications.read', ['notification' => '__ID__'], false) }}"
     data-unread-count="{{ $unreadCount }}">
    <button type="button"
            class="btn-icon-bell"
            data-bell-toggle
            aria-label="Thông báo hệ thống"
            aria-haspopup="true"
            aria-expanded="false"
            title="Thông báo hệ thống">
        <i class="bi bi-bell"></i>
        <span class="notification-badge {{ $unreadCount > 0 ? 'is-visible' : '' }}" data-bell-badge>
            {{ $unreadCount > 99 ? '99+' : $unreadCount }}
        </span>
    </button>

    <div class="admin-bell-dropdown" data-bell-dropdown role="dialog" aria-label="Thông báo hệ thống">
        <div class="bell-dropdown-header">
            <div class="bell-header-title">
                <i class="bi bi-bell-fill"></i>
                <span>Thông báo</span>
                <span class="bell-unread-pill {{ $unreadCount > 0 ? '' : 'is-hidden' }}" data-bell-pill>
                    {{ $unreadCount }} mới
                </span>
            </div>
            <button type="button"
                    class="btn-mark-all-read"
                    data-bell-read-all
                    @disabled($unreadCount === 0)>
                <i class="bi bi-check2-all"></i>
                <span>Đọc tất cả</span>
            </button>
        </div>

        <div class="bell-filter-tabs" role="tablist">
            <button type="button" class="bell-filter-tab active" data-bell-filter="all" role="tab" aria-selected="true">
                Tất cả
            </button>
            <button type="button" class="bell-filter-tab" data-bell-filter="unread" role="tab" aria-selected="false">
                Chưa đọc
            </button>
        </div>

        <div class="bell-dropdown-list" data-bell-list>
            <div class="bell-state-empty">
                <i class="bi bi-arrow-repeat"></i>
                <span>Đang tải thông báo...</span>
            </div>
        </div>
    </div>
</div>
