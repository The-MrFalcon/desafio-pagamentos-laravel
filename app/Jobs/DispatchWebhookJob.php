<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class DispatchWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $subadq;
    public $payload;
    public $referenceId;
    public $account;
    public $type;

    public function __construct($subadq, $payload, $referenceId, $account = null, $type = 'pix')
    {
        $this->subadq = $subadq;
        $this->payload = $payload;
        $this->referenceId = $referenceId;
        $this->account = $account;
        $this->type = $type;
    }

    public function handle()
    {
        $webhookPayload = $this->buildWebhookPayload();
        Http::post('http://localhost:8000/api/webhook/receive', [
            'subadq' => $this->subadq,
            'reference_id' => $this->referenceId,
            ...$webhookPayload,
        ]);
    }

    private function buildWebhookPayload(): array
    {
        $base = [
            'status' => $this->type === 'withdraw' ? 'DONE' : 'PAID',
            'amount' => $this->payload['amount'] ?? null,
        ];

        if ($this->account) {
            $base['account'] = $this->account;
        }

        if ($this->subadq === 'subadq_a') {
            return [
                'event' => $this->type === 'withdraw' ? 'withdraw_done' : 'pix_paid',
                ($this->type === 'withdraw' ? 'withdraw_id' : 'pix_id') => $this->payload[$this->type . '_id'] ?? null,
                ...$base,
            ];
        } elseif ($this->subadq === 'subadq_b') {
            return [
                'type' => $this->type,
                'data' => [
                    'id' => $this->payload[$this->type . '_id'] ?? null,
                    ...$base,
                ],
            ];
        }

        return $base;
    }

    public static function dispatchWithDelay($subadq, $payload, $referenceId)
    {
        $delayMs = match($subadq) {
            'subadq_a' => 1000,
            'subadq_b' => 2000,
            default => 0,
        };

        return static::dispatch($subadq, $payload, $referenceId)
            ->delay(now()->addMilliseconds($delayMs));
    }
}
