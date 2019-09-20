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

namespace Chevere\VarDump\Processors;

use ReflectionObject;
use Throwable;
use Chevere\Contracts\VarDump\ProcessorContract;
use Chevere\Path\Path;
use Chevere\Str\Str;
use Chevere\VarDump\Processors\Traits\ProcessorTrait;
use Chevere\VarDump\VarDump;

final class ObjectProcessor implements ProcessorContract
{
    use ProcessorTrait;

    /** @var object */
    private $expression;

    /** @var VarDump */
    private $varDump;

    /** @var Reflector */
    private $reflectionObject;

    /** @var array */
    private $properties;

    /** @var string */
    private $className;

    public function __construct(object $expression, VarDump $varDump)
    {
        $this->expression = $expression;
        $this->varDump = $varDump;
        $this->val = '';
        $this->parentheses = '';
        $this->reflectionObject = new ReflectionObject($expression);
        if (in_array($this->reflectionObject->getName(), $this->varDump->dontDump())) {
            $this->val .= $this->varDump->formatter()->wrap(
                VarDump::_OPERATOR,
                $this->varDump->formatter()->getEmphasis(
                    $this->reflectionObject->getName()
                )
            );

            return;
        }
        $this->setProperties();
        foreach ($this->properties as $k => $v) {
            $this->processObjectProperty($k, $v);
        }
        $this->className = get_class($expression);
        $this->handleNormalizeClassName();
        $this->parentheses = $this->className;
    }

    private function setProperties(): void
    {
        $this->properties = [];
        foreach (VarDump::PROPERTIES_REFLECTION_MAP as $visibility => $filter) {
            /** @scrutinizer ignore-call */
            $properties = $this->reflectionObject->getProperties($filter);
            foreach ($properties as $property) {
                if (!isset($this->properties[$property->getName()])) {
                    $property->setAccessible(true);
                    try {
                        $value = $property->getValue($this->expression);
                    } catch (Throwable $e) {
                        // $e
                    }
                    $this->properties[$property->getName()] = ['value' => $value];
                }
                $this->properties[$property->getName()]['visibility'][] = $visibility;
            }
        }
    }

    private function processObjectProperty($key, $var): void
    {
        $visibility = implode(' ', $var['visibility'] ?? $this->properties['visibility']);
        $operator = $this->varDump->formatter()->wrap(VarDump::_OPERATOR, '->');
        $this->val .= "\n" . $this->varDump->indentString() . $this->varDump->formatter()->getEmphasis($visibility) . ' ' . $this->varDump->formatter()->getEncodedChars($key) . " $operator ";
        $aux = $var['value'];
        if (is_object($aux) && property_exists($aux, $key)) {
            try {
                $r = new ReflectionObject($aux);
                $p = $r->getProperty($key);
                $p->setAccessible(true);
                if ($aux == $p->getValue($aux)) {
                    $this->val .= $this->varDump->formatter()->wrap(
                        VarDump::_OPERATOR,
                        '(' . $this->varDump->formatter()->getEmphasis('circular object reference') . ')'
                    );
                }
                return;
            } catch (Throwable $e) {
                return;
            }
        }
        if ($this->varDump->depth() < 4) {
            $new = $this->varDump->respawn();
            $new->dump($aux, $this->varDump->indent(), $this->varDump->depth());
            $this->val .= $new->toString();
        } else {
            $this->val .= $this->varDump->formatter()->wrap(
                VarDump::_OPERATOR,
                '(' . $this->varDump->formatter()->getEmphasis('max depth reached') . ')'
            );
        }
    }

    private function handleNormalizeClassName(): void
    {
        if (Str::startsWith(VarDump::ANON_CLASS, $this->className)) {
            $this->className = Path::normalize($this->className);
        }
    }
}
