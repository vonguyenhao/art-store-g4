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

INSERT INTO news (title, message, is_published)
SELECT 'New Darwin collection available', 'Our latest artworks are now available for online orders.', 1
WHERE NOT EXISTS (SELECT 1 FROM news);

INSERT INTO admins (email, password_hash, role)
SELECT 'admin@example.com', '$2y$10$kJ8OkVrmaROHTrACDZnRUO8VSuOtG0g0DNbbDDDQ48CDLceQwgiFG', 'owner'
WHERE NOT EXISTS (SELECT 1 FROM admins WHERE email = 'admin@example.com');
