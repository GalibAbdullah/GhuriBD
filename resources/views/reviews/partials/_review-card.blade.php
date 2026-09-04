<div class="border-bottom pb-3 mb-3">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="fw-semibold">{{ $review->user->name }}</div>
        <div class="small text-secondary">{{ $review->created_at->format('M d, Y') }}</div>
    </div>

    <div class="mb-2">
        @for ($i = 1; $i <= 5; $i++)
            <svg viewBox="0 0 24 24" width="14" height="14" fill="{{ $i <= $review->rating ? '#f59e0b' : 'none' }}" stroke="#f59e0b" stroke-width="1.5"><path d="M12 3l2.6 5.6 6.1.6-4.6 4 1.3 6-5.4-3.1-5.4 3.1 1.3-6-4.6-4 6.1-.6L12 3z"/></svg>
        @endfor
    </div>

    <p class="mb-0 text-body small">{{ $review->review_text }}</p>

    @if ($review->hasReply())
        <div class="mt-3 ms-3 ps-3 border-start border-2 border-success-subtle">
            <div class="small fw-semibold text-success-emphasis mb-1">Response from the provider</div>
            <p class="mb-0 small text-body">{{ $review->partner_reply }}</p>
        </div>
    @endif
</div>
