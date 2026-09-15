<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class AiClassificationService
{
    private string $model = 'gemini-3.6-flash';
    public function classify(string $photoPath, string $description, string $category): ?array
    {
        try {
            $absolutePath = Storage::disk('public')->path($photoPath);
            $imageData = base64_encode(file_get_contents($absolutePath));
            $mediaType = $this->detectMediaType($absolutePath);

            $apiKey = config('services.gemini.api_key');
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$apiKey}";

                $response = Http::timeout(45)->retry(2, 2000)->post($url, [                'contents' => [
                    [
                        'parts' => [
                            [
                                'inline_data' => [
                                    'mime_type' => $mediaType,
                                    'data' => $imageData,
                                ],
                            ],
                            [
                                'text' => $this->buildPrompt($description, $category),
                            ],
                        ],
                    ],
                ],
            ]);

            if ($response->failed()) {
                Log::error('AI classification failed', ['response' => $response->body()]);
                return null;
            }

            $text = $response->json('candidates.0.content.parts.0.text');

            return $this->parseJsonResponse($text);

        } catch (\Exception $e) {
            Log::error('AI classification error: ' . $e->getMessage());
            return null;
        }
    }

    private function buildPrompt(string $description, string $category): string
    {
        return <<<PROMPT
        Kamu adalah sistem klasifikasi laporan infrastruktur kota. Analisis foto dan deskripsi berikut.

        Kategori yang dilaporkan warga: {$category}
        Deskripsi warga: {$description}

        Kategori yang tersedia:
        - pothole: jalan berlubang
        - trash: sampah menumpuk
        - streetlight: lampu jalan mati/rusak
        - drainage: saluran air tersumbat/mampet
        - fallen_tree: pohon tumbang/menghalangi jalan

        Tugas kamu:
        1. Konfirmasi apakah foto memang menunjukkan kategori "{$category}". Jika foto justru menunjukkan kategori lain dari daftar di atas, koreksi ke kategori yang paling sesuai. Jika foto tidak relevan/tidak jelas, tetap pilih kategori yang paling mendekati.
        2. Tentukan tingkat keparahan (severity): "low", "medium", atau "high", berdasarkan seberapa parah kondisi yang terlihat di foto.

        Jawab HANYA dengan JSON valid, tanpa teks lain, tanpa markdown code block, format persis seperti ini:
        {"confirmed_category": "salah satu dari: pothole, trash, streetlight, drainage, fallen_tree", "severity": "low atau medium atau high", "reasoning": "alasan singkat 1 kalimat"}
        PROMPT;
    }

    private const VALID_CATEGORIES = ['pothole', 'trash', 'streetlight', 'drainage', 'fallen_tree'];
    private const VALID_SEVERITIES = ['low', 'medium', 'high'];

    private function parseJsonResponse(?string $text): ?array
    {
        if (!$text) {
            return null;
        }

        $cleaned = trim($text);
        $cleaned = preg_replace('/^```json\s*|\s*```$/', '', $cleaned);

        $decoded = json_decode($cleaned, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            return null;
        }

        return $this->validateEnums($decoded);
    }

    private function validateEnums(array $data): array
    {
        if (!in_array($data['confirmed_category'] ?? null, self::VALID_CATEGORIES, true)) {
            unset($data['confirmed_category']);
        }

        if (!in_array($data['severity'] ?? null, self::VALID_SEVERITIES, true)) {
            unset($data['severity']);
        }

        return $data;
    }

    private function detectMediaType(string $path): string
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return match ($extension) {
            'png' => 'image/png',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
            default => 'image/jpeg',
        };
    }
}