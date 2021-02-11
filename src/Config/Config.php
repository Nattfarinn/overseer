<?php

declare(strict_types=1);

namespace Overseer\Config;

final class Config
{
    private string $path;

    private string $directory;

    private array $repositories;

    public function __construct(string $path)
    {
        $this->path = $path;
        $this->directory = dirname($path);

        $this->repositories = $this->read();
    }

    public function getRepositories(): array
    {
        return $this->repositories;
    }

    public function setRepositories(array $repositories): void
    {
        $this->repositories = array_values(
            array_filter(
                array_unique(
                    array_map(
                        static function(string $path): string {
                            return rtrim($path, DIRECTORY_SEPARATOR);
                        },
                        $repositories
                    )
                ),
                static function(string $path): bool {
                    return is_dir($path) && is_readable($path);
                }
            )
        );

        $this->write();
    }

    public function addRepository(string $repository): void
    {
        $this->addRepositories([$repository]);
    }

    public function removeRepository(string $repository): void
    {
        $this->removeRepositories([$repository]);
    }

    public function removeRepositories(array $repositories): void
    {
        $this->setRepositories(array_diff($this->repositories, $repositories));
    }

    public function addRepositories(array $repositories): void
    {
        $this->setRepositories(array_merge($this->repositories, $repositories));
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getDirectory(): string
    {
        return $this->directory;
    }

    private function write(): void
    {
        file_put_contents(
            $this->path,
            json_encode(
                $this->repositories,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
            )
        );
    }

    private function read(): array
    {
        return is_readable($this->path)
            ? json_decode(file_get_contents($this->path), true)
            : [];
    }
}
