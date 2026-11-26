<?php

/**
 * Returns the id of the current main entity
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */

declare(strict_types=1);

namespace Tollwerk\TwBase\ViewHelpers\StructuredData\EntityContext;

use Tollwerk\TwBase\ViewHelpers\StructuredData\AbstractStructuredDataViewHelper;

/**
 * Returns the id of the current main entity
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */
final class GetViewHelper extends AbstractStructuredDataViewHelper
{
    /**
     * Return the id
     *
     * @return string|null Id, or NULL outside of any entity
     */
    public function render(): ?string
    {
        return $this->graph->mainEntity();
    }
}
