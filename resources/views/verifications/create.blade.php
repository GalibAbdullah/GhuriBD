@extends('layouts.app')

@section('title', 'Submit Verification')
@section('page-title', 'Submit Verification')

@section('sidebar')
    <a href="{{ route('partner.dashboard') }}" class="nav-item">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
        Dashboard
    </a>
    <a href="{{ route('profile.show') }}" class="nav-item">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4"><circle cx="12" cy="8" r="4"/><path d="M4 21c1.5-4 5-6 8-6s6.5 2 8 6"/></svg>
        My Profile
    </a>
    <a href="{{ route('partner.verifications.status') }}" class="nav-item active">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4"><path d="M12 3l8 3v6c0 4.5-3.4 7.9-8 9-4.6-1.1-8-4.5-8-9V6l8-3z"/></svg>
        Verification
    </a>
@endsection

@section('content')
    <div class="mx-auto max-w-[620px]">
        <div class="card card-pad">
            <div class="mb-5 flex items-center justify-between">
                <h3 class="text-[16px] font-semibold">Provider Verification</h3>
                <a href="{{ route('partner.verifications.status') }}" class="text-[12.5px] font-semibold text-primary">Back to status</a>
            </div>

            <form method="POST" action="{{ route('partner.verifications.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="input-group">
                    <label for="provider_name" class="input-label">Provider / Business Name</label>
                    <input id="provider_name" type="text" name="provider_name" value="{{ old('provider_name') }}" class="input @error('provider_name') !border-error @enderror" placeholder="e.g. Cox's Bazar Beach Resort" required autofocus>
                    @error('provider_name')<p class="mt-1 text-[11.5px] font-medium text-error">{{ $message }}</p>@enderror
                </div>

                <div class="input-group">
                    <label for="provider_type" class="input-label">Provider Type</label>
                    <select id="provider_type" name="provider_type" class="input @error('provider_type') !border-error @enderror" required>
                        <option value="">Select provider type</option>
                        @foreach ($providerTypes as $type)
                            <option value="{{ $type }}" @selected(old('provider_type') === $type)>{{ $type }}</option>
                        @endforeach
                    </select>
                    @error('provider_type')<p class="mt-1 text-[11.5px] font-medium text-error">{{ $message }}</p>@enderror
                </div>

                <div class="input-group">
                    <label for="business_address" class="input-label">Business Address</label>
                    <textarea id="business_address" name="business_address" rows="3" class="input @error('business_address') !border-error @enderror" placeholder="Registered business address" required>{{ old('business_address') }}</textarea>
                    @error('business_address')<p class="mt-1 text-[11.5px] font-medium text-error">{{ $message }}</p>@enderror
                </div>

                <div class="input-group">
                    <label for="phone" class="input-label">Phone</label>
                    <input id="phone" type="text" name="phone" value="{{ old('phone') }}" class="input @error('phone') !border-error @enderror" placeholder="01XXXXXXXXX" required>
                    @error('phone')<p class="mt-1 text-[11.5px] font-medium text-error">{{ $message }}</p>@enderror
                </div>

                <div class="input-group">
                    <label for="verification_document" class="input-label">Verification Document</label>
                    <input id="verification_document" type="file" name="verification_document" accept=".pdf,.doc,.docx,.jpeg,.png,.jpg" class="input @error('verification_document') !border-error @enderror" required>
                    <p class="input-hint">Trade license, NID, or other registration document. PDF, DOC, DOCX, JPEG, or PNG. Max 10 MB.</p>
                    @error('verification_document')<p class="mt-1 text-[11.5px] font-medium text-error">{{ $message }}</p>@enderror
                </div>

                <div class="input-group">
                    <label for="additional_information" class="input-label">Additional Information <span class="text-ink-faint font-normal">(optional)</span></label>
                    <textarea id="additional_information" name="additional_information" rows="4" class="input @error('additional_information') !border-error @enderror" placeholder="Any extra details for the admin review team">{{ old('additional_information') }}</textarea>
                    @error('additional_information')<p class="mt-1 text-[11.5px] font-medium text-error">{{ $message }}</p>@enderror
                </div>

                <button type="submit" class="btn btn-primary btn-block">Submit Verification</button>
            </form>
        </div>
    </div>
@endsection