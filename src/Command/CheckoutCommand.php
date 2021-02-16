<?php

declare(strict_types=1);

namespace Overseer\Command;

use Overseer\Config\Config;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class CheckoutCommand extends Command
{
    public function action(Config $config, InputInterface $input, OutputInterface $output): void
    {
        $repositories = $config->getRepositories();

        foreach ($repositories as $repository) {

        }
    }
}
