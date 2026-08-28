@extends('layouts.app')

@section('title', 'Add Room')
@section('page-title', 'Add Room')

@section('sidebar')
    @include('resorts.partials.sidebar')
@endsection

@section('content')
    <div class="mx-auto" style="max-width: 720px;">
        <div class="card">
            <div class="card-body">
                <div class="mb-4 d-flex align-items-center justify-content-between">
                    <div>
                        <h3 class="h6 fw-semibold mb-0">Add Room</h3>
                        <div class="small text-secondary">{{ $resort->name }}</div>
                    </div>
                    <a href="{{ route('partner.resorts.rooms.index', $resort) }}" class="small fw-semibold link-primary link-underline-opacity-0">Back to Rooms</a>
                </div>

                <form id="roomForm" method="POST" action="{{ route('partner.resorts.rooms.store', $resort) }}" enctype="multipart/form-data">
                    @csrf

                    @include('rooms.partials.form')
                </form>
            </div>
        </div>
    </div>
@endsection
