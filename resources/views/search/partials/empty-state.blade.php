<div class="card">
    <div class="card-body">
        <div class="empty-state">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="mx-auto mb-3 text-body-tertiary" width="40" height="40"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
            <h3>No destinations matched your search.</h3>
            <p>Try a different keyword or adjust your filters.</p>
            <a href="{{ route('search.results', array_filter(['q' => $filters['q'] ?? null, 'tab' => $filters['tab'] ?? null])) }}" class="btn btn-primary btn-sm mt-2">Clear Filters</a>
        </div>
    </div>
</div>
