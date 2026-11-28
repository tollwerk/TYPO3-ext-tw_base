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

use TYPO3\CMS\Core\Resource\Exception\ResourceDoesNotExistException;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface;
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
 * the language of the context and without hidden records. A file or a reference to a file of the core, as the
 * content elements of the core pass them, names the class of the core as `model`, such as
 * `['record' => 'sys_file_reference', 'uid' => 1, 'model' => FileReference::class]`.
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
     * @param ResourceFactory             $resources   Factory of the files of the core
     */
    public function __construct(
        private ClassesConfiguration $classes,
        private DataMapFactory $dataMaps,
        private PersistenceManagerInterface $persistence,
        private ResourceFactory $resources,
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
            throw new UnexpectedValueException('A record needs a table, a uid and a class, if any', 1795871878);
        }

        $object = null === $model ? null : $this->file($table, $uid, $model);
        $object ??= $this->persistence->getObjectByIdentifier($uid, $this->modelClass($table, $model));
        if (!\is_object($object)) {
            throw new UnexpectedValueException(\sprintf('There is no record %s:%d', $table, $uid), 1795871879);
        }

        return $object;
    }

    /**
     * A file or a reference to a file of the core
     *
     * @param string  $table Table
     * @param integer $uid   UID
     * @param string  $model Class the example names
     *
     * @return File|FileReference|null Null if the class is no file of the core for the table
     *
     * @throws UnexpectedValueException If there is no such file
     */
    private function file(string $table, int $uid, string $model): File|FileReference|null
    {
        try {
            return match ([$table, $model]) {
                ['sys_file', File::class] => $this->resources->getFileObject($uid),
                ['sys_file_reference', FileReference::class] => $this->resources->getFileReferenceObject($uid),
                default => null,
            };
        } catch (ResourceDoesNotExistException $exception) {
            throw new UnexpectedValueException(
                \sprintf('There is no record %s:%d', $table, $uid),
                1795871880,
                $exception,
            );
        }
    }

    /**
     * The class that maps to a table
     *
     * A named class counts if Extbase maps it to the table, also by its name alone, such as
     * Vendor\Ext\Domain\Model\Thing to tx_ext_domain_model_thing. Without a name, only the classes in the files
     * Configuration/Extbase/Persistence/Classes.php count, as nothing lists the others.
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
        if (null !== $model) {
            $maps = class_exists($model)
                && is_subclass_of($model, DomainObjectInterface::class)
                && $this->dataMaps->buildDataMap($model)->tableName === $table;

            return $maps ? $model : throw new UnexpectedValueException(
                \sprintf('The class "%s" maps not to the table "%s"', $model, $table),
                1795871881,
            );
        }

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

        if (1 !== \count($classes)) {
            throw new UnexpectedValueException(\sprintf(
                'The table "%s" maps to %d classes in Classes.php; the record needs to name its class as "model"',
                $table,
                \count($classes),
            ), 1795871882);
        }

        return $classes[0];
    }
}
