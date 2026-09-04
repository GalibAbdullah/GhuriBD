@extends('layouts.app')

@section('title', 'Reviews')
@section('page-title', 'Reviews')

@section('sidebar')
    @include('partials.admin-sidebar')
@endsection

@section('content')
    <div class="mb-4">
        <h3 class="h5 mb-1">Reviews</h3>
        <p class="mb-0 small text-secondary">Every review submitted across the platform. Delete anything inappropriate.</p>
    </div>

    @if ($reviews->isEmpty())
        <div class="card">
            <div class="card-body">
                <div class="empty-state">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="mx-auto mb-3 text-body-tertiary" width="40" height="40"><path d="M12 3l2.6 5.6 6.1.6-4.6 4 1.3 6-5.4-3.1-5.4 3.1 1.3-6-4.6-4 6.1-.6L12 3z"/></svg>
                    <h3>No reviews yet.</h3>
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
                        <div class="d-flex align-items-center gap-3">
                            <div class="small text-secondary">{{ $review->created_at->format('M d, Y') }}</div>
                            <form method="POST" action="{{ route('admin.reviews.destroy', $review) }}" onsubmit="return confirm('Delete this review permanently?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </div>
                    </div>

                    <div class="mb-2">
                        @for ($i = 1; $i <= 5; $i++)
                            <svg viewBox="0 0 24 24" width="14" height="14" fill="{{ $i <= $review->rating ? '#f59e0b' : 'none' }}" stroke="#f59e0b" stroke-width="1.5"><path d="M12 3l2.6 5.6 6.1.6-4.6 4 1.3 6-5.4-3.1-5.4 3.1 1.3-6-4.6-4 6.1-.6L12 3z"/></svg>
                        @endfor
                    </div>

                    <p class="mb-0 text-body small">{{ $review->review_text }}</p>

                    @if ($review->hasReply())
                        <div class="mt-3 ms-3 ps-3 border-start border-2 border-success-subtle">
                            <div class="small fw-semibold text-success-emphasis mb-1">Provider response</div>
                            <p class="mb-0 small text-body">{{ $review->partner_reply }}</p>
                        </div>
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
