<?php
/**
 * tollwerk
 *
 * @category   Tollwerk
 * @package    Tollwerk\TwBase
 * @subpackage Tollwerk\TwBase\ViewHelpers
 * @author     Joschi Kuphal <joschi@tollwerk.de> / @jkphl
 * @copyright  Copyright © 2019 Klaus Fiedler <klaus@tollwerk.de>
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

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;

/**
 * Call To Action viewhelper
 *
 * @package    Tollwerk\TwBase
 * @subpackage Tollwerk\TwBase\ViewHelpers
 */
class CtaViewHelper extends AbstractTagBasedViewHelper
{
    /**
     * HTML tag name
     *
     * @var string
     */
    protected $tagName = 'a';

    // Call To action types
    const TYPE_LINK = 'link';
    const TYPE_BUTTON = 'button';
    const TYPES = [self::TYPE_LINK => 'a', self::TYPE_BUTTON => 'button'];

    // CTA styles
    const STYLE_DEFAULT = 'default';

    /**
     * Initialize arguments
     *
     * @api
     */
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('cta-type', 'string', 'Call To Action type (one of "link" or "button")', false,
            self::TYPE_LINK);
        $this->registerArgument('cta-style', 'string', 'Call To Action style (e.g. "opaque", "outline" or "inline")',
            false, self::STYLE_DEFAULT);
        $this->registerArgument('cta-invert', 'boolean', 'Invert the CTA colors for bright backgrounds', false, false);
        $this->registerArgument('cta-theme', 'string', 'Call To Action theme', false, 'default');
        $this->registerArgument('disabled', 'bool', 'Specifies that the button is disabled', false, false);
        $this->registerTagAttribute('href', 'string', 'Link URL', false);
        $this->registerTagAttribute('type', 'string', 'Button type', false);
        $this->registerTagAttribute('name', 'string', 'Button name', false);
        $this->registerTagAttribute('value', 'string', 'Button value', false);
        $this->registerTagAttribute(
            'autofocus',
            'string',
            'Specifies that a button should automatically get focus when the page loads'
        );
        $this->registerTagAttribute('form', 'string', 'Specifies one or more forms the button belongs to');
        $this->registerTagAttribute(
            'formaction',
            'string',
            'Specifies where to send the form-data when a form is submitted. Only for type="submit"'
        );
        $this->registerTagAttribute(
            'formenctype',
            'string',
            'Specifies how form-data should be encoded before sending it to a server. Only for type="submit" (e.g. "application/x-www-form-urlencoded", "multipart/form-data" or "text/plain")'
        );
        $this->registerTagAttribute(
            'formmethod',
            'string',
            'Specifies how to send the form-data (which HTTP method to use). Only for type="submit" (e.g. "get" or "post")'
        );
        $this->registerTagAttribute(
            'formnovalidate',
            'string',
            'Specifies that the form-data should not be validated on submission. Only for type="submit"'
        );
        $this->registerTagAttribute(
            'formtarget',
            'string',
            'Specifies where to display the response after submitting the form. Only for type="submit" (e.g. "_blank", "_self", "_parent", "_top", "framename")'
        );
        $this->registerUniversalTagAttributes();
    }

    /**
     * Render the Call To Action
     *
     * @return string Rendered Call To Action
     * @api
     */
    public function render(): string
    {
        $class         = empty($this->arguments['class']) ? '' : ' '.trim($this->arguments['class']);
        $theme         = strtolower(trim($this->arguments['cta-theme'])) ?: 'default';
        $type          = strtolower(trim($this->arguments['cta-type']));
        $type          = array_key_exists($type, self::TYPES) ? $type : self::TYPE_LINK;
        $style         = strtolower(trim($this->arguments['cta-style']));
        $style         = strlen($style) ? $style : self::STYLE_DEFAULT;
        $invert        = (boolean)$this->arguments['cta-invert'];
        $this->tagName = self::TYPES[$type];
        $this->tag->setTagName($this->tagName);
        $this->tag->addAttribute(
            'class',
            'CallToAction CallToAction--'.$style.' '.($invert ? 'CallToAction--inverted ' : '').'CallToAction--theme-'.$theme.$class
        );

        if ($this->arguments['disabled'] && ($type == self::TYPE_BUTTON)) {
            $this->tag->addAttribute('disabled', 'disabled');
        }

        $this->tag->setContent(sprintf('<span class="CallToAction__content">%s</span>', $this->renderChildren()));
        $this->tag->forceClosingTag(true);

        return $this->tag->render();
    }
}
