<?php

/**
 * Base of the ViewHelpers that build the JSON-LD graph
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */

declare(strict_types=1);

namespace Tollwerk\TwBase\ViewHelpers\StructuredData;

use Tollwerk\TwBase\StructuredData\StructuredDataGraph;
use Tollwerk\TwBase\ViewHelpers\TypedArguments;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Base of the ViewHelpers that build the JSON-LD graph
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */
abstract class AbstractStructuredDataViewHelper extends AbstractViewHelper
{
    use TypedArguments;

    /**
     * Constructor
     *
     * @param StructuredDataGraph $graph JSON-LD graph of the page
     */
    public function __construct(protected readonly StructuredDataGraph $graph) {}

    /**
     * Make ids relative to the page that renders
     *
     * @return StructuredDataGraph
     */
    protected function graph(): StructuredDataGraph
    {
        $request = $this->request();
        if ($request instanceof \Psr\Http\Message\ServerRequestInterface) {
            $this->graph->useBaseUri((string) $request->getUri());
        }

        return $this->graph;
    }
}
