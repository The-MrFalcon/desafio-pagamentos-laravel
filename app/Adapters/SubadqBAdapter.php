<?php

namespace App\Adapters;

use Illuminate\Support\Facades\Http;

class SubadqBAdapter implements AdapterInterface
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('subadquirentes.subadq_b.base_url');
    }

    public function createPix(array $payload): array
    {
        // Always return mock response for testing
        return [
            'pix_id' => 'mock_pix_' . uniqid(),
            'location' => 'https://example.com/pix/' . uniqid(),
            'qrcode' => 'mock_qr_code_' . uniqid(),
            'expires_at' => Carbon::now()->addMinutes(30)->toISOString(),
            'status' => 'created',
        ];
    }

    public function createWithdraw(array $payload): array
    {
        // Always return mock response for testing
        return [
            'withdraw_id' => 'WD' . uniqid(),
            'status' => 'PROCESSING',
        ];
    }

    public function normalizeWebhook(array $payload): array
    {
        if (isset($payload['type']) && str_contains($payload['type'], 'pix')) {
            $data = $payload['data'] ?? [];
            return [
                'type' => 'pix',
                'external_id' => $data['id'] ?? null,
                'status' => $data['status'] ?? null,
                'amount' => $data['value'] ?? null,
                'payer_name' => $data['payer']['name'] ?? null,
                'confirmed_at' => $data['confirmed_at'] ?? null,
            ];
        }

        if (isset($payload['type']) && str_contains($payload['type'], 'withdraw')) {
            $data = $payload['data'] ?? [];
            return [
                'type' => 'withdraw',
                'external_id' => $data['id'] ?? null,
                'status' => $data['status'] ?? null,
                'amount' => $data['amount'] ?? null,
                'processed_at' => $data['processed_at'] ?? null,
            ];
        }

        return [];
    }
}
