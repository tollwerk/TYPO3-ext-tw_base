<?php

namespace Tollwerk\TwBase\ViewHelpers\Form;

/**
 * Extended Checkbox ViewHelper
 *
 * @package    Tollwerk\TwNdf
 * @subpackage Tollwerk\TwBase\ViewHelpers\Form
 */
class CheckboxViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Form\CheckboxViewHelper
{
    /**
     * Make the form field name unprefixable
     */
    use UnprefixedNameTrait;
}
