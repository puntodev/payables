<?php


namespace Tests\Unit;


use Illuminate\Foundation\Testing\RefreshDatabase;
use Puntodev\Payables\Models\Payment;
use Tests\TestCase;

class PaymentTest extends TestCase {

    use RefreshDatabase;

    /** @test */
    function amount_has_is_factored_by_100()
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
}
