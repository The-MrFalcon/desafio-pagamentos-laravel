<?php

namespace App\Adapters;

interface AdapterInterface
{
    public function createPix(array $payload): array;
    public function createWithdraw(array $payload): array;
    public function normalizeWebhook(array $payload): array;
}
