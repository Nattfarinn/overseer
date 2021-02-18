<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Overseer\Command;

use Composer\Factory;
use Composer\IO\ConsoleIO;
use Composer\Repository\InstalledRepository;
use Overseer\Config\Config;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class LicenceCommand extends Command
{
    public function action(Config $config, InputInterface $input, OutputInterface $output): void
    {
        $io = new ConsoleIO($input, $output, new HelperSet());
        $factory = new Factory();
        $composer = $factory->createComposer(
            $io,
            null,
            false,
            getcwd(),
            true
        );

        $repository = new InstalledRepository([
            $composer->getRepositoryManager()->getLocalRepository()
        ]);

        $licences = [];
        /** @var \Composer\Package\CompletePackage $package */
        foreach ($repository->getPackages() as $package) {
            $licences[$package->getName()] = $package->getLicense()[0];
        }

        $padding = max(array_map('strlen', array_keys($licences)));
        foreach ($licences as $package => $licence) {
            echo str_pad($package, $padding + 4, ' ', STR_PAD_RIGHT);
            echo $licence;
            echo "\n";
        }
    }
}
