<?php
declare(strict_types=1);

class GeminiVision {
    public static function analyze(string $imageData, string $apiKey, string $model, int $timeout): array {
        if (empty($apiKey)) {
            return ['success' => false, 'error' => 'Gemini API key is not configured.'];
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . urlencode($apiKey);

        $prompt = "Analyze this image containing a physical object. " .
            "Identify the main object precisely. If the exact model cannot be determined with certainty, use a generic product name (e.g., 'Dell Laptop' instead of an invented specific part number). " .
            "Return valid JSON strictly matching this schema with no markdown formatting around it: " .
            "{" .
            "\"objectLabel\": \"string (short category)\"," .
            "\"productName\": \"string (likely product name)\"," .
            "\"manufacturer\": \"string (brand name or Unknown)\"," .
            "\"specification\": \"string (brief specs or features)\"," .
            "\"description\": \"string (short professional description)\"," .
            "\"boundingBox\": {\"ymin\": int (0-1000), \"xmin\": int (0-1000), \"ymax\": int (0-1000), \"xmax\": int (0-1000)}," .
            "\"confidence\": float (0.0 to 1.0)" .
            "}";

        $payload = [
            "contents" => [
                [
                    "parts" => [
                        ["text" => $prompt],
                        [
                            "inline_data" => [
                                "mime_type" => "image/jpeg",
                                "data" => base64_encode($imageData)
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            return ['success' => false, 'error' => 'Gemini network error: ' . $curlError];
        }

        if ($httpCode !== 200) {
            return ['success' => false, 'error' => "Gemini API returned status code {$httpCode}: " . substr($response, 0, 200)];
        }

        $data = json_decode($response, true);
        $textResponse = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';

        // Clean markdown code block formatting if present
        $textResponse = preg_replace('/^```json\s*|\s*```$/i', '', trim($textResponse));

        $parsedJson = json_decode($textResponse, true);
        if (!$parsedJson) {
            return ['success' => false, 'error' => 'Failed to parse JSON response from Gemini Vision.'];
        }

        return ['success' => true, 'data' => $parsedJson];
    }
}