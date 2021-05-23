<?php


namespace Tests\Unit;


use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Puntodev\Payables\Models\Order;
use Puntodev\Payables\Models\Payment;
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
    function can_have_a_payable()
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
        $order = Order::factory()
            ->has(Product::factory(['amount' => '100']), 'payable')
            ->has(Payment::factory(['amount' => '100']), 'payments')
            ->create(['amount' => 100]);

        $this->assertTrue($order->payable->isPaid());
    }

    /** @test */
    function is_not_paid()
    {
        $order = Order::factory()
            /** @test */
            ->has(Payment::factory(['amount' => '50']), 'payments')
            ->create(['amount' => 50]);

        $this->assertFalse($order->payable->isPaid());
    }

    /** @test */
    function is_refunded()
    {
        /** @var Order $order */
        $order = Order::factory()
            ->has(Product::factory(['amount' => '100']), 'payable')
            ->has(Payment::factory(['amount' => '100', 'status' => Payment::PAID]), 'payments')
            ->has(Payment::factory(['amount' => '100', 'status' => Payment::REFUNDED]), 'payments')
            ->create(['amount' => 100]);

        $this->assertTrue($order->payable->isRefunded());
        $this->assertFalse($order->payable->isPaid());
    }

    /** @test */
    function is_refunded_but_then_paid_again()
    {
        /** @var Order $order */
        $order = Order::factory()
            ->has(Product::factory(['amount' => '100']), 'payable')
            ->has(Payment::factory(['amount' => '100', 'status' => Payment::PAID]), 'payments')
            ->has(Payment::factory(['amount' => '100', 'status' => Payment::REFUNDED]), 'payments')
            ->has(Payment::factory(['amount' => '100', 'status' => Payment::PAID]), 'payments')
            ->create(['amount' => 100]);

        $this->assertFalse($order->payable->isRefunded());
        $this->assertTrue($order->payable->isPaid());
    }

    /** @test */
    function is_paid_on()
    {
        $paidOn = Carbon::yesterday();
        $order = Order::factory()
            ->has(Product::factory(['amount' => '100']), 'payable')
            ->has(Payment::factory(['amount' => 100, 'status' => Payment::PAID, 'paid_on' => $paidOn]))
            ->create(['amount' => '100']);

        $this->assertTrue($order->payable->isPaid());
        $this->assertEquals($paidOn, $order->payable->paidOn());
    }

    /** @test */
    function is_paid_on_after_refund()
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $order = Order::factory()
            ->has(Product::factory(['amount' => '100']), 'payable')
            ->has(Payment::factory(['amount' => 100, 'status' => Payment::PAID, 'paid_on' => $today]))
            ->has(Payment::factory(['amount' => 100, 'status' => Payment::PAID, 'paid_on' => $yesterday]))
            ->has(Payment::factory(['amount' => 100, 'status' => Payment::REFUNDED, 'paid_on' => $yesterday]))
            ->create(['amount' => '100']);

        $this->assertTrue($order->payable->isPaid());
        $this->assertEquals($today, $order->payable->paidOn());
    }
}
