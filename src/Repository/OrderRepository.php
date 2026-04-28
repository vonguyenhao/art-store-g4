<?php

declare(strict_types=1);

namespace App\Repository;

use App\Core\Database;

final class OrderRepository
{
    public function __construct(private readonly Database $database)
    {
    }

    public function all(): array
    {
        return $this->database->fetchAll(
            'SELECT p.*, c.first_name, c.last_name, c.phone
             FROM purchases p
             JOIN customers c ON c.email = p.customer_email
             ORDER BY p.purchase_date DESC'
        );
    }

    public function findWithCustomer(int $purchaseNo): ?array
    {
        return $this->database->fetchOne(
            'SELECT p.*, c.first_name, c.last_name, c.phone
             FROM purchases p
             JOIN customers c ON c.email = p.customer_email
             WHERE p.purchase_no = ?',
            [$purchaseNo]
        );
    }

    public function items(int $purchaseNo): array
    {
        return $this->database->fetchAll('SELECT * FROM purchase_items WHERE purchase_no = ?', [$purchaseNo]);
    }

    public function counts(): array
    {
        return [
            'products' => $this->database->fetchOne('SELECT COUNT(*) AS total FROM products')['total'] ?? 0,
            'orders' => $this->database->fetchOne('SELECT COUNT(*) AS total FROM purchases')['total'] ?? 0,
            'pending' => $this->database->fetchOne("SELECT COUNT(*) AS total FROM testimonials WHERE status = 'pending'")['total'] ?? 0,
        ];
    }

    public function create(array $customer, string $deliveryAddress, array $items, float $total): int
    {
        $pdo = $this->database->connection();
        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare(
                'INSERT INTO customers (email, title, first_name, last_name, address, city, state, postcode, country, phone)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE
                    title = VALUES(title),
                    first_name = VALUES(first_name),
                    last_name = VALUES(last_name),
                    address = VALUES(address),
                    city = VALUES(city),
                    state = VALUES(state),
                    postcode = VALUES(postcode),
                    country = VALUES(country),
                    phone = VALUES(phone)'
            );
            $stmt->execute([
                $customer['email'],
                $customer['title'],
                $customer['first_name'],
                $customer['last_name'],
                $customer['address'],
                $customer['city'],
                $customer['state'],
                $customer['postcode'],
                $customer['country'],
                $customer['phone'],
            ]);

            $stmt = $pdo->prepare(
                'INSERT INTO purchases (customer_email, delivery_address, total_amount) VALUES (?, ?, ?)'
            );
            $stmt->execute([$customer['email'], $deliveryAddress, $total]);
            $purchaseNo = (int) $pdo->lastInsertId();

            $stmt = $pdo->prepare(
                'INSERT INTO purchase_items (purchase_no, product_no, quantity, item_price, description_snapshot)
                 VALUES (?, ?, ?, ?, ?)'
            );

            foreach ($items as $item) {
                $stmt->execute([
                    $purchaseNo,
                    $item['product']['product_no'],
                    $item['quantity'],
                    $item['product']['price'],
                    $item['product']['description'],
                ]);
            }

            $pdo->commit();
            return $purchaseNo;
        } catch (\Throwable $error) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $error;
        }
    }
}
