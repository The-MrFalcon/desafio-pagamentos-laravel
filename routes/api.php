<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PixController;
use App\Http\Controllers\WithdrawController;

Route::post('/pix', [PixController::class, 'store']);
Route::post('/withdraw', [WithdrawController::class, 'store']);

// debug receive webhook
Route::post('/webhook/receive', function (Request $request) {
    dispatch(new \App\Jobs\ProcessWebhookJob($request->input('subadq', 'subadq_a'), $request->all(), $request->input('reference_id')));
    return response()->json(['status' => 'enviado']);
});
