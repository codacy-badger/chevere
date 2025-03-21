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

namespace Chevere\Components\Screen;

use Chevere\Components\Screen\Formatters\SilentFormatter;
use Chevere\Components\Screen\Interfaces\FormatterInterface;
use Chevere\Components\Screen\Interfaces\ScreenInterface;
use Psr\Http\Message\StreamInterface;

/**
 * A completely silent screen.
 */
final class SilentScreen implements ScreenInterface
{
    public function __construct()
    {
        $this->formatter = new SilentFormatter;
    }

    public function traceability(): bool
    {
        return false;
    }

    public function formatter(): FormatterInterface
    {
        return $this->formatter;
    }

    public function trace(): array
    {
        return [];
    }

    public function add(string $display): ScreenInterface
    {
        return $this;
    }

    public function addNl(string $display): ScreenInterface
    {
        return $this;
    }

    public function addStream(StreamInterface $stream): ScreenInterface
    {
        return $this;
    }

    public function stream(): array
    {
        return [];
    }

    public function emit(): ScreenInterface
    {
        return $this;
    }

    /**
     * @return array StreamInterface[]
     */
    public function queue(): array
    {
        return [];
    }
}
