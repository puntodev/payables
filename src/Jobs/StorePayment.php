<?php


namespace Puntodev\Payables\Jobs;


use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Puntodev\Payables\Contracts\Gateway;
use Puntodev\Payables\Contracts\Merchant;

class StorePayment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public string $gateway, public Merchant $merchant, public array $data)
    {
    }

    public function handle()
    {
        /** @var Gateway $gateway */
        $gateway = app(config('payments.gateways')[$this->gateway]);

        return $gateway->processWebhook($this->merchant, $this->data);
    }
}
