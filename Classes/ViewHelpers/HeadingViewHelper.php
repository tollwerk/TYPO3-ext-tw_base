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
        $this->registerArgument('type', 'string', 'Visual type', false, 'medium');
        $this->registerArgument('hidden', 'boolean', 'Hide heading', false, false);
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
        $type = $this->arguments['type'];

        /** @var HeadlineContextManager $headlineContextManager */
        $headlineContextManager = GeneralUtility::makeInstance(HeadlineContextManager::class);

        // Set up a headline context
        $headlineContext = $headlineContextManager->setupContext($level, $type);

        $class = implode(' ', array_filter([
            'Heading Heading--'.$headlineContext->getVisualType(),
            $headlineContext->isError() ? 'Heading--semantic-error' : '',
            trim($this->arguments['class'])
        ]));

        $headingLevel = $headlineContext->getLevel();
        $this->tag->setTagName(($headingLevel > 6) ? 'div' : 'h'.$headingLevel);
        $this->tag->addAttribute('class', $class);
        $this->tag->setContent($this->arguments['content']);
        $content = parent::render().$this->renderChildren();

        // Tear down the headline context
        $headlineContextManager->tearDownContext($headlineContext);

        // Return the Rendered result
        return $content;
    }
}
