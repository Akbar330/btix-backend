<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->decimal('voucher_discount_amount', 15, 2)->default(0)->after('discount_amount');
            $table->string('voucher_code')->nullable()->after('payment_method_code');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['voucher_discount_amount', 'voucher_code']);
        });
    }
};
