<?php

declare(strict_types=1);

namespace Overseer\Command;

use Composer\Downloader\DownloadManager;
use Composer\Downloader\GitDownloader;
use Composer\Factory;
use Composer\IO\ConsoleIO;
use Composer\Repository\InstalledRepository;
use Overseer\Config\Config;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class DownloadCommand extends Command
{
    private const MASK = '*';
    private const MASK_PATTERN = '[\w-]+';

    protected function configure()
    {
        $this
            ->addArgument('target', InputArgument::REQUIRED)
            ->addArgument('repositories', InputArgument::IS_ARRAY | InputArgument::REQUIRED);
    }

    public function action(
        Config $config,
        InputInterface $input,
        OutputInterface $output
    ): void {
        $repositories = $input->getArgument('repositories');
        $target = $input->getArgument('target');
        $style = new SymfonyStyle($input, $output);
        $io = new ConsoleIO($input, $output, new HelperSet());

        $patterns = array_map(static function(string $repository): string {
            return sprintf(
                '|^%s$|',
                str_replace(self::MASK, self::MASK_PATTERN, $repository)
            );
        }, $repositories);

        $composer = Factory::create(
            $io,
            null,
            false
        );

        $repository = new InstalledRepository([
            $composer->getRepositoryManager()->getLocalRepository()
        ]);

        $downloadManager = new DownloadManager($io, true);
        $downloadManager->setPreferDist(false);
        $downloadManager->setPreferSource(true);
        $downloadManager->setDownloader('git', new GitDownloader($io, $composer->getConfig()));

        $packages = [];
        foreach ($repository->getPackages() as $package) {
            $packages[$package->getName()] = $package;
        }
        $packageNames = array_keys($packages);

        foreach ($patterns as $pattern) {
            $matches = preg_grep($pattern, $packageNames);

            foreach ($matches as $packageName) {
                /** @var \Composer\Package\PackageInterface $package */
                $package = $packages[$packageName];

                $targetPath = implode(DIRECTORY_SEPARATOR, [
                    $target,
                    $packageName
                ]);

                if (!is_dir($targetPath)) {
                    $downloadManager->download($package, $targetPath);
                    $downloader = $downloadManager->getDownloader('git');
                    $downloader->install($package, $targetPath);
                } else {
                    $style->note($packageName . ' already exists.');
                }

                $config->addRepository($targetPath);
            }
        }
    }
}
