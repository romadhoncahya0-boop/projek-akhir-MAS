@props(['title' => 'Belum ada data', 'text' => '', 'action' => null, 'href' => '#'])
<div class="text-center py-10 px-6">
    <p class="font-semibold text-navy">{{ $title }}</p>
    @if($text)<p class="text-sm text-muted mt-1 max-w-sm mx-auto">{{ $text }}</p>@endif
    @if($action)<a href="{{ $href }}" class="btn-primary mt-4">{{ $action }}</a>@endif
</div>
