<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $role }} | {{ config('app.name', 'GhuriBD') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4 p-md-5">
                        <p class="text-uppercase text-primary fw-semibold small mb-2">{{ $role }}</p>
                        <h1 class="h3 fw-bold mb-3">{{ $message }}</h1>
                        <p class="mb-0 text-muted">This is a protected placeholder page for the {{ $role }} role.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>