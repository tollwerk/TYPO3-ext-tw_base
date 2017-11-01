<?php

namespace Tollwerk\TwBase\ViewHelpers;

use Tollwerk\TwBase\Utility\HeadlineContextManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

/**
 * Heading view helper
 */
class HeadingViewHelper extends AbstractTagBasedViewHelper
{
    /**
     * Enable static rendering
     */
    use CompileWithRenderStatic;

    /**
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     * Arguments initialization
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerUniversalTagAttributes();
        $this->registerTagAttribute('level', 'int', 'Headline level', false, null);
        $this->registerTagAttribute('type', 'string', 'Visual type', false, 'medium');
        $this->registerTagAttribute('hidden', 'boolean', 'Hide heading', false, false);
        $this->registerTagAttribute('content', 'string', 'Headline content', true);
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
        $level = $arguments['level'];
        $type = $arguments['type'];

        /** @var HeadlineContextManager $headlineContextManager */
        $headlineContextManager = GeneralUtility::makeInstance(HeadlineContextManager::class);

        // Set up a headline context
        $headlineContext = $headlineContextManager->setupContext($level, $type);

        $class = implode(' ', array_filter([
            'heading heading--'.$headlineContext->getVisualType(),
            $headlineContext->isError() ? 'heading-semantic-error' : '',
            trim($arguments['class'])
        ]));

        $headingLevel = $headlineContext->getLevel();
        $headingElement = ($headingLevel > 6) ? 'div' : 'h'.$headingLevel;
        $headingTag = '<'.$headingElement.' class="'.htmlspecialchars($class).'">';
        $headingTag .= $arguments['content'];
        $headingTag .= '</'.$headingElement.'>'.PHP_EOL;
        $content = $headingTag.(string)$renderChildrenClosure();

        // Tear down the headline context
        $headlineContextManager->tearDownContext($headlineContext);

        // Return the Rendered result
        return $content;
    }
}
