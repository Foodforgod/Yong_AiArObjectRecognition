<?php
declare(strict_types=1);

class AgnesVision {
    public static function analyze(string $imageData, string $apiKey, string $model, int $timeout): array {
        if (empty($apiKey)) {
            return ['success' => false, 'error' => 'Agnes API key is not configured.'];
        }

        // Adapter structure for fallback/alternative provider endpoint
        $url = "https://api.agnesvision.example.com/v1/analyze"; 

        $payload = [
            "model" => $model,
            "image" => base64_encode($imageData)
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || $response === false) {
            return ['success' => false, 'error' => 'Agnes Vision service currently unavailable.'];
        }

        $parsedJson = json_decode($response, true);
        if (!$parsedJson || !isset($parsedJson['productName'])) {
            return ['success' => false, 'error' => 'Invalid response from Agnes Vision.'];
        }

        return ['success' => true, 'data' => $parsedJson];
    }
}