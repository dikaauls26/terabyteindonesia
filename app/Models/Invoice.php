<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_number',
        'customer_id',
        'site_id',
        'plan_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'site_name',
        'site_code',
        'site_address',
        'plan_name',
        'bandwidth_mbps',
        'price_inc_ppn',
        'billing_date',
        'billing_month',
        'due_date',
        'status',
        'notes',
    ];

    public function customer()
    {
        return $this->belongsTo(\App\Models\Customer::class);
    }
    public function site()
    {
        return $this->belongsTo(\App\Models\Site::class);
    }
    public function plan()
    {
        return $this->belongsTo(\App\Models\Plan::class);
    }
}
