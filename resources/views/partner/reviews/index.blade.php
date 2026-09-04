@extends('layouts.app')

@section('title', 'Reviews')
@section('page-title', 'Reviews')

@section('sidebar')
    @include('partials.partner-sidebar')
@endsection

@section('content')
    <div class="mb-4">
        <h3 class="h5 mb-1">Reviews</h3>
        <p class="mb-0 small text-secondary">Feedback travelers left on your resorts and tour packages.</p>
    </div>

    @if ($reviews->isEmpty())
        <div class="card">
            <div class="card-body">
                <div class="empty-state">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="mx-auto mb-3 text-body-tertiary" width="40" height="40"><path d="M12 3l2.6 5.6 6.1.6-4.6 4 1.3 6-5.4-3.1-5.4 3.1 1.3-6-4.6-4 6.1-.6L12 3z"/></svg>
                    <h3>No reviews yet.</h3>
                    <p>Reviews from your travelers will show up here once bookings are completed.</p>
                </div>
            </div>
        </div>
    @else
        @foreach ($reviews as $review)
            @php $subjectName = $review->resort->name ?? $review->tourPackage->title; @endphp
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-1">
                        <div>
                            <span class="badge text-bg-light border me-2">{{ $subjectName }}</span>
                            <span class="fw-semibold">{{ $review->user->name }}</span>
                        </div>
                        <div class="small text-secondary">{{ $review->created_at->format('M d, Y') }}</div>
                    </div>

                    <div class="mb-2">
                        @for ($i = 1; $i <= 5; $i++)
                            <svg viewBox="0 0 24 24" width="14" height="14" fill="{{ $i <= $review->rating ? '#f59e0b' : 'none' }}" stroke="#f59e0b" stroke-width="1.5"><path d="M12 3l2.6 5.6 6.1.6-4.6 4 1.3 6-5.4-3.1-5.4 3.1 1.3-6-4.6-4 6.1-.6L12 3z"/></svg>
                        @endfor
                    </div>

                    <p class="mb-3 text-body small">{{ $review->review_text }}</p>

                    @if ($review->hasReply())
                        <div class="ms-3 ps-3 border-start border-2 border-success-subtle">
                            <div class="small fw-semibold text-success-emphasis mb-1">Your response</div>
                            <p class="mb-0 small text-body">{{ $review->partner_reply }}</p>
                        </div>
                    @else
                        <form method="POST" action="{{ route('partner.reviews.reply', $review) }}">
                            @csrf
                            @method('PATCH')
                            <div class="input-group">
                                <textarea name="partner_reply" rows="2" class="form-control" placeholder="Write a reply to this review...">{{ old('partner_reply') }}</textarea>
                                <button type="submit" class="btn btn-outline-primary">Reply</button>
                            </div>
                            @error('partner_reply')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </form>
                    @endif
                </div>
            </div>
        @endforeach

        @if ($reviews->hasPages())
            <div class="mt-4">
                {{ $reviews->links() }}
            </div>
        @endif
    @endif
@endsection
