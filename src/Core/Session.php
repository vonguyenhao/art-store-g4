<?php

declare(strict_types=1);

namespace App\Core;

final class Session
{
    public function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public function flash(?string $message = null): ?string
    {
        if ($message !== null) {
            $this->set('flash', $message);
            return null;
        }

        $current = $this->get('flash');
        $this->remove('flash');
        return $current;
    }
}
