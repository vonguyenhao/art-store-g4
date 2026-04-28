<?php

declare(strict_types=1);

namespace App\Core;

final class Csrf
{
    public function __construct(private readonly Session $session)
    {
    }

    public function token(): string
    {
        $token = $this->session->get('csrf_token');
        if (!is_string($token) || $token === '') {
            $token = bin2hex(random_bytes(32));
            $this->session->set('csrf_token', $token);
        }

        return $token;
    }

    public function verify(array $post): void
    {
        $token = (string) ($post['csrf_token'] ?? '');
        if (!hash_equals((string) $this->session->get('csrf_token', ''), $token)) {
            http_response_code(400);
            exit('Invalid form token.');
        }
    }
}
