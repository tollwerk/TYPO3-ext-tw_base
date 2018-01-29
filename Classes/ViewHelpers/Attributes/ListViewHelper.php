<?php

namespace Tollwerk\TwBase\ViewHelpers\Attributes;

use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

/**
 * Render a list of HTML attributes to be added to an element
 */
class ListViewHelper extends AbstractViewHelper
{
    /**
     * Enable static rendering
     */
    use CompileWithRenderStatic;

    /**
     * Render
     *
     * @param array $arguments Arguments
     * @param \Closure $renderChildrenClosure Children rendering closure
     * @param RenderingContextInterface $renderingContext Rendering context
     * @return mixed|string Output
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        $attributes = [];
        $returnArray = $arguments['returnArray'];
        foreach ($arguments['attributes'] as $name => $value) {
            $attributes[$name] = $returnArray ?
                (strlen(trim($value)) ? trim($value) : null) :
                self::renderAttribute($name, $value, false);
        }
        foreach ($arguments['nonEmptyAttributes'] as $name => $value) {
            $attributes[$name] = $returnArray ?
                (strlen(trim($value)) ? trim($value) : null) :
                self::renderAttribute($name, $value, true);
        }
        $attributes = array_filter($attributes);
        return $returnArray ? $attributes : ((count($attributes) ? ' ' : '').implode(' ', $attributes));
    }

    /**
     * Render a single attribute
     *
     * @param string $name Attribute name
     * @param mixed $value Attribute value
     * @param bool $skipIfEmpty Skip attribute if value is empty
     * @return null|string Attribute string
     */
    protected static function renderAttribute($name, $value, $skipIfEmpty = false)
    {
        // Return if the value is empty and empty attributes should be skipped
        if (!strlen(trim($value)) && $skipIfEmpty) {
            return null;
        }
        $attribute = htmlspecialchars(trim($name));
        if (is_string($value)) {
            $attribute .= '="'.htmlspecialchars(trim($value)).'"';
        }
        return $attribute;
    }

    /**
     * Initialize arguments
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('attributes', 'array', 'Arbitrary number of HTML tag attributes', false, []);
        $this->registerArgument(
            'nonEmptyAttributes', 'array',
            'Arbitrary number of HTML tag attributes that only get rendered if they\'re not empty', false, []
        );
        $this->registerArgument('returnArray', 'boolean', 'Return an attribute list instead of string', false, false);
    }
}
