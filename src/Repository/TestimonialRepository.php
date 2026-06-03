<?php

declare(strict_types=1);

namespace App\Repository;

use App\Core\Database;
use RuntimeException;

final class TestimonialRepository
{
    private const STATUS_PENDING = 'pending';
    private const STATUS_APPROVED = 'approved';
    private const STATUS_REJECTED = 'rejected';

    public function __construct(private readonly Database $database)
    {
    }

    public function approved(?int $rating = null): array
    {
        if ($rating !== null) {
            return $this->database->fetchAll(
                "SELECT * FROM testimonials
                 WHERE status = ? AND rating = ?
                 ORDER BY submitted_at DESC",
                [self::STATUS_APPROVED, $rating]
            );
        }

        return $this->database->fetchAll(
            "SELECT * FROM testimonials
             WHERE status = ?
             ORDER BY submitted_at DESC",
            [self::STATUS_APPROVED]
        );
    }

    public function approvedLimit(int $limit): array
    {
        $limit = max(1, min(3, $limit));

        return $this->database->fetchAll(
            "SELECT * FROM testimonials
             WHERE status = ?
             ORDER BY submitted_at DESC
             LIMIT $limit",
            [self::STATUS_APPROVED]
        );
    }

    public function all(): array
    {
        return $this->database->fetchAll(
            'SELECT * FROM testimonials ORDER BY submitted_at DESC'
        );
    }

    public function create(string $email, string $name, string $message, int $rating): void
    {
        $email = trim($email);
        $name = trim($name);
        $message = trim($message);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('Please enter a valid email address.');
        }

        if ($name === '') {
            throw new RuntimeException('Please enter your name.');
        }

        if ($message === '') {
            throw new RuntimeException('Please enter your testimonial.');
        }

        if ($rating < 1 || $rating > 5) {
            throw new RuntimeException('Please choose a rating between 1 and 5.');
        }

        $this->database->execute(
            'INSERT INTO testimonials (customer_email, customer_name, message, rating, status)
             VALUES (?, ?, ?, ?, ?)',
            [$email, $name, $message, $rating, self::STATUS_PENDING]
        );
    }

    public function moderate(int $testimonialId, string $status): void
    {
        if ($testimonialId < 1) {
            throw new RuntimeException('Invalid testimonial selected.');
        }

        $allowedStatuses = [
            self::STATUS_PENDING,
            self::STATUS_APPROVED,
            self::STATUS_REJECTED,
        ];

        if (!in_array($status, $allowedStatuses, true)) {
            throw new RuntimeException('Invalid testimonial status.');
        }

        $this->database->execute(
            'UPDATE testimonials SET status = ? WHERE testimonial_id = ?',
            [$status, $testimonialId]
        );
    }
}