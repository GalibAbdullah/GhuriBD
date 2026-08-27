@extends('layouts.app')

@section('title', $title)
@section('page-title', $title)

@section('sidebar')
    <a href="{{ route('explore') }}" class="list-group-item list-group-item-action border-0 rounded active bg-primary-subtle text-primary-emphasis fw-semibold">Explore</a>
    <a href="{{ route('explore') }}" class="list-group-item list-group-item-action border-0 rounded">Resorts</a>
    <a href="{{ route('explore') }}" class="list-group-item list-group-item-action border-0 rounded">Tours</a>
    <a href="{{ route('explore') }}" class="list-group-item list-group-item-action border-0 rounded">Guides</a>
    <a href="{{ route('dashboard') }}" class="list-group-item list-group-item-action border-0 rounded">Back to Dashboard</a>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="mx-auto mb-3 text-body-tertiary" width="40" height="40"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
                <h3>{{ $title }}</h3>
                <p>{{ $message }}</p>
                <div class="mt-3">
                    <a href="{{ route('dashboard') }}" class="btn btn-primary btn-sm">Back to dashboard</a>
                </div>
            </div>
        </div>
    </div>
@endsection
