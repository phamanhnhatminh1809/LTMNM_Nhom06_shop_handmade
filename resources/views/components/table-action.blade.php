@props([
    'type' => 'view',
    'href' => null,
    'action' => null,
    'method' => 'POST',
    'confirm' => null,
    'confirmTitle' => null,
    'confirmLabel' => null,
    'confirmVariant' => 'danger',
    'icon' => null,
    'variant' => null,
    'label' => null,
    'tooltipAlign' => 'auto',
    'buttonType' => 'button',
])

@php
    $preset = config("ui.table_actions.{$type}", []);
    $resolvedIcon = $icon ?? ($preset['icon'] ?? 'bi-eye');
    $resolvedVariant = $variant ?? ($preset['variant'] ?? 'view');
    $resolvedLabel = $label ?? ($preset['label'] ?? '');

    $classes = ['table-action-btn', "is-{$resolvedVariant}"];
    if ($tooltipAlign === 'end') {
        $classes[] = 'is-tooltip-end';
    } elseif ($tooltipAlign === 'center') {
        $classes[] = 'is-tooltip-center';
    }
    $btnClass = implode(' ', $classes);

    $tooltipAttrs = [];
    if ($resolvedLabel !== '') {
        $tooltipAttrs['data-tooltip'] = $resolvedLabel;
        $tooltipAttrs['aria-label'] = $resolvedLabel;
    }

    $upperMethod = strtoupper((string) $method);
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->class([$btnClass])->merge($tooltipAttrs) }}>
        <i class="bi {{ $resolvedIcon }}" aria-hidden="true"></i>
    </a>
@elseif($action)
    <form action="{{ $action }}"
          method="{{ $upperMethod === 'GET' ? 'GET' : 'POST' }}"
          class="table-action-form"
          @if($confirm)
              data-confirm="{{ $confirm }}"
              data-confirm-title="{{ $confirmTitle ?? ($resolvedLabel !== '' ? $resolvedLabel : 'Xác nhận thao tác') }}"
              data-confirm-label="{{ $confirmLabel ?? ($resolvedLabel !== '' ? $resolvedLabel : 'Xác nhận') }}"
              data-confirm-variant="{{ $confirmVariant }}"
          @endif>
        @if($upperMethod !== 'GET')
            @csrf
            @if($upperMethod !== 'POST')
                @method($upperMethod)
            @endif
        @endif
        <button type="submit" {{ $attributes->class([$btnClass])->merge($tooltipAttrs) }}>
            <i class="bi {{ $resolvedIcon }}" aria-hidden="true"></i>
        </button>
    </form>
@else
    <button type="{{ $buttonType }}" {{ $attributes->class([$btnClass])->merge($tooltipAttrs) }}>
        <i class="bi {{ $resolvedIcon }}" aria-hidden="true"></i>
    </button>
@endif
