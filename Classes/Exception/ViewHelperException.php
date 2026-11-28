<?php

/**
 * An error of a view helper of tw_base, which Fluid and TYPO3 handle as that of any view helper
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */

declare(strict_types=1);

namespace Tollwerk\TwBase\Exception;

use Tollwerk\TwBase\Exception;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception as FluidViewHelperException;

/**
 * An error of a view helper of tw_base, which Fluid and TYPO3 handle as that of any view helper
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */
final class ViewHelperException extends FluidViewHelperException implements Exception {}
