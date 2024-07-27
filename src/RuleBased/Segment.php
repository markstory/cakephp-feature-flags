<?php
declare(strict_types=1);

namespace FeatureFlags\RuleBased;

/**
 * A segment is a group of your audience (users)
 * that should have access to a feature.
 *
 * For a segment to grant a feature, *all* conditions
 * must pass within the segment. If a segment has zero
 * conditions it will always pass.
 *
 * Segments are only granted if the conditions match,
 * *and* the current context's generated ID is within
 * the current `rollout` percentage. Setting
 * a segment's `rollout` to 0 will deny all checks.
 *
 * @internal
 */
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

    /**
     * Check if a segment's conditions match $context
     *
     * @param \FeatureFlags\RuleBased\FeaturreContext
     * @return bool
     */
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

    /**
     * Create a segment from an array of configuration
     *
     * @param array $config The data for a condition
     * @return \FeatureFlags\RuleBased\Condition
     */
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
