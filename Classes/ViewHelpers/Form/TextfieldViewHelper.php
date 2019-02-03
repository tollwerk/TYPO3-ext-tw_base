<?php

namespace Tollwerk\TwBase\ViewHelpers\Form;

/**
 * Extended Textfield ViewHelper
 *
 * @package    Tollwerk\TwBase
 * @subpackage Tollwerk\TwBase\ViewHelpers\Form
 */
class TextfieldViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Form\TextfieldViewHelper
{
    /**
     * Make the form field name unprefixable
     */
    use UnprefixedNameTrait;
}
