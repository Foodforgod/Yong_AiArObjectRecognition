<?php
declare(strict_types=1);

class ImageValidator {
    public static function validateBase64Image(string $base64Data): array {
        if (empty($base64Data)) {
            return ['valid' => false, 'error' => 'Empty image data provided.'];
        }

        if (preg_match('/^data:image\/(\w+);base64,/', $base64Data, $type)) {
            $base64Data = substr($base64Data, strpos($base64Data, ',') + 1);
            $type = strtolower($type[1]);
            
            if (!in_array($type, ['jpg', 'jpeg', 'png', 'webp'])) {
                return ['valid' => false, 'error' => 'Invalid image format. Only JPEG, PNG, and WEBP are supported.'];
            }
        } else {
            return ['valid' => false, 'error' => 'Invalid image encoding payload.'];
        }

        $decodedData = base64_decode($base64Data, true);
        if ($decodedData === false) {
            return ['valid' => false, 'error' => 'Base64 decoding failed.'];
        }

        if (strlen($decodedData) > 10 * 1024 * 1024) {
            return ['valid' => false, 'error' => 'Image size exceeds maximum limit of 10MB.'];
        }

        return ['valid' => true, 'data' => $decodedData, 'mime' => $type];
    }
}