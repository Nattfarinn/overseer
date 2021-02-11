<?php

declare(strict_types=1);

namespace Overseer\Command;

use Composer\Composer;
use Composer\Downloader\GitDownloader;
use Composer\Factory;
use Composer\IO\NullIO;
use Overseer\Config\Config;
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
            ->setName('download')
            ->addArgument('repositories', InputArgument::IS_ARRAY | InputArgument::REQUIRED);
    }

    public function action(
        Config $config,
        InputInterface $input,
        OutputInterface $output
    ): void {
        $repositories = $input->getArgument('repositories');
        $style = new SymfonyStyle($input, $output);

        $patterns = array_map(static function(string $repository): string {
            return sprintf(
                '|^%s$|',
                str_replace(self::MASK, self::MASK_PATTERN, $repository)
            );
        }, $repositories);

        $composer = (new Factory())->createComposer(
            new NullIO(),
            null,
            true,
            $config->getDirectory() ,
            true
        );

        $packages = array_merge(
            $composer->getPackage()->getRequires(),
            $composer->getPackage()->getDevRequires()
        );
        $packageNames = array_keys($packages);

        $gitDownloader = new GitDownloader(new NullIO(), $composer->getConfig());

        foreach ($patterns as $pattern) {
            $matches = preg_grep($pattern, $packageNames);

            foreach ($matches as $packageName) {
                /** @var \Composer\Package\Link $packageLink */
                $packageLink = $packages[$packageName];
                $package = $composer->getRepositoryManager()->findPackage(
                    $packageLink->getTarget(),
                    $packageLink->getPrettyConstraint()
                );

                $targetName = str_replace('/', '_', $packageLink->getTarget());
                $targetPath = implode(DIRECTORY_SEPARATOR, [
                    $config->getDirectory(),
                    'download',
                    $targetName
                ]);

                if (!is_dir($targetPath)) {
                    $style->note(sprintf('Downloading %s (target %s)', $packageName, $targetPath));

                    $gitDownloader->install($package, $targetPath);
                } else {
                    $style->note($packageName . ' already exists.');
                }

                $config->addRepository($targetPath);
            }
        }
    }
}
