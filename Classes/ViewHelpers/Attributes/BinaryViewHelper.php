<?php

namespace Tollwerk\TwBase\ViewHelpers\Attributes;

use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

/**
 * Render a list of binary HTML attributes to be added to an element
 */
class BinaryViewHelper extends AbstractViewHelper
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
        foreach ($arguments['attributes'] as $name => $value) {
            if (!empty($value)) {
                $attributes[] = $name;
            }
        }
        $attributes = array_unique($attributes);
        sort($attributes);
        return $arguments['returnArray'] ? $attributes : (count($attributes) ? ' ' : '').implode(' ', $attributes);
    }

    /**
     * Initialize arguments
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('attributes', 'array', 'Arbitrary number of HTML tag attributes', false, []);
        $this->registerArgument('returnArray', 'boolean',
            'Return a list of all active binary attributes instead of an HTML string', false, false);
    }
}
