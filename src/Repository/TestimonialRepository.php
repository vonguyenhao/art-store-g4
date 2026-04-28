<?php

declare(strict_types=1);

namespace App\Repository;

use App\Core\Database;

final class TestimonialRepository
{
    public function __construct(private readonly Database $database)
    {
    }

    public function approved(): array
    {
        return $this->database->fetchAll(
            "SELECT * FROM testimonials WHERE status = 'approved' ORDER BY submitted_at DESC"
        );
    }

    public function all(): array
    {
        return $this->database->fetchAll('SELECT * FROM testimonials ORDER BY submitted_at DESC');
    }

    public function create(string $email, string $name, string $message): void
    {
        $this->database->execute(
            'INSERT INTO testimonials (customer_email, customer_name, message, status) VALUES (?, ?, ?, ?)',
            [$email, $name, $message, 'pending']
        );
    }

    public function moderate(int $testimonialId, string $status): void
    {
        $this->database->execute(
            'UPDATE testimonials SET status = ? WHERE testimonial_id = ?',
            [$status, $testimonialId]
        );
    }
}
