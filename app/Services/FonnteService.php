<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FonnteService
{
    public function sendMessage(string $phoneNumber, string $message): bool
    {
        try {
            $response = Http::timeout(15)
                ->withHeaders([
                    'Authorization' => config('services.fonnte.api_token'),
                ])
                ->asForm()
                ->post('https://api.fonnte.com/send', [
                    'target' => $this->normalizePhoneNumber($phoneNumber),
                    'message' => $message,
                    'countryCode' => '62',
                ]);

            if ($response->failed()) {
                Log::error('Fonnte send failed', ['response' => $response->body()]);
                return false;
            }

            return true;

        } catch (\Exception $e) {
            Log::error('Fonnte send error: ' . $e->getMessage());
            return false;
        }
    }

    private function normalizePhoneNumber(string $phoneNumber): string
    {
        $cleaned = preg_replace('/[^0-9]/', '', $phoneNumber);

        if (str_starts_with($cleaned, '0')) {
            $cleaned = '62' . substr($cleaned, 1);
        }

        return $cleaned;
    }
}