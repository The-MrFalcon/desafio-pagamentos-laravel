<?php

namespace App\Adapters;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class SubadqAAdapter implements AdapterInterface
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('subadquirentes.subadq_a.base_url');
    }

    public function createPix(array $payload): array
    {
        // Always return mock response for testing
        return [
            'transaction_id' => 'mock_pix_' . uniqid(),
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
        if (isset($payload['event']) && str_contains($payload['event'], 'pix')) {
            return [
                'type' => 'pix',
                'external_id' => $payload['pix_id'] ?? null,
                'status' => $payload['status'] ?? null,
                'amount' => $payload['amount'] ?? null,
                'payer_name' => $payload['payer_name'] ?? null,
                'confirmed_at' => $payload['payment_date'] ?? null,
            ];
        }

        if (isset($payload['event']) && str_contains($payload['event'], 'withdraw')) {
            return [
                'type' => 'withdraw',
                'external_id' => $payload['withdraw_id'] ?? null,
                'status' => $payload['status'] ?? null,
                'amount' => $payload['amount'] ?? null,
                'processed_at' => $payload['completed_at'] ?? null,
            ];
        }

        return [];
    }
}
