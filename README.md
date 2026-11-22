# Laptop Store

Laptop Store is a Laravel marketplace for buying and selling used laptops. Users can publish laptop listings, browse available inventory, request purchases, and manage their marketplace activity. Administrators review listings and moderate marketplace activity from a dedicated dashboard.

## Features

- Browse, search, filter, compare, and wishlist available laptops.
- Create, edit, and manage laptop listings with multiple images.
- Admin approval, rejection, blocking, and featured-listing controls.
- Purchase requests with seller acceptance, completion, and cancellation workflows.
- Verified-purchase reviews and user complaints.
- User authentication, email verification, profiles, and notifications.
- Admin dashboard for listings, orders, users, and complaints.

## Tech stack

- [Laravel](https://laravel.com/)
- [Laravel Breeze](https://laravel.com/docs/starter-kits#laravel-breeze)
- [Tailwind CSS](https://tailwindcss.com/)
- MySQL

## Local setup

Requirements: PHP 8.3+, Composer, Node.js, npm, and MySQL.

1. Install PHP dependencies:

   ```bash
   composer install
   ```

2. Install frontend dependencies:

   ```bash
   npm install
   ```

3. Create the local environment file:

   ```bash
   cp .env.example .env
   ```

   Update the MySQL credentials and set unique values for `ADMIN_EMAIL` and `ADMIN_PASSWORD` in `.env`. The administrator password must be at least 16 characters long.

4. Generate the application key:

   ```bash
   php artisan key:generate
   ```

5. Create the database schema and administrator account:

   ```bash
   php artisan migrate --seed
   ```

6. Link the public storage disk:

   ```bash
   php artisan storage:link
   ```

   This step is required for uploaded laptop images to be available in the application.

7. Build frontend assets:

   ```bash
   npm run build
   ```

8. Start the local server:

   ```bash
   php artisan serve
   ```

Visit `http://127.0.0.1:8000` in your browser.

## Demo administrator login

After seeding, sign in with the email and password configured in `ADMIN_EMAIL` and `ADMIN_PASSWORD` in your local `.env` file. These are placeholders in `.env.example`; replace them before running the seed command, and do not commit populated environment files.

## Tests

Run the test suite with:

```bash
php artisan test
```

## Deployment

See the [deployment guide](DEPLOYMENT.md) for production configuration, hosting, storage, workers, and security requirements.
