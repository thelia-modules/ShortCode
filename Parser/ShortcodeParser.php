<?php

declare(strict_types=1);

/*
 * This file is part of the Thelia ShortCode module.
 *
 * It is derived from maiorano84/shortcodes v2.0.0-beta
 * (https://github.com/maiorano84/shortcodes), Copyright (c) Matt Maiorano,
 * released under the GNU General Public License version 2 or later.
 *
 * The regular expressions are themselves derived from WordPress, released
 * under the GNU General Public License version 2 or later.
 *
 * This file is therefore distributed under the GNU General Public License
 * version 2 or later.
 */

namespace ShortCode\Parser;

/**
 * Matches WordPress-style shortcodes such as [tag attr="value"]content[/tag]
 * and hands each occurrence over to a callback.
 */
class ShortcodeParser
{
    /**
     * Replaces every occurrence of the given tags using $callback,
     * which receives (string $tag, ?string $content, array $attributes)
     * and returns the replacement string.
     */
    public function replace(string $content, array $tags, \Closure $callback): string
    {
        if ([] === $tags) {
            return $content;
        }

        $regex = $this->getRegex($tags);

        $replaced = preg_replace_callback("/$regex/", function (array $match) use ($callback): string {
            // [[tag]] escapes the shortcode: strip one bracket on each side and leave it alone.
            if ('[' === $match[1] && ']' === $match[6]) {
                return substr($match[0], 1, -1);
            }

            $inner = $match[5] ?? null;
            $attributes = isset($match[3]) ? $this->parseAttributes($match[3]) : [];

            return (string) $callback($match[2], $inner, $attributes);
        }, $content);

        return null === $replaced ? $content : $replaced;
    }

    /**
     * Returns one entry per non-escaped occurrence of the given tags,
     * each as ['tag' => string, 'content' => ?string, 'attributes' => array].
     */
    public function match(string $content, array $tags): array
    {
        if ([] === $tags || !str_contains($content, '[')) {
            return [];
        }

        preg_match_all('/'.$this->getRegex($tags).'/', $content, $matches, \PREG_SET_ORDER);

        $results = [];

        foreach ($matches as $match) {
            if ('[' === $match[1] && ']' === $match[6]) {
                continue;
            }

            $results[] = [
                'tag' => $match[2],
                'content' => $match[5] ?? null,
                'attributes' => isset($match[3]) ? $this->parseAttributes($match[3]) : [],
            ];
        }

        return $results;
    }

    /**
     * Turns the raw text of an opening tag into an attribute map.
     * A valueless attribute is reported as true.
     *
     * @see https://core.trac.wordpress.org/browser/tags/4.9/src/wp-includes/shortcodes.php#L482
     */
    public function parseAttributes(string $text): array
    {
        $pattern = '/'.implode('|', [
            '([\w-]+)\s*=\s*"([^"]*)"(?:\s|$)',     // attribute="value"
            '([\w-]+)\s*=\s*\'([^\']*)\'(?:\s|$)',  // attribute='value'
            '([\w-]+)\s*=\s*([^\s\'"]+)(?:\s|$)',   // attribute=value
            '"([^"]*)"(?:\s|$)',                    // "attribute"
            '\'([^\']*)\'(?:\s|$)',                 // 'attribute'
            '(\S+)(?:\s|$)',                        // attribute
        ]).'/';

        $text = (string) preg_replace("/[\x{00a0}\x{200b}]+/u", ' ', $text);

        if (!preg_match_all($pattern, $text, $matches, \PREG_SET_ORDER)) {
            return [];
        }

        $attributes = [];

        foreach ($matches as $match) {
            $groups = array_filter($match);
            $name = $groups[1] ?? $groups[3] ?? $groups[5] ?? $groups[7] ?? $groups[8] ?? $groups[9] ?? '';
            $quoted = $groups[2] ?? $groups[4] ?? $groups[6] ?? false;
            $value = false !== $quoted ? stripcslashes($quoted) : true;

            // Reject a value that opens an HTML element it does not close.
            if (true !== $value && str_contains($value, '<') && 1 !== preg_match('/^[^<]*+(?:<[^>]*+>[^<]*+)*+$/', $value)) {
                $value = '';
            }

            $attributes[strtolower($name)] = $value;
        }

        return $attributes;
    }

    /**
     * @see https://core.trac.wordpress.org/browser/tags/4.9/src/wp-includes/shortcodes.php#L228
     */
    private function getRegex(array $tags): string
    {
        $tagRegexp = implode('|', array_map('preg_quote', $tags));

        return
            '\\['                // Opening bracket
            .'(\\[?)'            // 1: Optional second opening bracket for escaping shortcodes: [[tag]]
            ."($tagRegexp)"      // 2: Shortcode name
            .'(?![\\w-])'        // Not followed by word character or hyphen
            .'('                 // 3: Unroll the loop: Inside the opening shortcode tag
            .'[^\\]\\/]*'        // Not a closing bracket or forward slash
            .'(?:'
            .'\\/(?!\\])'        // A forward slash not followed by a closing bracket
            .'[^\\]\\/]*'        // Not a closing bracket or forward slash
            .')*?'
            .')'
            .'(?:'
            .'(\\/)'             // 4: Self closing tag ...
            .'\\]'               // ... and closing bracket
            .'|'
            .'\\]'               // Closing bracket
            .'(?:'
            .'('                 // 5: Unroll the loop: Optionally, anything between the opening and closing shortcode tags
            .'[^\\[]*+'          // Not an opening bracket
            .'(?:'
            .'\\[(?!\\/\\2\\])'  // An opening bracket not followed by the closing shortcode tag
            .'[^\\[]*+'          // Not an opening bracket
            .')*+'
            .')'
            .'\\[\\/\\2\\]'      // Closing shortcode tag
            .')?'
            .')'
            .'(\\]?)';           // 6: Optional second closing bracket for escaping shortcodes: [[tag]]
    }
}
