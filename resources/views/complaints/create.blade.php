@extends('layouts.app')

@section('title', 'File a Complaint')
@section('page-title', 'File a Complaint')

@section('sidebar')
    @include('partials.role-sidebar')
@endsection

@section('content')
    <div class="mb-3">
        <a href="{{ route('complaints.index') }}" class="small fw-semibold link-secondary link-underline-opacity-0">&larr; Back to Complaints</a>
    </div>

    <div class="card" style="max-width: 640px;">
        <div class="card-body">
            <form method="POST" action="{{ route('complaints.store') }}">
                @csrf

                <div class="mb-3">
                    <label for="category" class="form-label small fw-semibold">Category</label>
                    <select name="category" id="category" class="form-select @error('category') is-invalid @enderror">
                        @foreach ($categories as $category)
                            <option value="{{ $category }}" @selected(old('category') === $category)>{{ $category }}</option>
                        @endforeach
                    </select>
                    @error('category')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                @if ($bookings->isNotEmpty())
                    <div class="mb-3">
                        <label for="booking_id" class="form-label small fw-semibold">Related booking <span class="text-secondary fw-normal">(optional)</span></label>
                        <select name="booking_id" id="booking_id" class="form-select @error('booking_id') is-invalid @enderror">
                            <option value="">— Not related to a specific booking —</option>
                            @foreach ($bookings as $booking)
                                <option value="{{ $booking->id }}" @selected((string) old('booking_id') === (string) $booking->id)>{{ $booking->booking_reference }} &middot; {{ $booking->booking_type }}</option>
                            @endforeach
                        </select>
                        @error('booking_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                @endif

                <div class="mb-3">
                    <label for="subject" class="form-label small fw-semibold">Subject</label>
                    <input type="text" name="subject" id="subject" value="{{ old('subject') }}" maxlength="255" class="form-control @error('subject') is-invalid @enderror" placeholder="A short summary of the issue">
                    @error('subject')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="description" class="form-label small fw-semibold">Description</label>
                    <textarea name="description" id="description" rows="6" maxlength="5000" class="form-control @error('description') is-invalid @enderror" placeholder="Describe what happened in detail">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">Submit Complaint</button>
            </form>
        </div>
    </div>
@endsection
