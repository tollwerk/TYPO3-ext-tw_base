<?php

namespace Tollwerk\TwBase\ViewHelpers;

use Tollwerk\TwBase\Utility\HeadlineContextManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;

/**
 * Heading view helper
 */
class HeadingViewHelper extends AbstractTagBasedViewHelper
{
    /**
     * Escape the output
     *
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
        $this->registerArgument('level', 'int', 'Headline level', false, null);
        $this->registerArgument('type', 'string', 'Visual type', false, 1);
        $this->registerArgument('content', 'string', 'Headline content', true);
    }

    /**
     * Render the heading
     *
     * @return string Heading
     */
    public function render()
    {
        $level = $this->arguments['level'];
        $type  = $this->arguments['type'];

        /** @var HeadlineContextManager $headingContextManager */
        $headingContextManager = GeneralUtility::makeInstance(HeadlineContextManager::class);

        // Set up a headline context
        $headingContext = $headingContextManager->setupContext($level, $type);

        $class = implode(' ', array_filter([
            'Heading Heading--'.$headingContext->getVisualType(),
            $headingContext->isError() ? 'Heading--semantic-error' : '',
            $headingContext->isHidden() ? 'Heading--hidden' : '',
            trim($this->arguments['class'])
        ]));

        $headingLevel = $headingContext->getLevel();
        $this->tag->setTagName(($headingLevel > 6) ? 'div' : 'h'.$headingLevel);
        $this->tag->addAttribute('class', $class);
        $this->tag->setContent($this->arguments['content']);
        $content = parent::render().$this->renderChildren();

        // Tear down the headline context
        $headingContextManager->tearDownContext($headingContext);

        // Return the Rendered result
        return $content;
    }
}
