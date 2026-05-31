<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIService
{
    private string $baseUrl;
    private string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.ai.url');
        $this->apiKey  = config('services.ai.key');
    }

    // ─────────────────────────────
    // ENROLL FACE
    // ─────────────────────────────
    public function enrollFace(string $filePath, string $studentCode): bool
    {
        try {
            $fullPath = storage_path('app/public/' . $filePath);

            if (!file_exists($fullPath)) return false;

            $response = Http::timeout(30)
                ->withHeaders(['X-API-KEY' => $this->apiKey])
                ->attach('file', file_get_contents($fullPath), basename($fullPath))
                ->post($this->baseUrl . '/upload-image', [
                    'student_code' => $studentCode,
                ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('enrollFace error: ' . $e->getMessage());
            return false;
        }
    }

    // ─────────────────────────────
    // UPDATE FACE
    // ─────────────────────────────
    public function updateFace(string $filePath, string $studentCode): bool
    {
        try {
            $fullPath = storage_path('app/public/' . $filePath);
            if (!file_exists($fullPath)) return false;
            $response = Http::timeout(50)
                ->withHeaders(['X-API-KEY' => $this->apiKey])
                ->attach('file', file_get_contents($fullPath), basename($fullPath))
                ->post($this->baseUrl . '/students/' . $studentCode . '/image');

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('updateFace error: ' . $e->getMessage());
            return false;
        }
    }

    // ─────────────────────────────
    // DELETE FACE
    // ─────────────────────────────
    public function deleteFace(string $studentCode): bool
    {
        try {
            $response = Http::timeout(30)
                ->withHeaders(['X-API-KEY' => $this->apiKey])
                ->delete($this->baseUrl . '/students/' . $studentCode);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('deleteFace error: ' . $e->getMessage());
            return false;
        }
    }

    // ─────────────────────────────
    // START SESSION
    // ─────────────────────────────
    public function startSession(array $payload): array|bool
    {
        try {
            $response = Http::timeout(60)
                ->withHeaders(['X-API-KEY' => $this->apiKey])
                ->post($this->baseUrl . '/start-session', $payload);

            if (!$response->successful()) {
                Log::error('startSession failed: ' . $response->body());
                return false;
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('startSession error: ' . $e->getMessage());
            return false;
        }
    }
}
