<?php

/*
 * This file is part of Chevere.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Chevere\Components\Console;

use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Chevere\Components\Console\Contracts\SymfonyCommandContract;

/**
 * Wrapper for Symfony\Component\Console\Command\Command
 */
final class SymfonyCommand extends BaseCommand implements SymfonyCommandContract
{
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        return 0;
    }
}
