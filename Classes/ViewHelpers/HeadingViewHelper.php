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

use Tollwerk\TwBase\Utility\HeadingContextManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;

/**
 * Render a heading
 */
class HeadingViewHelper extends AbstractTagBasedViewHelper
{
    /**
     * Escape the output
     *
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     * Arguments initialization
     */
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerUniversalTagAttributes();
        $this->registerArgument('level', 'int', 'Heading level', false, null);
        $this->registerArgument('type', 'string', 'Visual type', false, null);
        $this->registerArgument('content', 'string', 'Heading content', true);
        $this->registerArgument('restoreContext', 'boolean', 'Restore the heading context', false, true);
    }

    /**
     * Render the heading
     *
     * @return string Heading
     */
    public function render(): string
    {
        $level   = $this->arguments['level'];
        $type    = $this->arguments['type'];
        $content = trim($this->arguments['content']);

        /** @var HeadingContextManager $headingContextManager */
        $headingContextManager = GeneralUtility::makeInstance(HeadingContextManager::class);

        // Set up a headline context
        $headingContext = $headingContextManager->setupContext($level, $type, $content);

//        echo 'heading context: '.$headingContext->getLevel().'/'.$headingContext->getVisualType()
//          .' ('.$this->arguments['level'].'/'.$this->arguments['type'].')<br/>';

        $class = implode(' ', array_filter([
            'Heading Heading--'.$headingContext->getVisualType(),
            $headingContext->isError() ? 'Heading--semantic-error' : '',
            $headingContext->isHidden() ? 'Heading--hidden' : '',
            trim($this->arguments['class'])
        ]));

        $headingLevel = $headingContext->getLevel();
        $this->tag->setTagName(($headingLevel > 6) ? 'div' : 'h'.$headingLevel);
        $this->tag->addAttribute('class', $class);
        $this->tag->setContent($content);
        $content = parent::render().$this->renderChildren();

        // Tear down the headline context
        if ((boolean)$this->arguments['restoreContext']) {
            $headingContextManager->tearDownContext($headingContext);
        }

        // Return the Rendered result
        return $content;
    }
}
