<?php
declare(strict_types=1);

namespace FeatureFlags;

use Cake\Console\CommandCollection;
use Cake\Core\BasePlugin;

/**
 * Plugin for FeatureFlags
 */
class FeatureFlagsPlugin extends BasePlugin
{
    /**
     * Add commands for the plugin.
     *
     * @param \Cake\Console\CommandCollection $commands The command collection to update.
     * @return \Cake\Console\CommandCollection
     */
    public function console(CommandCollection $commands): CommandCollection
    {
        // Add your commands here
        $commands = parent::console($commands);

        // TODO add feature flag validation tool?

        return $commands;
    }
}
