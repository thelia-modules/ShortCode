<?php

declare(strict_types=1);

/*
 * This file is part of the Thelia ShortCode module.
 *
 * It is derived from maiorano84/shortcodes v2.0.0-beta
 * (https://github.com/maiorano84/shortcodes), Copyright (c) Matt Maiorano,
 * released under the GNU General Public License version 2 or later.
 *
 * This file is therefore distributed under the GNU General Public License
 * version 2 or later.
 */

namespace ShortCode\Parser;

/**
 * Holds the handler of every known shortcode and replaces them in a document.
 */
class ShortcodeRenderer
{
    /** @var array<string, \Closure> */
    private array $handlers = [];

    private ShortcodeParser $parser;

    public function __construct(?ShortcodeParser $parser = null)
    {
        $this->parser = $parser ?? new ShortcodeParser();
    }

    /**
     * The handler receives (?string $content, array $attributes) and returns the replacement.
     */
    public function register(string $tag, \Closure $handler): self
    {
        $this->handlers[$tag] = $handler;

        return $this;
    }

    public function getRegisteredTags(): array
    {
        return array_keys($this->handlers);
    }

    /**
     * Replaces every known shortcode found in $content. When $deep is true, the
     * result is parsed again as long as it still contains a known shortcode, so
     * that a handler may itself emit shortcodes.
     */
    public function render(string $content, bool $deep = false): string
    {
        $tags = $this->getRegisteredTags();

        if ([] === $tags) {
            return $content;
        }

        $result = $this->parser->replace(
            $content,
            $tags,
            fn (string $tag, ?string $inner, array $attributes): string => (string) ($this->handlers[$tag])($inner, $attributes)
        );

        if ($deep && [] !== $this->parser->match($result, $tags)) {
            return $this->render($result, $deep);
        }

        return $result;
    }
}
