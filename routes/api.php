<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AppSettingController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminAnalyticsController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\MembershipPlanController;
use App\Http\Controllers\BannerController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/public-config', [ChatbotController::class, 'publicConfig']);
Route::post('/chatbot', [ChatbotController::class, 'chat']);
Route::get('/admin/membership-plans', [MembershipPlanController::class, 'index']);

Route::get('/tickets', [TicketController::class, 'index']);
Route::get('/tickets/{ticket}', [TicketController::class, 'show']);
Route::get('/settings', [AppSettingController::class, 'index']);

// Banners
Route::get('/banners', [BannerController::class, 'index']);

// Midtrans webhook
Route::post('/midtrans/callback', [TransactionController::class, 'midtransCallback']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Tickets (Admin logic handled in controller)
    Route::post('/tickets', [TicketController::class, 'store']);
    Route::put('/tickets/{ticket}', [TicketController::class, 'update']);
    Route::post('/tickets/{ticket}/update', [TicketController::class, 'update']); // for multipart file upload
    Route::delete('/tickets/{ticket}', [TicketController::class, 'destroy']);
    
    // Transactions
    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::post('/checkout', [TransactionController::class, 'checkout']);
    Route::post('/transactions/{id}/cancel', [TransactionController::class, 'cancel']);
    Route::post('/transactions/{id}/success', [TransactionController::class, 'success']);

    // Admin
    Route::post('/admin/scan', [TransactionController::class, 'scan']);
    Route::get('/admin/analytics', [AdminAnalyticsController::class, 'index']);
    Route::get('/admin/payment-methods', [PaymentMethodController::class, 'index']);
    Route::patch('/admin/payment-methods/{paymentMethod}', [PaymentMethodController::class, 'update']);
    Route::get('/admin/vouchers', [VoucherController::class, 'index']);
    Route::post('/admin/vouchers', [VoucherController::class, 'store']);
    Route::patch('/admin/vouchers/{voucher}', [VoucherController::class, 'update']);
    Route::delete('/admin/vouchers/{voucher}', [VoucherController::class, 'destroy']);
    
    // Membership Plans (Admin)
    Route::post('/admin/membership-plans', [MembershipPlanController::class, 'store']);
    Route::get('/admin/membership-plans/{membershipPlan}', [MembershipPlanController::class, 'show']);
    Route::patch('/admin/membership-plans/{membershipPlan}', [MembershipPlanController::class, 'update']);
    Route::delete('/admin/membership-plans/{membershipPlan}', [MembershipPlanController::class, 'destroy']);

    // Banners (Admin)
    Route::get('/admin/banners', [BannerController::class, 'adminIndex']);
    Route::post('/admin/banners', [BannerController::class, 'store']);
    Route::patch('/admin/banners/{banner}', [BannerController::class, 'update']);
    Route::delete('/admin/banners/{banner}', [BannerController::class, 'destroy']);

    // Membership
    Route::post('/membership/upgrade', [\App\Http\Controllers\MembershipController::class, 'upgrade']);
    Route::post('/membership/confirm', [\App\Http\Controllers\MembershipController::class, 'confirm']);
    Route::post('/vouchers/preview', [VoucherController::class, 'preview']);

    // Admin Settings
    Route::patch('/admin/settings', [AppSettingController::class, 'update']);
});
