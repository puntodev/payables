<?php


namespace Tests\Unit;


use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Puntodev\Payables\Models\Payment;
use Tests\TestCase;

class PaymentTest extends TestCase {

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
    function payable_relationship_is_morph() {
        $payment = Payment::factory()->create();
        $this->assertInstanceOf(MorphTo::class, $payment->payable());
    }

    /** @test */
    function lookup_by_secondary_key() {
        Payment::factory()->create([
            'payment_method' => 'mercado_pago',
            'payment_reference' => 'P123',
        ]);

        $this->assertDatabaseHas('payments', [
            'payment_method' => 'mercado_pago',
            'payment_reference' => 'P123',
        ]);
    }
    /** @test */
    function secondary_key_is_unique() {
        $this->expectException( QueryException::class);

        Payment::factory()->create([
            'payment_method' => 'mercado_pago',
            'payment_reference' => 'P123',
        ]);
        Payment::factory()->create([
            'payment_method' => 'mercado_pago',
            'payment_reference' => 'P123',
        ]);
    }
}
