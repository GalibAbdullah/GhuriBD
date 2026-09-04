@php
    $reviewsCount = $reviews->count();
    $averageRating = $reviewsCount ? round($reviews->avg('rating'), 1) : 0;
@endphp

<div class="card mb-4">
    <div class="card-body">
        <h4 class="h6 fw-semibold mb-3">Reviews &amp; Ratings</h4>

        <div class="d-flex align-items-center gap-4 flex-wrap">
            <div class="text-center">
                <div class="fs-1 fw-bold text-success">{{ $averageRating }}</div>
                <div class="mb-1">
                    @for ($i = 1; $i <= 5; $i++)
                        <svg viewBox="0 0 24 24" width="14" height="14" fill="{{ $i <= round($averageRating) ? '#f59e0b' : 'none' }}" stroke="#f59e0b" stroke-width="1.5"><path d="M12 3l2.6 5.6 6.1.6-4.6 4 1.3 6-5.4-3.1-5.4 3.1 1.3-6-4.6-4 6.1-.6L12 3z"/></svg>
                    @endfor
                </div>
                <div class="small text-secondary">{{ $reviewsCount }} {{ Str::plural('review', $reviewsCount) }}</div>
            </div>

            <div class="flex-fill" style="min-width: 200px;">
                @for ($star = 5; $star >= 1; $star--)
                    @php $count = $ratingCounts[$star] ?? 0; @endphp
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="small text-secondary" style="width: 42px;">{{ $star }} star</span>
                        <div class="progress flex-fill" style="height: 6px;">
                            <div class="progress-bar bg-warning" style="width: {{ $reviewsCount ? ($count / $reviewsCount) * 100 : 0 }}%"></div>
                        </div>
                        <span class="small text-secondary" style="width: 24px;">{{ $count }}</span>
                    </div>
                @endfor
            </div>
        </div>
    </div>
</div>
