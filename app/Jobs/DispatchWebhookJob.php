<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DispatchWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $subadq;
    public $payload;
    public $referenceId;

    public function __construct($subadq, $payload, $referenceId)
    {
        $this->subadq = $subadq;
        $this->payload = $payload;
        $this->referenceId = $referenceId;
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
  