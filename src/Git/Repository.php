<?php

declare(strict_types=1);

namespace Overseer\Git;

final class Repository extends Base
{
    public function status(bool $porcelain = false): ?string
    {
        $arguments = [];

        if ($porcelain) {
            $arguments[] = '--porcelain';
        }

        return $this->command('status', $arguments);
    }

    public function hasChanges(): bool
    {
        return !empty($this->status(true));
    }

    public function fetch(string $name, bool $tags): void
    {
        $arguments = [$name];

        if ($tags) {
            $arguments[] = '--tags';
        }

        $this->command('fetch', $arguments);
    }

    public function getCurrentBranch(): string
    {
        return $this->command('branch', ['--show-current']);
    }

    public function isDetached(): bool
    {
        return empty($this->getCurrentBranch());
    }

    public function getReferenceState(): string
    {
        return $this->command('show', [
            '-s', '--pretty=%d', 'HEAD'
        ]);
    }

    public function countCommitsBehindAhead(): array
    {
        if ($this->isDetached()) {
            return [0, 0];
        }

        $result = $this->command('rev-list', [
            '--left-right',
            '--count',
            sprintf(
                '%s...origin/%s',
                $this->getCurrentBranch(),
                $this->getCurrentBranch()
            )
        ]);

        preg_match("|(\d+)\s+(\d+)|", $result, $matches);

        return [
            (int)$matches[1],
            (int)$matches[2]
        ];
    }

    public function isBehind(): bool
    {
        return $this->isDetached()
            ? false
            : $this->countCommitsBehindAhead()[0] > 0;
    }

    public function isAhead(): bool
    {
        return $this->isDetached()
            ? false
            : $this->countCommitsBehindAhead()[1] > 0;
    }

    public function stash(): void
    {
        $this->command('stash');
    }

    public function stashPop(): void
    {
        $this->command('stash', ['pop']);
    }

    public function commit(string $message): void
    {
        $this->command('commit', [sprintf('-m "%s"', $message)]);
    }

    public function reset(string $target, bool $hard = false): void
    {
        $arguments = [$target];

        if ($hard) {
            $arguments[] = '--hard';
        }

        $this->command('reset', $arguments);
    }

    public function add(array $files): void
    {
        $this->command('add', $files);
    }

    public function rebase(string $target): void
    {
        $this->command('rebase', [$target]);
    }

    public function branchExists(string $branch, bool $remote = false): bool
    {
        if ($remote) {
            return !empty($this->command('ls-remote', ['--heads', 'origin', $branch]));
        }

        return !empty($this->command('branch', ['--list', $branch]));
    }

    public function checkout(string $branch, bool $track = false): void
    {
        if ($track) {
            $this->command('checkout', ['--track', sprintf('origin/%s', $branch)]);
        } else {
            $this->command('checkout', [$branch]);
        }
    }
}
