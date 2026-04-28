<?php

declare(strict_types=1);

namespace App\Service;

use App\Core\Session;
use App\Repository\AdminRepository;

final class AuthService
{
    public function __construct(
        private readonly Session $session,
        private readonly AdminRepository $admins
    ) {
    }

    public function attempt(string $email, string $password): bool
    {
        $admin = $this->admins->findByEmail($email);
        if (!$admin || !password_verify($password, $admin['password_hash'])) {
            return false;
        }

        $this->session->set('admin_id', $admin['admin_id']);
        $this->session->set('admin_email', $admin['email']);
        return true;
    }

    public function check(): bool
    {
        return (bool) $this->session->get('admin_id');
    }

    public function require(): void
    {
        if (!$this->check()) {
            redirect('/admin/login.php');
        }
    }

    public function logout(): void
    {
        $this->session->remove('admin_id');
        $this->session->remove('admin_email');
    }
}
