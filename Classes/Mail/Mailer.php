<?php

/**
 * Compose and send a mail from a template of an extension
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

use Doctrine\DBAL\Exception as DBALException;
use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Address;
use Tollwerk\TwBase\Routing\PageUri;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Mail\FluidEmail;
use TYPO3\CMS\Core\Mail\MailerInterface;
use TYPO3\CMS\Core\Mail\TemplatedEmailFactory;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use UnexpectedValueException;

/**
 * Compose and send a mail from a template of an extension
 *
 * A template lives in a template root path of $GLOBALS['TYPO3_CONF_VARS']['MAIL'], once as ".fluid.html" and once
 * as ".fluid.txt", and uses the layout "TwBase", which puts the HTML into the components "twc:mail.frame" and
 * "twc:mail.footer", and writes the styles of the components into the HTML, see StyleInliner. The footer names
 * the sender, the contact address and the legal notice from the settings
 * "twBase.mail.*" of the site, and why the person gets the mail, from the section "Reason" of the template. Texts in
 * the templates come from f:translate, in the language of the request.
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
     * @param MailerInterface        $mailer                 Mailer of the core
     * @param TemplatedEmailFactory  $emails                 Factory of mails, with the paths of the site
     * @param LanguageServiceFactory $languageServiceFactory Factory of language services, for the footer
     * @param MailRegistry           $registry               Mails of all extensions, for their examples
     * @param StyleInliner           $inliner                Writes the styles of the components into the HTML
     * @param PageUri                $pageUri                Address of the legal notice, if the site shows it
     * @param LoggerInterface        $logger                 Logger, for a legal notice that cannot be linked
     */
    public function __construct(
        private MailerInterface $mailer,
        private TemplatedEmailFactory $emails,
        private LanguageServiceFactory $languageServiceFactory,
        private MailRegistry $registry,
        private StyleInliner $inliner,
        private PageUri $pageUri,
        private LoggerInterface $logger,
    ) {}

    /**
     * Compose a registered mail with one of its examples, for its preview and its tests
     *
     * @param ServerRequestInterface $request Request, for the site and its language
     * @param string                 $key     Key of the mail, such as "tw_accounts.code"
     * @param string                 $example Name of the example
     * @param Address|null           $to      Recipient, a person at example.org if none
     *
     * @return FluidEmail
     *
     * @throws UnexpectedValueException If there is no such mail or example
     * @throws InvalidArgumentException If the format of mails or the contact address is invalid
     * @throws DBALException If the page of the legal notice cannot be read
     */
    public function composeExample(
        ServerRequestInterface $request,
        string $key,
        string $example,
        ?Address $to = null,
    ): FluidEmail {
        $mail = $this->registry->get($key);
        $configuration = $mail->examples[$example] ?? throw new UnexpectedValueException(
            \sprintf('The mail "%s" has no example "%s"', $key, $example),
            1795776307,
        );

        return $this->compose(
            $request,
            $to ?? new Address('person@example.org', 'Ada Lovelace'),
            $mail->template,
            $configuration->subject,
            $configuration->variables,
        );
    }

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
     * @throws InvalidArgumentException If the format of mails or the contact address is invalid
     * @throws DBALException If the page of the legal notice cannot be read
     * @throws UnexpectedValueException If the page of the legal notice cannot be read
     */
    public function compose(
        ServerRequestInterface $request,
        Address $to,
        string $template,
        string $subject,
        array $variables = [],
    ): FluidEmail {
        $language = $request->getAttribute('language');
        $language = $language instanceof SiteLanguage ? $language : null;
        $site = $request->getAttribute('site');
        $site = $site instanceof Site ? $site : null;
        $website = $this->website($site, $language);
        $contact = $this->setting($site, 'twBase.mail.contact');
        $sender = $this->setting($site, 'twBase.mail.sender');

        // The paths of the site for mails apply, the format of the site does not: the mail always has both parts
        $mail = $this->emails->createFromRequest($request);
        $mail->to($to)
            ->subject($subject)
            ->format(FluidEmail::FORMAT_BOTH)
            ->setTemplate($template)
            ->assignMultiple([
                ...$variables,
                'subject' => $subject,
                'languageCode' => $language?->getLocale()->getName() ?? 'en',
                'website' => $website,
                'sender' => '' === $sender ? $website : $sender,
                'footerLinks' => $this->footerLinks($site, $language, $contact),
                // Translated here like the footer, as f:translate needs a request of the frontend or backend
                'roleDescription' => $this->labels($language)->sL('tw_base.messages:mail.roledescription'),
            ]);
        if ('' !== $contact) {
            $mail->replyTo(new Address($contact, $website));
        }

        $this->inliner->inline($mail, $site);

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
     * @throws InvalidArgumentException If the format of mails or the contact address is invalid
     * @throws DBALException If the page of the legal notice cannot be read
     * @throws UnexpectedValueException If the page of the legal notice cannot be read
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

    /**
     * Title of the website in the language, or of the site
     *
     * @param Site|null         $site     Site
     * @param SiteLanguage|null $language Language
     *
     * @return string
     */
    private function website(?Site $site, ?SiteLanguage $language): string
    {
        $title = $language?->getWebsiteTitle() ?? '';
        if ('' === $title && $site instanceof Site) {
            $configured = $site->getConfiguration()['websiteTitle'] ?? '';
            $title = \is_string($configured) ? $configured : '';
        }

        return $title;
    }

    /**
     * A setting of the site that is a string
     *
     * @param Site|null $site Site
     * @param string    $name Name
     *
     * @return string
     */
    private function setting(?Site $site, string $name): string
    {
        $value = $site?->getSettings()->getAllFlat()[$name] ?? '';

        return \is_string($value) ? trim($value) : '';
    }

    /**
     * Links for the footer: the contact address and the legal notice, as far as the site has them
     *
     * @param Site|null         $site     Site
     * @param SiteLanguage|null $language Language
     * @param string            $contact  Contact address
     *
     * @return list<array{label: string, href: string, address: string}>
     *
     * @throws DBALException If the page of the legal notice cannot be read
     * @throws InvalidArgumentException If the address of the legal notice cannot be built
     * @throws UnexpectedValueException If the page of the legal notice cannot be read
     */
    private function footerLinks(?Site $site, ?SiteLanguage $language, string $contact): array
    {
        $labels = $this->labels($language);
        $links = [];
        if ('' !== $contact) {
            $links[] = [
                'label' => \sprintf($labels->sL('tw_base.messages:mail.footer.contact'), $contact),
                'href' => 'mailto:' . $contact,
                'address' => '',
            ];
        }

        $imprint = $site?->getSettings()->getAllFlat()['twBase.mail.imprint'] ?? 0;
        if ($site instanceof Site && \is_int($imprint) && $imprint > 0) {
            $url = $this->pageUri->ofPage($site, $imprint, $language);
            if (!$url instanceof UriInterface) {
                // The mail still goes out, as it may carry a code that the person waits for
                $this->logger->warning(
                    'The legal notice {page} of the site {site} is not a page the site shows, so mails lack the link',
                    ['page' => $imprint, 'site' => $site->getIdentifier()],
                );
            } else {
                $links[] = [
                    'label' => $labels->sL('tw_base.messages:mail.footer.imprint'),
                    'href' => (string) $url,
                    'address' => (string) $url,
                ];
            }
        }

        return $links;
    }

    /**
     * Labels in the language of the mail
     *
     * @param SiteLanguage|null $language Language
     *
     * @return LanguageService
     */
    private function labels(?SiteLanguage $language): LanguageService
    {
        return $language instanceof SiteLanguage
            ? $this->languageServiceFactory->createFromSiteLanguage($language)
            : $this->languageServiceFactory->create('en');
    }
}
