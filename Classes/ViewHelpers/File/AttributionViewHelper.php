<?php

/**
 * Returns who made a file and under which licence it may be used
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */

declare(strict_types=1);

namespace Tollwerk\TwBase\ViewHelpers\File;

use Tollwerk\TwBase\FileMetadata\Attribution;
use Tollwerk\TwBase\ViewHelpers\TypedArguments;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Extbase\Domain\Model\FileReference as ExtbaseFileReference;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Returns who made a file and under which licence it may be used
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */
final class AttributionViewHelper extends AbstractViewHelper
{
    use TypedArguments;

    /**
     * Constructor
     *
     * @param LanguageServiceFactory $languageServiceFactory Factory of language services, for the name of a licence
     */
    public function __construct(private readonly LanguageServiceFactory $languageServiceFactory) {}

    /**
     * Register the arguments
     *
     * @return void
     */
    public function initializeArguments(): void
    {
        $this->registerArgument('file', FileInterface::class . '|' . ExtbaseFileReference::class, 'File', true);
    }

    /**
     * Return the attribution
     *
     * @return Attribution|null Attribution, or NULL if the file names neither creator nor licence
     */
    public function render(): ?Attribution
    {
        $file = $this->arguments['file'] ?? null;
        if ($file instanceof ExtbaseFileReference) {
            $file = $file->getOriginalResource();
        }

        if (!$file instanceof File && !$file instanceof FileReference) {
            return null;
        }

        $labels = $this->labels();

        return Attribution::fromMetadata($file->getProperties(), $labels->sL(...));
    }

    /**
     * Labels in the language of the page
     *
     * @return LanguageService
     */
    private function labels(): LanguageService
    {
        $language = $this->request()?->getAttribute('language');

        return $language instanceof SiteLanguage
            ? $this->languageServiceFactory->createFromSiteLanguage($language)
            : $this->languageServiceFactory->create('en');
    }
}
