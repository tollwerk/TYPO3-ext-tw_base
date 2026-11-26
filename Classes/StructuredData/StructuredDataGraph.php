<?php

/**
 * The JSON-LD graph of a page
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */

declare(strict_types=1);

namespace Tollwerk\TwBase\StructuredData;

use JsonException;

/**
 * The JSON-LD graph of a page
 *
 * Templates register nodes and set or add properties, also from content elements that render before the node they
 * describe; such values wait until the node is registered. Ids starting with # are relative to the page.
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */
final class StructuredDataGraph
{
    /**
     * Vocabulary of the graph
     *
     * @var string
     */
    private const string CONTEXT = 'https://schema.org';

    /**
     * Nodes by id
     *
     * @var array<string, array<string, mixed>>
     */
    private array $nodes = [];

    /**
     * Values for nodes that are not registered yet
     *
     * @var list<array{id: string, key: string|list<string>, value: mixed, add: boolean}>
     */
    private array $pending = [];

    /**
     * Ids of the main entities of parts of the page, the innermost last
     *
     * @var list<string>
     */
    private array $mainEntities = [];

    /**
     * Id of the main entity of the page, which counts outside of the parts
     *
     * @var string|null
     */
    private ?string $pageEntity = null;

    /**
     * Address of the page, the base of relative ids
     *
     * @var string
     */
    private string $baseUri = '';

    /**
     * Set the address of the page, unless set already
     *
     * @param string $baseUri Address of the page
     *
     * @return void
     */
    public function useBaseUri(string $baseUri): void
    {
        if ($this->baseUri !== '') {
            return;
        }

        $this->baseUri = explode('#', $baseUri, 2)[0];
    }

    /**
     * Address of the page
     *
     * @return string
     */
    public function baseUri(): string
    {
        return $this->baseUri;
    }

    /**
     * Absolute id; ids starting with # are relative to the page
     *
     * @param string $id Id
     *
     * @return string
     */
    public function normalizeId(string $id): string
    {
        return str_starts_with($id, '#') ? $this->baseUri . $id : $id;
    }

    /**
     * Register a node, and apply the values waiting for it
     *
     * A node registered before keeps its type and its properties, including those set since, and gains the properties
     * it lacks: describing a record twice, such as in two plugins, loses nothing.
     *
     * @param string               $type Type, such as "Event"
     * @param string               $id   Id
     * @param array<string, mixed> $data Properties
     *
     * @return void
     */
    public function register(string $type, string $id, array $data = []): void
    {
        $id = $this->normalizeId($id);
        unset($data['@type'], $data['@id']);
        $this->nodes[$id] = ($this->nodes[$id] ?? ['@type' => $type, '@id' => $id]) + $data;

        $pending = $this->pending;
        $this->pending = [];
        foreach ($pending as $value) {
            $this->assign($value['id'], $value['key'], $value['value'], $value['add']);
        }
    }

    /**
     * Set a property of a node
     *
     * A key with dots sets a nested property; an empty part appends to a list. Values for a node that is not
     * registered yet wait until it is.
     *
     * @param string              $id    Id of the node
     * @param string|list<string> $key   Key, or its parts
     * @param mixed               $value Value
     *
     * @return void
     */
    public function set(string $id, string|array $key, mixed $value): void
    {
        $this->assign($id, $key, $value, false);
    }

    /**
     * Add a value to a property of a node, which becomes a list
     *
     * @param string $id    Id of the node
     * @param string $key   Key
     * @param mixed  $value Value
     *
     * @return void
     */
    public function add(string $id, string $key, mixed $value): void
    {
        $this->assign($id, rtrim($key, '.') . '.', $value, true);
    }

    /**
     * Enter the main entity of a part of the page
     *
     * @param string $id Id of the entity
     *
     * @return void
     */
    public function pushMainEntity(string $id): void
    {
        $this->mainEntities[] = $this->normalizeId($id);
    }

    /**
     * Leave the main entity of a part of the page
     *
     * @return void
     */
    public function popMainEntity(): void
    {
        array_pop($this->mainEntities);
    }

