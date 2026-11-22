# Laptop Store Deployment Guide

This application uses Laravel, MySQL, database-backed sessions/cache/queues, and compiled Vite assets. Deploy the contents of this project; never publish the application root, `.env`, `vendor`, or source files under the public web root.

## Before deployment

1. Use PHP 8.3+ with the extensions required by Laravel (including `BCMath`, `Ctype`, `cURL`, `DOM`, `Fileinfo`, `JSON`, `Mbstring`, `OpenSSL`, `PDO`, `Tokenizer`, and `XML`).
2. Set `APP_ENV=production`, `APP_DEBUG=false`, a public HTTPS `APP_URL`, and a unique `APP_KEY`.
3. Configure the MySQL database, SMTP mail settings, and a unique `ADMIN_EMAIL` plus `ADMIN_PASSWORD` (at least 16 characters).
4. Build and verify assets locally with `npm ci` and `npm run build`. Upload `public/build` with the release.
5. Ensure the web user can write to `storage` and `bootstrap/cache`. Keep `.env`, source, and logs outside the document root.

## A. Shared hosting / cPanel

### Upload and document root

1. In cPanel, create an application directory outside `public_html`, for example:

   ```text
   /home/CPANEL_USER/laptop-store
   ```

2. Upload the complete Laravel project there (including `vendor` installed with Composer and the compiled `public/build` assets). Do not upload `.env` from development.
3. Copy the *contents* of `laptop-store/public/` into `public_html/`. Keep `public/build`, `public/storage` (or its replacement), `.htaccess`, and `index.php`.
4. Edit `public_html/index.php` to point to the application directory. For the example paths above, change the Composer and bootstrap requires to:

   ```php
   require __DIR__.'/../laptop-store/vendor/autoload.php';
   $app = require_once __DIR__.'/../laptop-store/bootstrap/app.php';
   ```

   Verify any maintenance-file paths in that file also point to the Laravel project. Do not copy the rest of the application into `public_html`.

### Database and environment

1. In **MySQL Databases**, create a database and a dedicated database user. Assign that user all privileges on that database. Record the full cPanel-prefixed names and password.
2. Copy `.env.example` to `.env` in the application directory and set:

   ```dotenv
   APP_NAME="Laptop Store"
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://your-domain.example
   APP_KEY=base64:GENERATE_A_UNIQUE_KEY
   DB_CONNECTION=mysql
   DB_HOST=localhost
   DB_PORT=3306
   DB_DATABASE=CPANEL_PREFIX_laptopstore
   DB_USERNAME=CPANEL_PREFIX_storeuser
   DB_PASSWORD=YOUR_DATABASE_PASSWORD
   SESSION_DRIVER=database
   CACHE_STORE=database
   QUEUE_CONNECTION=database
   FILESYSTEM_DISK=public
   LOG_LEVEL=error
   ADMIN_EMAIL=admin@your-domain.example
   ADMIN_PASSWORD=USE_A_UNIQUE_PASSWORD_AT_LEAST_16_CHARACTERS
   ```

   Set SMTP `MAIL_*` values from your mail provider. Do not leave sample credentials in place. Generate the application key once (`php artisan key:generate`) using cPanel Terminal if available; otherwise generate it in a trusted local copy configured with the production `.env`, then copy only the resulting key.
3. Run migrations from **cPanel Terminal** if enabled:

   ```bash
   cd /home/CPANEL_USER/laptop-store
   php artisan migrate --force
   php artisan db:seed --force
   ```

   If Terminal is unavailable, a last-resort no-SSH option is a short-lived, signed endpoint. First set a random 64-character `TEMPORARY_MIGRATION_SECRET` in `.env` and clear config/route caches. Temporarily add this to `routes/web.php`:

   ```php
   use Illuminate\Http\Request;
   use Illuminate\Support\Facades\Artisan;

   Route::get('/_deploy/migrate/{secret}', function (Request $request, string $secret) {
       $expected = (string) config('services.temporary_migration_secret');
       abort_unless(
           app()->environment('production')
               && $expected !== ''
               && hash_equals($expected, $secret),
           404
       );

       Artisan::call('migrate', ['--force' => true]);
       Artisan::call('db:seed', ['--force' => true]);

       return response('Migrations and the administrator seed completed.');
   })->middleware(['signed', 'throttle:1,1'])->name('temporary.migrate');
   ```

   From a trusted local copy with the same production `APP_KEY`, `APP_URL`, and `TEMPORARY_MIGRATION_SECRET`, generate a 5-minute URL:

   ```bash
   php artisan optimize:clear
   php artisan tinker --execute="echo URL::temporarySignedRoute('temporary.migrate', now()->addMinutes(5), ['secret' => config('services.temporary_migration_secret')]);"
   ```

   Open the URL once over HTTPS. Then **immediately remove the temporary route and secret**, clear config/route caches, and rebuild caches. Never expose a general-purpose Artisan endpoint or run migrations on every web request. If you cannot implement and verify this protection, ask the host to enable Terminal rather than exposing a migration route.
