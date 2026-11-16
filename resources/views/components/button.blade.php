@props([
    'variant' => 'primary',
    'context' => 'admin',
    'size' => null,
    'type' => 'button',
    'href' => null,
    'icon' => null,
])

@php
    $ctx = config("ui.buttons.{$context}") ? $context : 'admin';
    $preset = config("ui.buttons.{$ctx}.{$variant}", config("ui.buttons.{$ctx}.primary", []));
    $classes = [$preset['class'] ?? 'btn-modal-save'];

    if ($ctx === 'shop' && $size && in_array($size, ['sm', 'lg', 'block'], true)) {
        $classes[] = "btn--{$size}";
    }

    $resolvedIcon = $icon !== null ? $icon : ($preset['icon'] ?? null);
    $classAttr = implode(' ', array_filter($classes));
@endphp

@if($href !== null)
    <a href="{{ $href }}" {{ $attributes->class([$classAttr]) }}>
        @if(!empty($resolvedIcon))
            <i class="bi {{ $resolvedIcon }}" aria-hidden="true"></i>
        @endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->class([$classAttr]) }}>
        @if(!empty($resolvedIcon))
            <i class="bi {{ $resolvedIcon }}" aria-hidden="true"></i>
        @endif
        {{ $slot }}
    </button>
@endif
