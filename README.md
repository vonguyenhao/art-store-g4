# Darwin Art Store

Functional prototype for HIT326 Option 2: a small Darwin art company moving from a static website to a database-driven online ordering system.

## Features

- Customer storefront showing available artworks.
- Shopping cart with add, update, and clear actions.
- Checkout form that stores customers, purchases, and purchase items in MySQL.
- Simulated buyer and business order emails written to `storage/mail/orders.log`.
- Front page news item controlled by the owner.
- Buyer testimonials with admin moderation.
- Admin login, product management, order viewing, news posting, and testimonial approval.
- GitHub Actions CI/CD starter workflow in `.github/workflows/ci-cd.yml`.

## Requirements

- PHP 8.2 or newer with PDO MySQL.
- MySQL 8 or MariaDB.
- A web server pointed at the `public` directory.

## Local Setup

1. Create a MySQL database named `art_store`.
2. Import the schema:

   ```bash
   mysql -u root -p art_store < database/schema.sql
   ```

3. Configure database credentials with environment variables if your local values are not the defaults:

   ```bash
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_NAME=art_store
   DB_USER=root
   DB_PASSWORD=
   ORDER_HANDLING_EMAIL=orders@example.com
   ```

4. Run the development server:

   ```bash
   php -S localhost:8000 -t public
   ```

5. Open `http://localhost:8000`.

## Admin Login

After importing `database/schema.sql`, use:

- Email: `admin@example.com`
- Password: `admin123`

Change this seeded password before any real deployment.

## Project Documents

- Project plan: `docs/option-2-project-plan.md`
- Database schema: `database/schema.sql`
- CI/CD workflow: `.github/workflows/ci-cd.yml`

## Code Structure

- `src/Core`: framework-style concerns such as database connection, session, CSRF, and layout rendering.
- `src/Repository`: database queries for products, news, orders, admins, and testimonials.
- `src/Service`: business logic for cart handling, authentication, checkout, and order notification.
- `public`: thin web entry points that validate requests, call services, and render HTML.

This keeps page scripts small and follows the main SOLID idea: each class has one clear responsibility, and checkout depends on an `OrderNotifierInterface` so the simulated file email can later be replaced by real email without rewriting checkout logic.

## CI/CD Note

The workflow line `uses: shivammathur/setup-php@v2` means GitHub Actions downloads and runs the `setup-php` action from the GitHub account or organisation named `shivammathur`. It is not an application user. That action installs the PHP version and PHP extensions requested by the workflow before linting and testing the project.
