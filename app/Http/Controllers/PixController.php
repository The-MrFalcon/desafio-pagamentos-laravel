<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class PixController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'merchant_id' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|string',
            'order_id' => 'required|string',
            'payer' => 'required|array',
            'payer.name' => 'required|string',
            'payer.cpf_cnpj' => 'required|string',
            'expires_in' => 'required|integer',
        ]);

        $user = User::find(1); // Assume test user
        $payload = $request->all();

        $resp = app(PaymentService::class)->createPix($user, $payload);

        return response()->json($resp);
    }
}
