<?php

/**
 * All mails the active extensions send
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */

declare(strict_types=1);

namespace Tollwerk\TwBase\Mail;

use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use TYPO3\CMS\Core\Package\PackageManager;
use UnexpectedValueException;

/**
 * All mails the active extensions send, from their "Configuration/Mails.php"
 *
 * The overview of all mails, their preview and their tests come from here.
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */
#[Autoconfigure(public: true)]
final class MailRegistry
{
    /**
     * Mails by key, once read
     *
     * @var array<string, MailDefinition>|null
     */
    private ?array $mails = null;

    /**
     * Constructor
     *
     * @param PackageManager $packageManager Package manager
     */
    public function __construct(private readonly PackageManager $packageManager) {}

    /**
     * All mails
     *
     * @return array<string, MailDefinition> Mails by key
     *
     * @throws UnexpectedValueException If a file is invalid or two extensions use the same key
     */
    public function all(): array
    {
        if (null === $this->mails) {
            $mails = [];
            foreach ($this->packageManager->getActivePackages() as $package) {
                $file = $package->getPackagePath() . 'Configuration/Mails.php';
                if (!is_file($file)) {
                    continue;
                }

                foreach (MailDefinition::fromFile($file) as $key => $mail) {
                    if (isset($mails[$key])) {
                        throw new UnexpectedValueException(
                            \sprintf('The mail "%s" is registered twice', $key),
                            1795776305,
                        );
                    }

                    $mails[$key] = $mail;
                }
            }

            ksort($mails);
            $this->mails = $mails;
        }

        return $this->mails;
    }

    /**
     * One mail
     *
     * @param string $key Key, such as "tw_accounts.code"
     *
     * @return MailDefinition
     *
     * @throws UnexpectedValueException If there is no such mail
     */
    public function get(string $key): MailDefinition
    {
        return $this->all()[$key] ?? throw new UnexpectedValueException(
            \sprintf('There is no mail "%s"', $key),
            1795776306,
        );
    }
}
