<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('membership_plan_id')->nullable()->after('membership')->constrained('membership_plans')->cascadeOnDelete();
            $table->timestamp('membership_until')->nullable()->after('membership_plan_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeignKeyConstraints();
            $table->dropColumn(['membership_plan_id', 'membership_until']);
        });
    }
};
