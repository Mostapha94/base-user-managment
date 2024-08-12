<?php

/*
 * This file is part of the JoliNotif project.
 *
 * (c) Loïck Piera <pyrech@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace qa\cs;

use Castor\Attribute\AsTask;

use function Castor\exit_code;
use function Castor\run;

#[AsTask(description: 'Fix CS', aliases: ['cs'])]
function cs(bool $dryRun = false): int
{
    $command = [
        __DIR__ . '/vendor/bin/php-cs-fixer',
        'fix',
    ];

    if ($dryRun) {
        $command[] = '--dry-run';
    }

    return exit_code($command);
}

#[AsTask(description: 'install dependencies')]
function install(): void
{
    run(['composer', 'install'], workingDirectory: __DIR__);
}

#[AsTask(description: 'Update dependencies')]
function update(): void
{
    run(['composer', 'update'], workingDirectory: __DIR__);
    run(['composer', 'bump'], workingDirectory: __DIR__);
}
