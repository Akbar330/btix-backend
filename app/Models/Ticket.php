<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $guarded = [];

    protected $casts = [
        'event_date' => 'datetime',
    ];

    public const STATUSES = [
        'draft',
        'published',
        'sold_out',
        'ended',
    ];
}
