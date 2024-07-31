<?php
declare(strict_types=1);

namespace FeatureFlags;

use Cake\Console\CommandCollection;
use Cake\Core\BasePlugin;
use Cake\Core\Configure;
use Cake\Core\PluginApplicationInterface;

/**
 * Plugin for FeatureFlags
 */
class FeatureFlagsPlugin extends BasePlugin
{
    /**
     * @inheritDoc
     */
    public function console(CommandCollection $commands): CommandCollection
    {
        // Add your commands here
        $commands = parent::console($commands);

        // TODO add feature flag validation tool?

        return $commands;
    }

    /**
     * @inheritDoc
     */
    public function bootstrap(PluginApplicationInterface $app): void
    {
        parent::bootstrap($app);

        Configure::load('features');
    }
}
