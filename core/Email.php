<?php

namespace Core;

class Email
{
    private $socket = null;

    public function send($content)
    {
        $this->socket = fsockopen('ssl://' . MAIL_HOST, MAIL_PORT, $errno, $errstr, 15);

        if (!$this->socket) {
            error_log("[Email] Erro de conexão SMTP: {$errno} - {$errstr}");
            return false;
        }

        $this->readResponse();

        $commands = [
            ["EHLO localhost", 250],
            ["AUTH LOGIN", 334],
            [base64_encode(MAIL_USER), 334],
            [base64_encode(MAIL_PASS), 235],
            ["MAIL FROM: <" . MAIL_USER . ">", 250],
            ["RCPT TO: <" . $content['to'] . ">", 250],
            ["DATA", 354]
        ];

        foreach ($commands as $cmd) {
            if (!$this->sendCommand($cmd[0], $cmd[1])) {
                fclose($this->socket);
                return false;
            }
        }

        $headers  = "From: " . MAIL_USER . "\r\n";
        $headers .= "To: " . $content['to'] . "\r\n";
        $headers .= "Subject: " . $content['subject'] . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n\r\n";

        if (!$this->sendCommand($headers . $content['body'] . "\r\n.", 250)) {
            fclose($this->socket);
            return false;
        }

        $this->sendCommand("QUIT", 221);
        fclose($this->socket);

        return true;
    }

    private function sendCommand($command, $expectedCode)
    {
        fputs($this->socket, $command . "\r\n");

        $response = $this->readResponse();

        if (substr($response, 0, 3) != $expectedCode) {
            error_log("Erro SMTP [$command]: Esperado $expectedCode, obteve $response");
            return false;
        }

        return true;
    }

    private function readResponse()
    {
        $response = "";

        while ($str = fgets($this->socket, 515)) {
            $response .= $str;

            if (substr($str, 3, 1) == ' ') {
                break;
            }
        }

        return $response;
    }
}
