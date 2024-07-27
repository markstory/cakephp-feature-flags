<?php
declare(strict_types=1);

namespace FeatureFlags\RuleBased;

class Segment
{
    /**
     * Constructor for segment
     *
     * @param string $name The name of the segment
     * @param array<\Feature\Condition> $conditions The conditions that are combined with AND
     */
    public function __construct(
        protected string $name,
        protected array $conditions,
        protected float|null $rollout,
    ) {
    }

    public function match(FeatureContext $context): bool
    {
        foreach ($this->conditions as $condition) {
            if (!$condition->match($context)) {
                return false;
            }
        }
        if ($this->rollout === 0.0) {
            return false;
        }
        if ($this->rollout != null) {
            $contextId = $context->getId();

            return $contextId % 100 <= $this->rollout;
        }

        return true;
    }

    public static function fromArray(array $config): Segment
    {
        $conditions = [];
        foreach ($config['conditions'] ?? [] as $conditionConfig) {
            $conditions[] = Condition::fromArray($conditionConfig);
        }
        $name = $config['name'] ?? 'unknown';
        $rollout = $config['rollout'] ?? 0;

        return new Segment($name, $conditions, $rollout);
    }
}
