<?php
/**
 * [mailer.php]
 * Collections - Research data packaging for the rest of us
 * Copyright (C) 2017 Intersect Australia Ltd (https://intersect.org.au)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace OCA\collections\lib;

/**
 * Class Mailer provides wrappers for sending plaintext or HTML bodied emails through the ownCloud core API.
 * @package OCA\collections\lib
 */
class Mailer {

  /**
   * Sends a plain text body email using the ownCloud Mailing interface
   * @link https://doc.owncloud.org/api/classes/OCP.Mail.IMailer.html
   *
   * @param string $to email recipient address
   * @param string $from email sender address, when null or empty uses the ownCloud "From address"
   * @param string $subject email subject
   * @param string $content plain text body content
   * @throws CollectionsException In case it was not possible to send the message. (for example if an invalid mail
   * address has been supplied.)
   */
  public function send($to, $from=null, $subject, $content) {
    \OCP\Util::writeLog('collections', __METHOD__, \OCP\Util::DEBUG);
    try {
      $mailer = \OC::$server->getMailer();
      $message = $mailer->createMessage();
      $message->setSubject($subject);
      if (isset($from) && !empty($from)) {
        $message->setFrom(array($from));
      }
      $message->setTo(array($to));
      $message->setPlainBody($content);
      $mailer->send($message);
    } catch (\Exception $e) {
      \OCP\Util::writeLog('collections', "Cannot send email {$e->getMessage()} : {$e->getTraceAsString()}", \OCP\Util::ERROR);
      throw new CollectionsException('Unable to send email at this time');
    }
  }

  /**
   * Sends a HTML body email using the ownCloud Mailing interface
   * @link https://doc.owncloud.org/api/classes/OCP.Mail.IMailer.html
   *
   * @param string $to email recipient address
   * @param string $from email sender address, when null or empty uses the ownCloud "From address"
   * @param string $subject email subject
   * @param string $content HTML body content
   * @throws CollectionsException In case it was not possible to send the message. (for example if an invalid mail
   * address has been supplied.)
   */
  public function sendHTML($to, $from=null, $subject, $content) {
    \OCP\Util::writeLog('collections', __METHOD__, \OCP\Util::DEBUG);
    try {
      $mailer = \OC::$server->getMailer();
      $message = $mailer->createMessage();
      $message->setSubject($subject);
      if (isset($from) && !empty($from)) {
        $message->setFrom(array($from));
      }
      $message->setTo(array($to));
      $message->setHtmlBody($content);
      $mailer->send($message);
    } catch (\Exception $e) {
      \OCP\Util::writeLog('collections', "Cannot send email {$e->getMessage()} : {$e->getTraceAsString()}", \OCP\Util::ERROR);
      throw new CollectionsException('Unable to send email at this time');
    }
  }
}


