<?php
declare(strict_types=1);

namespace FeatureFlags;

/**
 * Interface for a collection of features
 * that can be checked against the current application state.
 */
interface FeatureManagerInterface
{
    /**
     * Check whether or not a feature flag is enabled
     *
     * Feature flag implementations can consume different configuration
     * data and optionally use `$context` to make more informed decisions
     * about feature flags.
     *
     * @param string $name    The name of the feature flag to check
     * @param array  $context A mapping of application context data
     *                         that is used to make feature
     *                         decisions.
     * @return bool
     */
    public function has(string $name, array $context = []): bool;

    /**
     * Add a feature to the manager at runtime.
     *
     * If a feature is added more than once, subsequent
     * adds will overwrite the current flag state.
     *
     * @param string $name   The name of the feature.
     * @param mixed  $config The settings for the feature flag
     * @return $this
     */
    public function add(string $name, mixed $config);

    /**
     * Clear all existing feature flags
     *
     * @return void
     */
    public function reset(): void;
}
