<?php
declare(strict_types=1);

namespace App\Component\TelegramDialog;

interface StorageInterface
{
    public function set(string | int $key, mixed $value, int $ttl): void;

    public function get(string | int $key): mixed;

    public function has(string | int $key): bool;

    public function delete(string | int $key): void;
}
