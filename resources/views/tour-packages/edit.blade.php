@extends('layouts.app')

@section('title', 'Edit Package')
@section('page-title', 'Edit Package')

@section('sidebar')
    @include('tour-packages.partials.sidebar')
@endsection

@section('content')
    <div class="mx-auto" style="max-width: 720px;">
        <div class="card">
            <div class="card-body">
                <div class="mb-4 d-flex align-items-center justify-content-between">
                    <h3 class="h6 fw-semibold">Edit Package</h3>
                    <a href="{{ route('partner.packages.show', $tourPackage) }}" class="small fw-semibold link-primary link-underline-opacity-0">Back to Package</a>
                </div>

                <form id="packageForm" method="POST" action="{{ route('partner.packages.update', $tourPackage) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    @include('tour-packages.partials.form')
                </form>
            </div>
        </div>
    </div>
@endsection
