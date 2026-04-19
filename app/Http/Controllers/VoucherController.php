<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VoucherController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized action'], 403);
        }

        return response()->json(
            Voucher::query()->latest()->get()
        );
    }

    public function store(Request $request): JsonResponse
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized action'], 403);
        }

        $validated = $this->validateVoucher($request, true);
        $validated['code'] = strtoupper($validated['code']);

        $voucher = Voucher::create($validated + ['used_count' => 0]);

        return response()->json($voucher, 201);
    }

    public function update(Request $request, Voucher $voucher): JsonResponse
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized action'], 403);
        }

        $validated = $this->validateVoucher($request, false, $voucher->id);
        if (isset($validated['code'])) {
            $validated['code'] = strtoupper($validated['code']);
        }

        $voucher->update($validated);
        if ($request->has('is_active')) {
            $voucher->is_active = $request->boolean('is_active');
            $voucher->save();
        }

        return response()->json([
            'message' => 'Voucher updated successfully',
            'voucher' => $voucher->fresh()
        ]);
    }

    public function preview(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string',
            'subtotal' => 'required|numeric|min:0',
        ]);

        $voucher = Voucher::query()->where('code', strtoupper($validated['code']))->first();
        if (!$voucher || !$voucher->isCurrentlyValid((float) $validated['subtotal'])) {
            return response()->json(['message' => 'Voucher tidak valid atau sudah tidak aktif.'], 422);
        }

        return response()->json([
            'voucher' => $voucher,
            'discount_amount' => $voucher->calculateDiscount((float) $validated['subtotal']),
        ]);
    }

    public function destroy(Request $request, Voucher $voucher): JsonResponse
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized action'], 403);
        }

        $voucher->delete();

        return response()->json(['message' => 'Voucher deleted successfully']);
    }

    private function validateVoucher(Request $request, bool $isCreate, ?int $ignoreId = null): array
    {
        $codeRules = [
            $isCreate ? 'required' : 'sometimes',
            'string',
            'max:50',
            'unique:vouchers,code' . ($ignoreId ? ',' . $ignoreId : ''),
        ];

        return $request->validate([
            'code' => $codeRules,
            'description' => [$isCreate ? 'required' : 'sometimes', 'string', 'max:255'],
            'discount_type' => [$isCreate ? 'required' : 'sometimes', 'in:flat,percent'],
            'value' => [$isCreate ? 'required' : 'sometimes', 'numeric', 'min:0'],
            'min_purchase' => ['nullable', 'numeric', 'min:0'],
            'max_discount' => ['nullable', 'numeric', 'min:0'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'max_uses' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }
}
