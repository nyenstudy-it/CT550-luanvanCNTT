@if ($paginator->hasPages())
    <nav class="order-pagination" aria-label="Phân trang">
        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <span class="page-btn disabled"><i class="fa fa-chevron-left"></i></span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="page-btn"><i class="fa fa-chevron-left"></i></a>
        @endif

        {{-- Pages --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="page-btn disabled">{{ $element }}</span>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="page-btn active">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="page-btn">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="page-btn"><i class="fa fa-chevron-right"></i></a>
        @else
            <span class="page-btn disabled"><i class="fa fa-chevron-right"></i></span>
        @endif
    </nav>
@endif