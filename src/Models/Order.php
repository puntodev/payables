<?php

namespace Puntodev\Payables\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Puntodev\Payables\Database\Factories\OrderFactory;

class Order extends Model
{
    use HasFactory;

    public const CREATED = 'created';
    public const PAID = 'paid';
    public const REFUNDED = 'refunded';

    protected $fillable = [
        'payment_method',
        'status',
        'paid_on',
        'amount',
        'currency',
        'external_reference',
        'notified',
    ];

    protected $casts = [
        'amount' => 'float',
    ];

    protected $dates = [
        'paid_on',
    ];

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

    public function merchant(): MorphTo
    {
        return $this->morphTo();
    }

    protected static function newFactory()
    {
        return OrderFactory::new();
    }
}
