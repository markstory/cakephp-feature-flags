<?php
declare(strict_types=1);

namespace FeatureFlags\Simple;

use FeatureFlags\FeatureManagerInterface;
use InvalidArgumentException;

/**
 * A FeatureManager that implements simple configuration 
 * based feature flags.
 */
class FeatureManager implements FeatureManagerInterface
{
    /**
     * Constructor
     *
     * Create a feature manager and define a collection of features.
     * Use `config` to pass in an array of feature configuration.
     * The keys should be feature names, and values indicate whether
     * the feature is active or not.
     *
     * @param array<string, bool> $config Feature configuration to add on load.
     */
    public function __construct(
        protected array $config = [],
    ) {
    }

    /**
     * @inheritDoc
     */
    public function add(string $name, mixed $config)
    {
        if ($config !== true && $config !== false) {
            $type = get_debug_type($config);
            throw new InvalidArgumentException(
                "Flag values must be true/false. Received {$type}"
            );
        }
        $this->config[$name] = $config;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function has(string $name, array $context = []): bool
    {
        if (!isset($this->config[$name])) {
            return false;
        }

        return $this->config[$name];
    }

    /**
     * @inheritDoc
     */
    public function reset(): void
    {
        $this->config = [];
    }
}
