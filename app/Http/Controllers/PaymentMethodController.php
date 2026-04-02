<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized action'], 403);
        }

        return response()->json(
            PaymentMethod::query()->ordered()->get()
        );
    }

    public function update(Request $request, PaymentMethod $paymentMethod): JsonResponse
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized action'], 403);
        }

        $validated = $request->validate([
            'is_active' => 'required|boolean',
        ]);

        $paymentMethod->update($validated);

        return response()->json([
            'message' => 'Payment method updated successfully.',
            'payment_method' => $paymentMethod->fresh(),
        ]);
    }
}
