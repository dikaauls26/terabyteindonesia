<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'customer_no',
        'name',
        'email',
        'phone',
        'site_id',
        'plan_id',
        'is_active',
        'notes',
        'ont_brand',
        'ont_sn',
        'latitude',
        'longitude',
        'installed_at',
        'technician_name',
        'service_status',
    ];
    public function site()
    {
        return $this->belongsTo(Site::class);
    }
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
