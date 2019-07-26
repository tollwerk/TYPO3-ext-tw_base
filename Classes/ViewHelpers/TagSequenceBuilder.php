<?php

/**
 * tollwerk
 *
 * @category   Tollwerk
 * @package    Tollwerk\TwBase
 * @subpackage Tollwerk\TwBase\ViewHelpers
 * @author     Joschi Kuphal <joschi@tollwerk.de> / @jkphl
 * @copyright  Copyright © 2019 Joschi Kuphal <joschi@tollwerk.de> / @jkphl
 * @license    http://opensource.org/licenses/MIT The MIT License (MIT)
 */

/***********************************************************************************
 *  The MIT License (MIT)
 *
 *  Copyright © 2019 Joschi Kuphal <joschi@tollwerk.de>
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy of
 *  this software and associated documentation files (the "Software"), to deal in
 *  the Software without restriction, including without limitation the rights to
 *  use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
 *  the Software, and to permit persons to whom the Software is furnished to do so,
 *  subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
 *  FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 *  COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
 *  IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
 *  CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 ***********************************************************************************/

namespace Tollwerk\TwBase\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\TagBuilder;

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
