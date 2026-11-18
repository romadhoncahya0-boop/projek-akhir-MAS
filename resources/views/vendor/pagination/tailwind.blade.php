@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi halaman" class="flex flex-wrap items-center justify-between gap-3 text-sm">
        <p class="text-muted">
            Menampilkan <span class="font-medium text-ink tnum">{{ $paginator->firstItem() }}</span>–<span class="font-medium text-ink tnum">{{ $paginator->lastItem() }}</span>
            dari <span class="font-medium text-ink tnum">{{ $paginator->total() }}</span> data
        </p>

        <div class="flex items-center gap-1">
            @if ($paginator->onFirstPage())
                <span class="px-3 py-1.5 rounded border border-line text-muted/50 cursor-not-allowed">Sebelumnya</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="px-3 py-1.5 rounded border border-line text-navy hover:border-brand hover:bg-canvas">Sebelumnya</a>
            @endif

            <span class="sm:hidden px-2 text-muted tnum">{{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}</span>

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="hidden sm:inline-block px-2 text-muted">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page" class="hidden sm:inline-block min-w-[2rem] text-center px-2 py-1.5 rounded bg-navy text-white tnum">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" aria-label="Ke halaman {{ $page }}" class="hidden sm:inline-block min-w-[2rem] text-center px-2 py-1.5 rounded border border-line text-navy hover:border-brand hover:bg-canvas tnum">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="px-3 py-1.5 rounded border border-line text-navy hover:border-brand hover:bg-canvas">Berikutnya</a>
            @else
                <span class="px-3 py-1.5 rounded border border-line text-muted/50 cursor-not-allowed">Berikutnya</span>
            @endif
        </div>
    </nav>
@endif
