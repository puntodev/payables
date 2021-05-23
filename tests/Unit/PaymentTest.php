<?php


namespace Tests\Unit;


use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Puntodev\Payables\Models\Order;
use Puntodev\Payables\Models\Payment;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function amount_is_factored_by_100()
    {
        $payment = Payment::factory()->create([
            'amount' => 14,
        ]);
        $this->assertEquals(14, $payment->amount);
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'amount' => 1400,
        ]);
    }

    /** @test */
    function lookup_by_secondary_key()
    {
        $payment = Payment::factory()->create([
            'payment_reference' => 'P123',
        ]);

        $this->assertDatabaseHas('payments', [
            'order_id' => $payment->order->id,
            'payment_reference' => 'P123',
        ]);
    }

    /** @test */
    function secondary_key_is_unique()
    {
        $this->expectException(QueryException::class);

        $p1 = Payment::factory()->create([
            'payment_reference' => 'P123',
        ]);
        Payment::factory()->create([
            'order_id' => $p1->order->id,
            'payment_reference' => 'P123',
        ]);
    }

    /** @test */
    function order_relationship_is_morph()
    {
        $payment = Payment::factory()->create();
        $this->assertInstanceOf(BelongsTo::class, $payment->order());
        $this->assertInstanceOf(Order::class, $payment->order);
    }

    /** @test */
    function must_have_an_order()
    {
        $payment = Payment::factory()
            ->has(Order::factory(), 'order')
            ->create();
        $this->assertInstanceOf(Order::class, $payment->order);
    }

}
