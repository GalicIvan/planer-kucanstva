# Backend setup - Planer kućanstva (Laravel API)

This `backend/` folder contains **application source files only**
(controllers, models, migrations, seeders, middleware, routes, config).
It is **not** a runnable Laravel project by itself - Laravel's core
framework files (the `vendor/` directory, `artisan`, `public/index.php`,
default config files, etc.) are installed via Composer and are not
included here because this environment has no access to Packagist.

Follow the steps below on your own machine (with internet access and
Composer/PHP/MySQL installed) to get a working project.

## 1. Create a fresh Laravel 11 project

```bash
composer create-project laravel/laravel backend
cd backend
composer require laravel/sanctum
```

This generates the full framework skeleton (`vendor/`, `artisan`,
`public/`, default `app/`, `bootstrap/`, `config/`, `database/`,
`routes/`, `.env`, etc.).

## 2. Copy the project files over the fresh install

Copy the contents of this `backend/` folder **into** the freshly created
Laravel project, overwriting/merging as follows:

| From this folder | Goes to |
|---|---|
| `app/Models/*.php` | `app/Models/` |
| `app/Http/Controllers/Controller.php` | `app/Http/Controllers/Controller.php` (overwrite) |
| `app/Http/Controllers/Api/*.php` | `app/Http/Controllers/Api/` (new folder) |
| `app/Http/Middleware/*.php` | `app/Http/Middleware/` |
| `app/Http/Requests/*.php` | `app/Http/Requests/` |
| `database/migrations/2024_01_01_*.php` | `database/migrations/` (the default Laravel migrations for `users`, `cache` and `jobs` can be deleted - this project replaces the `users` table and does not use `cache`/`jobs` tables) |
| `database/seeders/*.php` | `database/seeders/` (overwrite `DatabaseSeeder.php`) |
| `routes/api.php` | `routes/api.php` (overwrite) |
| `config/cors.php` | `config/cors.php` (overwrite) |

### Middleware registration (`bootstrap/app.php`)

This project ships **two versions** of the middleware aliases, depending on
your Laravel version:

- **Laravel 11/12** (no `app/Http/Kernel.php`): open your generated
  `bootstrap/app.php` and merge in the `->withMiddleware(...)` block shown
  in `bootstrap/app.php` from this folder - it registers the `role` and
  `active` middleware aliases. Keep the rest of your generated file as is.
- **Laravel 10** (has `app/Http/Kernel.php`): instead, copy the snippet
  from `app/Http/Kernel.php.example` into the `$middlewareAliases` array of
  your `app/Http/Kernel.php`.

### Environment file

Copy the relevant values from `.env.example` (in this folder) into your
generated `.env` file - in particular:

```env
APP_NAME="Planer kucanstva"
DB_DATABASE=planer_kucanstva
DB_USERNAME=root
DB_PASSWORD=
FILESYSTEM_DISK=public
FRONTEND_URL=http://localhost:5173
SANCTUM_STATEFUL_DOMAINS=localhost:5173
```

Then generate the app key (if not already set):

```bash
php artisan key:generate
```

## 3. Create the database

Create an empty MySQL database matching `DB_DATABASE` in your `.env`
(default `planer_kucanstva`):

```sql
CREATE DATABASE planer_kucanstva CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

## 4. Run migrations and seeders

```bash
php artisan migrate
php artisan db:seed
```

This creates all tables (users, roles, permissions, households,
household_members, expenses, expense_shares, tasks, shopping_items,
receipts, notifications, personal_access_tokens) and seeds:

- Roles & permissions (`user`, `admin`, `super_admin`)
- 4 test users (see top-level `README.md` for credentials)
- A demo household ("Stan u Zagrebu") with all 4 users as members
- 5 sample expenses with split shares
- 5 sample tasks
- 5 sample shopping list items

## 5. Storage symlink (for receipt uploads)

```bash
php artisan storage:link
```

This makes uploaded receipts (stored in `storage/app/public/receipts`)
accessible at `http://localhost:8000/storage/receipts/...`.

## 6. Run the API server

```bash
php artisan serve
```

The API will be available at `http://localhost:8000/api`. Make sure the
frontend's `VITE_API_URL` points to this address (see
`frontend/.env.example`).

## Notes

- Authentication uses **Laravel Sanctum personal access tokens** (Bearer
  tokens), not cookie-based SPA sessions - this avoids CSRF/cookie
  configuration entirely. `config/cors.php` already allows requests from
  `http://localhost:5173`.
- Role-based access is implemented via the `role:...` middleware alias
  (see `app/Http/Middleware/EnsureRole.php`) and the `active` middleware
  alias blocks deactivated users (see `app/Http/Middleware/EnsureActiveUser.php`).
- All currency amounts are stored as `decimal(8,2)` and represent EUR.
