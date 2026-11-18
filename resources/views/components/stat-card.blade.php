@props(['label', 'value', 'tone' => 'text-navy', 'hint' => null, 'href' => null])
@php $tag = $href ? 'a' : 'div'; @endphp
<{{ $tag }} @if($href) href="{{ $href }}" @endif class="stat-cell {{ $href ? 'hover:bg-canvas' : '' }}">
    <p class="text-xs text-muted">{{ $label }}</p>
    <p class="text-2xl font-semibold mt-0.5 tnum {{ $tone }}">{{ $value }}</p>
    @if($hint)<p class="text-xs text-muted mt-0.5">{{ $hint }}</p>@endif
</{{ $tag }}>
