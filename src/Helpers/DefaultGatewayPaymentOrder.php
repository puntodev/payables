<?php


namespace Puntodev\Payables\Helpers;


use Puntodev\Payables\Contracts\GatewayPaymentOrder;

class DefaultGatewayPaymentOrder implements GatewayPaymentOrder
{
    /**
     * DefaultGatewayPaymentOrder constructor.
     */
    public function __construct(
        private string $gateway,
        private string $id,
        private string $redirectLink,
        private string $externalId,
    )
    {
    }

    public function gateway(): string
    {
        return $this->gateway;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function redirectLink(): string
    {
        return $this->redirectLink;
    }

    public function externalId(): string
    {
        return $this->externalId;
    }
}
