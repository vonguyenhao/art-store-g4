CREATE TABLE IF NOT EXISTS customers (
    email VARCHAR(255) PRIMARY KEY,
    title VARCHAR(20) NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    address VARCHAR(255) NOT NULL,
    city VARCHAR(120) NOT NULL,
    state VARCHAR(80) NOT NULL,
    postcode VARCHAR(20) NOT NULL,
    country VARCHAR(120) NOT NULL DEFAULT 'Australia',
    phone VARCHAR(40) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS products (
    product_no INT AUTO_INCREMENT PRIMARY KEY,
    description VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    colour VARCHAR(80) NULL,
    size VARCHAR(80) NULL,
    image_path VARCHAR(255) NULL,
    is_available TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS purchases (
    purchase_no INT AUTO_INCREMENT PRIMARY KEY,
    purchase_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    customer_email VARCHAR(255) NOT NULL,
    delivery_address TEXT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status VARCHAR(40) NOT NULL DEFAULT 'received',
    FOREIGN KEY (customer_email) REFERENCES customers(email)
);

CREATE TABLE IF NOT EXISTS purchase_items (
    item_no INT AUTO_INCREMENT PRIMARY KEY,
    purchase_no INT NOT NULL,
    product_no INT NOT NULL,
    quantity INT NOT NULL,
    item_price DECIMAL(10,2) NOT NULL,
    description_snapshot VARCHAR(255) NOT NULL,
    FOREIGN KEY (purchase_no) REFERENCES purchases(purchase_no) ON DELETE CASCADE,
    FOREIGN KEY (product_no) REFERENCES products(product_no)
);

CREATE TABLE IF NOT EXISTS news (
    news_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(160) NOT NULL,
    message TEXT NOT NULL,
    is_published TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS testimonials (
    testimonial_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_email VARCHAR(255) NOT NULL,
    customer_name VARCHAR(160) NOT NULL,
    message TEXT NOT NULL,
    rating TINYINT NOT NULL DEFAULT 5,
    status VARCHAR(20) NOT NULL DEFAULT 'pending',
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS admins (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(40) NOT NULL DEFAULT 'owner',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO products (description, category, price, colour, size, image_path, is_available)
SELECT 'Darwin Harbour Sunset', 'Painting', 450.00, 'Orange and blue', '60cm x 40cm', NULL, 1
WHERE NOT EXISTS (SELECT 1 FROM products);

INSERT INTO products (description, category, price, colour, size, image_path, is_available)
SELECT 'Mindil Beach Market Study', 'Print', 120.00, 'Mixed', 'A3', NULL, 1
WHERE (SELECT COUNT(*) FROM products) = 1;

INSERT INTO products (description, category, price, colour, size, image_path, is_available)
SELECT 'Wet Season Clouds', 'Photography', 180.00, 'Grey and green', 'A2', NULL, 1
WHERE (SELECT COUNT(*) FROM products) = 2;

INSERT INTO products (description, category, price, colour, size, image_path, is_available)
SELECT 'Larrakia Country Abstract', 'Painting', 520.00, 'Earth tones', '70cm x 50cm', NULL, 1
WHERE (SELECT COUNT(*) FROM products) = 3;

INSERT INTO products (description, category, price, colour, size, image_path, is_available)
SELECT 'Nightcliff Foreshore Print', 'Print', 95.00, 'Blue and sand', 'A3', NULL, 1
WHERE (SELECT COUNT(*) FROM products) = 4;

INSERT INTO products (description, category, price, colour, size, image_path, is_available)
SELECT 'Kakadu Wetlands Study', 'Photography', 210.00, 'Green and gold', 'A2', NULL, 1
WHERE (SELECT COUNT(*) FROM products) = 5;

INSERT INTO news (title, message, is_published)
SELECT 'New Darwin collection available', 'Our latest artworks are now available for online orders.', 1
WHERE NOT EXISTS (SELECT 1 FROM news);

INSERT INTO admins (email, password_hash, role)
SELECT 'admin@example.com', '$2y$10$kJ8OkVrmaROHTrACDZnRUO8VSuOtG0g0DNbbDDDQ48CDLceQwgiFG', 'owner'
WHERE NOT EXISTS (SELECT 1 FROM admins WHERE email = 'admin@example.com');

INSERT INTO testimonials (customer_email, customer_name, message, rating, status)
SELECT 'mia@example.com', 'Mia Thompson', 'The artwork arrived with clear order details and the checkout process was easy to follow.', 5, 'approved'
WHERE NOT EXISTS (
    SELECT 1 FROM testimonials WHERE customer_email = 'mia@example.com'
);

INSERT INTO testimonials (customer_email, customer_name, message, rating, status)
SELECT 'josh@example.com', 'Josh Williams', 'I liked being able to view the artwork details before adding it to the cart.', 4, 'approved'
WHERE NOT EXISTS (
    SELECT 1 FROM testimonials WHERE customer_email = 'josh@example.com'
);

INSERT INTO testimonials (customer_email, customer_name, message, rating, status)
SELECT 'sarah@example.com', 'Sarah Lee', 'The testimonial form was simple to use. I understand that feedback needs approval before appearing publicly.', 5, 'pending'
WHERE NOT EXISTS (
    SELECT 1 FROM testimonials WHERE customer_email = 'sarah@example.com'
);

INSERT INTO news (title, message, is_published)
SELECT 'Mindil Beach inspired prints added', 'A new range of Mindil Beach inspired prints has been added to our online collection. These pieces highlight colour, movement, and local Darwin scenery.', 0
WHERE NOT EXISTS (
    SELECT 1 FROM news WHERE title = 'Mindil Beach inspired prints added'
);

INSERT INTO news (title, message, is_published)
SELECT 'Local artist spotlight', 'This month we are highlighting local Northern Territory artists whose work is inspired by Darwin landscapes, coastline, markets, and wet season colours.', 0
WHERE NOT EXISTS (
    SELECT 1 FROM news WHERE title = 'Local artist spotlight'
);

INSERT INTO news (title, message, is_published)
SELECT 'Online ordering now available', 'Customers can now browse artworks, add selected pieces to the cart, and submit purchase requests directly through the Darwin Art Store website.', 0
WHERE NOT EXISTS (
    SELECT 1 FROM news WHERE title = 'Online ordering now available'
);

INSERT INTO testimonials (customer_email, customer_name, message, rating, status)
SELECT 'amelia@example.com', 'Amelia Brown', 'The product details were clear and the artwork information helped me choose the right piece for my living room.', 5, 'approved'
WHERE NOT EXISTS (
    SELECT 1 FROM testimonials WHERE customer_email = 'amelia@example.com'
);

INSERT INTO testimonials (customer_email, customer_name, message, rating, status)
SELECT 'noah@example.com', 'Noah Martin', 'The online store was easy to use and I liked being able to review my cart before submitting an order.', 5, 'approved'
WHERE NOT EXISTS (
    SELECT 1 FROM testimonials WHERE customer_email = 'noah@example.com'
);

INSERT INTO testimonials (customer_email, customer_name, message, rating, status)
SELECT 'ava@example.com', 'Ava Wilson', 'The Darwin-themed artworks feel unique and local. The ordering process was simple and professional.', 4, 'approved'
WHERE NOT EXISTS (
    SELECT 1 FROM testimonials WHERE customer_email = 'ava@example.com'
);

INSERT INTO testimonials (customer_email, customer_name, message, rating, status)
SELECT 'liam@example.com', 'Liam Harris', 'I appreciated the clear artwork categories, prices, and size information before placing my order.', 4, 'approved'
WHERE NOT EXISTS (
    SELECT 1 FROM testimonials WHERE customer_email = 'liam@example.com'
);

INSERT INTO testimonials (customer_email, customer_name, message, rating, status)
SELECT 'olivia@example.com', 'Olivia Taylor', 'The testimonial submission form was straightforward. I understand feedback is reviewed before being published.', 5, 'pending'
WHERE NOT EXISTS (
    SELECT 1 FROM testimonials WHERE customer_email = 'olivia@example.com'
);

INSERT INTO testimonials (customer_email, customer_name, message, rating, status)
SELECT 'ethan@example.com', 'Ethan Clark', 'The website looks clean and the artwork pages provide enough detail for customers to make a decision.', 5, 'pending'
WHERE NOT EXISTS (
    SELECT 1 FROM testimonials WHERE customer_email = 'ethan@example.com'
);