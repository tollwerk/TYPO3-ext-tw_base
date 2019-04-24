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

use Html2Text\Html2Text;
use Html2Text\Html2TextException;
use Swift_Image;
use Swift_SwiftException;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Email Utility
 *
 * @package    Tollwerk\TwBase
 * @subpackage Tollwerk\TwBase\Utility
 */
class EmailUtility
{
    /**
     * Sender Name
     *
     * @var string
     */
    protected $senderName;
    /**
     * Sender Address
     *
     * @var string
     */
    protected $senderAddress;

    /**
     * Constructor
     *
     * @param string $senderName
     * @param string $senderAddress
     */
    public function __construct(string $senderName, string $senderAddress)
    {
        $this->senderName    = $senderName;
        $this->senderAddress = $senderAddress;
    }

    /**
     * Send an email to one or more recipients
     *
     * @param array $recipients Recipients
     * @param string $subject   Betreff
     * @param string $html      HTML content
     * @param string $plain     Plaintext content
     *
     * @return int Number of successfully sent emails
     * @throws Swift_SwiftException If the email would be empty
     */
    public function send(array $recipients, string $subject, string $html = '', string $plain = ''): int
    {
        $html  = trim($html);
        $plain = trim($plain);
        if (!strlen($html) && !strlen($plain)) {
            throw new Swift_SwiftException('Cowardly refusing to send empty email', 1547713437);
        }

        /** @var MailMessage $mail */
        $mail = GeneralUtility::makeInstance(MailMessage::class);
        $mail->setSubject($subject)
             ->setFrom([$this->senderAddress => $this->senderName])
             ->setTo($recipients);

        // If there's HTML content
        if (strlen($html)) {
            // Embed images
            if (preg_match_all('/<img\s+.*?src=(\042|\047)(.+?)\1/', $html, $images)) {
                foreach ($images[2] as $index => $image) {
                    $cid  = $mail->embed(Swift_Image::fromPath(PATH_site.$image));
                    $html = str_replace($image, $cid, $html);
                }
            }
            $mail->setBody($html, 'text/html');

            // If no plaintext content is given: derive from HTML
            if (!strlen($plain)) {
                try {
                    $plain = Html2Text::convert($html);
                } catch (Html2TextException $e) {
                    $plain = nl2br(strip_tags($html));
                }
                $mail->addPart($plain, 'text/plain');
            }
        } else {
            $mail->setBody($plain, 'text/plain');
        }

        return $mail->send();
    }
}
