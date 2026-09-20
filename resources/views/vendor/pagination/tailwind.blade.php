@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex items-center justify-center w-full my-2">
        <div class="flex items-center gap-1.5 bg-white p-2 rounded-2xl border border-slate-200/90 shadow-md">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                    <span class="w-9 h-9 flex items-center justify-center rounded-xl bg-slate-50 text-slate-300 cursor-not-allowed border border-slate-100 text-xs" aria-hidden="true">
                        <i class="fa-solid fa-chevron-left"></i>
                    </span>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="w-9 h-9 flex items-center justify-center rounded-xl bg-white hover:bg-blue-50 text-slate-600 hover:text-blue-600 border border-slate-200 hover:border-blue-200 text-xs transition-all shadow-sm" aria-label="{{ __('pagination.previous') }}">
                    <i class="fa-solid fa-chevron-left"></i>
                </a>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <span aria-disabled="true">
                        <span class="w-9 h-9 flex items-center justify-center rounded-xl bg-white text-slate-400 text-xs font-bold border border-slate-100">{{ $element }}</span>
                    </span>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page">
                                <span class="w-9 h-9 flex items-center justify-center rounded-xl bg-blue-600 text-white font-extrabold text-xs shadow-md shadow-blue-600/30 border border-blue-600">{{ $page }}</span>
                            </span>
                        @else
                            <a href="{{ $url }}" class="w-9 h-9 flex items-center justify-center rounded-xl bg-white hover:bg-blue-50 text-slate-700 hover:text-blue-600 font-bold text-xs border border-slate-200 hover:border-blue-200 transition-all shadow-sm" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="w-9 h-9 flex items-center justify-center rounded-xl bg-white hover:bg-blue-50 text-slate-600 hover:text-blue-600 border border-slate-200 hover:border-blue-200 text-xs transition-all shadow-sm" aria-label="{{ __('pagination.next') }}">
                    <i class="fa-solid fa-chevron-right"></i>
                </a>
            @else
                <span aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                    <span class="w-9 h-9 flex items-center justify-center rounded-xl bg-slate-50 text-slate-300 cursor-not-allowed border border-slate-100 text-xs" aria-hidden="true">
                        <i class="fa-solid fa-chevron-right"></i>
                    </span>
                </span>
            @endif
        </div>
    </nav>
@endif
