<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Services\PaymentService;

class WithdrawController extends Controller
{
    public function store(Request $request, PaymentService $service)
    {
        $validated = $request->validate([
            'merchant_id' => 'required|string',
            'account' => 'required|array',
            'account.bank_code' => 'required|string',
            'account.agencia' => 'required|string',
            'account.conta' => 'required|string',
            'account.type' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'transaction_id' => 'required|string',
        ]);

        $user = User::find(1); // Assuming test user
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }
        $resp = $service->createWithdraw($user, $validated);

        return response()->json($resp, 201);
    }
}
