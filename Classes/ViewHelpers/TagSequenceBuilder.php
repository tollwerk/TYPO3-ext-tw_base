<?php

namespace Tollwerk\TwBase\ViewHelpers;

use TYPO3\CMS\Fluid\Core\ViewHelper\TagBuilder;

/**
 * Tag sequence builder
 */
class TagSequenceBuilder extends TagBuilder
{
    /**
     * Contained tags
     *
     * @var TagBuilder[]
     */
    protected $tags;

    /**
     * Constructor
     *
     * @param TagBuilder[] $tags Contained tags
     */
    public function __construct(array $tags)
    {
        $this->tags = $tags;
    }

    /**
     * Add another tag to the sequence
     *
     * @param TagBuilder $tag Tag
     */
    public function addTag(TagBuilder $tag)
    {
        $this->tags[] = $tag;
    }

    /**
     * Renders and returns the tag sequence
     *
     * @return string
     */
    public function render()
    {
        $sequence = '';
        /** @var TagBuilder $tag */
        foreach ($this->tags as $tag) {
            $sequence .= $tag->render();
        }
        return $sequence;
    }
}
