<?php

/**
 * tollwerk
 *
 * @category   Tollwerk
 * @package    Tollwerk\TwBase
 * @subpackage Tollwerk\TwBase\ViewHelpers\Form\Field
 * @author     Joschi Kuphal <joschi@tollwerk.de> / @jkphl
 * @copyright  Copyright © 2020 Joschi Kuphal <joschi@tollwerk.de> / @jkphl
 * @license    http://opensource.org/licenses/MIT The MIT License (MIT)
 */

/***********************************************************************************
 *  The MIT License (MIT)
 *
 *  Copyright © 2020 Joschi Kuphal <joschi@tollwerk.de>
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

namespace Tollwerk\TwBase\ViewHelpers\Form\Field;

use Tollwerk\TwBase\Domain\Validator\ValidationErrorMapper;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Error\Result;
use TYPO3\CMS\Form\Domain\Model\FormElements\GenericFormElement;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;
use TYPO3\CMS\Form\Service\TranslationService;
use TYPO3\CMS\Form\ViewHelpers\RenderRenderableViewHelper;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

/**
 * Prepare additional attributes for form fields
 *
 * @package    Tollwerk\TwBase
 * @subpackage Tollwerk\TwBase\ViewHelpers\Form\Field
 */
class AdditionalAttributesViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    /**
     * Compile a list of additional attributes for a form field
     *
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     *
     * @return mixed
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        /** @var GenericFormElement $element */
        $element = $arguments['element'];
        /** @var Result $validationResults */
        $validationResults = $arguments['validationResults'];

        $properties = $element->getProperties();
        $additionalAttributes = $properties['fluidAdditionalAttributes'] ?? [];
        $additionalAttributes['aria-errormessage'] = $element->getUniqueIdentifier().'-error';
        $ariaDescribedBy = GeneralUtility::trimExplode(' ',
            $additionalAttributes['aria-describedby'] ?? '', true);
        $ariaDescribedBy[] = $additionalAttributes['aria-errormessage'];
        $additionalAttributes['aria-describedby'] = implode(' ', $ariaDescribedBy);
        if ($element->isRequired()) {
            $additionalAttributes['required'] = 'required';
            $additionalAttributes['aria-required'] = 'true';
        }
        $additionalAttributes['aria-invalid'] = $validationResults->hasErrors() ? 'true' : 'false';

        $elementValidators = [];
        foreach ($element->getValidators() as $validatorInstance) {
            $elementValidators[get_class($validatorInstance)] = true;
        }

        if (count($elementValidators)) {
            /** @var FormRuntime $formRuntime */
            $formRuntime = $renderingContext
                ->getViewHelperVariableContainer()
                ->get(RenderRenderableViewHelper::class, 'formRuntime');

            // Run through all  element validators
            foreach (array_keys($elementValidators) as $validatorClass) {
                // Run through all potential constraints
                foreach (ValidationErrorMapper::getInverseMap($validatorClass) as $constraint => $errorCodes) {
                    // Run through all associated Extbase error codes
                    foreach ($errorCodes as $errorCode) {
                        // Try to get a translated error message
                        $errorMessage = TranslationService::getInstance()->translateFormElementError(
                            $element,
                            $errorCode,
                            [],
                            'default',
                            $formRuntime
                        );
                        // Add as constraint error message attribute
                        if (strlen($errorMessage)) {
                            $additionalAttributes['data-errormsg-'.$constraint] = $errorMessage;
                            break;
                        }
                    }
                }
            }
        }

        return $additionalAttributes;
    }

    /**
     * Initialize all arguments. You need to override this method and call
     * $this->registerArgument(...) inside this method, to register all your arguments.
     *
     * @return void
     * @api
     */
    public function initializeArguments()
    {
        $this->registerArgument('element', GenericFormElement::class, 'Form element', true);
        $this->registerArgument('validationResults', Result::class, 'Validation results', false, null);
    }
}
