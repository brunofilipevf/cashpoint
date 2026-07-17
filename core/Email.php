<?php
namespace Core;

class Email
{
    private $socket = null;

    public function send($data)
    {
        try {
            if (!$this->connect()) {
                error_log('[Email] Falha ao conectar ao servidor SMTP');
                return false;
            }

            if (!$this->command("EHLO localhost", 250)) {
                error_log('[Email] Falha em EHLO');
                return false;
            }

            if (!$this->command("AUTH LOGIN", 334)) {
                error_log('[Email] Falha em AUTH LOGIN');
                return false;
            }

            if (!$this->command(base64_encode(MAIL_USER), 334)) {
                error_log('[Email] Falha ao enviar usuário');
                return false;
            }

            if (!$this->command(base64_encode(MAIL_PASS), 235)) {
                error_log('[Email] Falha ao autenticar (senha incorreta?)');
                return false;
            }

            if (!$this->command("MAIL FROM:<" . MAIL_USER . ">", 250)) {
                error_log('[Email] Falha em MAIL FROM');
                return false;
            }

            if (!$this->command("RCPT TO:<" . $data['to'] . ">", 250)) {
                error_log('[Email] Falha em RCPT TO - destinatário inválido');
                return false;
            }

            if (!$this->command("DATA", 354)) {
                error_log('[Email] Falha em DATA');
                return false;
            }

            $message = implode("\r\n", [
                'MIME-Version: 1.0',
                'Content-type: text/plain; charset=UTF-8',
                'From: ' . APP_NAME . ' <' . MAIL_USER . '>',
                'To: ' . $data['to'],
                'Subject: ' . $data['subject'],
                '',
                $data['body'],
                '.'
            ]);

            if (!$this->command($message, 250)) {
                error_log('[Email] Falha ao enviar mensagem');
                return false;
            }

            if (!$this->command("QUIT", 221)) {
                error_log('[Email] Falha em QUIT');
            }

            fclose($this->socket);
            return true;

        } catch (\Throwable $e) {
            error_log('[Email] Exceção: ' . $e->getMessage() . ' | ' . $e->getFile() . ':' . $e->getLine());

            if ($this->socket) {
                fclose($this->socket);
            }

            return false;
        }
    }

    private function connect()
    {
        $this->socket = fsockopen(MAIL_HOST, MAIL_PORT, $errno, $errstr, 30);

        if (!$this->socket) {
            error_log("[Email] Falha na conexão SMTP: {$errstr} ({$errno})");
            return false;
        }

        $response = fgets($this->socket, 515);

        if ($response === false) {
            error_log("[Email] Não recebeu resposta inicial do servidor");
            fclose($this->socket);
            return false;
        }

        return true;
    }

    private function command($command, $expectedCode = null)
    {
        if (!$this->socket) {
            error_log('[Email] Socket não está disponível');
            return false;
        }

        $written = fwrite($this->socket, $command . "\r\n");

        if ($written === false) {
            error_log('[Email] Erro ao escrever comando: ' . $command);
            return false;
        }

        $response = '';
        $lastLine = '';

        while (true) {
            $line = fgets($this->socket, 515);

            if ($line === false) {
                error_log('[Email] Erro ao ler resposta para: ' . $command);
                return false;
            }

            $response .= $line;
            $lastLine = $line;

            if (strlen($line) >= 4 && $line[3] !== '-') {
                break;
            }
        }

        if ($expectedCode !== null) {
            $code = (int)substr($lastLine, 0, 3);

            if ($code !== $expectedCode) {
                error_log("[Email] Código esperado {$expectedCode}, recebido {$code}: " . trim($lastLine));
                return false;
            }
        }

        return true;
    }
}
