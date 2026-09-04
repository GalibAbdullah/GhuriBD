@extends('layouts.app')

@section('title', 'Conversation with '.$conversation->other(auth()->user())->name)
@section('page-title', 'Messages')

@section('sidebar')
    @include('partials.role-sidebar')
@endsection

@php
    $other = $conversation->other(auth()->user());
@endphp

@section('content')
    <div class="mb-3">
        <a href="{{ route('messages.index') }}" class="small fw-semibold link-secondary link-underline-opacity-0">&larr; Back to Messages</a>
    </div>

    <div class="card">
        <div class="card-body border-bottom d-flex align-items-center gap-3">
            <div class="d-flex align-items-center justify-content-center flex-shrink-0 rounded-circle bg-primary-subtle text-primary-emphasis fw-bold" style="width: 40px; height: 40px; font-size: .8rem;">
                {{ strtoupper(substr($other->name, 0, 1)) }}
            </div>
            <div>
                <div class="fw-semibold">{{ $other->name }}</div>
                <div class="small text-secondary">{{ $other->hasRole('Travel Partner') ? 'Travel Partner' : 'Traveler' }}</div>
            </div>
        </div>

        <div class="card-body" style="max-height: 480px; overflow-y: auto;">
            @forelse ($conversation->messages as $message)
                @php $mine = $message->sender_id === auth()->id(); @endphp
                <div class="d-flex mb-3 {{ $mine ? 'justify-content-end' : 'justify-content-start' }}">
                    <div class="{{ $mine ? 'bg-primary text-white' : 'bg-light text-body' }} rounded-3 px-3 py-2" style="max-width: 70%;">
                        <div class="small" style="white-space: pre-wrap;">{{ $message->body }}</div>
                        <div class="mt-1 small {{ $mine ? 'text-white-50' : 'text-body-tertiary' }}">{{ $message->created_at->format('M d, h:i A') }}</div>
                    </div>
                </div>
            @empty
                <p class="text-secondary small mb-0">No messages yet — say hello.</p>
            @endforelse
        </div>

        <div class="card-body border-top">
            <form method="POST" action="{{ route('messages.reply', $conversation) }}" class="d-flex gap-2">
                @csrf
                <input type="text" name="body" class="form-control @error('body') is-invalid @enderror" placeholder="Write a message…" required maxlength="5000" autocomplete="off">
                <button type="submit" class="btn btn-primary flex-shrink-0">Send</button>
            </form>
            @error('body')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>
    </div>
@endsection
