<?php


namespace Tests\Unit;


use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Puntodev\Payables\Models\Payment;
use Tests\Product;
use Tests\TestCase;
use Tests\User;

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

    /** @test */
    function payable_relationship_is_morph() {
        $payment = Payment::factory()->create();
        $this->assertInstanceOf(MorphTo::class, $payment->payable());
    }

    /** @test */
    function merchant_relationship_is_morph() {
        $payment = Payment::factory()->create();
        $this->assertInstanceOf(MorphTo::class, $payment->merchant());
    }

    /** @test */
    function can_have_a_merchant() {
        $payment = Payment::factory()
            ->has(User::factory(), 'merchant')
            ->create();
        $this->assertInstanceOf(User::class, $payment->merchant);

        /** @var User $user */
        $user = $payment->refresh()->merchant;

        $this->assertEquals(1, $user->payments->count());
        $this->assertTrue($payment->is($user->payments->first()));
    }

    /** @test */
    function can_have_a_product() {
        $payment = Payment::factory()
            ->has(Product::factory(), 'payable')
            ->create();
        $this->assertInstanceOf(Product::class, $payment->payable);

        /** @var Product $product */
        $product = $payment->refresh()->payable;

        $this->assertEquals(1, $product->payments->count());
        $this->assertTrue($payment->is($product->payments->first()));
    }

}
