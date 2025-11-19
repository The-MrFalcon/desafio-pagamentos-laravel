<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Adapters\SubadqAAdapter;
use App\Adapters\SubadqBAdapter;
use App\Models\Pix;
use App\Models\Withdraw;
use Illuminate\Support\Facades\DB;

class ProcessWebhookJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public $subadq;
    public $payload;
    public $referenceId;

    public function __construct($subadq, $payload, $referenceId)
    {
        $this->subadq = $subadq;
        $this->payload = $payload;
        $this->referenceId = $referenceId;
    }

    public function handle(): void
    {
        $adapter = $this->subadq === 'subadq_b' ? new SubadqBAdapter() : new SubadqAAdapter();
        $normalized = $adapter->normalizeWebhook($this->payload);

        if (empty($normalized)) {
            return;
        }

        DB::transaction(function () use ($normalized) {
            if ($normalized['type'] === 'pix') {
                $pix = Pix::where('id', $this->referenceId)->lockForUpdate()->first();
                if (! $pix) {
                    return;
                }

                $status = $this->mapPixStatus($normalized['status']);

                if ($this->isFinalStatus($pix->status) && $pix->status !== $status) {
                    return;
                }

                $pix->status = $status;
                $pix->metadata = array_merge($pix->metadata ?? [], $normalized);
                $pix->save();
            }

            if ($normalized['type'] === 'withdraw') {
                $wd = Withdraw::where('id', $this->referenceId)->lockForUpdate()->first();
                if (! $wd) {
                    return;
                }

                $status = $this->mapWithdrawStatus($normalized['status']);

                if ($this->isFinalStatus($wd->status) && $wd->status !== $status) {
                    return;
                }

                $wd->status = $status;
                $wd->metadata = array_merge($wd->metadata ?? [], $normalized);
                $wd->save();
            }
        });
    }

    private function mapPixStatus($s): string
    {
        $s = strtoupper($s ?? '');
        return match ($s) {
            'CONFIRMED', 'PAID' => 'PAID',
            'CANCELLED' => 'CANCELLED',
            'FAILED' => 'FAILED',
            default => 'PROCESSING',
        };
    }

    private function mapWithdrawStatus($s): string
    {
        $s = strtoupper($s ?? '');
        return match ($s) {
            'SUCCESS', 'DONE' => 'DONE',
            'CANCELLED' => 'CANCELLED',
            'FAILED' => 'FAILED',
            default => 'PROCESSING',
        };
    }

    private function isFinalStatus($s): bool
    {
        return in_array($s, ['PAID', 'DONE', 'CANCELLED', 'FAILED']);
    }
}
