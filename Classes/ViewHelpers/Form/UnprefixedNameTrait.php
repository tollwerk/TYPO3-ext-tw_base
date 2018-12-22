<?php

namespace Tollwerk\TwBase\ViewHelpers\Form;

use TYPO3\CMS\Fluid\ViewHelpers\FormViewHelper;

/**
 * Trait for form fields without name prefix
 */
trait UnprefixedNameTrait
{
    /**
     * Initialize the arguments.
     *
     * @throws \TYPO3Fluid\Fluid\Core\ViewHelper\Exception
     * @api
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('dontPrefixName', 'boolean', 'Suppress the name prefex', false, false);
    }

    /**
     * Get the name of this form element.
     * Either returns arguments['name'], or the correct name for Object Access.
     *
     * In case property is something like bla.blubb (hierarchical), then [bla][blubb] is generated.
     *
     * @return string Name
     */
    public function getName()
    {
        $formObjectName = null;
        if ((boolean)$this->arguments['dontPrefixName']) {
            $formObjectName = $this->viewHelperVariableContainer->get(FormViewHelper::class, 'formObjectName');
            $this->viewHelperVariableContainer->addOrUpdate(FormViewHelper::class, 'formObjectName', '');
            $name = $this->getNameWithoutPrefix();
            if ($formObjectName) {
                $this->viewHelperVariableContainer->add(FormViewHelper::class, 'formObjectName', $formObjectName);
            }
        } else {
            $name = parent::getName();
        }

        return $name;
    }
}
