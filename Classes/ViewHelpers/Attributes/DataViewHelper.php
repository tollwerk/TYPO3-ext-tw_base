<?php

namespace Tollwerk\TwBase\ViewHelpers\Attributes;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

/**
 * Render a list of HTML data attributes to be added to an element
 */
class DataViewHelper extends ListViewHelper
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
        $dataAttributes = self::renderAttributes($arguments['attributes'] ?? [], $arguments['nonEmptyAttributes'] ?? [], true);
        $excludeAttributes = is_array($arguments['exclude']) ?
            $arguments['exclude'] : GeneralUtility::trimExplode(',', $arguments['exclude'], true);
        if (count($excludeAttributes)) {
            $dataAttributes = array_diff_key($dataAttributes, array_flip($excludeAttributes));
        }
        $attributes = [];
        $returnArray = $arguments['returnArray'];
        foreach ($dataAttributes as $name => $value) {
            $attributes["data-$name"] = $returnArray ?
                (strlen(trim($value)) ? trim($value) : null) :
                self::renderAttribute("data-$name", $value, false);
        }
        $attributes = array_filter($attributes);
        return $returnArray ? $attributes : ((count($attributes) ? ' ' : '').implode(' ', $attributes));
    }

    /**
     * Initialize arguments
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->overrideArgument('attributes', 'array',
            'Arbitrary number of values to be rendered as HTML data attributes', false, []);
        $this->registerArgument('exclude', 'mixed',
            'List of variables to be excluded from the data attributes', false, []);
    }
}
