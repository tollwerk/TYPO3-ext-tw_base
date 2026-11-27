<?php

/**
 * The examples of a component and the components it requires
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */

declare(strict_types=1);

namespace Tollwerk\TwBase\Component\Example;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use UnexpectedValueException;

/**
 * The examples of a component and the components it requires, from "<Name>.examples.php" next to its template
 *
 * The file returns an array:
 *
 * ```php
 * return [
 *     'requires' => ['twc:event.list'],
 *     'examples' => [
 *         'horizontal' => [
 *             'label' => 'Horizontal',
 *             'group' => 'Variants',
 *             'within' => ['twc:event.list', 'default'],
 *             'arguments' => ['title' => 'Meetup #8', 'orientation' => 'horizontal'],
 *             'slots' => ['default' => '<p>Text</p>'],
 *         ],
 *     ],
 * ];
 * ```
 *
 * "within" names a component, its example and optionally the slot, "default" if there is none. A component that
 * requires another has each example within one of the other.
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */
final readonly class Examples
{
    /**
     * Constructor
     *
     * @param list<string>           $requires Components the component works in only, with their prefix
     * @param array<string, Example> $examples Examples by name
     */
    public function __construct(public array $requires = [], public array $examples = []) {}

    /**
     * Read the examples from a file
     *
     * @param string $file Absolute path of "<Name>.examples.php"
     *
     * @return self
     *
     * @throws UnexpectedValueException If the file is missing or its content is invalid
     */
    public static function fromFile(string $file): self
    {
        if (!is_file($file)) {
            throw new UnexpectedValueException(\sprintf('There is no file "%s"', $file), 1795776101);
        }

        $content = require $file;
        if (!\is_array($content)) {
            throw new UnexpectedValueException(\sprintf('"%s" returns no array', $file), 1795776102);
        }

        $requires = self::strings($content['requires'] ?? [], 'requires', $file);
        $examples = [];
        foreach (self::array($content['examples'] ?? null, 'examples', $file) as $name => $example) {
            $examples[(string) $name] = self::example((string) $name, $example, $file);
        }

        if ([] === $examples) {
            throw new UnexpectedValueException(\sprintf('"%s" has no examples', $file), 1795776103);
        }

        foreach ($examples as $example) {
            if ([] !== $requires && !\in_array($example->within?->component, $requires, true)) {
                throw new UnexpectedValueException(\sprintf(
                    'The example "%s" in "%s" is within none of the components it requires',
                    $example->name,
                    $file,
                ), 1795776104);
            }
        }

        return new self($requires, $examples);
    }

    /**
     * Find the files with examples in a folder of components, without TYPO3, for the data providers of tests
     *
     * @param string $folder Absolute path of "Resources/Private/Components"
     * @param string $prefix Prefix of the components, such as "twc"
     *
     * @return array<string, string> Absolute path of each file by the name of its component, such as
     *                               "twc:mail.button"
     *
     * @throws UnexpectedValueException If the folder does not exist
     */
    public static function find(string $folder, string $prefix = 'twc'): array
    {
        $files = [];
        $folder = rtrim($folder, '/') . '/';
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($folder, RecursiveDirectoryIterator::SKIP_DOTS),
        );
        foreach ($iterator as $file) {
            if (!$file instanceof SplFileInfo || !str_ends_with($file->getFilename(), '.examples.php')) {
                continue;
            }

            $path = explode('/', substr($file->getPath(), \strlen($folder)));
            $files[$prefix . ':' . implode('.', array_map(lcfirst(...), $path))] = $file->getPathname();
        }

        ksort($files);

        return $files;
    }

    /**
     * One example
     *
     * @param string $name    Name
     * @param mixed  $example Configuration
     * @param string $file    File, for messages
     *
     * @return Example
     *
     * @throws UnexpectedValueException If the configuration is invalid
     */
    private static function example(string $name, mixed $example, string $file): Example
    {
        $example = self::array($example, 'examples.' . $name, $file);
        $label = $example['label'] ?? null;
        $group = $example['group'] ?? null;
        if (!\is_string($label) || '' === $label || (null !== $group && !\is_string($group))) {
            throw new UnexpectedValueException(\sprintf(
                'The example "%s" in "%s" needs a label and a group that is a string, if any',
                $name,
                $file,
            ), 1795776105);
        }

        $arguments = [];
        $configured = self::array($example['arguments'] ?? [], 'examples.' . $name . '.arguments', $file);
        foreach ($configured as $argument => $value) {
            $arguments[(string) $argument] = $value;
        }

        $slots = [];
        foreach (self::array($example['slots'] ?? [], 'examples.' . $name . '.slots', $file) as $slot => $html) {
            if (!\is_string($html)) {
                throw new UnexpectedValueException(\sprintf(
                    'The slot "%s" of the example "%s" in "%s" is no string of HTML',
                    $slot,
                    $name,
                    $file,
                ), 1795776106);
            }

            $slots[(string) $slot] = $html;
        }

        $within = null;
        if (isset($example['within'])) {
            $parts = self::strings($example['within'], 'examples.' . $name . '.within', $file);
            if (\count($parts) < 2 || \count($parts) > 3) {
                throw new UnexpectedValueException(\sprintf(
                    'The example "%s" in "%s" is within a component, its example and optionally a slot',
                    $name,
                    $file,
                ), 1795776107);
            }

            $within = new Within($parts[0], $parts[1], $parts[2] ?? 'default');
        }

        return new Example(
            $name,
            $label,
            $group,
            $arguments,
            $slots,
            $within,
            self::uid($example, 'page', $name, $file),
            self::uid($example, 'language', $name, $file),
        );
    }

    /**
     * The uid of the page or the language of an example
     *
     * @param array<array-key, mixed> $example Configuration of the example
     * @param string                  $key     "page" or "language"
     * @param string                  $name    Name of the example, for messages
     * @param string                  $file    File, for messages
     *
     * @return integer|null Uid; null if the example names none
     *
     * @throws UnexpectedValueException If the value is no uid
     */
    private static function uid(array $example, string $key, string $name, string $file): ?int
    {
        $uid = $example[$key] ?? null;
        if (null !== $uid && (!\is_int($uid) || $uid < 0)) {
            throw new UnexpectedValueException(
                \sprintf('The %s of the example "%s" in "%s" is no uid', $key, $name, $file),
                1795776110,
            );
        }

        return $uid;
    }

    /**
     * Require an array
     *
     * @param mixed  $value Value
     * @param string $key   Key, for messages
     * @param string $file  File, for messages
     *
     * @return array<array-key, mixed>
     *
     * @throws UnexpectedValueException If the value is no array
     */
    private static function array(mixed $value, string $key, string $file): array
    {
        if (!\is_array($value)) {
            throw new UnexpectedValueException(\sprintf('"%s" in "%s" is no array', $key, $file), 1795776108);
        }

        return $value;
    }

    /**
     * Require a list of strings
     *
     * @param mixed  $value Value
     * @param string $key   Key, for messages
     * @param string $file  File, for messages
     *
     * @return list<string>
     *
     * @throws UnexpectedValueException If the value is no list of strings
     */
    private static function strings(mixed $value, string $key, string $file): array
    {
        $value = self::array($value, $key, $file);
        $strings = [];
        foreach ($value as $item) {
            if (!\is_string($item)) {
                break;
            }

            $strings[] = $item;
        }

        if (!array_is_list($value) || \count($strings) !== \count($value)) {
            throw new UnexpectedValueException(\sprintf('"%s" in "%s" is no list of strings', $key, $file), 1795776109);
        }

        return $strings;
    }
}
