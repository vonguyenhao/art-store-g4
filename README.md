# HIT326 Darwin Art Store

## Project Brief

This is a PHP + MySQL database-driven online art store for Darwin Art Company. Customers can browse artworks, view product details, add items to a cart, and checkout using email. Admin users can manage products, upload product images, view orders, manage news, and moderate testimonials. The official submitted app is the PHP/MySQL version served from `public/index.php`.

## Technology Stack

- PHP
- MySQL
- HTML
- CSS
- JavaScript

`frontend/` contains an optional React/Vite experiment, but React is not required for final testing or submission.

## Application Flow

### Customer Flow

`Homepage -> Product Listing/Search -> Product Detail -> Add to Cart -> Cart -> Guest Checkout -> Order Confirmation`

Customers start from the homepage or product listing page, browse/search artworks, open a product detail page, and add products to the session cart. They can review, update, remove, or clear cart items before completing guest checkout using email, name, phone, and delivery address. The order is saved into the database and displayed on the order confirmation page.

### Admin Flow

`Admin Login -> Dashboard -> Manage Products / Orders / News / Testimonials`

Admin users log in through the admin login page. They can manage products and upload product images, view customer orders and update order status, create/edit/delete/select homepage news, and approve or reject testimonials before they appear publicly.

### Data Flow

`PHP Page/Form -> PHP Service/Repository -> MySQL Database -> PHP Rendered Response`

User actions are submitted through PHP pages/forms. PHP validates input and uses service/repository classes to store or retrieve data from MySQL, then renders the result back through PHP pages.

## Local Setup

1. Start MySQL using XAMPP, WAMP, or MAMP.
2. Create a database named:
   `art_store`
3. Import:
   `database/schema.sql`
4. From the project root, run:
   `php -S 127.0.0.1:8000 -t public`
5. Open:
   `http://127.0.0.1:8000`

`public/` is the document root.

If using an old local database, run:

```sql
ALTER TABLE testimonials ADD COLUMN rating TINYINT NOT NULL DEFAULT 5;
```

## Team Roles

- Kevin/Hao: Product and cart flow, product detail page, cart validation, integration checking, final review.
- Olice: Database, product management, admin support, order/news integration.
- James: Checkout and order flow, customer lookup/create by email, purchase and purchase item saving.
- Ashesh: Frontend layout, homepage, news display, testimonials, UI/UX polish.

## Key Features

- Product listing and search
- Product detail page
- Product image upload and display
- Session-based shopping cart
- Guest checkout by email
- Order confirmation
- Admin login
- Product management
- Order status management
- News management
- Testimonial submission, moderation, rating, and filtering
- PHP CI validation

## Scope Notes

- Customer login/register/account/my_orders pages are outside the baseline proposal scope.
- Baseline customer flow is guest checkout by email.
- Product images are not stored as database BLOBs.
- Uploaded product images are stored in `storage/product-images/` and served through `/product_image.php?id=<product_id>`.
- GitHub Actions is CI only.
- No Spinetail, no GitHub Pages, and no automatic deployment.

## Do Not Commit

- `frontend/node_modules/`
- `storage/mail/orders.log`
- uploaded files inside `storage/product-images/`
- local secrets or database credentials
