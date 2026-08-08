@extends('layouts.app')

@section('title', $title)
@section('page-title', $title)

@section('sidebar')
    <a href="{{ route('traveler.explore') }}" class="nav-item active">Explore</a>
    <a href="{{ route('traveler.explore') }}" class="nav-item">Resorts</a>
    <a href="{{ route('traveler.explore') }}" class="nav-item">Tours</a>
    <a href="{{ route('traveler.explore') }}" class="nav-item">Guides</a>
    <a href="{{ route('traveler.dashboard') }}" class="nav-item">Back to Dashboard</a>
@endsection

@section('content')
    <div class="card card-pad">
        <div class="empty-state">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="mx-auto mb-3 h-10 w-10 text-ink-faint"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
            <h3>{{ $title }}</h3>
            <p>{{ $message }}</p>
            <div class="mt-4">
                <a href="{{ route('traveler.dashboard') }}" class="btn btn-primary btn-sm">Back to dashboard</a>
            </div>
        </div>
    </div>
@endsection