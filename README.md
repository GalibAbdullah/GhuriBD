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
touch database/database.sqlite
php artisan migrate
npm run dev
php artisan serve
```

The SQLite database file (`database/database.sqlite`) is **not** committed —
each developer keeps their own. Create it with `touch database/database.sqlite`
(or `New-Item database/database.sqlite` on PowerShell) before the first migrate.

### Pulling new changes

After `git pull`, run `php artisan migrate` to apply any new migrations. If the
schema looks out of sync, rebuild the local database:

```bash
php artisan migrate:fresh --seed
```

## Entry points

- Web app: `routes/web.php`
- API routes: `routes/api.php`
- Frontend assets: `resources/css/app.css` and `resources/js/app.js`
