<?php

declare(strict_types=1);

namespace Overseer\Command;

use Overseer\Config\Config;
use Overseer\Config\ConfigResolver;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class Command extends SymfonyCommand
{
    final public function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $configResolver = new ConfigResolver(getcwd());

        $this->action(
            $configResolver->getConfig(),
            $input,
            $output
        );

        return SymfonyCommand::SUCCESS;
    }

    abstract public function action(
        Config $config,
        InputInterface $input,
        OutputInterface $output
    ): void;
}
