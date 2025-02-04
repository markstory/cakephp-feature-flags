<?php
declare(strict_types=1);

namespace FeatureFlags\RuleBased;

use InvalidArgumentException;

/**
 * An individual condition in a segment
 *
 * The following `op` values are supported:
 *
 * - `equal` Match if the context value matches `value`
 * - `not_equal` Match if the context value is not equal to `value`
 * - `in` Match if the context value is within the array of `value`.
 * - `not_in` Match if the context value is not contained in the array of `value`.
 *
 * @internal
 */
class Condition
{
    /**
     * Constructor
     *
     * @param string $property The property name for the condition
     * @param string $op The operator for the field of the feature.
     * @param array|string|float|int|bool $value The value to compare against.
     */
    public function __construct(
        protected string $property,
        protected string $op,
        protected string|array|int|float|bool $value,
    ) {
    }

    /**
     * Check if a condition matches $context
     *
     * @param \FeatureFlags\RuleBased\FeatureContext $context The feature context to match with
     * @return bool
     */
    public function match(FeatureContext $context): bool
    {
        if (!$context->has($this->property)) {
            return false;
        }
        $contextVal = $context->get($this->property);
        switch ($this->op) {
            // TODO add more operators
            case 'equal':
                return $this->value == $contextVal;
            case 'in':
                if (!is_array($this->value)) {
                    return false;
                }

                return in_array($contextVal, $this->value);
            case 'not_equal':
                return $this->value != $contextVal;
            case 'not_in':
                if (!is_array($this->value)) {
                    return false;
                }

                return !in_array($contextVal, $this->value);
        }

        return false;
    }

    /**
     * Create a condition from an array of configuration
     *
     * @param array $config The data for a condition
     * @return self
     */
    public static function fromArray(array $config): Condition
    {
        $property = $config['property'] ?? null;
        $op = $config['op'] ?? null;
        $value = $config['value'] ?? null;
        if ($property === null || $op === null || $value === null) {
            throw new InvalidArgumentException('Condition with config ' . json_encode($config) . ' is invalid.');
        }

        return new Condition($property, $op, $value);
    }
}
