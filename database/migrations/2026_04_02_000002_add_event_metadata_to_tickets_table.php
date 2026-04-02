<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->string('category')->nullable()->after('title');
            $table->string('status')->default('published')->after('event_date');
            $table->string('venue')->nullable()->after('status');
            $table->string('city')->nullable()->after('venue');
            $table->string('organizer')->nullable()->after('city');
            $table->text('highlights')->nullable()->after('organizer');
            $table->text('terms')->nullable()->after('highlights');
        });

        DB::table('tickets')->update([
            'category' => 'General',
            'status' => 'published',
        ]);
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn(['category', 'status', 'venue', 'city', 'organizer', 'highlights', 'terms']);
        });
    }
};
