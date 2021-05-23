<?php


namespace Puntodev\Payables\Concerns;


use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;
use Puntodev\Payables\Models\Order;
use Puntodev\Payables\Models\Payment;

/**
 * Trait Payable
 * @property Collection $orders
 * @property float $amount
 */
trait HasOrders
{
    public function orders(): MorphMany
    {
        return $this->morphMany(Order::class, 'payable');
    }

    public function isPaid(): bool
    {
        $paid = $this->orders
            ->filter(fn(Order $order) => $order->status !== Payment::CREATED)
            ->reduce(fn(float $carry, Order $order) => $carry + $order->amount * ($order->status === 'paid' ? 1 : -1), 0.0);

        return $this->amount <= $paid;
    }

    public function isRefunded(): bool
    {
        $refundTransactions = $this->orders
            ->filter(fn(Order $order) => $order->status === Payment::REFUNDED)
            ->count();

        return $refundTransactions > 0 && !$this->isPaid();
    }
//
//    public function paidOn(): ?Carbon
//    {
//        $lastPayment = $this->orders
//            ->filter(fn(Order $order) => $order->status === Payment::PAID)
//            ->sortBy('paid_on')
//            ->last();
//
//        return $this->isPaid() ? $lastPayment->paid_on : null;
//    }
}
