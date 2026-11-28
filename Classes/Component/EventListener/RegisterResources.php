<?php

/**
 * Register the resources of each component the page renders
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */

declare(strict_types=1);

namespace Tollwerk\TwBase\Component\EventListener;

use LogicException;
use Tollwerk\TwBase\Component\ComponentLocator;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Page\AssetCollector;
use TYPO3\CMS\Fluid\Event\RenderComponentEvent;

/**
 * Register the resources of each component the page renders, once per page
 *
 * The asset collector keeps them in the page cache, so a cached page has them as well. The stylesheet of the page of
 * a site, such as "twc:a11yclub.page", comes before all others, since every component of the site builds on it, even
 * if the page renders its content first.
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */
final readonly class RegisterResources
{
    /**
     * Constructor
     *
     * @param ComponentLocator $locator        Locator of components
     * @param AssetCollector   $assetCollector Asset collector of the page
     */
    public function __construct(private ComponentLocator $locator, private AssetCollector $assetCollector) {}

    /**
     * Register the resources of the component
     *
     * @param RenderComponentEvent $event Event
     *
     * @return void
     *
     * @throws LogicException If the component has an ES module its package maps in no import
     */
    #[AsEventListener('tollwerk/tw-base/register-component-resources')]
    public function __invoke(RenderComponentEvent $event): void
    {
        $component = $this->locator->locate($event->getComponentCollection(), $event->getViewHelperName());
        if (!$component instanceof \Tollwerk\TwBase\Component\Component) {
            return;
        }

        $resources = $this->locator->resources($component);
        if (null !== $resources->critical) {
            $this->assetCollector->addInlineStyleSheet(
                $resources->identifier,
                $resources->critical,
                [],
                ['priority' => true],
            );
        }

        if (null !== $resources->styleSheet) {
            $this->assetCollector->addStyleSheet(
                $resources->identifier,
                $resources->styleSheet,
                [],
                $component->isPage() ? ['priority' => true] : [],
            );
        }

        if (null === $resources->module) {
            return;
        }

        $this->assetCollector->addJavaScriptModule($resources->module);
    }
}
