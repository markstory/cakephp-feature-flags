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

    /**
     * Build a Feature from an array of data
     *
     * @param string $name   The name of the feature flagl
     * @param array  $config An array of cofiguration and segmented users
     * @return \FeatureFlags\Feature
     */
    public static function fromArray(string $name, array $config): Feature
    {
        $segments = [];
        foreach ($config['segments'] ?? [] as $segmentConfig) {
            $segments[] = Segment::fromArray($segmentConfig);
        }

        return new Feature($name, $segments);
    }
}
