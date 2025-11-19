<?php

namespace App\Services;

use App\Adapters\SubadqAAdapter;
use App\Adapters\SubadqBAdapter;
use App\Jobs\DispatchWebhookJob;
use App\Models\Pix;
use App\Models\Withdraw;
use App\Models\User;
use Illuminate\Foundation\Bus\Dispatchable;

class PaymentService
{
    public function adapterForUser(User $user)
    {
        return match($user->subadquirente) {
            'subadq_b' => new SubadqBAdapter(),
            default => new SubadqAAdapter(),
        };
    }

    public function createPix(User $user, array $payload): Pix
    {
        $adapter = $this->adapterForUser($user);
        $resp = $adapter->createPix($payload);

        $pix = Pix::create([
            'user_id' => $user->id,
            'pix_id' => $resp['pix_id'] ?? ($resp['id'] ?? null),
            'amount' => $payload['amount'],
            'status' => 'PROCESSING',
            'metadata' => $resp,
        ]);

        DispatchWebhookJob::dispatch($user->subadquirente, $resp, $pix->id);

        return $pix->fresh();
    }

    public function createWithdraw(User $user, array $payload): Withdraw
    {
        $adapter = $this->adapterForUser($user);
        $resp = $adapter->createWithdraw($payload);

        $wd = Withdraw::create([
            'user_id' => $user->id,
            'withdraw_id' => $resp['withdraw_id'] ?? ($resp['id'] ?? null),
            'amount' => $payload['amount'],
            'status' => 'PROCESSING',
            'metadata' => $resp,
        ]);

        DispatchWebhookJob::dispatch($user->subadquirente, $resp, $wd->id);

        return $wd->fresh();
    }

    public function processPayment(Pix $pix): array
    {
        $user = $pix->user;
        $adapter = $this->adapterForUser($user);
        $payload = [
            'amount' => $pix->amount,
            'mock_response' => $pix->mock_response,
        ];
        $resp = $adapter->createPix($payload);

        $pix->update([
            'pix_id' => $resp['pix_id'] ?? ($resp['id'] ?? null),
            'status' => 'PROCESSING',
            'metadata' => $resp,
        ]);

        DispatchWebhookJob::dispatch($user->subadquirente, $resp, $pix->id);

        return $resp;
    }
}