4. `DatabaseSeeder` creates only the administrator and requires the `ADMIN_EMAIL` and unique `ADMIN_PASSWORD` above. Do not use the demo seeder on production.
5. Run Laravel optimizations (Terminal or a trusted release runner):

   ```bash
   php artisan optimize:clear
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

### Storage, permissions, workers, and SSL

1. Prefer the normal public storage symlink. With Terminal:

   ```bash
   php artisan storage:link
   ```

   If the host disallows symlinks, configure the `public` disk's root/URL to a dedicated writable public uploads directory, copy `storage/app/public` there, and keep that directory limited to non-executable images. Do not expose private storage.
2. Set `storage/` and `bootstrap/cache/` writable by the PHP/web account (typically directory mode `775` with correct owner/group). Avoid world-writable `777`.
3. Add a cPanel cron job each minute for the scheduler:

   ```cron
   * * * * * /usr/local/bin/php /home/CPANEL_USER/laptop-store/artisan schedule:run >> /dev/null 2>&1
   ```

   Use the PHP binary/version path provided by the host. For queued notifications, add a frequent cron that drains the database queue, for example:

   ```cron
   * * * * * cd /home/CPANEL_USER/laptop-store && /usr/local/bin/php artisan queue:work database --stop-when-empty --tries=3 --timeout=90 >> /dev/null 2>&1
   ```

4. Enable AutoSSL or install a valid certificate in cPanel. Force HTTPS in the hosting control panel or web server, set `APP_URL=https://...`, and set `SESSION_SECURE_COOKIE=true`. Only enable trusted-proxy HTTPS handling when the host's proxy configuration is known.

## B. VPS: Ubuntu, Nginx, PHP 8.3-FPM, MySQL

### Server and database

1. Update the server and install the runtime/tooling:

   ```bash
   sudo apt update && sudo apt upgrade -y
   sudo apt install -y nginx mysql-server git unzip curl supervisor certbot python3-certbot-nginx \
     php8.3-fpm php8.3-cli php8.3-mysql php8.3-mbstring php8.3-xml php8.3-curl \
     php8.3-bcmath php8.3-zip php8.3-gd php8.3-intl
   ```

2. Install Composer from its official installer and Node.js LTS (or build Vite assets in CI and publish `public/build`). Create a least-privilege MySQL database/user:

   ```sql
   CREATE DATABASE laptop_store CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER 'laptop_store'@'localhost' IDENTIFIED BY 'USE_A_UNIQUE_DATABASE_PASSWORD';
   GRANT ALL PRIVILEGES ON laptop_store.* TO 'laptop_store'@'localhost';
   FLUSH PRIVILEGES;
   ```

3. Create a deployment user and release directory, then clone the repository. Example:

   ```bash
   sudo adduser --disabled-password --gecos "" deploy
   sudo mkdir -p /var/www/laptop-store
   sudo chown deploy:www-data /var/www/laptop-store
   sudo -u deploy git clone YOUR_PRIVATE_REPOSITORY_URL /var/www/laptop-store
   ```

### Install, configure, and migrate

1. Install production PHP dependencies and build assets:

   ```bash
   cd /var/www/laptop-store
   composer install --no-dev --prefer-dist --optimize-autoloader
   npm ci
   npm run build
   ```

2. Create `.env` from `.env.example`. Set production values, including `APP_ENV=production`, `APP_DEBUG=false`, HTTPS `APP_URL`, MySQL, SMTP, `SESSION_DRIVER=database`, `CACHE_STORE=database`, `QUEUE_CONNECTION=database`, and secure admin credentials.
3. Generate `APP_KEY` once and run the first migration/seed:

   ```bash
   php artisan key:generate
   php artisan migrate --force
   php artisan db:seed --force
   ```

