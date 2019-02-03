<?php

namespace Tollwerk\TwBase\ViewHelpers\Collection;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Merge view helper
 *
 * @package    Tollwerk\TwBase
 * @subpackage Tollwerk\TwFh\ViewHelpers
 */
class MergeViewHelper extends AbstractViewHelper
{
    /**
     * Initialize all arguments. You need to override this method and call
     * $this->registerArgument(...) inside this method, to register all your arguments.
     *
     * @return void
     * @api
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('a', 'mixed', 'The base argument to merge over values', true);
        $this->registerArgument('b', 'mixed', 'The second argument with values to merge over the first argument', true);
    }

    /**
     * Merge two arrays and return the result
     *
     * @return array Resulting array
     */
    public function render()
    {
        return array_replace($this->purge($this->arguments['a']), $this->purge($this->arguments['b']));
    }

    /**
     * Cast an argument to an array and purge empty values
     *
     * @param mixed $array String or array
     *
     * @return array Purged array
     */
    protected function purge($array)
    {
        return array_filter(array_map('trim', (array)$array));
    }
}
