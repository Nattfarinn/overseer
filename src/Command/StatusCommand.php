<?php

declare(strict_types=1);

namespace Overseer\Command;

use Overseer\Config\Config;
use Overseer\Git\Repository;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class StatusCommand extends Command
{
    protected function configure()
    {
        $this
            ->addOption('dirty', 'd', InputOption::VALUE_NONE)
            ->addOption('fetch', 'f', InputOption::VALUE_NONE);
    }

    public function action(Config $config, InputInterface $input, OutputInterface $output): void
    {
        $repositories = $config->getRepositories();

        $table = new Table($output);
        $table->setStyle('box');
        $table->setHeaders(['Repository', 'Branch', 'State']);

        $dirty = $input->getOption('dirty');
        $fetch = $input->getOption('fetch');

        if ($fetch) {
            $progress = new ProgressBar($output, count($repositories));
        }

        foreach ($repositories as $repository) {
            $git = new Repository($repository);

            if (!$dirty || $git->hasChanges()) {
                if ($fetch) {
                    $git->fetch('origin', true);
                }

                $states = [];
                if ($git->isAhead()) {
                    $states[] = $this->color('yellow', 'Ahead');
                }
                if ($git->isBehind()) {
                    $states[] = $this->color('black', 'Behind');
                }
                if ($git->hasChanges()) {
                    $states[] = $this->color('red', 'Dirty');
                } else {
                    $states[] = $this->color('green', 'Clean');
                }

                $table->addRow([
                    $this->color('green', $this->getRepositoryName($repository)),
                    $git->isDetached() ? $this->color('black', $git->getReferenceState()) : $git->getCurrentBranch(),
                    implode(', ', $states)
                ]);
            }

            if ($fetch) {
                $progress->advance();
            }
        }

        if ($fetch) {
            $progress->finish();
            $output->writeln('');
        }

        $table->render();
    }

    private function getRepositoryName(string $path): string
    {
        $name = basename($path);
        $vendor = basename(dirname($path));

        return sprintf('%s/%s', $vendor, $name);
    }

    private function color(string $color, string $text): string
    {
        return sprintf('<fg=%s>%s</>', $color, $text);
    }
}
