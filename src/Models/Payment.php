<?php

namespace Puntodev\Payables\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Puntodev\Payables\Database\Factories\PaymentFactory;

class Payment extends Model
{
    use HasFactory;

    public const CREATED = 'created';
    public const PAID = 'paid';
    public const REFUNDED = 'refunded';

    protected $fillable = [
        'payment_method',
        'payment_reference',
        'payer_email',
        'status',
        'paid_on',
        'amount',
        'currency',
        'external_reference',
        'provider_id',
        'notified',
        'raw',
    ];

    protected $casts = [
        'raw' => 'array',
        'amount' => 'float',
        'notified' => 'boolean',
    ];

    protected $dates = [
        'paid_on',
    ];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function getAmountAttribute($amount)
    {
        return $amount / 100;
    }

    public function setAmountAttribute($amount)
    {
        $this->attributes['amount'] = $amount * 100;
    }

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    protected static function newFactory()
    {
        return PaymentFactory::new();
    }
}
