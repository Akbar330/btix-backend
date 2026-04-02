<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use App\Models\Ticket;
use App\Models\Transaction;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TransactionController extends Controller
{
    private function configureMidtrans(): void
    {
        \Midtrans\Config::$serverKey = config('services.midtrans.server_key');
        \Midtrans\Config::$isProduction = (bool) config('services.midtrans.is_production', false);
        \Midtrans\Config::$isSanitized = true;
        \Midtrans\Config::$is3ds = true;
    }

    public function index(Request $request)
    {
        if ($request->user()->role === 'admin') {
            return response()->json(Transaction::with(['user', 'ticket'])->get());
        }
        return response()->json($request->user()->transactions()->with('ticket')->get());
    }

    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'ticket_id' => 'required|exists:tickets,id',
            'quantity' => 'required|integer|min:1',
            'payment_method' => 'required|string',
            'voucher_code' => 'nullable|string|max:50',
        ]);

        $paymentMethod = PaymentMethod::query()
            ->where('code', $validated['payment_method'])
            ->where('is_active', true)
            ->first();

        if (!$paymentMethod) {
            return response()->json(['message' => 'Metode pembayaran tidak tersedia.'], 422);
        }

        $ticket = Ticket::findOrFail($validated['ticket_id']);

        if (in_array($ticket->status, ['draft', 'ended'], true)) {
            return response()->json(['message' => 'Event tidak tersedia untuk dibeli.'], 422);
        }

        if ($ticket->quota < $validated['quantity']) {
            return response()->json(['message' => 'Not enough ticket quota available'], 400);
        }

        $originalPrice = $ticket->price * $validated['quantity'];
        $discountRate = $request->user()->getDiscountRate();
        $membershipDiscountAmount = $originalPrice * $discountRate;
        $voucher = null;
        $voucherDiscountAmount = 0;

        if (!empty($validated['voucher_code'])) {
            $voucher = Voucher::query()->where('code', strtoupper($validated['voucher_code']))->first();

            if (!$voucher || !$voucher->isCurrentlyValid((float) ($originalPrice - $membershipDiscountAmount))) {
                return response()->json(['message' => 'Voucher tidak valid atau sudah tidak aktif.'], 422);
            }

            $voucherDiscountAmount = $voucher->calculateDiscount((float) ($originalPrice - $membershipDiscountAmount));
        }

        $discountAmount = $membershipDiscountAmount + $voucherDiscountAmount;
        $totalPrice = max(0, $originalPrice - $discountAmount);

        DB::beginTransaction();
        try {
            // Deduct quota
            $ticket->decrement('quota', $validated['quantity']);

            // Create transaction
            $transaction = Transaction::create([
                'user_id' => $request->user()->id,
                'ticket_id' => $ticket->id,
                'quantity' => $validated['quantity'],
                'original_price' => $originalPrice,
                'discount_amount' => $membershipDiscountAmount,
                'voucher_discount_amount' => $voucherDiscountAmount,
                'total_price' => $totalPrice,
                'payment_status' => 'pending',
                'midtrans_id' => null,
                'payment_method_code' => $paymentMethod->code,
                'voucher_code' => $voucher?->code,
            ]);

            // Read keys from config so this still works when config cache is enabled.
            $this->configureMidtrans();

            $itemDetails = [
                [
                    'id' => $ticket->id,
                    'price' => (int)$ticket->price,
                    'quantity' => $validated['quantity'],
                    'name' => Str::limit($ticket->title, 50),
                ]
            ];

            if ($membershipDiscountAmount > 0) {
                $itemDetails[] = [
                    'id' => 'MEMBERSHIP-DISCOUNT',
                    'price' => -(int)$membershipDiscountAmount,
                    'quantity' => 1,
                    'name' => 'Membership Discount (' . ($discountRate * 100) . '%)',
                ];
            }

            if ($voucherDiscountAmount > 0 && $voucher) {
                $itemDetails[] = [
                    'id' => 'VOUCHER-' . $voucher->code,
                    'price' => -(int) $voucherDiscountAmount,
                    'quantity' => 1,
                    'name' => Str::limit('Voucher ' . $voucher->code, 50),
                ];
            }

            $params = [
                'transaction_details' => [
                    'order_id' => $transaction->id . '-' . time(),
                    'gross_amount' => (int)$totalPrice,
                ],
                'customer_details' => [
                    'first_name' => $request->user()->name,
                    'email' => $request->user()->email,
                ],
                'item_details' => $itemDetails
            ];

            $params['enabled_payments'] = [$paymentMethod->code];

            $snapToken = \Midtrans\Snap::getSnapToken($params);
            $transaction->update(['snap_token' => $snapToken]);

            if ($voucher) {
                $voucher->increment('used_count');
            }

            if ($ticket->fresh()->quota <= 0) {
                $ticket->update(['status' => 'sold_out']);
            }

            DB::commit();

            return response()->json($transaction, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Checkout failed: ' . $e->getMessage()], 500);
        }
    }

    public function midtransCallback(Request $request)
    {
        $serverKey = config('services.midtrans.server_key');
        $hashed = hash("sha512", $request->order_id . $request->status_code . $request->gross_amount . $serverKey);
        
        if ($hashed !== $request->signature_key) {
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        // order_id is formatted as transactionId-timestamp
        $transactionId = explode('-', $request->order_id)[0];
        $transaction = Transaction::find($transactionId);

        if (!$transaction) {
            return response()->json(['message' => 'Transaction not found'], 404);
        }

        $transaction->update(['midtrans_id' => $request->transaction_id]);

        if ($request->transaction_status == 'capture' || $request->transaction_status == 'settlement') {
            $transaction->update(['payment_status' => 'success']);
        } elseif (in_array($request->transaction_status, ['cancel', 'deny', 'expire'])) {
            $transaction->update(['payment_status' => 'failed']);
            // Restore quota
            $transaction->ticket()->increment('quota', $transaction->quantity);
            if ($transaction->ticket && $transaction->ticket->status === 'sold_out') {
                $transaction->ticket->update(['status' => 'published']);
            }
        } elseif ($request->transaction_status == 'pending') {
            $transaction->update(['payment_status' => 'pending']);
        }

        return response()->json(['message' => 'Callback processed']);
    }

    public function scan(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized actions'], 403);
        }

        $qrData = $request->qr_data;
        if (!$qrData) return response()->json(['message' => 'QR Data required'], 400);

        $parts = explode('-', $qrData);
        if (count($parts) < 4 || $parts[0] !== 'ticket') {
            return response()->json(['message' => 'Invalid QR Code Format'], 400);
        }
        
        $transactionId = $parts[1];
        $transaction = Transaction::find($transactionId);
        
        if (!$transaction || $transaction->payment_status !== 'success') {
            return response()->json(['message' => 'Invalid or unpaid ticket'], 400);
        }
        
        if ($transaction->is_scanned) {
            return response()->json(['message' => 'Ticket HAS ALREADY BEEN SCANNED!'], 400);
        }
        
        $transaction->update(['is_scanned' => true]);
        
        return response()->json([
            'message' => 'Ticket successfully verified and checked in!',
            'transaction' => $transaction->load('user', 'ticket')
        ]);
    }

    public function cancel(Request $request, $id)
    {
        $transaction = Transaction::where('id', $id)->where('user_id', $request->user()->id)->firstOrFail();
        
        if ($transaction->payment_status === 'pending') {
            $transaction->update(['payment_status' => 'failed']);
            $transaction->ticket()->increment('quota', $transaction->quantity);
            if ($transaction->ticket && $transaction->ticket->status === 'sold_out') {
                $transaction->ticket->update(['status' => 'published']);
            }
            if ($transaction->voucher_code) {
                Voucher::query()->where('code', $transaction->voucher_code)->decrement('used_count');
            }
            return response()->json(['message' => 'Transaction cancelled and quota restored']);
        }
        
        return response()->json(['message' => 'Cannot cancel this transaction'], 400);
    }

    // fallback for local testing without ngrok
    public function success(Request $request, $id)
    {
        $transaction = Transaction::where('id', $id)->where('user_id', $request->user()->id)->firstOrFail();
        
        if ($transaction->payment_status === 'pending') {
            $transaction->update(['payment_status' => 'success']);
            return response()->json(['message' => 'Transaction marked as success']);
        }
        
        return response()->json(['message' => 'Cannot update this transaction'], 400);
    }
}
