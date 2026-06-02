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

    public function searchAvailable(string $search): array
    {
        $search = trim($search);

        if ($search === '') {
            return $this->available();
        }

        $term = '%' . $search . '%';

        return $this->database->fetchAll(
            'SELECT * FROM products
             WHERE is_available = 1
             AND (
                description LIKE ?
                OR category LIKE ?
                OR colour LIKE ?
                OR size LIKE ?
             )
             ORDER BY product_no DESC',
            [$term, $term, $term, $term]
        );
    }

    public function availableById(int $productNo): ?array
    {
        return $this->database->fetchOne(
            'SELECT * FROM products WHERE product_no = ? AND is_available = 1',
            [$productNo]
        );
    }

    public function all(): array
    {
        return $this->database->fetchAll('SELECT * FROM products ORDER BY product_no DESC');
    }

    public function find(int $productNo): ?array
    {
        return $this->database->fetchOne('SELECT * FROM products WHERE product_no = ?', [$productNo]);
    }

    public function imagePathUseCount(string $imagePath): int
    {
        $row = $this->database->fetchOne('SELECT COUNT(*) AS total FROM products WHERE image_path = ?', [$imagePath]);
        return (int) ($row['total'] ?? 0);
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
                 SET description = ?, category = ?, price = ?, colour = ?, size = ?, image_path = ?, is_available = ?
                 WHERE product_no = ?',
                [
                    $data['description'],
                    $data['category'],
                    $data['price'],
                    $data['colour'],
                    $data['size'],
                    $data['image_path'],
                    $data['is_available'],
                    $data['product_no'],
                ]
            );
            return;
        }

        $this->database->execute(
            'INSERT INTO products (description, category, price, colour, size, image_path, is_available)
             VALUES (?, ?, ?, ?, ?, ?, ?)',
            [
                $data['description'],
                $data['category'],
                $data['price'],
                $data['colour'],
                $data['size'],
                $data['image_path'],
                $data['is_available'],
            ]
        );
    }
}