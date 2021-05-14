<?php


namespace Puntodev\Payables\Concerns;


use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;
use Puntodev\Payables\Models\Payment;

/**
 * Trait Payable
 * @property Collection $payments
 */
trait Merchant
{
    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'merchant');
    }
}
