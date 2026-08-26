@extends('layouts.app')

@section('title', 'Add Availability')
@section('page-title', 'Add Availability')

@section('sidebar')
    @include('availability.partials.sidebar')
@endsection

@section('content')
    <div class="mx-auto max-w-[620px]">
        <div class="card card-pad">
            <div class="mb-5 flex items-center justify-between">
                <h3 class="text-[16px] font-semibold">New availability slot</h3>
                <a href="{{ route('partner.availability.index') }}" class="text-[12.5px] font-semibold text-primary">Back to calendar</a>
            </div>

            <form method="POST" action="{{ route('partner.availability.store') }}">
                @csrf
                @include('availability.partials.form', ['slot' => null])
                <button type="submit" class="btn btn-primary btn-block">Add slot</button>
            </form>
        </div>
    </div>
@endsection
