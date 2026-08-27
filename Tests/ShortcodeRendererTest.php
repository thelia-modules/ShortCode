<?php

declare(strict_types=1);

/*
 * This file is part of the Thelia ShortCode module.
 *
 * (c) OpenStudio <info@thelia.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ShortCode\Tests;

use PHPUnit\Framework\TestCase;
use ShortCode\Parser\ShortcodeRenderer;

/**
 * The parser has no Thelia dependency, so these tests need neither a kernel nor a database.
 */
class ShortcodeRendererTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        spl_autoload_register(static function (string $class): void {
            if (!str_starts_with($class, 'ShortCode\\Parser\\')) {
                return;
            }

            $path = \dirname(__DIR__).'/Parser/'.substr($class, \strlen('ShortCode\\Parser\\')).'.php';

            if (is_file($path)) {
                require_once $path;
            }
        });
    }

    private function renderer(): ShortcodeRenderer
    {
        return (new ShortcodeRenderer())
            ->register('product', static fn (?string $content, array $attributes): string => sprintf(
                'P(%s|%s)',
                json_encode($attributes),
                $content ?? 'NULL'
            ))
            ->register('category-link', static fn (?string $content, array $attributes): string => 'C');
    }

    public function testLeavesContentWithoutShortcodeUntouched(): void
    {
        self::assertSame('<p>Hello world</p>', $this->renderer()->render('<p>Hello world</p>'));
    }

    public function testLeavesUnknownShortcodeUntouched(): void
    {
        self::assertSame('[unknown id=1]', $this->renderer()->render('[unknown id=1]'));
    }

    public function testRendersShortcodeWithoutAttributeOrContent(): void
    {
        self::assertSame('P([]|)', $this->renderer()->render('[product]'));
    }

    public function testRendersSelfClosingShortcode(): void
    {
        self::assertSame('P([]|NULL)', $this->renderer()->render('[product /]'));
    }

    public function testRendersEnclosedContent(): void
    {
        self::assertSame('P([]|Chair)', $this->renderer()->render('[product]Chair[/product]'));
    }

    public function testParsesEveryAttributeSyntax(): void
    {
        self::assertSame(
            'P({"id":"1","name":"Big Chair","ref":"XY","data-slot":"top","promo":true}|)',
            $this->renderer()->render('[product id=1 name="Big Chair" ref=\'XY\' data-slot=top promo]')
        );
    }

    public function testLowercasesAttributeNames(): void
    {
        self::assertSame('P({"id":"1"}|)', $this->renderer()->render('[product ID=1]'));
    }

    public function testRendersInsideSurroundingMarkup(): void
    {
        self::assertSame(
            '<div>P({"id":"1"}|)</div><span>C</span>',
            $this->renderer()->render('<div>[product id=1]</div><span>[category-link]</span>')
        );
    }

    public function testUnescapesDoubledBrackets(): void
    {
        self::assertSame('[product id=1]', $this->renderer()->render('[[product id=1]]'));
    }

    public function testDeepRenderingResolvesShortcodesEmittedByAHandler(): void
    {
        $renderer = (new ShortcodeRenderer())
            ->register('outer', static fn (): string => '[inner]')
            ->register('inner', static fn (): string => 'done');

        self::assertSame('[inner]', $renderer->render('[outer]'));
        self::assertSame('done', $renderer->render('[outer]', true));
    }

    public function testRendersNothingWhenNoShortcodeIsRegistered(): void
    {
        $content = 'a [product id=1] and a lone [ bracket';

        self::assertSame($content, (new ShortcodeRenderer())->render($content, true));
    }
}
