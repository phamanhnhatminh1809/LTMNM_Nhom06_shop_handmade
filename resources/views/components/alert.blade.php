@props([
    'type' => 'info',
    'context' => 'admin',
    'message' => null,
    'dismissible' => true,
    'autoDismiss' => null,
    'icon' => null,
])

@php
    $cfg = config("ui.alerts.types.{$type}", config('ui.alerts.types.info'));
    $defaultDelay = (int) config('ui.alerts.auto_dismiss_ms', 4000);
    $resolvedAutoDismiss = $autoDismiss === null
        ? ($type === 'success' ? $defaultDelay : 0)
        : (is_bool($autoDismiss) ? ($autoDismiss ? $defaultDelay : 0) : (int) $autoDismiss);

    $resolvedIcon = $icon ?? ($cfg['icon'] ?? 'bi-info-circle-fill');
    $baseClass = $context === 'shop'
        ? 'ui-alert ' . ($cfg['shop_class'] ?? 'is-info')
        : 'admin-alert ' . ($cfg['admin_class'] ?? 'info');
@endphp

<div {{ $attributes->class([$baseClass]) }}
     role="{{ $type === 'error' ? 'alert' : 'status' }}"
     data-ui-alert
     @if($resolvedAutoDismiss > 0) data-auto-dismiss="{{ $resolvedAutoDismiss }}" @endif>
    <div class="{{ $context === 'shop' ? 'ui-alert__content' : 'admin-alert-content' }}">
        <i class="bi {{ $resolvedIcon }}" aria-hidden="true"></i>
        <div class="{{ $context === 'shop' ? 'ui-alert__body' : 'admin-alert-body' }}">
            @if($message !== null)
                <span>{{ trim((string) $message) }}</span>
            @else
                {{ $slot }}
            @endif
        </div>
    </div>

    @if($dismissible)
        <button type="button"
                class="ui-alert__close"
                data-alert-close
                aria-label="Đóng thông báo"
                title="Đóng thông báo">
            <i class="bi bi-x-lg" aria-hidden="true"></i>
        </button>
    @endif
</div>
