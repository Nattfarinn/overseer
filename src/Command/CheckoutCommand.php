<?php

declare(strict_types=1);

namespace Overseer\Command;

use Overseer\Config\Config;
use Overseer\Git\Repository;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class CheckoutCommand extends Command
{
    protected function configure()
    {
        $this
            ->addArgument('branch', InputArgument::REQUIRED)
            ->addOption('reset', 'r', InputOption::VALUE_NONE)
            ->addOption('force', 'f', InputOption::VALUE_NONE);
    }

    public function action(Config $config, InputInterface $input, OutputInterface $output): void
    {
        $repositories = $config->getRepositories();
        $branch = $input->getArgument('branch');
        $reset = $input->getOption('reset');
        $force = $input->getOption('force');

        $progress = new ProgressBar($output, count($repositories));

        foreach ($repositories as $repository) {
            $git = new Repository($repository);
            if ($force) {
                $git->add(['.']);
                $git->reset('HEAD', true);
            }
            $dirty = $git->hasChanges();
            if (!$dirty) {
                $this->checkout($git, $branch, $reset);
            }
            $progress->advance();
        }
        $progress->finish();
        $output->writeln('');
    }

    private function checkout(Repository $git, string $branch, bool $reset): void
    {
        if ($git->branchExists($branch)) {
            $git->checkout($branch);
        } elseif ($git->branchExists($branch, true)) {
            $git->checkout($branch, true);
        } elseif ($branch === 'master') {
            $this->checkout($git, 'main', $reset);
            return;
        }

        if ($reset) {
            $git->reset(sprintf('origin/%s', $branch), true);
        }
    }
}
