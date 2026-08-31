@if (auth()->user()->hasRole('Admin'))
    @include('partials.admin-sidebar')
@elseif (auth()->user()->hasRole('Travel Partner'))
    @include('partials.partner-sidebar')
@else
    @include('traveler.partials.sidebar')
@endif
