<?php

// app/Services/WablasService.php
namespace App\Services;

class WablasService
{
    public function __construct(private string $token, private string $endpoint) {}
    public function sendMessage(string $phone, string $message): array
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => rtrim($this->endpoint, '/') . '/api/send-message',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Authorization: ' . $this->token],
            CURLOPT_POSTFIELDS => ['phone' => $phone, 'message' => $message],
        ]);
        $res = curl_exec($ch);
        if ($res === false) throw new \RuntimeException(curl_error($ch));
        curl_close($ch);
        return json_decode($res, true) ?: [];
    }
}
