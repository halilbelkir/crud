@props([
    'rows',
    'unit' => 'kayıttan',
])

@if ($rows->hasPages() || $rows->total() > 0)
    <div data-pagination {{ $attributes->class('px-3.5 py-3 lg:px-5 lg:py-3.5 flex items-center gap-2.5 lg:gap-4 flex-wrap') }}>
        <span class="text-[12.5px] lg:text-[13px] text-muted">
            {{ number_format($rows->total(), 0, ',', '.') }} {{ $unit }} {{ $rows->firstItem() }}–{{ $rows->lastItem() }} arası gösteriliyor
        </span>

        @if ($rows->hasPages())
            <span class="ml-auto flex items-center gap-2">
                <a
                    href="{{ $rows->previousPageUrl() ?? '#' }}"
                    @class([
                        'w-[30px] h-[30px] lg:w-8 lg:h-8 rounded-[15px] lg:rounded-2xl border border-line inline-flex items-center justify-center',
                        'text-disabled pointer-events-none' => ! $rows->previousPageUrl(),
                        'text-body hover:border-line-2' => $rows->previousPageUrl(),
                    ])
                >‹</a>

                @foreach ($rows->getUrlRange(max(1, $rows->currentPage() - 2), min($rows->lastPage(), $rows->currentPage() + 2)) as $page => $url)
                    <a
                        href="{{ $url }}"
                        @class([
                            'w-[30px] h-[30px] lg:w-8 lg:h-8 rounded-[15px] lg:rounded-2xl inline-flex items-center justify-center font-label text-[12.5px] lg:text-[13px] font-semibold',
                            'bg-primary text-white' => $page === $rows->currentPage(),
                            'border border-line text-body hover:border-line-2' => $page !== $rows->currentPage(),
                        ])
                    >{{ $page }}</a>
                @endforeach

                <a
                    href="{{ $rows->nextPageUrl() ?? '#' }}"
                    @class([
                        'w-[30px] h-[30px] lg:w-8 lg:h-8 rounded-[15px] lg:rounded-2xl border border-line inline-flex items-center justify-center',
                        'text-disabled pointer-events-none' => ! $rows->nextPageUrl(),
                        'text-body hover:border-line-2' => $rows->nextPageUrl(),
                    ])
                >›</a>
            </span>
        @endif
    </div>
@endif
