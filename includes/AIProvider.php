<?php
declare(strict_types=1);

require_once __DIR__ . '/GeminiVision.php';
require_once __DIR__ . '/AgnesVision.php';

class AIProvider {
    public static function recognize(string $imageData, array $config): array {
        $providerSetting = $config['ai_provider'] ?? 'auto';
        $timeout = (int)($config['request_timeout'] ?? 30);

        $geminiKey = $config['gemini_api_key'] ?? '';
        $geminiModel = $config['gemini_model'] ?? 'gemini-1.5-flash';

        $agnesKey = $config['agnes_api_key'] ?? '';
        $agnesModel = $config['agnes_model'] ?? 'agnes-vision-standard';

        $result = ['success' => false, 'error' => 'No AI provider successfully processed the image.'];

        if ($providerSetting === 'gemini' || $providerSetting === 'auto') {
            if (!empty($geminiKey)) {
                $res = GeminiVision::analyze($imageData, $geminiKey, $geminiModel, $timeout);
                if ($res['success']) {
                    $res['data']['provider'] = 'Gemini';
                    return self::validateAndNormalize($res['data']);
                }
                $result['error'] = 'Gemini Error: ' . $res['error'];
            } else {
                $result['error'] = 'Gemini API key is missing.';
            }
        }

        if ($providerSetting === 'agnes' || ($providerSetting === 'auto' && !$result['success'])) {
            if (!empty($agnesKey)) {
                $res = AgnesVision::analyze($imageData, $agnesKey, $agnesModel, $timeout);
                if ($res['success']) {
                    $res['data']['provider'] = 'Agnes';
                    return self::validateAndNormalize($res['data']);
                }
                $result['error'] .= ' | Agnes Error: ' . $res['error'];
            }
        }

        return ['success' => false, 'error' => $result['error']];
    }

    private static function validateAndNormalize(array $data): array {
        $normalized = [
            'objectLabel' => htmlspecialchars(trim((string)($data['objectLabel'] ?? 'Unknown Object'))),
            'productName' => htmlspecialchars(trim((string)($data['productName'] ?? 'Unidentified Product'))),
            'manufacturer' => htmlspecialchars(trim((string)($data['manufacturer'] ?? 'Unknown Manufacturer'))),
            'specification' => htmlspecialchars(trim((string)($data['specification'] ?? 'No specifications available.'))),
            'description' => htmlspecialchars(trim((string)($data['description'] ?? 'No description provided.'))),
            'confidence' => min(1.0, max(0.0, (float)($data['confidence'] ?? 0.85))),
            'provider' => htmlspecialchars((string)($data['provider'] ?? 'AI Vision'))
        ];

        $box = $data['boundingBox'] ?? [];
        $normalized['boundingBox'] = [
            'ymin' => max(0, min(1000, (int)($box['ymin'] ?? 150))),
            'xmin' => max(0, min(1000, (int)($box['xmin'] ?? 150))),
            'ymax' => max(0, min(1000, (int)($box['ymax'] ?? 850))),
            'xmax' => max(0, min(1000, (int)($box['xmax'] ?? 850)))
        ];

        return ['success' => true, 'data' => $normalized];
    }
}