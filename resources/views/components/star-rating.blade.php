@props(['rating' => 0, 'size' => 18])

@php
    $rating = max(0, min(5, (float) $rating));
    $full = (int) floor($rating);
    $decimal = round($rating - $full, 2);

    $hasHalf = false;
    if ($decimal >= 0.99) {
        $full++;
    } elseif ($decimal >= 0.25) {
        $hasHalf = true;
    }

    $empty = 5 - $full - ($hasHalf ? 1 : 0);
@endphp

<span class="star-rating" title="{{ $rating }}/5">
    @for ($i = 0; $i < $full; $i++)
        <svg class="star-rating__icon" viewBox="0 0 24 24" style="width: {{ $size }}px; height: {{ $size }}px;">
            <path class="star-rating__filled" d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
        </svg>
    @endfor

    @if ($hasHalf)
        <span class="star-rating__half" style="width: {{ $size }}px; height: {{ $size }}px;">
            <svg class="star-rating__icon" viewBox="0 0 24 24" style="width: {{ $size }}px; height: {{ $size }}px; position: absolute; top: 0; left: 0;">
                <path class="star-rating__empty" d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
            </svg>
            <svg class="star-rating__icon" viewBox="0 0 24 24" style="width: {{ $size }}px; height: {{ $size }}px; position: absolute; top: 0; left: 0; clip-path: inset(0 50% 0 0);">
                <path class="star-rating__filled" d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
            </svg>
        </span>
    @endif

    @for ($i = 0; $i < $empty; $i++)
        <svg class="star-rating__icon" viewBox="0 0 24 24" style="width: {{ $size }}px; height: {{ $size }}px;">
            <path class="star-rating__empty" d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
        </svg>
    @endfor
</span>