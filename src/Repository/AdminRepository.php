<?php

declare(strict_types=1);

namespace App\Repository;

use App\Core\Database;

final class AdminRepository
{
    public function __construct(private readonly Database $database)
    {
    }

    public function findByEmail(string $email): ?array
    {
        return $this->database->fetchOne('SELECT * FROM admins WHERE email = ?', [$email]);
    }
}
