<?php

/*
 * This file is part of Chevere.
 *
 * (c) Rodolfo Berrios <rodolfo@chevere.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Chevere\Components\ExceptionHandler\Formatters;

use Chevere\Components\ExceptionHandler\Interfaces\FormatterInterface;
use Chevere\Components\ExceptionHandler\Interfaces\TraceInterface;
use Chevere\Components\VarDump\Interfaces\FormatterInterface as VarDumpFormatterInterface;
use JakubOnderka\PhpConsoleColor\ConsoleColor;

abstract class AbstractFormatter implements FormatterInterface
{
    protected VarDumpFormatterInterface $varDumpFormatter;

    /**
     * Creates a new instance.
     */
    final public function __construct()
    {
        $this->varDumpFormatter = $this->getVarDumpFormatter();
    }

    final public function varDumpFormatter(): VarDumpFormatterInterface
    {
        return $this->varDumpFormatter;
    }

    abstract public function getVarDumpFormatter(): VarDumpFormatterInterface;

    public function getTraceEntryTemplate(): string
    {
        return '#' . TraceInterface::TAG_ENTRY_POS . ' ' . TraceInterface::TAG_ENTRY_FILE_LINE . "\n"
            . TraceInterface::TAG_ENTRY_CLASS . TraceInterface::TAG_ENTRY_TYPE . TraceInterface::TAG_ENTRY_FUNCTION
            . '()' . TraceInterface::TAG_ENTRY_ARGUMENTS;
    }

    public function getHr(): string
    {
        return '------------------------------------------------------------';
    }

    public function getLineBreak(): string
    {
        return "\n\n";
    }

    public function wrapLink(string $value): string
    {
        return $value;
    }

    public function wrapSectionTitle(string $value): string
    {
        return $value;
    }

    public function wrapTitle(string $value): string
    {
        return $value;
    }

    public function wrapContent(string $value): string
    {
        return $value;
    }
}
