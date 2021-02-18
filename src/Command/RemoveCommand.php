<?php

declare(strict_types=1);

namespace Overseer\Command;

use Overseer\Config\Config;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class RemoveCommand extends Command
{
    protected function configure()
    {
        $this->addArgument('repositories', InputArgument::IS_ARRAY | InputArgument::REQUIRED);
    }

    public function action(
        Config $config,
        InputInterface $input,
        OutputInterface $output
    ): void {
        $repositories = $input->getArgument('repositories');

        $config->removeRepositories($repositories);
    }
}
