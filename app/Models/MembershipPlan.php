<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MembershipPlan extends Model
{
    protected $fillable = [
        'name',
        'display_name',
        'price',
        'discount_percentage',
        'description',
        'features',
        'is_active',
        'order',
    ];

    protected $casts = [
        'features' => 'array',
        'is_active' => 'boolean',
        'price' => 'integer',
        'discount_percentage' => 'float',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
