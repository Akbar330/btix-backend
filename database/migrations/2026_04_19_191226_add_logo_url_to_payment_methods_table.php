<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->string('logo_url')->after('name')->nullable();
        });

        // Update default methods with logos
        $logos = [
            'bank_transfer' => 'https://img.icons8.com/color/96/bank--v1.png',
            'gopay' => 'https://upload.wikimedia.org/wikipedia/commons/8/86/Gopay_logo.svg',
            'shopeepay' => 'https://imgs.search.brave.com/E74qgNbhfWCmsx9-r_IkDV6wF73j1ydIGG9ATyC5pLw/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9pbWFn/ZXMuc2Vla2xvZ28u/Y29tL2xvZ28tcG5n/LzQwLzIvc2hvcGVl/LXBheS1sb2dvLXBu/Z19zZWVrbG9nby00/MDY4MzkucG5n',
            'indomaret' => 'https://imgs.search.brave.com/FcUDfuDSVZTkos7Ct4U0O9PMLpfiMKReUB0iy5KC8p0/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9pbWFn/ZXMuc2Vla2xvZ28u/Y29tL2xvZ28tcG5n/LzUwLzEvaW5kb21h/cmV0LWxvZ28tcG5n/X3NlZWtsb2dvLTUw/NDA1Ni5wbmc',
        ];

        foreach ($logos as $code => $url) {
            DB::table('payment_methods')
                ->where('code', $code)
                ->update(['logo_url' => $url]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->dropColumn('logo_url');
        });
    }
};
