<?php

/**
 * Records that examples name in place of models
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

use TYPO3\CMS\Extbase\Persistence\ClassesConfiguration;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapFactory;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;
use UnexpectedValueException;

/**
 * Records that examples name in place of models
 *
 * An argument that expects a model names a record instead, as `['record' => 'tx_twevents_event', 'uid' => 3]`, also
 * within a list. The record becomes the model of the class that Extbase maps to its table; if several classes map to
 * it, such as for "fe_users", the reference names the class as `model`. Records load like any other in Extbase, in
 * the language of the context and without hidden records.
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */
final readonly class Records
{
    /**
     * Constructor
     *
     * @param ClassesConfiguration        $classes     Classes of models and their tables
     * @param DataMapFactory              $dataMaps    Factory of the maps between classes and tables
     * @param PersistenceManagerInterface $persistence Persistence of Extbase
     */
    public function __construct(
        private ClassesConfiguration $classes,
        private DataMapFactory $dataMaps,
        private PersistenceManagerInterface $persistence,
    ) {}

    /**
     * Arguments with models in place of the records they name
     *
     * @param array<string, mixed> $arguments Arguments by name
     *
     * @return array<string, mixed>
     *
     * @throws UnexpectedValueException If a record does not exist or its table maps to no single class
     */
    public function resolve(array $arguments): array
    {
        $resolved = [];
        foreach ($arguments as $name => $value) {
            $resolved[$name] = $this->value($value);
        }

        return $resolved;
    }

    /**
     * A value with models in place of the records it names
     *
     * @param mixed $value Value
     *
     * @return mixed The model if the value names a record, an array with models in place of the records it holds,
     *               or the value itself
     *
     * @throws UnexpectedValueException If a record does not exist or its table maps to no single class
     */
    private function value(mixed $value): mixed
    {
        if (!\is_array($value)) {
            return $value;
        }

        $keys = array_keys($value);
        sort($keys);
        if (!\in_array($keys, [['record', 'uid'], ['model', 'record', 'uid']], true)) {
            return array_map($this->value(...), $value);
        }

        $table = $value['record'] ?? null;
        $uid = $value['uid'] ?? null;
        $model = $value['model'] ?? null;
        if (!\is_string($table) || !\is_int($uid) || (null !== $model && !\is_string($model))) {
            throw new UnexpectedValueException('A record needs a table, a uid and a class, if any', 1795776301);
        }

        $object = $this->persistence->getObjectByIdentifier($uid, $this->modelClass($table, $model));
        if (!\is_object($object)) {
            throw new UnexpectedValueException(\sprintf('There is no record %s:%d', $table, $uid), 1795776302);
        }

        return $object;
    }

    /**
     * The class that maps to a table
     *
     * @param string      $table Table
     * @param string|null $model Class the example names, if any
     *
     * @return class-string
     *
     * @throws UnexpectedValueException If the table maps to no single class, or not to the one named
     */
    private function modelClass(string $table, ?string $model): string
    {
        $classes = [];
        foreach (array_keys($this->classes->getConfiguration()) as $class) {
            if (!\is_string($class) || !class_exists($class)) {
                continue;
            }

            if ($this->dataMaps->buildDataMap($class)->tableName !== $table) {
                continue;
            }

            $classes[] = $class;
        }

        if (null !== $model) {
            return \in_array($model, $classes, true) ? $model : throw new UnexpectedValueException(
                \sprintf('The class "%s" maps not to the table "%s"', $model, $table),
                1795776303,
            );
        }

        if (1 !== \count($classes)) {
            throw new UnexpectedValueException(\sprintf(
                'The table "%s" maps to %d classes; the record needs to name one as "model"',
                $table,
                \count($classes),
            ), 1795776304);
        }

        return $classes[0];
    }
}
