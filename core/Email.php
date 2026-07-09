<?php

namespace Core;

class Email
{
    public static function send($to, $subject, $body)
    {
        if (!$socket = fsockopen(MAIL_HOST, MAIL_PORT, $errno, $errstr, 10)) {
            return false;
        }

        self::sendCommand($socket, "EHLO localhost");
        self::sendCommand($socket, "AUTH LOGIN");
        self::sendCommand($socket, base64_encode(MAIL_USER));
        self::sendCommand($socket, base64_encode(MAIL_PASS));
        self::sendCommand($socket, "MAIL FROM:<" . MAIL_USER . ">");
        self::sendCommand($socket, "RCPT TO:<{$to}>");

        $headers = implode("\r\n", [
            'MIME-Version: 1.0',
            'Content-type: text/plain; charset=UTF-8',
            'From: ' . APP_NAME . ' <' . MAIL_USER . '>',
            'Reply-To: ' . MAIL_USER,
            'Subject: ' . $subject
        ]);

        self::sendCommand($socket, "DATA");
        self::sendCommand($socket, $headers . "\r\n\r\n" . $body . "\r\n.");
        self::sendCommand($socket, "QUIT");

        fclose($socket);
        return true;
    }

    private static function sendCommand($socket, $command)
    {
        fwrite($socket, $command . "\r\n");

        $response = '';

        while ($line = fgets($socket, 515)) {
            $response .= $line;
            if (substr($line, 3, 1) === ' ') {
                break;
            }
        }

        return $response;
    }
}
