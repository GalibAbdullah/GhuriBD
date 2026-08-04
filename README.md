# GhuriBd

A Laravel application scaffold for this workspace.

## What is included

- Laravel-style backend structure
- Blade frontend entry point
- Vite/Tailwind frontend assets
- Web and API route files
- Database seeders, factories, and tests

## Setup

This workspace does not currently have PHP or Composer installed, so Laravel cannot be bootstrapped automatically here yet.

Once PHP and Composer are available, run:

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm run dev
php artisan serve
```

## Entry points

- Web app: `routes/web.php`
- API routes: `routes/api.php`
- Frontend assets: `resources/css/app.css` and `resources/js/app.js`
