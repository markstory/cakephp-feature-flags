<?php
declare(strict_types=1);

namespace FeatureFlags\RuleBased;

class Feature
{
    /**
     * Constructor
     *
     * @param string $name The name of the feature.
     * @var array<\Feature\Segment> $segments List of segments in the feature that are combined with OR.
     */
    public function __construct(
        protected string $name,
        protected array $segments = [],
    ) {
    }

    /**
     * Check if the $context matches a segment in this feature.
     *
     * @param \Feature\FeatureContext $context
     * @return bool
     */
    public function match(FeatureContext $context): bool
    {
        foreach ($this->segments as $segment) {
            if ($segment->match($context)) {
                return true;
            }
        }

        return false;
    }

    public static function fromArray(string $name, array $config): Feature
    {
        $segments = [];
        foreach ($config['segments'] ?? [] as $segmentConfig) {
            $segments[] = Segment::fromArray($segmentConfig);
        }

        return new Feature($name, $segments);
    }
}
