<?php
declare(strict_types=1);

namespace App\Component\TelegramDialog;

use App\Component\TelegramDialog\Exception\StorageException;
use Redis;
use RedisException;

class Storage implements StorageInterface
{
    public const  STORE_PREFIX = 'telegram:dialog:';

    public function __construct(
        private readonly Redis $redis,
    ) {
    }

    /**
     * @throws StorageException
     */
    public function set(string|int $key, mixed $value, int $ttl = -1): void
    {
        try {
            $this->redis->set(
                $this->decorateKey($key),
                $this->serialize($value),
                ['ttl' => $ttl],
            );
        } catch (RedisException $e) {
            throw new StorageException($e->getMessage());
        }
    }

    private function decorateKey(string|int $key): string
    {
        return sprintf('%s:%s', static::STORE_PREFIX, $key);
    }

    private function serialize(mixed $value): string|int|float
    {
        return is_numeric($value) && !in_array($value, [\INF, -\INF], true) && !is_nan($value)
            ? $value
            : serialize($value);
    }

    /**
     * @throws StorageException
     */
    public function get(string|int $key): mixed
    {
        try {
            $value = $this->redis->get($this->decorateKey($key));
        } catch (RedisException $e) {
            throw new StorageException($e->getMessage());
        }

        return $value ? $this->unserialize($value) : null;
    }

    private function unserialize(string|int|float $value): mixed
    {
        return is_numeric($value)
            ? $value
            : unserialize($value, ['allowed_classes' => true]);
    }

    /**
     * @throws StorageException
     */
    public function has(int|string $key): bool
    {
        try {
            return (bool)$this->redis->exists($this->decorateKey($key));
        } catch (RedisException $e) {
            throw new StorageException($e->getMessage());
        }
    }

    /**
     * @throws StorageException
     */
    public function delete(string|int $key): void
    {
        try {
            $this->redis->del($this->decorateKey($key));
        } catch (RedisException $e) {
            throw new StorageException($e->getMessage());
        }
    }
}
