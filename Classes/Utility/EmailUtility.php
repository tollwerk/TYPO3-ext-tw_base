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

use Soundasleep\Html2Text;
use Soundasleep\Html2TextException;
use Symfony\Component\Mime\Exception\RuntimeException;
use TYPO3\CMS\Core\Core\Environment;
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
     * @param array $recipients          Recipients
     * @param string $subject            Betreff
     * @param string $html               HTML content
     * @param string $plain              Plaintext content
     * @param array $cc                  CC repipients
     * @param array $bcc                 BCC recipients
     * @param array|string|null $replyTo Reply-To recipient
     * @param array $attachments         Array with attachments. Each item can either be a string or an array.
     *                                   If string, it contains the absolute paths to an existing file on the server.
     *                                   If array, the attached file will be created on-the-fly. The array must be
     *                                   formed like this: ['body' => 'This is the content of the attachment!', 'name' => 'my_attachment.txt', 'contentType' => 'text/plain']
     *
     * @return int Number of successfully sent emails
     * @throws RuntimeException If the email would be empty
     */
    public function send(
        array $recipients,
        string $subject,
        string $html = '',
        string $plain = '',
        array $cc = [],
        array $bcc = [],
        $replyTo = null,
        array $attachments = []
    ): int {
        $html  = trim($html);
        $plain = trim($plain);
        if (!strlen($html) && !strlen($plain)) {
            throw new RuntimeException('Cowardly refusing to send empty email', 1547713437);
        }

        /** @var MailMessage $mail */
        $mail = GeneralUtility::makeInstance(MailMessage::class);
        $mail->setFrom([$this->senderAddress => $this->senderName])
             ->setTo($recipients)
             ->setCc($cc)
             ->setBcc($bcc);
        if ($replyTo) {
            $mail->setReplyTo($replyTo);
        }
        $mail->setSubject($subject);

        // If there's HTML content
        if (strlen($html)) {
            // Replace images with a temporary content ID (CID), will be processed further by mail transport
            if (preg_match_all('/<img\s+.*?src=(\042|\047)(.+?)\1/', $html, $images)) {
                foreach ($images[2] as $image) {
                    $imagePath      = Environment::getPublicPath().'/'.$image;
                    $imageContentId = md5($imagePath);
                    $html           = str_replace($image, "cid:$imageContentId", $html);
                    $mail->embedFromPath($imagePath, $imageContentId);
                }
            }
            $mail->html($html);

            // If no plaintext content is given: derive from HTML
            if (!strlen($plain)) {
                try {
                    $plain = Html2Text::convert($html);
                } catch (Html2TextException $e) {
                    $plain = nl2br(strip_tags($html));
                }
            }
        }

        // Set the plaintext content
        $mail->text($plain);

        // Add attachments
        foreach($attachments as $key => $attachment) {

            // Determine the filename
            $filename = is_string($key) ? $key : null;

            // If $attachment is an array, we create the attached file on-the-fly,
            // otherwise, we assume it's a string and the absolute path to a physical file on the server
            if(is_array($attachment)) {
                $mail->attach($attachment['body'], $attachment['name'], $attachment['contentType']);
            } else {
                $mail->attachFromPath($attachment, $filename);
            }
        }

        return $mail->send();
    }
}
