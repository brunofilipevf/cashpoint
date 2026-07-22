<?php

namespace Core;

class ZAPI
{
    public function send($content)
    {
        $payload = [
            'phone' => 55 . $content['phone'],
            'message' => $content['message']
        ];

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => ZAPI_URL . '/' . ZAPI_INSTANCE . '/token/' . ZAPI_TOKEN . '/send-text',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json']
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        $result = json_decode($response, true) ?: [];

        if ($httpCode !== 200 || isset($result['error'])) {
            error_log("[ZAPI] Erro [{$httpCode}]: " . json_encode($result));
        }

        return $result;
    }
}
