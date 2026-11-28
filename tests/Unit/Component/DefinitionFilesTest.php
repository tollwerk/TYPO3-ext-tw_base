<?php

/**
 * Files with mails and examples of components are validated
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */

declare(strict_types=1);

namespace Tollwerk\TwBase\Tests\Unit\Component;

use LogicException;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Random\RandomException;
use Tollwerk\TwBase\Component\Component;
use Tollwerk\TwBase\Component\ComponentLocator;
use Tollwerk\TwBase\Component\Example\Examples;
use Tollwerk\TwBase\Mail\MailDefinition;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;
use UnexpectedValueException;

/**
 * Files with mails and examples of components are validated
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */
final class DefinitionFilesTest extends UnitTestCase
{
    /**
     * Folder for the files of a test
     *
     * @var string
     */
    private string $folder = '';

    /**
     * Create the folder
     *
     * @return void
     * @throws RandomException If there is no randomness
     */
    #[Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->folder = Environment::getVarPath() . '/tests/tw-base-' . bin2hex(random_bytes(4));
        mkdir($this->folder . '/Resources/Private/Components/Event/Teaser', 0o777, true);
        mkdir($this->folder . '/Resources/Public/Components/Event/Teaser', 0o777, true);
        $this->testFilesToDelete[] = $this->folder;
    }

    /**
     * Invalid files with examples
     *
     * @return iterable<string, array{string, integer}>
     */
    public static function invalidExamples(): iterable
    {
        yield 'no array' => ['"examples"', 1795776102];
        yield 'no examples' => ['["examples" => []]', 1795776103];
        $label = '"label" => "A"';
        yield 'not within what it requires' => [
            '["requires" => ["twc:event.list"], "examples" => ["a" => [' . $label . ']]]',
            1795776104,
        ];
        yield 'no label' => ['["examples" => ["a" => []]]', 1795776105];
        $example = static fn(string $more): string => '["examples" => ["a" => [' . $label . ', ' . $more . ']]]';
        yield 'slot no string' => [$example('"slots" => ["default" => 1]'), 1795776106];
        yield 'within too short' => [$example('"within" => ["twc:event.list"]'), 1795776107];
        yield 'arguments no array' => [$example('"arguments" => "x"'), 1795776108];
        yield 'requires no list' => ['["requires" => [1], "examples" => ["a" => ["label" => "A"]]]', 1795776109];
    }

    /**
     * Invalid examples are errors with a message
     *
     * @param string  $content Returned PHP expression
     * @param integer $code    Expected code
     *
     * @return void
     * @throws UnexpectedValueException If a file is invalid
     */
    #[Test]
    #[DataProvider('invalidExamples')]
    public function invalidExamplesAreErrors(string $content, int $code): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionCode($code);
        Examples::fromFile($this->file('Resources/Private/Components/Event/Teaser/Teaser.examples.php', $content));
    }

    /**
     * A missing file is an error
     *
     * @return void
     * @throws UnexpectedValueException If a file is invalid
     */
    #[Test]
    public function missingExamplesAreAnError(): void
    {
        $this->expectExceptionCode(1795776101);
        Examples::fromFile($this->folder . '/missing.php');
    }

    /**
     * Files are found by the name of their component, and a slot defaults to "default"
     *
     * @return void
     * @throws UnexpectedValueException If a file is invalid
     */
    #[Test]
    public function examplesAreFoundByComponent(): void
    {
        $file = $this->file(
            'Resources/Private/Components/Event/Teaser/Teaser.examples.php',
            '["examples" => ["a" => ["label" => "A", "within" => ["twc:event.list", "default"]]]]',
        );

        self::assertSame(
            ['twc:event.teaser' => $file],
            Examples::find($this->folder . '/Resources/Private/Components'),
        );
        self::assertSame('default', (Examples::fromFile($file)->examples['a'] ?? null)?->within?->slot);
    }

    /**
     * Invalid files with mails
     *
     * @return iterable<string, array{string, integer}>
     */
    public static function invalidMails(): iterable
    {
        $mail = '"template" => "T", "purpose" => "P", "recipient" => "R"';
        yield 'no array' => ['"mails"', 1795776301];
        yield 'no template' => ['["a" => ["purpose" => "P", "recipient" => "R"]]', 1795776302];
        yield 'no examples' => ['["a" => [' . $mail . ']]', 1795776303];
        yield 'no subject' => ['["a" => [' . $mail . ', "examples" => ["e" => ["label" => "E"]]]]', 1795776304];
    }

    /**
     * Invalid mails are errors with a message
     *
     * @param string  $content Returned PHP expression
     * @param integer $code    Expected code
     *
     * @return void
     * @throws UnexpectedValueException If a file is invalid
     */
    #[Test]
    #[DataProvider('invalidMails')]
    public function invalidMailsAreErrors(string $content, int $code): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionCode($code);
        MailDefinition::fromFile($this->file('Configuration/Mails.php', $content));
    }

    /**
     * An ES module needs an import of its package
     *
     * @return void
     * @throws LogicException If the module has no import
     */
    #[Test]
    public function moduleNeedsAnImport(): void
    {
        file_put_contents($this->folder . '/Resources/Public/Components/Event/Teaser/Teaser.js', 'export {};');
        $component = new Component(
            'Tollwerk\Components',
            'event.teaser',
            $this->folder . '/Resources/Private/Components/Event/Teaser/Teaser.fluid.html',
            'tw_events',
            $this->folder . '/',
            'Event/Teaser/Teaser',
        );

        $this->expectException(LogicException::class);
        $this->expectExceptionCode(1795776001);
        new ComponentLocator(self::createStub(PackageManager::class))->resources($component);
    }

    /**
     * An import may map its path with the files it excludes
     *
     * @return void
     * @throws LogicException If the module has no import
     */
    #[Test]
    public function importMayExcludeFiles(): void
    {
        file_put_contents($this->folder . '/Resources/Public/Components/Event/Teaser/Teaser.js', 'export {};');
        $this->file('Configuration/JavaScriptModules.php', '["imports" => ["@tollwerk/tw-events/" => ['
            . '"path" => "EXT:tw_events/Resources/Public/", "exclude" => ["EXT:tw_events/Resources/Public/Build/"]]]]');
        $component = new Component(
            'Tollwerk\Components',
            'event.teaser',
            $this->folder . '/Resources/Private/Components/Event/Teaser/Teaser.fluid.html',
            'tw_events',
            $this->folder . '/',
            'Event/Teaser/Teaser',
        );

        $resources = new ComponentLocator(self::createStub(PackageManager::class))->resources($component);

        self::assertSame('@tollwerk/tw-events/Components/Event/Teaser/Teaser.js', $resources->module);
    }

    /**
     * Write a PHP file that returns an expression
     *
     * @param string $path    Path in the folder
     * @param string $content Expression
     *
     * @return string Absolute path
     */
    private function file(string $path, string $content): string
    {
        $file = $this->folder . '/' . $path;
        if (!is_dir(\dirname($file))) {
            mkdir(\dirname($file), 0o777, true);
        }
        file_put_contents($file, "<?php\nreturn " . $content . ";\n");

        return $file;
    }
}
