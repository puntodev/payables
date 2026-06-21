# Payables

[![Latest Version on Packagist](https://img.shields.io/packagist/v/puntodev/payables.svg?style=flat-square)](https://packagist.org/packages/puntodev/payables)
[![Total Downloads](https://img.shields.io/packagist/dt/puntodev/payables.svg?style=flat-square)](https://packagist.org/packages/puntodev/payables)

A gateway-agnostic payments abstraction for Laravel. Charge any of your Eloquent
models, collect payments through a payment gateway, and reconcile them automatically
from incoming webhooks.

The package ships its own `Order`/`Payment` models, migrations, webhook routes and a
`Payments` facade. It is built around small contracts that your application implements,
so the rest of your code never talks to a specific provider directly.

Currently bundled gateway: **MercadoPago** (via [`puntodev/mercadopago`](https://github.com/puntodev/mercadopago)).

## Requirements

- PHP `>=8.4`
- Laravel `^12.0`

## Installation

Install via composer:

```bash
composer require puntodev/payables
```

Publish and run the migrations:

```bash
php artisan vendor:publish --tag=migrations
php artisan migrate
```

This creates the `orders` and `payments` tables.

## How it works

The library models payments around a few small interfaces (`Puntodev\Payables\Contracts`):

| Contract | Implemented by | Purpose |
|----------|----------------|---------|
| `Payable` | your "thing to charge" model | Converts itself into a `PaymentOrder` |
| `PaymentOrder` | a value object you build | Checkout details: items, payer, back URLs |
| `PaymentOrderItem` | a value object you build | A single line item |
| `Merchant` | your account model (or the default) | Holds gateway credentials |
| `Gateway` | the package (e.g. MercadoPago) | Creates orders and processes webhooks |
| `GatewayPaymentOrder` | the package | Result of a checkout (redirect link, ids) |

There are also two traits to drop on your models:

- `HasOrders` — for your **payable** model. Adds `orders()`, `isPaid()`, `isRefunded()`, `paidOn()`.
- `OwnsPayments` — for your **merchant** model. Adds `orders()`.

> **Note:** amounts are stored in cents in the database, but the `Order`/`Payment`
> models expose them as decimals through accessors. Always read/write `amount` through
> the models.

## Usage

### 1. Make a model payable

```php
use Illuminate\Database\Eloquent\Model;
use Puntodev\Payables\Concerns\HasOrders;
use Puntodev\Payables\Contracts\Payable;
use Puntodev\Payables\Contracts\PaymentOrder;

class Product extends Model implements Payable
{
    use HasOrders;

    public function toPaymentOrder(): PaymentOrder
    {
        return new ProductPaymentOrder($this);
    }
}
```

Your `PaymentOrder` implementation describes the checkout — its items, the payer's
details, the URLs MercadoPago should redirect to, excluded payment methods and an
optional expiration. Each item implements `PaymentOrderItem` (`amount`, `quantity`,
`currency`, `description`).

### 2. Create a checkout

Using a specific merchant (a model that implements `Merchant` and uses `OwnsPayments`):

```php
use Puntodev\Payables\Facades\Payments;

$order = Payments::checkout('mercado_pago', $product, $merchant);

return redirect($order->redirectLink());
```

Or, if you operate a single MercadoPago account, use the default merchant
(credentials read from `config('mercadopago.*')`):

```php
$order = Payments::checkoutForDefaultMerchant('mercado_pago', $product);

return redirect($order->redirectLink());
```

This persists a local `Order`, creates the payment preference on the gateway, and
returns a `GatewayPaymentOrder` exposing `gateway()`, `id()`, `redirectLink()` and
`externalId()`.

### 3. Receive webhooks

The package registers webhook routes automatically (prefixed with `payments` by
default):

- `POST /payments/{gateway}/{merchantType}/{merchantId}` — per-merchant
- `POST /payments/{gateway}` — default merchant

Point your gateway's notification URL there (it is set automatically when the order is
created). Incoming notifications are handled by a queued `StorePayment` job that fetches
the order from the gateway and upserts the corresponding `Payment`, mapping it to
`paid` / `refunded` / `created`.

### 4. Query payment status

```php
$product->isPaid();     // bool
$product->isRefunded(); // bool
$product->paidOn();     // ?Carbon
```

## Configuration

Publish the config if you need to customize gateways, the route prefix or middleware:

```bash
php artisan vendor:publish --provider="Puntodev\Payables\PaymentsServiceProvider"
```

```php
// config/payments.php
return [
    'gateways'   => ['mercado_pago' => MercadoPagoGateway::class],
    'prefix'     => 'payments',
    'middleware' => 'web',
];
```

### Adding a new gateway

Implement `Puntodev\Payables\Contracts\Gateway`, register it in the `gateways` map, and
bind it in a service provider if it has constructor dependencies. The rest of your code
keeps using the `Payments` facade unchanged.

## Testing

```bash
composer test                 # run the test suite
composer test-f <filter>      # run a single test by name
composer test-coverage        # generate an HTML coverage report
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email mariano.goldman@puntodev.com.ar
instead of using the issue tracker.

## Credits

- [Mariano Goldman](https://github.com/marianogoldman)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
