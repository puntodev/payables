<?php


namespace Tests\Unit;


use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Puntodev\Payables\Models\Order;
use Tests\Product;
use Tests\TestCase;
use Tests\User;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function amount_is_factored_by_100()
    {
        $order = Order::factory()->create([
            'amount' => 14,
        ]);
        $this->assertEquals(14, $order->amount);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'amount' => 1400,
        ]);
    }

    /** @test */
    function payable_relationship_is_morph()
    {
        $payment = Order::factory()->create();
        $this->assertInstanceOf(MorphTo::class, $payment->payable());
    }

    /** @test */
    function merchant_relationship_is_morph()
    {
        $payment = Order::factory()->create();
        $this->assertInstanceOf(MorphTo::class, $payment->merchant());
    }

    /** @test */
    function can_have_a_merchant()
    {
        $order = Order::factory()
            ->has(User::factory(), 'merchant')
            ->create();
        $this->assertInstanceOf(User::class, $order->merchant);

        /** @var User $user */
        $user = $order->refresh()->merchant;

        $this->assertEquals(1, $user->orders->count());
        $this->assertTrue($order->is($user->orders->first()));
    }

    /** @test */
    function can_have_a_product()
    {
        $payment = Order::factory()
            ->has(Product::factory(), 'payable')
            ->create();
        $this->assertInstanceOf(Product::class, $payment->payable);

        /** @var Product $product */
        $product = $payment->refresh()->payable;

        $this->assertEquals(1, $product->orders->count());
        $this->assertTrue($payment->is($product->orders->first()));
    }

    /** @test */
    function is_paid()
    {
        $payment = Order::factory()
            ->has(Product::factory(['amount' => '100']), 'payable')
            ->create(['amount' => 100]);

        $this->assertTrue($payment->payable->isPaid());
    }

    /** @test */
    function is_not_paid()
    {
        $payment = Order::factory()
            ->has(Product::factory(['amount' => '100']), 'payable')
            ->create(['amount' => 50]);

        $this->assertFalse($payment->payable->isPaid());
    }

    /** @test */
    function is_refunded()
    {
        $product = Product::factory()
            ->has(Order::factory(['amount' => 100, 'status' => Order::PAID]))
            ->has(Order::factory(['amount' => 100, 'status' => Order::REFUNDED]))
            ->create(['amount' => '100']);

        $this->assertTrue($product->isRefunded());
        $this->assertFalse($product->isPaid());

    }

    /** @test */
    function is_refunded_but_then_paid_again()
    {
        $product = Product::factory()
            ->has(Order::factory(['amount' => 100, 'status' => Order::PAID]))
            ->has(Order::factory(['amount' => 100, 'status' => Order::REFUNDED]))
            ->has(Order::factory(['amount' => 100, 'status' => Order::PAID]))
            ->create(['amount' => '100']);

        $this->assertFalse($product->isRefunded());
        $this->assertTrue($product->isPaid());
    }
//
//    /** @test */
//    function is_paid_on()
//    {
//        $paidOn = Carbon::yesterday();
//        $product = Product::factory()
//            ->has(Order::factory(['amount' => 100, 'status' => Order::PAID, 'paid_on' => $paidOn]))
//            ->create(['amount' => '100']);
//
//        $this->assertTrue($product->isPaid());
//        $this->assertEquals($paidOn, $product->paidOn());
//    }
//
//    /** @test */
//    function is_paid_on_after_refund()
//    {
//        $today = Carbon::today();
//        $yesterday = Carbon::yesterday();
//        $product = Product::factory()
//            ->has(Order::factory(['amount' => 100, 'status' => Order::PAID, 'paid_on' => $today]))
//            ->has(Order::factory(['amount' => 100, 'status' => Order::PAID, 'paid_on' => $yesterday]))
//            ->has(Order::factory(['amount' => 100, 'status' => Order::REFUNDED, 'paid_on' => $yesterday]))
//            ->create(['amount' => '100']);
//
//        $this->assertTrue($product->isPaid());
//        $this->assertEquals($today, $product->paidOn());
//    }
}
