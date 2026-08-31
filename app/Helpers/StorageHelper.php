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
            // Fallback to local if config missing
            return self::uploadLocal($fileInfo, $folder);
        }

        $ext = pathinfo($fileInfo['name'], PATHINFO_EXTENSION);
        $uniqueName = trim($folder, '/') . '/' . uniqid(date('Ymd_His_') . '_') . '.' . strtolower($ext);

        $uploadUrl = "{$baseUrl}/object/{$bucket}/{$uniqueName}";

        $fileData = file_get_contents($fileInfo['tmp_name']);
        $mimeType = $fileInfo['type'] ?? 'application/octet-stream';

        $ch = curl_init($uploadUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fileData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer {$apiKey}",
            "apiKey: {$apiKey}",
            "Content-Type: {$mimeType}",
            "x-upsert: true"
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            // Return public URL from Supabase CDN
            return "{$baseUrl}/object/public/{$bucket}/{$uniqueName}";
        } else {
            // Fallback to local if cURL fails
            return self::uploadLocal($fileInfo, $folder);
        }
    }
}
