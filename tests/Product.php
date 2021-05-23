<?php


namespace Tests;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Puntodev\Payables\Concerns\HasOrders;

class Product extends Model
{
    use HasOrders;
    use HasFactory;

    protected $guarded = [];

    protected $table = 'products';

    protected static function newFactory()
    {
        return ProductFactory::new();
    }
}
