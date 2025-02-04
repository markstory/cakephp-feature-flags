<?php
declare(strict_types=1);

namespace FeatureFlags\RuleBased;

use Closure;
use FeatureFlags\FeatureManagerInterface;
use InvalidArgumentException;
use RuntimeException;

/**
 * A FeatureManager that implements rule based feature decisions.
 */
class FeatureManager implements FeatureManagerInterface
{
    protected array $features = [];

    /**
     * Constructor
     *
     * Create a feature manager and define a collection of features.
     * Use `config` to pass in an array of feature configuration.
     * The keys should be feature names, and values a collection of segments, and conditions.
     *
     * @param \Closure             $contextBuilder A closure that transforms a basic array into a `FeatureContext`.
     * @param array<string, mixed> $config         Feature configuration to add on load.
     */
    public function __construct(
        protected Closure $contextBuilder,
        protected array $config = [],
    ) {
    }

    /**
     * @inheritDoc
     */
    public function add(string $name, mixed $config)
    {
        if (!is_array($config)) {
            $type = get_debug_type($config);
            throw new InvalidArgumentException(
                "Invalid configuration for {$name}. Expected an array, got {$type}."
            );
        }
        $this->config[$name] = $config;
        unset($this->features[$name]);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function has(string $name, array $context = []): bool
    {
        $feature = $this->get($name);
        if (!$feature) {
            return false;
        }
        $builder = $this->contextBuilder;
        $featureContext = $builder($context);
        if (is_array($featureContext)) {
            $featureContext = new FeatureContext($featureContext);
        }
        if (!($featureContext instanceof FeatureContext)) {
            throw new RuntimeException("Generated context for {$name} is invalid.");
        }

        return $feature->match($featureContext);
    }

    /**
     * Get a Feature instance for the named feature
     *
     * Once a Feature is created, it will be re-used for the rest of the process.
     *
     * @param string $name The feaeture name to get.
     * @return ?\FeatureFlags\RuleBased\Feature
     */
    public function get(string $name): ?Feature
    {
        if (isset($this->features[$name])) {
            return $this->features[$name];
        }
        if (!isset($this->config[$name])) {
            return null;
        }
        $config = $this->config[$name];
        $this->features[$name] = Feature::fromArray($name, $config);

        return $this->features[$name];
    }

    /**
     * @inheritDoc
     */
    public function reset(): void
    {
        $this->features = [];
        $this->config = [];
    }
}
