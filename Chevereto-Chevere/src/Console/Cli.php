<?php

declare(strict_types=1);

/*
 * This file is part of Chevere.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevere\Console;

use Chevere\Console\Commands\BuildCommand;
use Monolog\Logger;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Chevere\Console\Commands\RequestCommand;
use Chevere\Console\Commands\RunCommand;
use Chevere\Console\Commands\InspectCommand;
use Chevere\Contracts\Console\CliContract;
use Chevere\Contracts\Console\CommandContract;

/**
 * This class provides console facade for Symfony\Component\Console.
 */
final class Cli implements CliContract
{
    const NAME = __NAMESPACE__.' cli';
    const VERSION = '1.0';

    /** @var string Cli name */
    public $name;

    /** @var string Cli version */
    public $version;

    /** @var ArgvInput */
    public $input;

    /** @var ConsoleOutput */
    public $output;

    /** @var Logger */
    public $logger;

    /** @var Application */
    public $client;

    /** @var SymfonyStyle */
    public $out;

    /** @var CommandContract */
    public $command;

    public function __construct(ArgvInput $input)
    {
        $this->input = $input;
        $this->name = static::NAME;
        $this->version = static::VERSION;
        $this->output = new ConsoleOutput();
        $this->client = new Application($this->name, $this->version);
        $this->logger = new Logger($this->name);
        $this->out = new SymfonyStyle($this->input, $this->output);

        $this->client->addCommands([
            (new RequestCommand($this))->symfonyCommand(),
            (new RunCommand($this))->symfonyCommand(),
            (new InspectCommand($this))->symfonyCommand(),
            (new BuildCommand($this))->symfonyCommand(),
        ]);
        $this->client->setAutoExit(false);
    }

    public function runner()
    {
        $this->client->run($this->input, $this->output);
    }
}
