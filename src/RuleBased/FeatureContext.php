<?php
declare(strict_types=1);

namespace FeatureFlags\RuleBased;

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

    public function get(string $key): mixed
    {
        if (!$this->has($key)) {
            return null;
        }

        return $this->data[$key];
    }

    public function has(string $key): mixed
    {
        return array_key_exists($key, $this->data);
    }

    public function getId(): int
    {
        $string = json_encode($this->data);

        return intval(sha1($string), 16);
    }
}
