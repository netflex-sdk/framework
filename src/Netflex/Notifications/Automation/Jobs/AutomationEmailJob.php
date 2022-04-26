<?php

namespace Netflex\Notifications\Automation\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Netflex\API\Facades\API;
use Netflex\Customers\Customer;

class AutomationEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $newsletterId;
    public $customerId;
    public $payload;
    public $locale;

    protected function __construct($newsletterId)
    {
        $this->newsletterId = $newsletterId;
        $this->locale = app()->getLocale();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->locale = app()->getLocale();

        return API::post('relations/newsletters/send/single/' . $this->newsletterId, [
            'customer_id' => $this->customerId,
            'data' => $this->payload
        ]);
    }

    /**
     * @param int|null $newsletterId
     * @param int|null $customerId
     * @param array|null $payload
     * @return static
     */
    public static function make($newsletterId, $customerId = null, ?array $payload = [])
    {
        $automationEmail = new static($newsletterId);
        $automationEmail->newsletterId = $newsletterId;
        $automationEmail->customerId = $customerId;
        $automationEmail->payload = $payload;
        return $automationEmail;
    }

    public function send(?Carbon $sendAt = null)
    {
        return dispatch($this)->delay($sendAt);
    }

    public function withPayload(array $payload = [])
    {
        $this->payload = $payload;
        return $this;
    }

    public function toCustomer($customer)
    {
        if ($customer instanceof Customer) {
            $this->customerId = $customer->id;
        } else {
            $this->customerId = $customer;
        }

        return $this;
    }
}
