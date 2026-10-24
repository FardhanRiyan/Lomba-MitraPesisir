@if ($paginator->hasPages())
    <nav class="mt-4 flex flex-wrap items-center justify-between gap-3" role="navigation" aria-label="Navigasi halaman katalog ikan">
        <p class="text-sm text-grey-600">
            Menampilkan {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} dari {{ $paginator->total() }} ikan
        </p>

        <div class="flex flex-wrap items-center gap-1">
            @if (! $paginator->onFirstPage())
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="rounded-md border border-grey-100 bg-white px-3 py-2 text-sm font-semibold text-grey-700 hover:bg-sky-50">
                    Sebelumnya
                </a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="px-2 py-2 text-sm text-grey-600">{{ $element }}</span>
                @elseif (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page === $paginator->currentPage())
                            <span aria-current="page" class="rounded-md bg-navy-900 px-3 py-2 text-sm font-bold text-white">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" aria-label="Ke halaman {{ $page }}" class="rounded-md border border-grey-100 bg-white px-3 py-2 text-sm font-semibold text-grey-700 hover:bg-sky-50">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="rounded-md border border-grey-100 bg-white px-3 py-2 text-sm font-semibold text-grey-700 hover:bg-sky-50">
                    Berikutnya
                </a>
            @endif
        </div>
    </nav>
@endif
