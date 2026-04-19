<?php

namespace App\Http\Controllers;

use App\Models\MembershipPlan;
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
            'membership_plan_id' => 'required|exists:membership_plans,id',
            'payment_method' => 'required|string',
        ]);

        $membershipPlan = MembershipPlan::find($validated['membership_plan_id']);

        if (!$membershipPlan || !$membershipPlan->is_active) {
            return response()->json(['message' => 'Paket membership tidak tersedia.'], 422);
        }

        $paymentMethod = PaymentMethod::query()
            ->where('code', $validated['payment_method'])
            ->where('is_active', true)
            ->first();

        if (!$paymentMethod) {
            return response()->json(['message' => 'Metode pembayaran tidak tersedia.'], 422);
        }

        $price = $membershipPlan->price;

        // If price is 0 (basic plan), no payment needed
        if ($price === 0) {
            return response()->json(['free_plan' => true, 'message' => 'Plan gratis, tidak perlu pembayaran']);
        }

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
                    'id' => 'MEM-' . $membershipPlan->name,
                    'price' => $price,
                    'quantity' => 1,
                    'name' => 'Membership: ' . $membershipPlan->display_name,
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
            'membership_plan_id' => 'required|exists:membership_plans,id',
        ]);

        $membershipPlan = MembershipPlan::find($validated['membership_plan_id']);

        if (!$membershipPlan || !$membershipPlan->is_active) {
            return response()->json(['message' => 'Paket membership tidak tersedia.'], 422);
        }

        $user = $request->user();
        $membershipUntil = now()->addMonth();

        $user->update([
            'membership' => $membershipPlan->name,
            'membership_plan_id' => $membershipPlan->id,
            'membership_until' => $membershipUntil,
        ]);

        return response()->json([
            'message' => 'Membership activated: ' . $membershipPlan->display_name,
            'user' => $user->load('membershipPlan')
        ]);
    }
}
