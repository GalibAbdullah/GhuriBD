@extends('layouts.app')

@section('title', 'Submit Verification')
@section('page-title', 'Submit Verification')

@section('sidebar')
    @include('partials.partner-sidebar')
@endsection

@section('content')
    <div class="mx-auto" style="max-width: 620px;">
        <div class="card">
            <div class="card-body">
                <div class="mb-4 d-flex align-items-center justify-content-between">
                    <h3 class="h6 fw-semibold">Provider Verification</h3>
                    <a href="{{ route('partner.verifications.status') }}" class="small fw-semibold link-primary link-underline-opacity-0">Back to status</a>
                </div>

                <form method="POST" action="{{ route('partner.verifications.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label for="provider_name" class="form-label">Provider / Business Name</label>
                        <input id="provider_name" type="text" name="provider_name" value="{{ old('provider_name') }}" class="form-control @error('provider_name') is-invalid @enderror" placeholder="e.g. Cox's Bazar Beach Resort" required autofocus>
                        @error('provider_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="provider_type" class="form-label">Provider Type</label>
                        <select id="provider_type" name="provider_type" class="form-select @error('provider_type') is-invalid @enderror" required>
                            <option value="">Select provider type</option>
                            @foreach ($providerTypes as $type)
                                <option value="{{ $type }}" @selected(old('provider_type') === $type)>{{ $type }}</option>
                            @endforeach
                        </select>
                        @error('provider_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="business_address" class="form-label">Business Address</label>
                        <textarea id="business_address" name="business_address" rows="3" class="form-control @error('business_address') is-invalid @enderror" placeholder="Registered business address" required>{{ old('business_address') }}</textarea>
                        @error('business_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <input id="phone" type="text" name="phone" value="{{ old('phone') }}" class="form-control @error('phone') is-invalid @enderror" placeholder="01XXXXXXXXX" required>
                        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="verification_document" class="form-label">Verification Document</label>
                        <input id="verification_document" type="file" name="verification_document" accept=".pdf,.doc,.docx,.jpeg,.png,.jpg" class="form-control @error('verification_document') is-invalid @enderror" required>
                        <div class="form-text">Trade license, NID, or other registration document. PDF, DOC, DOCX, JPEG, or PNG. Max 10 MB.</div>
                        @error('verification_document')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="additional_information" class="form-label">Additional Information <span class="text-body-tertiary fw-normal">(optional)</span></label>
                        <textarea id="additional_information" name="additional_information" rows="4" class="form-control @error('additional_information') is-invalid @enderror" placeholder="Any extra details for the admin review team">{{ old('additional_information') }}</textarea>
                        @error('additional_information')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Submit Verification</button>
                </form>
            </div>
        </div>
    </div>
@endsection
