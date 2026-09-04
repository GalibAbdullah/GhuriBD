@extends('layouts.app')

@section('title', 'Messages')
@section('page-title', 'Messages')

@section('sidebar')
    @include('partials.role-sidebar')
@endsection

@section('content')
    <div class="mb-3 small text-secondary">
        Chat directly with the {{ auth()->user()->hasRole('Travel Partner') ? 'travelers who booked with you' : 'resort and tour package providers you\'ve booked' }}.
    </div>

    <div class="card">
        <div class="card-body p-0">
            @forelse ($conversations as $conversation)
                @php
                    $other = $conversation->other(auth()->user());
                    $unread = $conversation->unreadCountFor(auth()->user());
                @endphp
                <a href="{{ route('messages.show', $conversation) }}" class="d-flex align-items-start gap-3 border-bottom px-3 py-3 text-decoration-none text-body {{ $loop->last ? 'border-bottom-0' : '' }} {{ $unread ? 'bg-primary-subtle bg-opacity-25' : '' }}">
                    <div class="d-flex align-items-center justify-content-center flex-shrink-0 rounded-circle bg-primary-subtle text-primary-emphasis fw-bold" style="width: 40px; height: 40px; font-size: .8rem;">
                        {{ strtoupper(substr($other->name, 0, 1)) }}
                    </div>

                    <div class="min-w-0 flex-fill">
                        <div class="d-flex align-items-center gap-2">
                            <div class="small fw-semibold {{ $unread ? 'text-primary-emphasis' : 'text-body' }}">{{ $other->name }}</div>
                            @if ($unread)
                                <span class="badge text-bg-primary">{{ $unread }} new</span>
                            @endif
                        </div>
                        <div class="mt-1 small text-secondary text-truncate">
                            {{ $conversation->latestMessage?->body ?? 'No messages yet.' }}
                        </div>
                    </div>

                    <div class="flex-shrink-0 small text-body-tertiary">
                        {{ ($conversation->last_message_at ?? $conversation->created_at)->diffForHumans() }}
                    </div>
                </a>
            @empty
                <div class="empty-state">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="mx-auto mb-3 text-body-tertiary" width="40" height="40"><path d="M21 15a2 2 0 01-2 2H8l-5 4V6a2 2 0 012-2h14a2 2 0 012 2v9z"/></svg>
                    <h3>No conversations yet.</h3>
                    <p>Message a provider from one of your bookings to start a conversation.</p>
                </div>
            @endforelse
        </div>
    </div>

    @if ($conversations->hasPages())
        <div class="mt-4">
            {{ $conversations->links() }}
        </div>
    @endif
@endsection
