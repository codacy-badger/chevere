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

namespace Chevere\Components\VarDump;

use Chevere\Components\Message\Message;
use Chevere\Components\Type\Interfaces\TypeInterface;
use Chevere\Components\Type\Type;
use Chevere\Components\VarDump\Interfaces\VarDumpeableInterface;
use Chevere\Components\VarDump\Interfaces\ProcessorInterface;
use Chevere\Components\VarDump\Interfaces\VarFormatInterface;
use LogicException;
use function ChevereFn\varType;

/**
 * Allows to interact with dumpeable variables.
 */
final class VarDumpeable implements VarDumpeableInterface
{
    /** @var mixed */
    private $var;

    private string $type;

    private string $processorName;

    private array $template;

    /**
     * Creates a new instance.
     *
     * @throws LogicException if it is not possible to dump the passed variable.
     */
    public function __construct($var)
    {
        $this->var = $var;
        $this->type = varType($this->var);
        $this->assertSetProcessorName();
        $this->setTemplate();
    }

    public function var()
    {
        return $this->var;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function template(): array
    {
        return $this->template;
    }

    public function processorName(): string
    {
        return $this->processorName;
    }

    private function assertSetProcessorName(): void
    {
        $processorName = VarFormatInterface::PROCESSORS[$this->type] ?? null;
        if (!isset($processorName)) {
            // @codeCoverageIgnoreStart
            throw new LogicException(
                (new Message('No processor for variable of type %type%'))
                ->code('%type%', $this->type)
                ->toString()
            );
            // @codeCoverageIgnoreEnd
        }
        if (!is_subclass_of($processorName, ProcessorInterface::class, true)) {
            // @codeCoverageIgnoreStart
            throw new LogicException(
                (new Message('Processor %processorName% must implement the %interfaceName% interface'))
                ->code('%processorName%', $processorName)
                ->code('%interfaceName%', ProcessorInterface::class)
                ->toString()
            );
            // @codeCoverageIgnoreEnd
        }
        $this->processorName = $processorName;
    }

    private function setTemplate(): void
    {
        switch ($this->type) {
            case TypeInterface::ARRAY:
            case TypeInterface::OBJECT:
                $this->template = ['%type%', '%info%', '%val%'];
                break;
            default:
                $this->template = ['%type%', '%val%', '%info%'];
                break;
        }
    }
}
