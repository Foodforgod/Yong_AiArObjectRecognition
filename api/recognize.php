<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/../includes/AppConfig.php';
require_once __DIR__ . '/../includes/ImageValidator.php';
require_once __DIR__ . '/../includes/AIProvider.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed.']);
    exit;
}

$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);

if (!isset($input['image'])) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'No image data submitted.']);
    exit;
}

$validation = ImageValidator::validateBase64Image($input['image']);
if (!$validation['valid']) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => $validation['error']]);
    exit;
}

$config = AppConfig::get();
$recognitionResult = AIProvider::recognize($validation['data'], $config);

if (!$recognitionResult['success']) {
    http_response_code(502);
    echo json_encode(['ok' => false, 'error' => $recognitionResult['error']]);
    exit;
}

echo json_encode([
    'ok' => true,
    'data' => $recognitionResult['data']
]);