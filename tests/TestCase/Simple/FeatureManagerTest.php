<?php
declare(strict_types=1);

namespace Test\TestCase\Simple;

use Cake\TestSuite\TestCase;
use FeatureFlags\Simple\FeatureManager;
use InvalidArgumentException;

class SimpleFeatureManagerTest extends TestCase
{
    public function testConstructorAdd(): void
    {
        $manager = new FeatureManager([
            'calendar-v2' => true,
            'shop-v2' => false,
        ]);
        $this->assertTrue($manager->has('calendar-v2'));
        $this->assertFalse($manager->has('shop-v2'));
    }

    public function testAddInvalidConfig(): void
    {
        $manager = new FeatureManager();

        $this->expectException(InvalidArgumentException::class);
        $manager->add('calendar-v2', 'derp');
    }

    public function testAddOverwrite(): void
    {
        $manager = new FeatureManager();
        $manager->add('calendar-v2', true);
        $manager->add('calendar-v2', false);

        $this->assertFalse($manager->has('calendar-v2'));
    }

    public function testAdd(): void
    {
        $manager = new FeatureManager();
        $manager->add('calendar-v2', true);
        $this->assertSame($manager, $manager->add('shop-v2', false));

        $this->assertTrue($manager->has('calendar-v2'));
        $this->assertFalse($manager->has('shop-v2'));
    }

    public function testHasUndefined(): void
    {
        $manager = new FeatureManager();
        $this->assertFalse($manager->has('undefined-feature'));
    }

    public function testReset(): void
    {
        $manager = new FeatureManager();

        $manager->add('calendar-v2', true);
        $this->assertTrue($manager->has('calendar-v2'));

        $manager->reset();
        $this->assertFalse($manager->has('calendar-v2'));
    }
}
