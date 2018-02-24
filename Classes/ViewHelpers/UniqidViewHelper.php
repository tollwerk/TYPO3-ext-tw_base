<?php

namespace Tollwerk\TwBase\ViewHelpers;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

/**
 * Create a unique ID for a data set
 */
class UniqidViewHelper extends AbstractViewHelper
{
    /**
     * Enable static rendering
     */
    use CompileWithRenderStatic;

    /**
     * Initialize arguments
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('prefix', 'string', 'ID prefix', true);
        $this->registerArgument('data', 'mixed', 'Data to create the unique ID from', true);
        $this->registerArgument('prefer', 'string', 'Preferred ID (if not empty)', false);
    }

    /**
     * Render
     *
     * @param array $arguments Arguments
     * @param \Closure $renderChildrenClosure Children rendering closure
     * @param RenderingContextInterface $renderingContext Rendering context
     * @return mixed|string Output
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        if (!empty($arguments['prefer'])) {
            return $arguments['prefer'];
        }

        // If it's a scalar component value
        if (is_scalar($arguments['data'])) {
            return $arguments['prefix'].substr(md5($arguments['data']), 0, 10);

            // Else if it's an array
        } elseif (is_array($arguments['data'])) {
            return $arguments['prefix'].substr(md5(serialize($arguments['data'])), 0, 10);

            // Else if it's an object
        } elseif (is_object($arguments['data'])) {
            return $arguments['prefix'].substr(md5(spl_object_hash($arguments['data'])), 0, 10);

            // Else: create a random ID
        } else {
            return $arguments['prefix'].substr(md5(rand(0, time())), 0, 10);
        }
    }
}
