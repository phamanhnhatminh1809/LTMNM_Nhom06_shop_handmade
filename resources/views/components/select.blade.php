@props([
    'id' => null,
    'name' => null,
    'options' => [],
    'value' => null,
    'placeholder' => '-- Chọn --',
    'required' => false,
    'disabled' => false,
    'searchable' => false,
    'submitOnChange' => false,
    'variant' => 'admin',
    'icon' => null,
    'menuMaxHeight' => '240px',
])

@php
    $chevronIcon = config('ui.select.chevron_icon', 'bi-chevron-down');
    $checkIcon = config('ui.select.check_icon', 'bi-check2');
    $emptyText = config('ui.select.empty_text', 'Không có lựa chọn phù hợp');

    $normalizedOptions = [];
    foreach ($options as $key => $opt) {
        if (is_array($opt)) {
            $optVal = (string) ($opt['value'] ?? '');
            $optLabel = trim((string) ($opt['label'] ?? ''));
            $normalizedOptions[] = [
                'value'    => $optVal,
                'label'    => $optLabel,
                'icon'     => $opt['icon'] ?? null,
                'disabled' => (bool) ($opt['disabled'] ?? false),
            ];
        } else {
            $normalizedOptions[] = [
                'value'    => (string) $key,
                'label'    => trim((string) $opt),
                'icon'     => null,
                'disabled' => false,
            ];
        }
    }

    $currentValue = $value === null ? '' : (string) $value;
    $selectedLabel = $placeholder;
    foreach ($normalizedOptions as $opt) {
        if ($opt['value'] === $currentValue) {
            $selectedLabel = $opt['label'];
            break;
        }
    }

    $selectId = $id ?: ($name ? 'ui_select_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $name) : null);
    $wrapperClasses = [
        'ui-select',
        'ui-select--' . ($variant === 'shop' ? 'shop' : 'admin'),
    ];
    if ($disabled) {
        $wrapperClasses[] = 'is-disabled';
    }
@endphp

<div {{ $attributes->class($wrapperClasses) }}
     data-ui-select
     data-searchable="{{ $searchable ? 'true' : 'false' }}"
     data-submit-on-change="{{ $submitOnChange ? 'true' : 'false' }}"
     data-placeholder="{{ $placeholder }}"
     data-check-icon="{{ $checkIcon }}"
     data-empty-text="{{ $emptyText }}">
    <select class="ui-select__native"
            @if($selectId) id="{{ $selectId }}" @endif
            @if($name) name="{{ $name }}" @endif
            @if($required) required @endif
            @if($disabled) disabled @endif
            tabindex="-1"
            aria-hidden="true">
        @if(count($normalizedOptions) > 0)
            @foreach($normalizedOptions as $opt)
                <option value="{{ $opt['value'] }}"
                        @if($opt['value'] === $currentValue) selected @endif
                        @if($opt['disabled']) disabled @endif
                        @if($opt['icon']) data-icon="{{ $opt['icon'] }}" @endif>{{ $opt['label'] }}</option>
            @endforeach
        @else
            {{ $slot }}
        @endif
    </select>

    <button type="button"
            class="ui-select__trigger"
            data-ui-select-trigger
            aria-haspopup="listbox"
            aria-expanded="false"
            @if($disabled) disabled aria-disabled="true" @endif>
        @if(!empty($icon))
            <i class="bi {{ $icon }} ui-select__leading-icon" aria-hidden="true"></i>
        @endif
        <span class="ui-select__label">{{ $selectedLabel }}</span>
        <i class="bi {{ $chevronIcon }} ui-select__chevron" aria-hidden="true"></i>
    </button>

    <div class="ui-select__menu" role="listbox" style="max-height: {{ $menuMaxHeight }};">
        @if($searchable)
            <div class="ui-select__search-wrap">
                <i class="bi bi-search" aria-hidden="true"></i>
                <input type="text"
                       class="ui-select__search"
                       data-ui-select-search
                       data-no-trim
                       placeholder="Tìm nhanh..."
                       autocomplete="off">
            </div>
        @endif
        <div class="ui-select__list" data-ui-select-list>
            @foreach($normalizedOptions as $opt)
                @php $isSel = $opt['value'] === $currentValue; @endphp
                <button type="button"
                        class="ui-select__option {{ $isSel ? 'is-selected' : '' }}"
                        role="option"
                        aria-selected="{{ $isSel ? 'true' : 'false' }}"
                        data-value="{{ $opt['value'] }}"
                        @if($opt['disabled']) disabled @endif>
                    <span class="ui-select__option-text">
                        @if(!empty($opt['icon']))
                            <i class="bi {{ $opt['icon'] }}" aria-hidden="true"></i>
                        @endif
                        <span>{{ $opt['label'] }}</span>
                    </span>
                    @if($isSel)
                        <i class="bi {{ $checkIcon }} ui-select__check" aria-hidden="true"></i>
                    @endif
                </button>
            @endforeach
        </div>
    </div>
</div>
