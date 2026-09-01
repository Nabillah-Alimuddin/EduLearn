<?php
namespace App\Helpers;

class StorageHelper {

    /**
     * Upload file based on STORAGE_DRIVER (.env)
     * Returns relative path (local) or full CDN URL (supabase)
     */
    public static function upload(array $fileInfo, string $folder = 'general'): ?string {
        if (!isset($fileInfo['tmp_name']) || $fileInfo['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $driver = defined('STORAGE_DRIVER') ? STORAGE_DRIVER : 'local';

        if ($driver === 'supabase') {
            return self::uploadToSupabase($fileInfo, $folder);
        } else {
            return self::uploadLocal($fileInfo, $folder);
        }
    }

    private static function uploadLocal(array $fileInfo, string $folder): ?string {
        $targetDir = "uploads/" . trim($folder, '/') . "/";
        $fullPath = __DIR__ . '/../../' . $targetDir;

        if (!is_dir($fullPath)) {
            mkdir($fullPath, 0777, true);
        }

        $ext = pathinfo($fileInfo['name'], PATHINFO_EXTENSION);
        $uniqueName = uniqid(date('Ymd_His_') . '_') . '.' . strtolower($ext);
        $relativePath = $targetDir . $uniqueName;

        if (move_uploaded_file($fileInfo['tmp_name'], __DIR__ . '/../../' . $relativePath)) {
            return $relativePath;
        }

        return null;
    }

    private static function uploadToSupabase(array $fileInfo, string $folder): ?string {
        $baseUrl = defined('SUPABASE_STORAGE_URL') ? rtrim(SUPABASE_STORAGE_URL, '/') : '';
        $apiKey = defined('SUPABASE_ANON_KEY') ? SUPABASE_ANON_KEY : '';
        $bucket = defined('SUPABASE_BUCKET') ? SUPABASE_BUCKET : 'elearning';

        if (empty($baseUrl) || empty($apiKey)) {
            self::logError("Missing SUPABASE_STORAGE_URL or SUPABASE_ANON_KEY in configuration.");
            return self::uploadLocal($fileInfo, $folder);
        }

        $filePath = $fileInfo['tmp_name'];
        if (!file_exists($filePath) || !is_readable($filePath)) {
            self::logError("Tmp file not readable: {$filePath}");
            return self::uploadLocal($fileInfo, $folder);
        }

        $ext = pathinfo($fileInfo['name'], PATHINFO_EXTENSION);
        $uniqueName = trim($folder, '/') . '/' . date('Ymd_His') . '_' . uniqid() . '.' . strtolower($ext);

        $uploadUrl = "{$baseUrl}/object/{$bucket}/{$uniqueName}";
        $fileSize = filesize($filePath);

        // Advanced Zero-RAM Streaming Upload via PHP File Pointer
        $fp = fopen($filePath, 'r');
        if (!$fp) {
            return self::uploadLocal($fileInfo, $folder);
        }

        $ch = curl_init($uploadUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_INFILE, $fp);
        curl_setopt($ch, CURLOPT_INFILESIZE, $fileSize);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer {$apiKey}",
            "apiKey: {$apiKey}",
            "Content-Type: application/octet-stream",
            "x-upsert: true"
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        fclose($fp);
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            // Return public URL from Supabase CDN
            return "{$baseUrl}/object/public/{$bucket}/{$uniqueName}";
        } else {
            self::logError("Supabase Upload Failed (HTTP {$httpCode}): {$response} | cURL error: {$curlError} | URL: {$uploadUrl}");
            // Fallback to local if Supabase upload fails
            return self::uploadLocal($fileInfo, $folder);
        }
    }

    private static function logError(string $message): void {
        $logFile = __DIR__ . '/../../logs/app_error.log';
        $logDir = dirname($logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }
        $formatted = '[' . date('Y-m-d H:i:s') . "] [SUPABASE STORAGE] " . $message . PHP_EOL;
        file_put_contents($logFile, $formatted, FILE_APPEND);
    }
}
