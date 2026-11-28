<?php

/**
 * A mail an extension sends
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

use Tollwerk\TwBase\Exception\UnexpectedValueException;

/**
 * A mail an extension sends, from its "Configuration/Mails.php"
 *
 * The file returns the mails by key:
 *
 * ```php
 * return [
 *     'tw_accounts.code' => [
 *         'template' => 'TwAccounts/Code',
 *         'purpose' => 'Confirms the email address with a code',
 *         'recipient' => 'The person who asked for the code',
 *         'examples' => [
 *             'registration' => [
 *                 'label' => 'Registration',
 *                 'subject' => '482916 is your code to register',
 *                 'variables' => ['purpose' => 'registration', 'code' => '482916'],
 *             ],
 *         ],
 *     ],
 * ];
 * ```
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */
final readonly class MailDefinition
{
    /**
     * Constructor
     *
     * @param string                     $key       Key, such as "tw_accounts.code"
     * @param string                     $template  Name of the template, such as "TwAccounts/Code"
     * @param string                     $purpose   What the mail is for
     * @param string                     $recipient Who gets it
     * @param array<string, MailExample> $examples  Examples by name
     */
    public function __construct(
        public string $key,
        public string $template,
        public string $purpose,
        public string $recipient,
        public array $examples,
    ) {}

    /**
     * Read the mails from a file
     *
     * @param string $file Absolute path of "Configuration/Mails.php"
     *
     * @return array<string, self> Mails by key
     *
     * @throws UnexpectedValueException If the file is missing or its content is invalid
     */
    public static function fromFile(string $file): array
    {
        $content = is_file($file) ? require $file : null;
        if (!\is_array($content)) {
            throw new UnexpectedValueException(\sprintf('"%s" returns no array', $file), 1795776301);
        }

        $mails = [];
        foreach ($content as $key => $mail) {
            $mails[(string) $key] = self::mail((string) $key, $mail, $file);
        }

        return $mails;
    }

    /**
     * One mail
     *
     * @param string $key  Key
     * @param mixed  $mail Configuration
     * @param string $file File, for messages
     *
     * @return self
     *
     * @throws UnexpectedValueException If the configuration is invalid
     */
    private static function mail(string $key, mixed $mail, string $file): self
    {
        $mail = \is_array($mail) ? $mail : [];
        $strings = [];
        foreach (['template', 'purpose', 'recipient'] as $field) {
            $value = $mail[$field] ?? null;
            if (!\is_string($value) || '' === $value) {
                throw new UnexpectedValueException(
                    \sprintf('The mail "%s" in "%s" needs "%s"', $key, $file, $field),
                    1795776302,
                );
            }

            $strings[$field] = $value;
        }

        $examples = [];
        foreach (\is_array($mail['examples'] ?? null) ? $mail['examples'] : [] as $name => $example) {
            $examples[(string) $name] = self::example($key, (string) $name, $example, $file);
        }

        if ([] === $examples) {
            throw new UnexpectedValueException(
                \sprintf('The mail "%s" in "%s" has no examples', $key, $file),
                1795776303,
            );
        }

        return new self($key, $strings['template'], $strings['purpose'], $strings['recipient'], $examples);
    }

    /**
     * One example
     *
     * @param string $key     Key of the mail
     * @param string $name    Name of the example
     * @param mixed  $example Configuration
     * @param string $file    File, for messages
     *
     * @return MailExample
     *
     * @throws UnexpectedValueException If the configuration is invalid
     */
    private static function example(string $key, string $name, mixed $example, string $file): MailExample
    {
        $example = \is_array($example) ? $example : [];
        $label = $example['label'] ?? null;
        $subject = $example['subject'] ?? null;
        $variables = $example['variables'] ?? [];
        if (!\is_string($label) || '' === $label || !\is_string($subject) || '' === $subject
            || !\is_array($variables)
        ) {
            throw new UnexpectedValueException(\sprintf(
                'The example "%s" of the mail "%s" in "%s" needs a label, a subject and variables that are an array',
                $name,
                $key,
                $file,
            ), 1795776304);
        }

        $named = [];
        foreach ($variables as $variable => $value) {
            $named[(string) $variable] = $value;
        }

        return new MailExample($name, $label, $subject, $named);
    }
}
