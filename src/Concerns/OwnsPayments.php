<?php


namespace Puntodev\Payables\Concerns;


use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;
use Puntodev\Payables\Models\Order;

/**
 * Trait Payable
 * @property Collection $orders
 */
trait OwnsPayments
{
    public function orders(): MorphMany
    {
        return $this->morphMany(Order::class, 'merchant');
    }
}
