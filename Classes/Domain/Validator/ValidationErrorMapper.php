<?php

/**
 * tollwerk
 *
 * @category   Tollwerk
 * @package    Tollwerk\TwBase
 * @subpackage Tollwerk\TwBase\Domain\Validator
 * @author     Joschi Kuphal <joschi@tollwerk.de> / @jkphl
 * @copyright  Copyright © 2020 Joschi Kuphal <joschi@tollwerk.de> / @jkphl
 * @license    http://opensource.org/licenses/MIT The MIT License (MIT)
 */

/***********************************************************************************
 *  The MIT License (MIT)
 *
 *  Copyright © 2020 Joschi Kuphal <joschi@tollwerk.de>
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

namespace Tollwerk\TwBase\Domain\Validator;

use Tollwerk\TwBase\Error\Constraint;
use TYPO3\CMS\Extbase\Error\Error;
use TYPO3\CMS\Extbase\Validation\Validator\EmailAddressValidator;
use TYPO3\CMS\Extbase\Validation\Validator\NotEmptyValidator;
use TYPO3\CMS\Extbase\Validation\Validator\UrlValidator;

/**
 * Extbase validation error mapper
 *
 * @package    Tollwerk\TwBase
 * @subpackage Tollwerk\TwBase\Domain\Validator
 */
class ValidationErrorMapper
{
    /**
     * Error map (Extbase to JavaScript)
     *
     * @var string[]
     */
    const ERROR_MAP = [
        NotEmptyValidator::class => [
            1221560910 => Constraint::VALUE_MISSING,
            1221560718 => Constraint::VALUE_MISSING,
            1347992400 => Constraint::VALUE_MISSING,
            1347992453 => Constraint::VALUE_MISSING,
        ],
        EmailAddressValidator::class => [
            1221559976 => Constraint::TYPE_MISMATCH,
        ],
        UrlValidator::class => [
            1238108078 => Constraint::TYPE_MISMATCH,
        ],
    ];

    /*
     * JavaScript constraint API
     *
        badInput
        patternMismatch
        rangeOverflow
        rangeUndeflow
        stepMismatch
        tooLong
        tooShort
        valueMissing
     */

    /**
     * Return an inverse error map (JavaScript to Extbase error codes) for a particular validator
     *
     * @param string $validatorClass Validator class
     *
     * @return array[] Inverse error map (JavaScript to Extbase error codes)
     */
    public static function getInverseMap(string $validatorClass): array
    {
        $inverseMap = [];
        if (!empty(self::ERROR_MAP[$validatorClass])) {
            foreach (self::ERROR_MAP[$validatorClass] as $errorCode => $constraint) {
                $constraintCode = Constraint::CODES[$constraint];
                if (empty($inverseMap[$constraint])) {
                    $inverseMap[$constraint] = [$constraintCode];
                } else {
                    $inverseMap[$constraint][] = $constraintCode;
                }
            }
        }

        return $inverseMap;
    }

    /**
     * Map an Extbase error to a JavaScript constraint
     *
     * @param Error $error Extbase error
     *
     * @return string|null JavaScript constraint
     */
    public static function mapErrorToConstraint(Error $error): ?string
    {
        foreach (self::ERROR_MAP as $errorTypeCodes) {
            foreach ($errorTypeCodes as $errorCode => $constraint) {
                if ($error->getCode() == $errorCode) {
                    return $constraint;
                }
            }
        }

        return null;
    }
}
