<?php

/**
 * tollwerk
 *
 * @category   Tollwerk
 * @package    Tollwerk\TwTollwerk
 * @subpackage Tollwerk\TwTollwerk\Utility
 * @author     Joschi Kuphal <joschi@tollwerk.de> / @jkphl
 * @copyright  Copyright © 2019 Joschi Kuphal <joschi@tollwerk.de> / @jkphl
 * @license    http://opensource.org/licenses/MIT The MIT License (MIT)
 */

/***********************************************************************************
 *  The MIT License (MIT)
 *
 *  Copyright © 2019 Joschi Kuphal <joschi@tollwerk.de>
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy of
 *  this software and associated documentation files (the "Software"), to deal in
 *  the Software without restriction, including without limitation the rights to
 *  use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
 *  the Software, and to permit persons to whom the Software is furnished to do so,
 *  subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
 *  FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 *  COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
 *  IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
 *  CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 ***********************************************************************************/

namespace Tollwerk\TwBase\Utility;

use stdClass;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Structured Data Manager
 *
 * @package    Tollwerk\TwBase
 * @subpackage Tollwerk\TwBase\Utility
 */
class StructuredDataManager implements SingletonInterface
{
    /**
     * Context
     *
     * @var string
     */
    protected $type = 'http://schema.org';
    /**
     * Graph
     *
     * @var array
     */
    protected $graph = [];
    /**
     * Preregistered values
     *
     * @var array
     */
    protected $register = [];
    /**
     * Base URI
     *
     * @var string
     */
    protected $baseUri;
    /**
     * Registered main entities
     *
     * @var string[]
     */
    protected $mainEntity = [];
    /**
     * Add / overwrite modes
     */
    const MODE_SET = 0;
    const MODE_ADD = 1;

    /**
     * Constructor
     */
    public function __construct()
    {
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $uriBuilder    = $objectManager->get(UriBuilder::class);
        $this->baseUri = rtrim($uriBuilder->reset()->setCreateAbsoluteUri(true)->setAddQueryString(true)->build(), '/');

        // Call initialization hooks
        $params = [];
        foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['structuredData']['initialize'] as $initializeHook) {
            GeneralUtility::callUserFunction($initializeHook, $params, $this);
        }
    }

    /**
     * Register a new main entity
     *
     * @param string $id Main entity ID
     *
     * @internal
     */
    public function pushMainEntity(string $id): void
    {
        array_push($this->mainEntity, $this->normalizeId($id));
    }

    /**
     * Deregister the latest main entity
     *
     * @internal
     */
    public function popMainEntity(): ?string
    {
        return array_pop($this->mainEntity);
    }

    /**
     * Return the current main entity
     *
     * @return string|null Main entity ID
     * @internal
     */
    public function getMainEntity(): ?string
    {
        return count($this->mainEntity) ? $this->mainEntity[count($this->mainEntity) - 1] : null;
    }

    /**
     * Create and add a new structured data node to a list of nodes
     *
     * @param string $origId    Node ID
     * @param string|array $key Key
     * @param mixed $value      data
     */
    public function add(string $origId, $key, $value): void
    {
        $this->set($origId, rtrim($key, '.').'.', $value, self::MODE_ADD);
    }

    /**
     * Create a new structured data node
     *
     * @param string $origId    Node ID
     * @param string|array $key Key
     * @param mixed $value      data
     * @param int $mode         Add / overwrite mode
     */
    public function set(string $origId, $key, $value, int $mode = self::MODE_SET): void
    {
        $id = $this->normalizeId($origId);

        // If the ID doesn't exist (yet): pre-register the value
        if (empty($this->graph[$id])) {
            $this->register[] = [$origId, $key, $value, $mode];

            // Else: Add to the graph
        } else {
            $pointer  =& $this->graph[$id];
            $keyParts = is_array($key) ? $key : explode('.', $key);
//            $keyCount = count($keyParts);

            // Run through all key parts
            foreach ($keyParts as $index => $keyPart) {
                // If this is an existing key
                if (strlen($keyPart) && array_key_exists($keyPart, $pointer)) {
                    $pointer =& $pointer[$keyPart];
                    continue;
                }

                // If it's a zero-length key
                if (!strlen($keyPart)) {
                    // If the pointer is not an array already, but should be turned into one ...
                    // Or if the pointer contains a singular node only
                    if (($mode === self::MODE_ADD) && (!is_array($pointer) || array_key_exists('@type', $pointer))) {
                        $pointer = [$pointer];
                    }

                    // If the pointer is an array already, item is added
                    if (is_array($pointer)) {
                        $pointer[] = [];
                        $pointer   =& $pointer[count($pointer) - 1];
                        continue;
                    }

                    // Else: Error
                    return;
                }

                $pointer[$keyPart] = [];
                $pointer           =& $pointer[$keyPart];
            }

            $pointer = $value;
        }
    }

    /**
     * Create a new structured data node
     *
     * @param string $type Node type
     * @param string $id   Node ID
     * @param array $data  Node data
     *
     * @return array Structured data node
     */
    public function register(string $type, string $id, array $data): array
    {
        $node                      = $this->createNode($type, $id, $data);
        $this->graph[$node['@id']] = $node;

        // Run through all pre-registered values and apply if possibly
        $register       = $this->register;
        $this->register = [];
        foreach ($register as $set) {
            $this->set(...$set);
        }

        return $this->graph[$node['@id']];
    }

    /**
     * Create a new structured data node
     *
     * @param string $type Node type
     * @param string $id   Node ID
     * @param array $data  Node data
     *
     * @return array Structured data node
     */
    public function createNode(string $type, string $id, array $data): array
    {
        $node = ['@type' => $type, '@id' => $this->normalizeId($id)];
        unset($data['@type']);
        unset($data['@id']);
        foreach ($data as $key => $value) {
            $node[$key] = $value;
        }

        return $node;
    }

    /**
     * Normalize an ID
     *
     * @param string $id ID
     *
     * @return string Normalized ID
     */
    public function normalizeId(string $id): string
    {
        return strncmp('#', $id, 1) ? $id : $this->baseUri.'/'.$id;
    }

    /**
     * Return the graph
     *
     * @return stdClass Graph
     */
    public function getGraph(): stdClass
    {
        return (object)[
            '@context' => 'http://schema.org',
            '@graph'   => array_values($this->graph),
        ];
    }

    /**
     * Return the base URI
     *
     * @return string Base URI
     */
    public function getBaseUri(): string
    {
        return $this->baseUri;
    }
}
