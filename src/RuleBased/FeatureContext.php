<?php
declare(strict_types=1);

namespace FeatureFlags\RuleBased;

/**
 * A mapping of context data used in checking feature conditions.
 */
class FeatureContext
{
    /**
     * Constructor
     *
     * @param array<string, mixed> $data the context data.
     */
    public function __construct(
        protected array $data,
    ) {
    }

    /**
     * Get a value by key
     *
     * If the key doesn't exist, null will be returned
     *
     * @param string $key The key to read
     * @return mixed
     */
    public function get(string $key): mixed
    {
        if (!$this->has($key)) {
            return null;
        }

        return $this->data[$key];
    }

    /**
     * Check if the context has a value by key
     *
     * @param string $key The key to read
     * @return bool
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * Get the canonical id for a context
     *
     * @return int
     */
    public function getId(): int
    {
        $string = json_encode($this->data);

        return intval(sha1($string), 16);
    }
}
