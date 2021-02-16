<?php

declare(strict_types=1);

namespace Overseer\Git;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

abstract class Base
{
    /** @var string */
    private $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    protected function command(string $name, array $arguments = []): string
    {
        $command = array_merge(['git', $name], $arguments);

        $process = new Process($command, $this->path, [

        ]);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return trim($process->getOutput());
    }
}
