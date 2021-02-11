<?php

declare(strict_types=1);

namespace Overseer\Composer;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Repository\PathRepository;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use Overseer\Config\ConfigResolver;

final class OverseerPlugin implements PluginInterface, EventSubscriberInterface
{
    public function activate(Composer $composer, IOInterface $io): void
    {
    }

    public function deactivate(Composer $composer, IOInterface $io): void
    {
    }

    public function uninstall(Composer $composer, IOInterface $io): void
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ScriptEvents::PRE_INSTALL_CMD => 'prependRepositories',
            ScriptEvents::PRE_UPDATE_CMD => 'prependRepositories',
        ];
    }

    public function prependRepositories(Event $event): void
    {
        $composer = $event->getComposer();
        $io = $event->getIO();

        $configResolver = new ConfigResolver($composer->getPackage()->getTargetDir());
        $config = $configResolver->getConfig();

        foreach ($config->getRepositories() as $repository) {
            $composer->getRepositoryManager()->prependRepository(
                new PathRepository([
                    'url' => $repository,
                ], $io, $composer->getConfig())
            );
        }
    }
}
