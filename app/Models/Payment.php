<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model

{
    protected $fillable = ['invoice_id', 'provider', 'provider_ref', 'amount', 'paid_at', 'raw_webhook_json'];
}
