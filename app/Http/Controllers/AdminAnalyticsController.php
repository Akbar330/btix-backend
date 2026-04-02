<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use App\Models\Ticket;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminAnalyticsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized action'], 403);
        }

        $successfulTransactions = Transaction::query()->where('payment_status', 'success');

        return response()->json([
            'overview' => [
                'revenue' => (float) $successfulTransactions->sum('total_price'),
                'tickets_sold' => (int) $successfulTransactions->sum('quantity'),
                'active_events' => Ticket::query()->whereIn('status', ['published', 'sold_out'])->count(),
                'registered_users' => User::query()->count(),
            ],
            'sales_by_event' => Transaction::query()
                ->select('ticket_id', DB::raw('SUM(quantity) as total_quantity'), DB::raw('SUM(total_price) as total_revenue'))
                ->with('ticket:id,title,status')
                ->where('payment_status', 'success')
                ->groupBy('ticket_id')
                ->orderByDesc('total_revenue')
                ->limit(5)
                ->get(),
            'payment_method_breakdown' => PaymentMethod::query()
                ->leftJoin('transactions', 'payment_methods.code', '=', 'transactions.payment_method_code')
                ->select(
                    'payment_methods.code',
                    'payment_methods.name',
                    DB::raw("SUM(CASE WHEN transactions.payment_status = 'success' THEN transactions.total_price ELSE 0 END) as revenue"),
                    DB::raw("SUM(CASE WHEN transactions.payment_status = 'success' THEN transactions.quantity ELSE 0 END) as quantity")
                )
                ->groupBy('payment_methods.code', 'payment_methods.name')
                ->orderByDesc('revenue')
                ->get(),
            'status_breakdown' => Ticket::query()
                ->select('status', DB::raw('COUNT(*) as total'))
                ->groupBy('status')
                ->pluck('total', 'status'),
            'membership_breakdown' => User::query()
                ->select('membership', DB::raw('COUNT(*) as total'))
                ->groupBy('membership')
                ->pluck('total', 'membership'),
            'voucher_overview' => [
                'active' => Voucher::query()->where('is_active', true)->count(),
                'used' => (int) Voucher::query()->sum('used_count'),
            ],
        ]);
    }
}
