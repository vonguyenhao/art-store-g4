# HIT326 Option 2 Project Plan: Darwin Art Store

## Project Description

This project will build a database-driven online art store for a small Darwin art company. Customers will browse available artworks, add items to a shopping cart, submit an order, and receive an email-style purchase order summary. Store staff will manage products, orders, news, and moderated testimonials through a backend interface.

## Problem

The business currently uses a static website to sell artworks, which makes online ordering, product updates, and order tracking manual and error-prone. The company does not want online payment processing at this stage, so the prototype must support order capture and email-based handling instead of PayPal or similar services.

## Proposed Solution

Build a PHP + MySQL web application with a customer-facing storefront and a small admin backend. The frontend will support product browsing, cart management, checkout, customer details, and order confirmation. The backend will support product availability, purchase order review, news item management, and testimonial moderation.

## Core Inclusions

- Product catalogue showing only currently available artworks.
- Shopping cart with add, remove, update quantity, and clear cart actions.
- Checkout form collecting customer email, name, phone, and delivery address.
- Order persistence in MySQL using customer, purchase, purchase item, and product tables.
- Incrementing purchase number for each new order.
- Purchase order summary showing selected items, item costs, total cost, and delivery address.
- Simulated or implemented order emails to the buyer and the business handling address.
- Admin product management for adding, editing, hiding, and removing unavailable items from the frontend.
- Basic input validation and server-side sanitisation.
- Responsive layout for mobile and desktop.

## Extended Inclusions

- Front page news item controlled by the owner, showing only the most recent post.
- Buyer testimonials with moderation before publication.
- Admin login for backend-only actions.
- Security controls for password hashing, session handling, CSRF protection on forms, and prepared SQL statements.
- CI/CD pipeline using GitHub Actions for checks and later deployment.

## Exclusions

- Real payment gateway integration such as PayPal, Stripe, or bank payment processing.
- Inventory stock tracking, because products can be made when an order is received.
- Customer account registration and login.
- Advanced artwork search, filtering, recommendations, or wishlists.
- Complex email delivery infrastructure; emails may be simulated in development.
- Full production-grade design or branding beyond a clean functional prototype.

## Suggested Database Tables

- `customers`: email, first name, last name, title, address, city, state, postcode, country, phone.
- `products`: product number, description, category, price, colour, size, availability status, optional image path.
- `purchases`: purchase number, purchase date, customer email, delivery address snapshot, total amount.
- `purchase_items`: item number, purchase number, product number, quantity, item price snapshot.
- `news`: news id, title, message, created date, published status.
- `testimonials`: testimonial id, customer email, message, submitted date, moderation status.
- `admins`: admin id, email, password hash, role.

## Milestone Plan

| Date | Focus | Planned Output |
| --- | --- | --- |
| 28 Apr 2026 | Project setup and proposal planning | Confirm option 2 scope, create proposal outline, choose PHP + MySQL stack, define inclusions and exclusions. |
| 1 May 2026 | Data model and wireframes | ERD, SQL schema draft, page list, checkout flow, admin flow. |
| 5 May 2026 | Application skeleton | Basic PHP project structure, routing approach, database connection, shared layout, local config example. |
| 8 May 2026 | Product catalogue and cart | Product listing from database, cart add/remove/update, cart summary page. |
| 12 May 2026 | Checkout and order storage | Customer form, validation, order creation, purchase and purchase item records. |
| 15 May 2026 | Email/order confirmation | Buyer order summary, business order notification, simulated email log if SMTP is not available. |
| 19 May 2026 | Admin backend | Admin login, product management, order list/detail view. |
| 22 May 2026 | Optional features | Latest news item, moderated testimonials, responsive polish. |
| 26 May 2026 | Security and CI/CD hardening | Prepared statements review, CSRF tokens, session checks, GitHub Actions checks, deployment dry run. |
| 29 May 2026 | Final prototype target | Final testing, Spinetail deployment, code package, final report evidence. |

## CI/CD Plan

Use GitHub Actions from the start, even while the app is small.

Initial pipeline on every pull request and push:

- Check out the repository.
- Set up PHP.
- Validate PHP syntax with `php -l`.
- Install Composer dependencies if Composer is introduced.
- Run PHPUnit tests once test files exist.
- Start a MySQL service for database tests once migrations or schema scripts exist.
- Run SQL schema smoke checks.
- Upload test/build artefacts if useful for marking evidence.

Later deployment pipeline on pushes to `main`:

- Re-run all checks.
- Build a clean deploy artefact excluding local config, tests, and development files.
- Deploy to Spinetail using SFTP or SSH with GitHub repository secrets.
- Keep database credentials, mail settings, and deployment keys out of source control.

Required GitHub secrets once deployment is implemented:

- `SPINETAIL_HOST`
- `SPINETAIL_USER`
- `SPINETAIL_SSH_KEY`
- `SPINETAIL_PATH`
- `APP_ENV`
- `DB_HOST`
- `DB_NAME`
- `DB_USER`
- `DB_PASSWORD`
- `ORDER_HANDLING_EMAIL`

## Proposal Writing Notes

The proposal should be written as a realistic intention, not as a guarantee that every extended feature will be complete. The strongest core promise is a working order flow: products, cart, checkout, stored purchase order, and buyer/business order summaries. News, testimonials, and richer admin features can be framed as planned extensions if time allows.
