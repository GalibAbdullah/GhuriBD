@extends('layouts.app')

@section('title', 'Edit Resort')
@section('page-title', 'Edit Resort')

@section('sidebar')
    @include('resorts.partials.sidebar')
@endsection

@section('content')
    <div class="mx-auto" style="max-width: 720px;">
        <div class="card">
            <div class="card-body">
                <div class="mb-4 d-flex align-items-center justify-content-between">
                    <h3 class="h6 fw-semibold">Edit Resort</h3>
                    <a href="{{ route('partner.resorts.show', $resort) }}" class="small fw-semibold link-primary link-underline-opacity-0">Back to Resort</a>
                </div>

                <form id="resortForm" method="POST" action="{{ route('partner.resorts.update', $resort) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    @include('resorts.partials.form')
                </form>
            </div>
        </div>
    </div>
@endsection
