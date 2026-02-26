<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table = 'payments';
    
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    
    protected $fillable = [
        'id',
        'tenant_id',
        'stripe_payment_intent_id',
        'amount',
        'currency',
        'status',
        'plan',
        'stripe_invoice_id',
        'receipt_url',
        'paid_at',
        'refunded_at',
        'failure_message',
    ];
    
    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'refunded_at' => 'datetime',
        'created_at' => 'datetime',
    ];
}
