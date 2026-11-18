@props(['title', 'subtitle' => null])
<div class="page-banner flex flex-wrap items-center justify-between gap-4 rounded-2xl bg-brand px-5 py-5 md:px-7 mb-6">
    <div class="min-w-0">
        <h1 class="text-xl md:text-2xl font-semibold text-white">{{ $title }}</h1>
        @if($subtitle)<p class="text-sm text-white/80 mt-1 max-w-2xl">{{ $subtitle }}</p>@endif
    </div>
    @isset($actions)
        <div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>
    @endisset
</div>
