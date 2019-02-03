<?php

namespace Tollwerk\TwBase\ViewHelpers\Form;

use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\CMS\Form\Domain\Model\FormDefinition;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

/**
 * Return a form element by its identifier
 */
class ElementViewHelper extends AbstractViewHelper
{
    /**
     * Enable static rendering
     */
    use CompileWithRenderStatic;

    /**
     * Render
     *
     * @param array $arguments                            Arguments
     * @param \Closure $renderChildrenClosure             Children rendering closure
     * @param RenderingContextInterface $renderingContext Rendering context
     *
     * @return mixed|string Output
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        /** @var FormDefinition $formDefinition */
        $formDefinition   = $arguments['form']->getFormDefinition();
        $formIdentifier   = $formDefinition->getIdentifier();
        $elementIdentfier = $arguments['element'];
        if (strpos($elementIdentfier, $formIdentifier.'.') === 0) {
            $elementIdentfier = substr($elementIdentfier, strlen($formIdentifier) + 1);
        }

        return $formDefinition->getElementByIdentifier($elementIdentfier);
    }

    /**
     * Initialize arguments
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('form', FormRuntime::class, 'Form framework form', true);
        $this->registerArgument('element', 'string', 'Element identifier', true);
    }
}
