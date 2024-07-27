<?php
declare(strict_types=1);

namespace FeatureFlags\RuleBased;

use InvalidArgumentException;

class Condition
{
    /**
     * Constructor
     *
     * @param string $name The name of the feature.
     */
    public function __construct(
        protected string $property,
        protected string $op,
        protected string|array|int|float|bool $value,
    ) {
    }

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
            break;
            case 'in':
                if (!is_array($this->value)) {
                    return false;
                }

                return in_array($contextVal, $this->value);
            break;
            case 'not_equal':
                return $this->value != $contextVal;
            break;
            case 'not_in':
                if (!is_array($this->value)) {
                    return false;
                }

                return !in_array($contextVal, $this->value);
            break;
        }

        return false;
    }

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
