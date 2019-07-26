<?php

/**
 * tollwerk
 *
 * @category   Tollwerk
 * @package    Tollwerk\TwBase
 * @subpackage Tollwerk\TwBase\ViewHelpers\Form\Page
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

namespace Tollwerk\TwBase\ViewHelpers\Form\Page;

use TYPO3\CMS\Form\Domain\Model\FormElements\GenericFormElement;
use TYPO3\CMS\Form\Domain\Model\FormElements\Page;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * ElementsByIdentifierViewHelper
 *
 * Return an associative array with all elements of a form page with their
 * identifiers as array keys.
 *
 * @package    Tollwerk\TwBase
 * @subpackage Tollwerk\TwBase\ViewHelpers\Form
 */
class ElementsByIdentifierViewHelper extends AbstractViewHelper
{
    /**
     * Arguments initialization
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('page', Page::class, 'The form definition', true);
    }

    /**
     * Return a lst of form elements by identifier
     *
     * @return array List of form elements by identifier
     */
    public function render()
    {
        /**
         * @var Page $page
         * @var GenericFormElement $element
         */
        $page     = $this->arguments['page'];
        $elements = [];
        foreach ($page->getElements() as $element) {
            $elements[$element->getIdentifier()] = $element;
        }

        return $elements;
    }
}
