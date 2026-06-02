<?php

declare(strict_types=1);

namespace App\Repository;

use App\Core\Database;

final class NewsRepository
{
    public function __construct(private readonly Database $database)
    {
    }

    public function latestPublished(): ?array
    {
        return $this->database->fetchOne('SELECT * FROM news WHERE is_published = 1 ORDER BY created_at DESC LIMIT 1');
    }

    public function all(): array
    {
        return $this->database->fetchAll('SELECT * FROM news ORDER BY created_at DESC');
    }

    public function create(string $title, string $message, bool $isPublished): void
    {
        $this->database->execute(
            'INSERT INTO news (title, message, is_published) VALUES (?, ?, ?)',
            [$title, $message, $isPublished ? 1 : 0]
        );
    }

    public function update(int $newsId, string $title, string $message, bool $isPublished): void
    {
        $this->database->execute(
            'UPDATE news
             SET title = ?, message = ?, is_published = ?
             WHERE news_id = ?',
            [$title, $message, $isPublished ? 1 : 0, $newsId]
        );
    }

    public function delete(int $newsId): void
    {
        $this->database->execute(
            'DELETE FROM news WHERE news_id = ?',
            [$newsId]
        );
    }
}