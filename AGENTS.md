# AGENTS.md

Guidance for AI coding agents working in this repository.

## What this is

`puntodev/payables` is a **Laravel package** (PHP `^8.0`, `illuminate/support ^8.0`) that
provides a gateway-agnostic abstraction for charging customers and reconciling payments.
It ships its own Eloquent models, migrations, webhook routes and a facade.

Today the only implemented gateway is **MercadoPago**, built on top of the
`puntodev/mercadopago` package (`^2.0.0`).

- Namespace: `Puntodev\Payables\` → `src/`
- Factories namespace: `Puntodev\Payables\Database\Factories\` → `database/factories/`
- Tests namespace: `Tests\` → `tests/`
- Auto-discovered service provider: `Puntodev\Payables\PaymentsServiceProvider`
- Facade alias: `Payments`

## Core domain concepts

The package is built around small **contracts** (`src/Contracts/`) that the host
application implements on its own models:

- **`Payable`** — anything that can be charged. Implements `toPaymentOrder(): PaymentOrder`.
- **`PaymentOrder`** — the checkout details (items, payer name/email, back URLs,
  excluded payment methods, optional expiration).
- **`PaymentOrderItem`** — line item (amount, quantity, currency, description).
- **`Merchant`** — credentials holder (`clientId()`, `clientSecret()`).
- **`Gateway`** — a payment provider: `createOrder()`, `processWebhook()`, `defaultMerchant()`.
- **`GatewayPaymentOrder`** — the result of a checkout (gateway name, id, redirect link,
  external id). Default impl: `src/Helpers/DefaultGatewayPaymentOrder.php`.

### Entry point: the `Payments` facade

`src/Payments.php` (exposed via `src/Facades/Payments.php`) is the public API:

- `checkout(string $gateway, Payable $payable, Merchant $merchant): GatewayPaymentOrder`
- `checkoutForDefaultMerchant(string $gateway, Payable $payable): GatewayPaymentOrder`

It resolves the gateway class from `config('payments.gateways')` and throws
`Puntodev\Payables\Exceptions\InvalidGateway` when the gateway key is unknown.

### Models (`src/Models/`)

- **`Order`** — local record created at checkout. Polymorphic `payable` (what is being
  paid) and nullable polymorphic `merchant`. Statuses: `created`, `paid`, `refunded`.
- **`Payment`** — belongs to an `Order`, one per gateway payment reference. Stores the raw
  gateway payload in `raw` (json cast). Statuses: `created`, `paid`, `refunded`.

> **Important — money is stored in cents.** Both models define
> `getAmountAttribute`/`setAmountAttribute` accessors that multiply/divide by 100. Always
> work with decimal amounts through the model attributes; the DB column is an integer.

### Traits / Concerns (`src/Concerns/`)

Apply these to host-app models:

- **`HasOrders`** — for a `Payable` model. Adds `orders()` (morphMany) plus
  `isPaid()`, `isRefunded()`, `paidOn()`. Requires the model to expose an `$amount`.
- **`OwnsPayments`** — for a `Merchant` model. Adds `orders()` (morphMany as `merchant`).

## Payment flow

1. **Checkout** — `Payments::checkout(...)` → `MercadoPagoGateway::createOrder()`:
   - persists a local `Order` (uuid, amount summed from items, currency),
   - builds a MercadoPago payment preference (items, payer, back URLs, notification URL,
     `binaryMode(true)`),
   - returns a `GatewayPaymentOrder` with the redirect link (sandbox vs prod init point
     depends on `MercadoPago::usingSandbox()`).
2. **Webhook** — MercadoPago calls back to routes in `routes/web.php`:
   - `POST /{prefix}/{gateway}/{merchantType}/{merchantId}` → `incoming` (named
     `payments.incoming`), resolves the merchant by morph type/id.
   - `POST /{prefix}/{gateway}` → `incomingDefault` (named `payments.incoming.default`),
     uses `DefaultMercadoPagoMerchant`.
   - The controller (`PaymentsWebhookController`) dispatches the **`StorePayment`** queued
     job, which calls `Gateway::processWebhook()`.
3. **Reconciliation** — `processWebhook()` fetches the merchant order from MercadoPago,
   matches the local `Order` by `uuid` (external_reference), and upserts a `Payment`
   mapping status (`paid` / `refunded` / `created`) from paid vs refunded vs total amounts.

The default merchant (`DefaultMercadoPagoMerchant`) reads credentials from
`config('mercadopago.client_id'|'client_secret')` — used when the app has a single
MercadoPago account instead of per-merchant credentials.

## Configuration

`config/payments.php` (merged via the service provider):

```php
'gateways'   => ['mercado_pago' => MercadoPagoGateway::class],
'prefix'     => 'payments',   // route prefix
'middleware' => 'web',        // route middleware group
```

Register a new gateway by implementing `Contracts\Gateway` and adding it to the
`gateways` map. Bind it as a singleton in `PaymentsServiceProvider::register()` if it has
constructor dependencies (see the `MercadoPagoGateway` binding).

## Migrations

Published as stubs from `database/migrations/*.php.stub` (the `migrations` tag). Tables:

- `orders` — `uuid` (unique), `payment_method`, polymorphic `payable`, nullable
  polymorphic `merchant`, `status`, `currency`, integer `amount` (cents).
- `payments` — `order_id` FK, `payment_reference`, `status`, `paid_on`, `currency`,
  integer `amount` (cents), `raw` json. Unique on `(order_id, payment_reference)`.

The `users`/`products` stubs exist only for the test suite.

## Testing

Built on **Orchestra Testbench** (`tests/TestCase.php`). The base test case includes the
migration stubs and runs their `up()` in `getEnvironmentSetUp`, and registers both the
`PaymentsServiceProvider` and `MercadoPagoServiceProvider`.

Test doubles live at the `tests/` root: `Product` + `ProductPaymentOrder(Item)`
(a `Payable`) and `User` (a `Merchant`), with their factories.

Commands (see `composer.json` scripts):

```bash
composer test                  # vendor/bin/phpunit
composer test-f <filter>       # run a single test by name
composer test-coverage         # HTML coverage report
```

DB connection for tests is `testing` (in-memory sqlite via Testbench), configured in
`phpunit.xml.dist`.

## Conventions for agents

- Match the existing style: typed properties, constructor property promotion, arrow
  functions, `Illuminate\Support\Arr`/collections.
- Gateway identifier strings are snake_case (e.g. `mercado_pago`) and must stay in sync
  between `config/payments.php`, the `Order::$payment_method`, and the
  `GatewayPaymentOrder::gateway()` value.
- Keep money handling consistent: never write raw integers to `amount`; go through the
  model accessors.
- When adding behavior, add a corresponding test under `tests/Unit` or `tests/Feature`
  and a test double under `tests/` if a new contract implementation is needed.
- Status constants live on the models (`Order::CREATED|PAID|REFUNDED`,
  `Payment::CREATED|PAID|REFUNDED`); use them rather than string literals.
