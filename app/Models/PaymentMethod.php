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
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public const DEFAULT_METHODS = [
        [
            'code' => 'bank_transfer',
            'name' => 'Bank Transfer',
            'description' => 'Virtual account dan transfer bank via Midtrans.',
            'is_active' => true,
            'sort_order' => 1,
        ],
        [
            'code' => 'gopay',
            'name' => 'GoPay',
            'description' => 'Pembayaran instan lewat aplikasi GoPay.',
            'is_active' => true,
            'sort_order' => 2,
        ],
        [
            'code' => 'shopeepay',
            'name' => 'ShopeePay',
            'description' => 'Pembayaran lewat aplikasi ShopeePay.',
            'is_active' => true,
            'sort_order' => 3,
        ],
        [
            'code' => 'indomaret',
            'name' => 'Indomaret',
            'description' => 'Bayar tunai atau cashless lewat gerai Indomaret.',
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