4. Set ownership and minimum writable permissions:

   ```bash
   sudo chown -R deploy:www-data /var/www/laptop-store
   sudo find /var/www/laptop-store -type d -exec chmod 755 {} \;
   sudo find /var/www/laptop-store -type f -exec chmod 644 {} \;
   sudo chmod -R 775 /var/www/laptop-store/storage /var/www/laptop-store/bootstrap/cache
   ```

### Nginx

Create `/etc/nginx/sites-available/laptop-store` (replace the domain and PHP-FPM socket as needed):

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name your-domain.example www.your-domain.example;
    root /var/www/laptop-store/public;
    index index.php;

    client_max_body_size 14m;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable and validate it:

```bash
sudo ln -s /etc/nginx/sites-available/laptop-store /etc/nginx/sites-enabled/laptop-store
sudo nginx -t
sudo systemctl reload nginx
```

### Storage, queue, scheduler, and TLS

```bash
cd /var/www/laptop-store
php artisan storage:link
```

Run a persistent queue worker with Supervisor. Create `/etc/supervisor/conf.d/laptop-store-worker.conf`:

```ini
[program:laptop-store-worker]
process_name=%(program_name)s_%(process_num)02d
command=/usr/bin/php /var/www/laptop-store/artisan queue:work database --sleep=3 --tries=3 --timeout=90
directory=/var/www/laptop-store
user=deploy
numprocs=2
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
redirect_stderr=true
stdout_logfile=/var/www/laptop-store/storage/logs/worker.log
stopwaitsecs=100
```

Load Supervisor, then register Laravel's scheduler:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start 'laptop-store-worker:*'
```

Add to the deploy user's crontab (`crontab -e`):

```cron
* * * * * cd /var/www/laptop-store && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

Install SSL and enable the HTTPS redirect:

```bash
sudo certbot --nginx -d your-domain.example -d www.your-domain.example
sudo certbot renew --dry-run
```

### Production deploy/update script

Save as `/var/www/laptop-store/deploy.sh`, make executable, and run as `deploy`. Back up first and ensure the new release has been reviewed.

```bash
#!/usr/bin/env bash
set -euo pipefail
cd /var/www/laptop-store

git pull --ff-only
composer install --no-dev --prefer-dist --optimize-autoloader
npm ci --include=dev
npm run build
php artisan down
trap 'php artisan up' EXIT
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart
php artisan up
trap - EXIT
```

Keep deploy scripts and secrets outside the public directory; test rollbacks and maintenance mode before the first release.

## Go-live checklist

- [ ] Admin email/password are unique, secret, and changed from any setup values.
- [ ] `APP_ENV=production`, `APP_DEBUG=false`, and `APP_URL` uses HTTPS.
- [ ] `.env` is outside the public root, not tracked, and not downloadable.
- [ ] Database user has access only to the application database; migrations are current.
- [ ] Storage uploads are on the `public` disk with no script execution enabled.
- [ ] HTTPS redirect and secure session cookies work.
- [ ] SMTP delivery and password reset emails work.
- [ ] Queue worker and scheduler are running; expired featured listings are cleared.
- [ ] Test registration approval, listing approval, purchase request, seller acceptance, completion, review, and cancellation end to end.
- [ ] Backups have been created and a restore has been tested.
- [ ] Production build, route/config/view caches, logs, and error pages have been checked.

## Backup

Back up both the MySQL database and user-uploaded files in `storage/app/public`. Example manual backup (store backups outside the web root and encrypt off-server copies):

```bash
mkdir -p /var/backups/laptop-store
mysqldump --single-transaction --routines --triggers -u laptop_store -p laptop_store \
  | gzip > /var/backups/laptop-store/database-$(date +%F-%H%M).sql.gz
tar -czf /var/backups/laptop-store/storage-$(date +%F-%H%M).tar.gz \
  -C /var/www/laptop-store storage/app/public
```

For unattended daily backups, configure database credentials in a root/deploy-owned `0600` MySQL option file (not in the cron line or source), then schedule a script that dumps the DB, archives `storage/app/public`, rotates old backups, and copies encrypted backups off-server. For example, save the commands above in `/usr/local/sbin/backup-laptop-store`, adjust them to use the protected MySQL option file and retention policy, and add:

```cron
0 2 * * * /usr/local/sbin/backup-laptop-store
```

Monitor backup failures and periodically restore into a separate test database.
