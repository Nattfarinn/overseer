<?php

declare(strict_types=1);

namespace Overseer\Config;

final class ConfigResolver
{
    private const OVERSEER_FILE_NAME = 'overseer.json';
    private const COMPOSER_FILE_NAME = 'composer.json';

    private string $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function getConfig(): Config
    {
        $configDirectory = $this->seekParentDirectory($this->path, self::OVERSEER_FILE_NAME)
            ?? $this->seekParentDirectory($this->path, self::COMPOSER_FILE_NAME)
            ?? $this->path;

        return new Config(implode(DIRECTORY_SEPARATOR, [
            $configDirectory,
            self::OVERSEER_FILE_NAME
        ]));
    }

    public function seekParentDirectory(string $path, string $match): ?string
    {
        $nextLocation = $path;
        do {
            $currentLocation = $nextLocation;

            $path = implode(DIRECTORY_SEPARATOR, [
                $nextLocation,
                $match
            ]);

            if (is_readable($path)) {
                return $currentLocation;
            }

            $nextLocation = dirname($currentLocation);
        } while($nextLocation !== $currentLocation);

        return null;
    }
}
