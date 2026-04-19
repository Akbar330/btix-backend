<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'is_active',
        'sort_order',
        'logo_url',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public const DEFAULT_METHODS = [
        [
            'code' => 'bank_transfer',
            'name' => 'Bank Transfer',
            'description' => 'Virtual account dan transfer bank via Midtrans.',
            'logo_url' => 'https://img.icons8.com/color/96/bank--v1.png',
            'is_active' => true,
            'sort_order' => 1,
        ],
        [
            'code' => 'gopay',
            'name' => 'GoPay',
            'description' => 'Pembayaran instan lewat aplikasi GoPay.',
            'logo_url' => 'https://upload.wikimedia.org/wikipedia/commons/8/86/Gopay_logo.svg',
            'is_active' => true,
            'sort_order' => 2,
        ],
        [
            'code' => 'shopeepay',
            'name' => 'ShopeePay',
            'description' => 'Pembayaran lewat aplikasi ShopeePay.',
            'logo_url' => 'https://upload.wikimedia.org/wikipedia/commons/f/fe/ShopeePay_logo.svg',
            'is_active' => true,
            'sort_order' => 3,
        ],
        [
            'code' => 'indomaret',
            'name' => 'Indomaret',
            'description' => 'Bayar tunai atau cashless lewat gerai Indomaret.',
            'logo_url' => 'https://upload.wikimedia.org/wikipedia/commons/4/41/Indomaret_logo.svg',
            'is_active' => true,
            'sort_order' => 4,
        ],
    ];

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public static function activeCodes(): array
    {
        return static::query()
            ->where('is_active', true)
            ->ordered()
            ->pluck('code')
            ->all();
    }
}
