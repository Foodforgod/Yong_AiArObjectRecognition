<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/../includes/AppConfig.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $config = AppConfig::get();
    echo json_encode([
        'ok' => true,
        'data' => [
            'ai_provider' => $config['ai_provider'],
            'gemini_model' => $config['gemini_model'],
            'agnes_model' => $config['agnes_model'],
            'request_timeout' => $config['request_timeout'],
            'gemini_configured' => !empty($config['gemini_api_key']),
            'agnes_configured' => !empty($config['agnes_api_key'])
        ]
    ]);
    exit;
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Invalid JSON input.']);
        exit;
    }

    $newSettings = [
        'ai_provider' => $input['ai_provider'] ?? 'auto',
        'gemini_model' => trim($input['gemini_model'] ?? 'gemini-1.5-flash'),
        'agnes_model' => trim($input['agnes_model'] ?? 'agnes-vision-standard'),
        'request_timeout' => (int)($input['request_timeout'] ?? 30)
    ];

    if (!empty($input['gemini_api_key'])) {
        $newSettings['gemini_api_key'] = trim($input['gemini_api_key']);
    }
    if (!empty($input['agnes_api_key'])) {
        $newSettings['agnes_api_key'] = trim($input['agnes_api_key']);
    }

    if (AppConfig::save($newSettings)) {
        echo json_encode(['ok' => true, 'message' => 'Settings saved successfully.']);
    } else {
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => 'Failed to save configuration file.']);
    }
    exit;
}

http_response_code(405);
echo json_encode(['ok' => false, 'error' => 'Method not allowed.']);