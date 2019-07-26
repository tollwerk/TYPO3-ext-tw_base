<?php

/**
 * tollwerk
 *
 * @category   Tollwerk
 * @package    Tollwerk\TwBase
 * @subpackage Tollwerk\TwBase\Domain\Model
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

namespace Tollwerk\TwBase\Domain\Model;

use TYPO3\CMS\Form\Domain\Model\FormDefinition;
use TYPO3\CMS\Form\Domain\Model\Renderable\CompositeRenderableInterface;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;

/**
 * Form definition for unsubmittable forms
 *
 * @package    Tollwerk\TwBase
 * @subpackage Tollwerk\TwBase\Domain\Model
 */
class UnsubmittableFormDefinition extends FormDefinition
{
    /**
     * Hook after current page initialization
     *
     * This is just an example hook — don't use it directly (might have unintended side effects!) but use as a template
     * for rolling your own hook method in some custom class.
     * See https://docs.typo3.org/c/typo3/cms-form/master/en-us/I/ApiReference/Index.html#afterinitializecurrentpage for
     * documentation on the `afterInitializeCurrentPage` hook.
     *
     * @param FormRuntime $formRuntime
     * @param CompositeRenderableInterface $currentPage
     * @param null|CompositeRenderableInterface $lastPage
     * @param array $requestArguments
     *
     * @return CompositeRenderableInterface
     * @throws \TYPO3\CMS\Form\Exception
     * @see https://docs.typo3.org/c/typo3/cms-form/master/en-us/I/ApiReference/Index.html#afterinitializecurrentpage
     */
    public function afterInitializeCurrentPage(
        FormRuntime $formRuntime,
        CompositeRenderableInterface $currentPage = null,
        CompositeRenderableInterface $lastPage = null,
        array $requestArguments = []
    ): ?CompositeRenderableInterface {
        /** @var FormDefinition $formDefinition */
        $formDefinition = $formRuntime->getFormDefinition();

        return ($formDefinition instanceof UnsubmittableFormDefinition) ? $formDefinition->getPageByIndex(0) : $currentPage;
    }
}
