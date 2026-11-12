# Upgrade Laravel 13

## Target runtime

- PHP 8.3 or 8.4 (PHP 8.3 is the minimum declared by the project)
- Laravel 13
- Livewire 4 with the existing Blade views
- MySQL connection and existing schema remain unchanged

The previous XAMPP PHP 7.4 runtime cannot execute this version. Point Apache
and the CLI to PHP 8.3+ before deploying the upgraded source.

For local development, a project-scoped PHP 8.3 runtime can be used without
changing XAMPP globally:

```powershell
.\serve.bat
```

Other Artisan commands can be run through `artisan83.ps1`, for example:

```powershell
.\artisan83.bat route:list
```

Required PHP extensions include `curl`, `exif`, `fileinfo`, `gd`, `intl`,
`mbstring`, `openssl`, `pdo_mysql`, and `zip`.

## Deployment

1. Back up the application files and database.
2. Install or select PHP 8.3+ and verify it with `php -v`.
3. Run `composer install --no-dev --optimize-autoloader`.
4. Preserve the current `.env`, especially every `DB_*` value and `APP_KEY`.
5. Run `php artisan package:discover` and `php artisan optimize:clear`.
6. Restart Apache/PHP-FPM and queue workers.
7. Verify login, permissions, file uploads, PDF/Excel exports, mail, and queues.

Do not run `php artisan migrate` as part of this upgrade. The compatibility
layer in `App\Activity` intentionally keeps Activitylog v4 on the existing
`activity_log` columns, so no database migration is required.

## Test isolation

The PHPUnit configuration forces SQLite `:memory:`. Running the suite therefore
does not connect to or modify the MySQL database configured in `.env`:

```bash
php vendor/bin/phpunit
```

## Livewire migration strategy

Existing controllers and Blade pages remain valid. New interactive features can
be moved one module at a time to `app/Livewire` and
`resources/views/livewire`. The import-status notification is the first migrated
component: it uses server-side polling and authorization while retaining the
same table and query rules as the former jQuery implementation.
