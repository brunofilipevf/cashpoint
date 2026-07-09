<?php

namespace Core;

class Email
{
    public static function send($to, $subject, $body)
    {
        if (!$socket = fsockopen(MAIL_HOST, MAIL_PORT, $errno, $errstr, 10)) {
            error_log("[Email] Falha ao conectar em " . MAIL_HOST . ":" . MAIL_PORT . " - [$errno] $errstr");
            return false;
        }

        $response = self::sendCommand($socket, "EHLO localhost");

        if (substr($response, 0, 3) !== '250') {
            error_log("[Email] Falha no comando EHLO - Resposta: " . trim($response));
            fclose($socket);
            return false;
        }

        $response = self::sendCommand($socket, "AUTH LOGIN");

        if (substr($response, 0, 3) !== '334') {
            error_log("[Email] Falha no comando AUTH LOGIN - Resposta: " . trim($response));
            fclose($socket);
            return false;
        }

        $response = self::sendCommand($socket, base64_encode(MAIL_USER));

        if (substr($response, 0, 3) !== '334') {
            error_log("[Email] Falha na autenticação do usuário - Resposta: " . trim($response));
            fclose($socket);
            return false;
        }

        $response = self::sendCommand($socket, base64_encode(MAIL_PASS));

        if (substr($response, 0, 3) !== '235') {
            error_log("[Email] Falha na autenticação da senha - Resposta: " . trim($response));
            fclose($socket);
            return false;
        }

        $response = self::sendCommand($socket, "MAIL FROM:<" . MAIL_USER . ">");

        if (substr($response, 0, 3) !== '250') {
            error_log("[Email] Falha no comando MAIL FROM - Resposta: " . trim($response));
            fclose($socket);
            return false;
        }

        $response = self::sendCommand($socket, "RCPT TO:<{$to}>");

        if (substr($response, 0, 3) !== '250') {
            error_log("[Email] Falha no comando RCPT TO para '$to' - Resposta: " . trim($response));
            fclose($socket);
            return false;
        }

        $headers = implode("\r\n", [
            'MIME-Version: 1.0',
            'Content-type: text/plain; charset=UTF-8',
            'From: ' . APP_NAME . ' <' . MAIL_USER . '>',
            'Reply-To: ' . MAIL_USER,
            'Subject: ' . $subject
        ]);

        $response = self::sendCommand($socket, "DATA");

        if (substr($response, 0, 3) !== '354') {
            error_log("[Email] Falha no comando DATA - Resposta: " . trim($response));
            fclose($socket);
            return false;
        }

        $response = self::sendCommand($socket, $headers . "\r\n\r\n" . $body . "\r\n.");

        if (substr($response, 0, 3) !== '250') {
            error_log("[Email] Falha ao enviar o corpo da mensagem - Resposta: " . trim($response));
            fclose($socket);
            return false;
        }

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
