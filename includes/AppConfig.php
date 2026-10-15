<?php
declare(strict_types=1);

class AppConfig {
    private static string $configFile = __DIR__ . '/../config/config.php';

    public static function get(): array {
        if (!file_exists(self::$configFile)) {
            $example = __DIR__ . '/../config/config.example.php';
            if (file_exists($example)) {
                copy($example, self::$configFile);
            } else {
                return [
                    'ai_provider' => 'auto',
                    'gemini_api_key' => '',
                    'gemini_model' => 'gemini-1.5-flash',
                    'agnes_api_key' => '',
                    'agnes_model' => 'agnes-vision-standard',
                    'request_timeout' => 30
                ];
            }
        }
        return include self::$configFile;
    }

    public static function save(array $newConfig): bool {
        $current = self::get();
        $merged = array_merge($current, $newConfig);
        
        // Don't overwrite API keys with empty strings if they were already set and submitted empty
        if (empty($merged['gemini_api_key']) && !empty($current['gemini_api_key'])) {
            $merged['gemini_api_key'] = $current['gemini_api_key'];
        }
        if (empty($merged['agnes_api_key']) && !empty($current['agnes_api_key'])) {
            $merged['agnes_api_key'] = $current['agnes_api_key'];
        }

        $content = "<?php\ndeclare(strict_types=1);\n\nreturn " . var_export($merged, true) . ";\n";
        return file_put_contents(self::$configFile, $content) !== false;
    }
}