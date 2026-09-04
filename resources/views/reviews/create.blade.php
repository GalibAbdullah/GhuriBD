@extends('layouts.app')

@php
    $subjectName = $booking->resort->name ?? $booking->tourPackage->title;
@endphp

@section('title', 'Write a Review')
@section('page-title', 'Write a Review')

@section('sidebar')
    @include('traveler.partials.sidebar')
@endsection

@section('content')
    <div class="mb-3">
        <a href="{{ route('traveler.bookings.show', $booking) }}" class="small fw-semibold link-secondary link-underline-opacity-0">&larr; Back to Booking</a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-body">
                    <h4 class="h6 fw-semibold mb-1">Reviewing {{ $subjectName }}</h4>
                    <p class="small text-secondary mb-4">Booking reference <span class="font-monospace">{{ $booking->booking_reference }}</span></p>

                    <form method="POST" action="{{ route('traveler.reviews.store', $booking) }}">
                        @csrf

                        <div class="mb-4">
                            <label class="form-label fw-semibold d-block">Your Rating</label>
                            <div class="d-flex gap-2" id="star-rating">
                                @for ($i = 1; $i <= 5; $i++)
                                    <label class="star-label" style="cursor: pointer;">
                                        <input type="radio" name="rating" value="{{ $i }}" class="d-none star-input" {{ old('rating') == $i ? 'checked' : '' }} required>
                                        <svg viewBox="0 0 24 24" width="32" height="32" fill="none" stroke="#f59e0b" stroke-width="1.5" class="star-icon"><path d="M12 3l2.6 5.6 6.1.6-4.6 4 1.3 6-5.4-3.1-5.4 3.1 1.3-6-4.6-4 6.1-.6L12 3z"/></svg>
                                    </label>
                                @endfor
                            </div>
                            @error('rating')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="review_text" class="form-label fw-semibold">Your Review</label>
                            <textarea id="review_text" name="review_text" rows="5" class="form-control @error('review_text') is-invalid @enderror" placeholder="Tell other travelers about your experience...">{{ old('review_text') }}</textarea>
                            @error('review_text')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">Submit Review</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('#star-rating .star-label').forEach((label) => {
            label.addEventListener('click', () => {
                const value = parseInt(label.querySelector('.star-input').value, 10);
                document.querySelectorAll('#star-rating .star-label').forEach((otherLabel, index) => {
                    otherLabel.querySelector('.star-icon').setAttribute('fill', index < value ? '#f59e0b' : 'none');
                });
            });
        });

        const checked = document.querySelector('#star-rating .star-input:checked');
        if (checked) {
            checked.closest('.star-label').dispatchEvent(new Event('click'));
        }
    </script>
@endsection
