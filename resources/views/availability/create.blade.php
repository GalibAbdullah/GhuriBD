@extends('layouts.app')

@section('title', 'Add Availability')
@section('page-title', 'Add Availability')

@section('sidebar')
    @include('availability.partials.sidebar')
@endsection

@section('content')
    <div class="mx-auto" style="max-width: 620px;">
        <div class="card">
            <div class="card-body">
                <div class="mb-4 d-flex align-items-center justify-content-between">
                    <h3 class="h6 fw-semibold">New availability slot</h3>
                    <a href="{{ route('partner.availability.index') }}" class="small fw-semibold link-primary link-underline-opacity-0">Back to calendar</a>
                </div>

                <form method="POST" action="{{ route('partner.availability.store') }}">
                    @csrf
                    @include('availability.partials.form', ['slot' => null])
                    <button type="submit" class="btn btn-primary w-100">Add slot</button>
                </form>
            </div>
        </div>
    </div>
@endsection
