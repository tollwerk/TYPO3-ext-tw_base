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
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\FileReference;
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

        return ($file instanceof File || $file instanceof FileReference)
            ? Attribution::fromMetadata($file->getProperties())
            : null;
    }
}
