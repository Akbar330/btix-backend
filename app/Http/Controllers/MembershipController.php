<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class MembershipController extends Controller
{
    private function configureMidtrans(): void
    {
        \Midtrans\Config::$serverKey = config('services.midtrans.server_key');
        \Midtrans\Config::$isProduction = (bool) config('services.midtrans.is_production', false);
        \Midtrans\Config::$isSanitized = true;
        \Midtrans\Config::$is3ds = true;
    }

    public function upgrade(Request $request)
    {
        $validated = $request->validate([
            'membership' => 'required|in:regular,premium',
            'payment_method' => 'required|string',
        ]);

        $paymentMethod = PaymentMethod::query()
            ->where('code', $validated['payment_method'])
            ->where('is_active', true)
            ->first();

        if (!$paymentMethod) {
            return response()->json(['message' => 'Metode pembayaran tidak tersedia.'], 422);
        }

        $prices = [
            'regular' => 50000,
            'premium' => 100000,
        ];

        $price = $prices[$validated['membership']];

        // Read keys from config so this still works when config cache is enabled.
        $this->configureMidtrans();

        $params = [
            'transaction_details' => [
                'order_id' => 'MEM-' . $request->user()->id . '-' . time(),
                'gross_amount' => $price,
            ],
            'customer_details' => [
                'first_name' => $request->user()->name,
                'email' => $request->user()->email,
            ],
            'item_details' => [
                [
                    'id' => 'MEM-' . $validated['membership'],
                    'price' => $price,
                    'quantity' => 1,
                    'name' => 'Membership Upgrade: ' . strtoupper($validated['membership']),
                ]
            ],
            'enabled_payments' => [$paymentMethod->code],
        ];

        try {
            $snapToken = \Midtrans\Snap::getSnapToken($params);
            return response()->json(['snap_token' => $snapToken]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to generate payment token: ' . $e->getMessage()], 500);
        }
    }

    public function confirm(Request $request)
    {
        $validated = $request->validate([
            'membership' => 'required|in:regular,premium',
        ]);

        $user = $request->user();
        $user->update(['membership' => $validated['membership']]);

        return response()->json([
            'message' => 'Membership activated: ' . strtoupper($validated['membership']),
            'user' => $user
        ]);
    }
}
