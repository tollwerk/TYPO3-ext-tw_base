<?php

/**
 * tollwerk
 *
 * @category   Tollwerk
 * @package    Tollwerk\TwBase
 * @subpackage Tollwerk\TwBase\ViewHelpers
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

namespace Tollwerk\TwBase\ViewHelpers;

use DOMAttr;
use DOMDocument;
use OutOfBoundsException;
use Tollwerk\TwBase\Utility\SvgIconManager;
use Tollwerk\TwBase\ViewHelpers\Icon\IconViewHelperTrait;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;

/**
 * Render a single SVG icon
 *
 * @package    Tollwerk\TwBase
 * @subpackage Tollwerk\TwBase\ViewHelpers
 */
class IconViewHelper extends AbstractTagBasedViewHelper
{
    /**
     * Use shared icon viewhelper methods
     */
    use IconViewHelperTrait;
    /**
     * Icon types
     */
    const TYPE_INLINE = 'inline';
    const TYPE_OUTLINE = 'outline';
    const TYPE_OPAQUE = 'opaque';
    const TYPES = [self::TYPE_INLINE, self::TYPE_OUTLINE, self::TYPE_OPAQUE];
    /**
     * HTML tag name
     *
     * @var string
     */
    protected $tagName = 'svg';

    /**
     * Initialize arguments
     *
     * @api
     */
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerUniversalTagAttributes();
        $this->registerArgument('icon', 'string', 'Name of the icon', true);
        $this->registerArgument('type', 'string', 'Icon type (one of inline, outline or opaque)', false, 'inline');
        $this->registerArgument('theme', 'string', 'Icon theme', false, 'default');
    }

    /**
     * Render the icon
     *
     * @return string Rendered icon
     * @throws InvalidConfigurationTypeException
     * @throws Exception
     * @api
     */
    public function render(): string
    {
        try {
            $class    = empty($this->arguments['class']) ? '' : ' '.trim($this->arguments['class']);
            $theme    = strtolower(trim($this->arguments['theme']));
            $type     = strtolower(trim($this->arguments['type']));
            $type     = in_array($type, self::TYPES) ? $type : self::TYPE_INLINE;
            $icon     = ucfirst(pathinfo($this->arguments['icon'], PATHINFO_FILENAME)).'.svg';
            $iconFile = $this->getIconFile($icon);

            $this->setIconProperties($this->getIconDom($iconFile));
            $this->tag->addAttribute('class', 'Icon Icon--'.$type.' Icon--theme-'.$theme.$class);
            $this->tag->addAttribute('aria-hidden', 'true');
            $this->tag->addAttribute('focusable', 'false');
            $this->tag->forceClosingTag(true);

            return $this->tag->render();
        } catch (OutOfBoundsException $e) {
            return '<!-- Unknown SVG icon "'.$e->getMessage().'" -->';
        }
    }

    /**
     * Set the icon properties
     *
     * @param DOMDocument $iconDom Icon dom
     */
    protected function setIconProperties(DOMDocument $iconDom): void
    {
        // Copy attributes
        /** @var DOMAttr $attribute */
        foreach ($iconDom->documentElement->attributes as $attribute) {
            $this->tag->addAttribute($attribute->localName, $attribute->value);
        }

        // Copy children
        $content = '';
        foreach ($iconDom->documentElement->childNodes as $child) {
            $content .= $iconDom->saveXML($child);
        }
        $this->tag->setContent($content);
    }

    /**
     * Get the icon DOM
     *
     * @param string $iconFile Icon file path
     *
     * @return DOMDocument Icon DOM
     */
    protected function getIconDom(string $iconFile): DOMDocument
    {
        return SvgIconManager::getIcon($iconFile);
    }
}
