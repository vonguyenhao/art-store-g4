<?php

declare(strict_types=1);

namespace App\Repository;

use App\Core\Database;

final class ProductRepository
{
    public function __construct(private readonly Database $database)
    {
    }

    public function available(): array
    {
        return $this->database->fetchAll('SELECT * FROM products WHERE is_available = 1 ORDER BY product_no DESC');
    }

    public function all(): array
    {
        return $this->database->fetchAll('SELECT * FROM products ORDER BY product_no DESC');
    }

    public function availableByIds(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        return $this->database->fetchAll(
            "SELECT * FROM products WHERE is_available = 1 AND product_no IN ($placeholders)",
            array_values($ids)
        );
    }

    public function save(array $data): void
    {
        if ((int) ($data['product_no'] ?? 0) > 0) {
            $this->database->execute(
                'UPDATE products
                 SET description = ?, category = ?, price = ?, colour = ?, size = ?, is_available = ?
                 WHERE product_no = ?',
                [
                    $data['description'],
                    $data['category'],
                    $data['price'],
                    $data['colour'],
                    $data['size'],
                    $data['is_available'],
                    $data['product_no'],
                ]
            );
            return;
        }

        $this->database->execute(
            'INSERT INTO products (description, category, price, colour, size, is_available)
             VALUES (?, ?, ?, ?, ?, ?)',
            [
                $data['description'],
                $data['category'],
                $data['price'],
                $data['colour'],
                $data['size'],
                $data['is_available'],
            ]
        );
    }
}
