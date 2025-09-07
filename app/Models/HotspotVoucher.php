<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class HotspotVoucher extends Model
{
    protected $fillable = [
        'code','profile','duration_minutes','quota_mb','price','currency',
        'status','batch_id','router','buyer_name','buyer_email','buyer_phone',
        'reserved_at','sold_at','redeemed_at','expired_at','is_active','notes'
    ];

    protected $casts = [
        'reserved_at' => 'datetime',
        'sold_at' => 'datetime',
        'redeemed_at' => 'datetime',
        'expired_at' => 'datetime',
        'is_active' => 'boolean',
        'price' => 'integer',
        'duration_minutes' => 'integer',
        'quota_mb' => 'integer',
    ];

    // scope umum
    public function scopeAvailable(Builder $q): Builder {
        return $q->where('status','available')->where('is_active',1);
    }
}
