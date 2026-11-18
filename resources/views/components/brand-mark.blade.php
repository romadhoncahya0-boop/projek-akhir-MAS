@props(['size' => 36])
<span {{ $attributes->merge(['class' => 'inline-flex items-center justify-center rounded-sm bg-brand text-white font-bold shrink-0 select-none']) }} style="width:{{ $size }}px;height:{{ $size }}px;font-size:{{ round($size * .45) }}px">M</span>
