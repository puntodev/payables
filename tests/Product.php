<?php


namespace Tests;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Puntodev\Payables\Concerns\HasOrders;
use Puntodev\Payables\Contracts\Payable;
use Puntodev\Payables\Contracts\PaymentOrder;

class Product extends Model implements Payable
{
    use HasOrders;
    use HasFactory;

    protected $guarded = [];

    protected $table = 'products';

    protected static function newFactory()
    {
        return ProductFactory::new();
    }

    public function toPaymentOrder(): PaymentOrder
    {
        return new ProductPaymentOrder($this);
    }
}
