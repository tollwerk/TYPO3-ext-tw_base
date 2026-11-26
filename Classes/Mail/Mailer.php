<?php

/**
 * Mails as text and accessible HTML in the language of the site
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

use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Address;
use TYPO3\CMS\Core\Mail\FluidEmail;
use TYPO3\CMS\Core\Mail\MailerInterface;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Compose and send a mail from a template of an extension
 *
 * A template lives in a template root path of $GLOBALS['TYPO3_CONF_VARS']['MAIL'], once as ".fluid.html" and once
 * as ".fluid.txt", and uses the layout "TwBase", which gives the HTML the language, the title and a plain structure.
 * Texts in the templates come from f:translate, in the language of the request.
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */
#[Autoconfigure(public: true)]
final readonly class Mailer
{
    /**
     * Constructor
     *
     * @param MailerInterface $mailer Mailer of the core
     */
    public function __construct(private MailerInterface $mailer) {}

    /**
     * Compose a mail
     *
     * @param ServerRequestInterface $request   Request, for the site and its language
     * @param Address                $to        Recipient
     * @param string                 $template  Name of the template, such as "TwAccounts/Code"
     * @param string                 $subject   Subject, already translated
     * @param array<string, mixed>   $variables Variables of the template
     *
     * @return FluidEmail
     *
     * @throws InvalidArgumentException If the format of mails is unknown
     */
    public function compose(
        ServerRequestInterface $request,
        Address $to,
        string $template,
        string $subject,
        array $variables = [],
    ): FluidEmail {
        $language = $request->getAttribute('language');
        $mail = GeneralUtility::makeInstance(FluidEmail::class);
        $mail->setRequest($request)
            ->to($to)
            ->subject($subject)
            ->format(FluidEmail::FORMAT_BOTH)
            ->setTemplate($template)
            ->assignMultiple([
                ...$variables,
                'subject' => $subject,
                'languageCode' => $language instanceof SiteLanguage ? $language->getLocale()->getName() : 'en',
            ]);

        return $mail;
    }

    /**
     * Compose and send a mail
     *
     * @param ServerRequestInterface $request   Request, for the site and its language
     * @param Address                $to        Recipient
     * @param string                 $template  Name of the template, such as "TwAccounts/Code"
     * @param string                 $subject   Subject, already translated
     * @param array<string, mixed>   $variables Variables of the template
     *
     * @return void
     *
     * @throws TransportExceptionInterface If the mail cannot be sent
     * @throws InvalidArgumentException If the format of mails is unknown
     */
    public function send(
        ServerRequestInterface $request,
        Address $to,
        string $template,
        string $subject,
        array $variables = [],
    ): void {
        $this->mailer->send($this->compose($request, $to, $template, $subject, $variables));
    }
}
