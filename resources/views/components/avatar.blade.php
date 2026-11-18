@props(['user', 'size' => 32, 'class' => 'bg-navy text-white'])
@php
    $initial = strtoupper(mb_substr($user->name, 0, 1));
    $fs = max(11, round($size * .4));
@endphp
@if($user->avatar_path)
    <img src="{{ $user->avatarUrl() }}" alt="Foto {{ $user->name }}" width="{{ $size }}" height="{{ $size }}" loading="lazy"
         class="rounded-full object-cover shrink-0" style="width:{{ $size }}px;height:{{ $size }}px;object-fit:cover"
         onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
    <span class="rounded-full items-center justify-center font-semibold shrink-0 {{ $class }}" style="display:none;width:{{ $size }}px;height:{{ $size }}px;font-size:{{ $fs }}px">{{ $initial }}</span>
@else
    <span class="rounded-full flex items-center justify-center font-semibold shrink-0 {{ $class }}" style="width:{{ $size }}px;height:{{ $size }}px;font-size:{{ $fs }}px">{{ $initial }}</span>
@endif
