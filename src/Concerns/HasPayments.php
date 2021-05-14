<?php


namespace Puntodev\Payables\Concerns;


use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;
use Puntodev\Payables\Models\Payment;

/**
 * Trait Payable
 * @property Collection $payments
 * @property float $amount
 */
trait HasPayments
{
    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    public function isPaid(): bool
    {
        $paid = $this->payments
            ->filter(fn(Payment $payment) => $payment->status !== 'created')
            ->reduce(fn(float $carry, Payment $item) => $carry + $item->amount * ($item->status === 'paid' ? 1 : -1), 0.0);

        return $this->amount <= $paid;
    }

    public function isRefunded(): bool
    {
        $refundTransactions = $this->payments
            ->filter(fn(Payment $payment) => $payment->status === 'refunded')
            ->count();

        return $refundTransactions > 0 && !$this->isPaid();
    }

    public function paidOn(): ?Carbon
    {
        $lastPayment = $this->payments
            ->filter(fn(Payment $payment) => $payment->status === 'paid')
            ->sortBy('paid_on')
            ->last();

        return $this->isPaid() ? $lastPayment->paid_on : null;
    }
}
