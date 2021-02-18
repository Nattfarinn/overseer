<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Overseer\Command;

use Overseer\Config\Config;
use Overseer\Git\Repository;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class RebaseCommand extends Command
{
    public function action(Config $config, InputInterface $input, OutputInterface $output): void
    {
        $repositories = $config->getRepositories();
        $progress = new ProgressBar($output, count($repositories));

        foreach ($repositories as $repository) {
            $git = new Repository($repository);
            $dirty = $git->hasChanges();

            if (!$git->isDetached()) {
                $branch = $git->getCurrentBranch();

                $git->fetch('origin', true);

                if ($git->branchExists($branch, true)) {
                    if ($dirty) {
                        $git->add(['.']);
                        $git->stash();
                    }

                    $git->rebase(sprintf('origin/%s', $branch));

                    if ($dirty) {
                        $git->stashPop();
                    }
                }
            }

            $progress->advance();
        }

        $progress->finish();
        $output->writeln('');
    }
}