    /**
     * Set the main entity of the page, unless set already
     *
     * Unlike a main entity of a part, it needs no end: it counts wherever no part has one.
     *
     * @param string $id Id of the entity
     *
     * @return void
     */
    public function usePageEntity(string $id): void
    {
        $this->pageEntity ??= $this->normalizeId($id);
    }

    /**
     * Id of the current main entity: that of the innermost part, or else that of the page
     *
     * @return string|null Id, or NULL outside of any entity
     */
    public function mainEntity(): ?string
    {
        return array_last($this->mainEntities) ?? $this->pageEntity;
    }

    /**
     * A node of the graph
     *
     * @param string $id Id of the node, possibly relative to the base address
     *
     * @return array<string, mixed>|null Node, or NULL if the graph has none with this id
     */
    public function get(string $id): ?array
    {
        return $this->nodes[$this->normalizeId($id)] ?? null;
    }

    /**
     * Ids of the nodes of a type, in the order of their registration
     *
     * @param string $type Type, such as "ItemList"
     *
     * @return list<string>
     */
    public function ofType(string $type): array
    {
        $ids = [];
        foreach ($this->nodes as $id => $node) {
            if (!(($node['@type'] ?? null) === $type)) {
                continue;
            }

            $ids[] = $id;
        }

        return $ids;
    }

    /**
     * Whether the graph has no nodes
     *
     * @return boolean
     */
    public function isEmpty(): bool
    {
        return $this->nodes === [];
    }

    /**
     * The graph as JSON, safe to embed in a script element
     *
     * @return string
     *
     * @throws JsonException If a value cannot be encoded
     */
    public function toJson(): string
    {
        return json_encode(
            ['@context' => self::CONTEXT, '@graph' => array_values($this->nodes)],
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_THROW_ON_ERROR,
        );
    }

    /**
     * Start over for the next page
     *
     * @return void
     */
    public function reset(): void
    {
        $this->nodes = [];
        $this->pending = [];
        $this->mainEntities = [];
        $this->pageEntity = null;
        $this->baseUri = '';
    }

    /**
     * Assign a value to a property of a node, or keep it until the node is registered
     *
     * @param string              $id    Id of the node
     * @param string|list<string> $key   Key, or its parts
     * @param mixed               $value Value
     * @param boolean             $add   Whether to turn a single value into a list
     *
     * @return void
     */
    private function assign(string $id, string|array $key, mixed $value, bool $add): void
    {
        $normalized = $this->normalizeId($id);
        if (!isset($this->nodes[$normalized])) {
            $this->pending[] = ['id' => $id, 'key' => $key, 'value' => $value, 'add' => $add];

            return;
        }

        $path = \is_array($key) ? $key : explode('.', $key);
        $property = array_shift($path);
        // A node itself is no list to append to
        if (($property === null) || ($property === '')) {
            return;
        }

        $node = $this->nodes[$normalized];
        $node[$property] = self::withValue($node[$property] ?? [], $path, $value, $add);
        $this->nodes[$normalized] = $node;
    }

    /**
     * A structure with a value at a path
     *
     * @param mixed        $target Structure
     * @param list<string> $path   Keys; an empty key appends to a list
     * @param mixed        $value  Value
     * @param boolean      $add    Whether to turn a single value into a list
     *
     * @return mixed Structure with the value
     */
    private static function withValue(mixed $target, array $path, mixed $value, bool $add): mixed
    {
        if ($path === []) {
            return $value;
        }

        $key = array_shift($path);
        if ($key === '') {
            if ($add && (!\is_array($target) || !array_is_list($target))) {
                $target = [$target];
            }

            if (!\is_array($target)) {
                return $target;
            }

            $target[] = self::withValue([], $path, $value, $add);

            return $target;
        }

        $target = \is_array($target) ? $target : [];
        $target[$key] = self::withValue($target[$key] ?? [], $path, $value, $add);

        return $target;
    }
}
