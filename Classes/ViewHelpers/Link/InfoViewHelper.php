<?php

/**
 * Returns information about the target of a typolink
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */

declare(strict_types=1);

namespace Tollwerk\TwBase\ViewHelpers\Link;

use Tollwerk\TwBase\Link\LinkInfo;
use Tollwerk\TwBase\Link\LinkInfoFactory;
use Tollwerk\TwBase\ViewHelpers\TypedArguments;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Returns information about the target of a typolink
 *
 * Such as {base:link.info(typolink: coverage.url).host} for the host a link leads to.
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */
final class InfoViewHelper extends AbstractViewHelper
{
    use TypedArguments;

    /**
     * Constructor
     *
     * @param LinkInfoFactory $linkInfoFactory Resolves typolinks
     */
    public function __construct(private readonly LinkInfoFactory $linkInfoFactory) {}

    /**
     * Register the arguments
     *
     * @return void
     */
    public function initializeArguments(): void
    {
        $this->registerArgument('typolink', 'string', 'Typolink', true);
    }

    /**
     * Return the information
     *
     * @return LinkInfo
     */
    public function render(): LinkInfo
    {
        $site = $this->request()?->getAttribute('site');

        return $this->linkInfoFactory->create(
            $this->stringArgument('typolink') ?? '',
            ($site instanceof Site) ? $site : null,
        );
    }
}
