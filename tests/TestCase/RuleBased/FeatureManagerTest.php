<?php
declare(strict_types=1);

namespace Test\TestCase\RuleBased;

use Cake\TestSuite\TestCase;
use Closure;
use FeatureFlags\RuleBased\FeatureManager;
use InvalidArgumentException;

class FeatureManagerTest extends TestCase
{
    private Closure $contextBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->contextBuilder = function (array $data) {
            $context = [];
            if (isset($data['user'])) {
                $context['user_email'] = $data['user']->email;
            }

            return $context;
        };
    }

    public function testConstructorAdd(): void
    {
        $config = [
            'calendar-v2' => [
                'segments' => [
                    [
                        'name' => 'internal user',
                        'conditions' => [
                            [
                                'property' => 'user_email',
                                'op' => 'in',
                                'value' => ['test@example.com'],
                            ],
                        ],
                        'rollout' => 50,
                    ],
                ],
            ],
            'shop-v2' => [
                'segments' => [
                    [
                        'name' => 'internal user',
                        'conditions' => [
                            [
                                'property' => 'user_email',
                                'op' => 'in',
                                'value' => ['other@example.com'],
                            ],
                        ],
                        'rollout' => 50,
                    ],
                ],

            ],
        ];
        $manager = new FeatureManager($this->contextBuilder, $config);

        $good = (object)['email' => 'test@example.com'];
        $this->assertTrue($manager->has('calendar-v2', ['user' => $good]));
        $this->assertFalse($manager->has('shop-v2', ['user' => $good]));
    }

    public function testAddInvalidConfigType(): void
    {
        $manager = new FeatureManager($this->contextBuilder);

        $this->expectException(InvalidArgumentException::class);
        $manager->add('calendar-v2', 'derp');
    }

    public function testAddInvalidSegment(): void
    {
        $manager = new FeatureManager($this->contextBuilder);

        $manager->add('calendar-v2', [
            'segments' => [
                ['derp'],
            ],
        ]);
        // Invalid config doesn't generate errors.
        $this->assertFalse($manager->has('calendar-v2', []));
    }

    public function testAddInvalidCondition(): void
    {
        $manager = new FeatureManager($this->contextBuilder);

        $manager->add('calendar-v2', [
            'segments' => [
                [
                    'name' => 'internal users',
                    'conditions' => [
                        ['derp'],
                    ],
                ],
            ],
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Condition with config ["derp"] is invalid');
        $manager->has('calendar-v2', []);
    }

    public function testAddInvalidConditionOperator(): void
    {
        $manager = new FeatureManager($this->contextBuilder);
        $manager->add('calendar-v2', [
            'segments' => [
                [
                    'name' => 'internal users',
                    'conditions' => [
                        [
                            'property' => 'user_email',
                            'operator' => 'lolz',
                            'value' => 'yes',
                        ],
                    ],
                ],
            ],
        ]);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Condition with config {"property":');
        $manager->has('calendar-v2');
    }

    public function testAddOverwrite(): void
    {
        // No segments means all contexts pass
        $config = [
            'segments' => [
                [
                    'name' => 'all users',
                    'conditions' => [],
                    'rollout' => 100,
                ],
            ],
        ];
        $manager = new FeatureManager($this->contextBuilder);
        $manager->add('calendar-v2', $config);
        $this->assertTrue($manager->has('calendar-v2'));

        $config = [
            'segments' => [
                [
                    'name' => 'all users',
                    'conditions' => [
                        [
                            'property' => 'user_email',
                            'op' => 'equal',
                            'value' => 'user@example.com',
                        ],
                    ],
                ],
            ],
        ];
        $manager->add('calendar-v2', $config);
        $this->assertFalse($manager->has('calendar-v2'));
    }

    public function testHasBasicMatching(): void
    {
        $manager = new FeatureManager($this->contextBuilder);
        $manager->add('calendar-v2', [
            'segments' => [
                [
                    'name' => 'internal user',
                    'conditions' => [
                        [
                            'property' => 'user_email',
                            'op' => 'in',
                            'value' => ['test@example.com'],
                        ],
                    ],
                    'rollout' => 100,
                ],
            ],
        ]);

        $bad = (object)['email' => 'bad@example.com'];
        $good = (object)['email' => 'test@example.com'];
        $this->assertFalse($manager->has('calendar-v2'));
        $this->assertFalse($manager->has('calendar-v2', ['user' => $bad]));
        $this->assertTrue($manager->has('calendar-v2', ['user' => $good]));
    }

    public function testHasRollout(): void
    {
        $manager = new FeatureManager($this->contextBuilder);
        $manager->add('calendar-v2', [
            'segments' => [
                [
                    'name' => 'internal user',
                    'conditions' => [
                        [
                            'property' => 'user_email',
                            'op' => 'in',
                            'value' => ['test@example.com'],
                        ],
                    ],
                    'rollout' => 50,
                ],
            ],
        ]);

        $bad = (object)['email' => 'bad@example.com'];
        $good = (object)['email' => 'test@example.com'];
        $this->assertFalse($manager->has('calendar-v2'));
        $this->assertFalse($manager->has('calendar-v2', ['user' => $bad]));
        $this->assertTrue($manager->has('calendar-v2', ['user' => $good]));
    }

    public function testHasUndefined(): void
    {
        $manager = new FeatureManager($this->contextBuilder);
        // Missing features don't cause failures as we're are generally
        // not using the ORM directly
        $this->assertFalse($manager->has('undefined-feature'));
    }

    public function testHasSegmentZero(): void
    {
        $manager = new FeatureManager($this->contextBuilder);
        $manager->add('calendar-v2', [
            'segments' => [
                [
                    'name' => 'internal user',
                    'conditions' => [
                        [
                            'property' => 'user_email',
                            'op' => 'in',
                            'value' => ['test@example.com'],
                        ],
                    ],
                    'rollout' => 0,
                ],
            ],
        ]);

        $good = (object)['email' => 'test@example.com'];
        $this->assertFalse($manager->has('calendar-v2', ['user' => $good]));
    }

    public function testHasNoContext(): void
    {
        $manager = new FeatureManager($this->contextBuilder);
        $manager->add('calendar-v2', [
            'segments' => [
                [
                    'name' => 'internal user',
                    'conditions' => [
                        [
                            'property' => 'user_email',
                            'op' => 'in',
                            'value' => ['test@example.com'],
                        ],
                    ],
                    'rollout' => 0,
                ],
            ],
        ]);

        $this->assertFalse($manager->has('calendar-v2'));
    }
}
