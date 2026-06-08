# OV500 Laravel 12 + Filament 4 Portal Scaffold

This directory is a Laravel 12 / Filament 4 rebuild target for the legacy OV500 CodeIgniter portal. It is designed for an incremental migration: keep the existing SQL dumps in `../config/database`, import them into MariaDB/MySQL, and point this application at those databases.

## What is included

- Laravel 12 application skeleton and Composer/NPM manifests.
- Filament 4 admin panel registered at `/admin`.
- Multi-database connection placeholders for `switch`, `kamailio`, and `switchcdr`.
- Eloquent models and Filament CRUD resources for the first OV500 business modules:
  - Customers
  - Resellers
  - Carriers
  - Customer SIP accounts
  - Customer rates
  - Carrier rates
  - DID inventory
  - Invoices
  - Tickets

## Setup

```bash
cd laravel12_filament4
composer install
cp .env.example .env
php artisan key:generate
php artisan filament:install --panels
php artisan make:filament-user
npm install
npm run build
php artisan serve
```

> Note: this repository environment could not reach Packagist through the configured proxy, so dependencies were not installed here. Run the commands above in an environment with normal Composer network access.

## Database migration path

1. Import the legacy schemas from `../config/database/switch.sql`, `../config/database/kamailio.sql`, and `../config/database/switchcdr.sql`.
2. Configure `.env` with the `DB_*`, `KAMAILIO_DB_*`, and `CDR_DB_*` credentials.
3. Validate each Filament resource against live data and then migrate module-specific business logic from `../portal/application/modules`.
4. Add Laravel migrations only after the legacy schema is stabilized or replaced.

## Compatibility notes

- Filament 4 requires Laravel 11.28+ and PHP 8.2+, and this scaffold pins Laravel Framework `^12.0` with Filament `^4.0`.
- The legacy OV500 tables use non-standard timestamp and primary-key names, so each model explicitly declares table, key, timestamp, and guarded/fillable behavior.
