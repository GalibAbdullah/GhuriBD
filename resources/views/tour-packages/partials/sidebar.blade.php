@if (auth()->user()->hasRole('Admin'))
    @include('partials.admin-sidebar')
@else
    @include('partials.partner-sidebar')
@endif
