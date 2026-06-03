<?php

declare(strict_types=1);

namespace App\Repository;

use App\Core\Database;
use RuntimeException;

final class NewsRepository
{
    public function __construct(private readonly Database $database)
    {
    }

    public function latestPublished(): ?array
    {
        return $this->database->fetchOne(
            'SELECT * FROM news WHERE is_published = 1 ORDER BY created_at DESC LIMIT 1'
        );
    }

    public function all(): array
    {
        return $this->database->fetchAll(
            'SELECT * FROM news ORDER BY created_at DESC'
        );
    }

    public function create(string $title, string $message, bool $isPublished): void
    {
        $title = trim($title);
        $message = trim($message);

        if ($title === '' || $message === '') {
            throw new RuntimeException('Title and message are required.');
        }

        if ($isPublished) {
            $this->database->execute('UPDATE news SET is_published = 0');
        }

        $this->database->execute(
            'INSERT INTO news (title, message, is_published) VALUES (?, ?, ?)',
            [$title, $message, $isPublished ? 1 : 0]
        );
    }

    public function setHomepageNews(int $newsId): void
    {
        if ($newsId < 1) {
            throw new RuntimeException('Invalid news item selected.');
        }

        $this->database->execute('UPDATE news SET is_published = 0');

        $this->database->execute(
            'UPDATE news SET is_published = 1 WHERE news_id = ?',
            [$newsId]
        );
    }

    public function unpublish(int $newsId): void
    {
        if ($newsId < 1) {
            throw new RuntimeException('Invalid news item selected.');
        }

        $this->database->execute(
            'UPDATE news SET is_published = 0 WHERE news_id = ?',
            [$newsId]
        );
    }
}