<?php

/**
 * tollwerk
 *
 * @category   Tollwerk
 * @package    Tollwerk\TwBase
 * @subpackage Tollwerk\TwBase\Utility
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

namespace Tollwerk\TwBase\Utility;

/**
 * Heading context
 */
class HeadingContext
{
    /**
     * Heading level
     *
     * @var int
     */
    protected $level;
    /**
     * Visual headline type
     *
     * @var string
     */
    protected $visualType;
    /**
     * Follow-up headline level
     *
     * @var int
     */
    protected $afterLevel;
    /**
     * Heading is hidden
     *
     * @var boolean
     */
    protected $hidden;
    /**
     * Semantic error
     *
     * @var boolean
     */
    protected $error;

    /**
     * Constructor
     *
     * @param int $level         Heading level
     * @param string $visualType Visual headline type
     * @param int $afterLevel    Previous headline level
     * @param bool $hidden       Heading is hidden
     * @param bool $error        Heading violates semantic structure
     */
    public function __construct($level, $visualType, $afterLevel, $hidden = false, $error = false)
    {
        $this->level      = $level;
        $this->visualType = $visualType;
        $this->afterLevel = $afterLevel;
        $this->hidden     = (boolean)$hidden;
        $this->error      = (boolean)$error;
    }

    /**
     * Return the headline level
     *
     * @return int Heading level
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * Return the visual headline type
     *
     * @return string Visual headline type
     */
    public function getVisualType()
    {
        return $this->visualType;
    }

    /**
     * Return the follow-up headline level
     *
     * @return int Follow-up headline level
     */
    public function getAfterLevel()
    {
        return $this->afterLevel;
    }

    /**
     * Return whether the headline is hidden
     *
     * @return bool Heading is hidden
     */
    public function isHidden()
    {
        return $this->hidden;
    }

    /**
     * Return whether the headline violates the semantic structure
     *
     * @return bool Heading error
     */
    public function isError()
    {
        return $this->error;
    }
}
