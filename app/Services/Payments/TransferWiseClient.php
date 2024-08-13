<?php

namespace App\Services\Payments;

use App\Models\Payments\Payment;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TransferWiseClient
{
    /** @var string */
    protected string $accessToken;

    /** @var array<string,string> */
    protected array $defaultHeaders = [
        'Accept' => 'application/json',
    ];

    /** @var Client|null */
    protected $client;

    public function getClient(): Client
    {
        if (!is_null($this->client)) {
            return $this->client;
        }
        $this->client = new Client([
            'base_uri' => config('services.transferwise.url'),
        ]);

        return $this->client;
    }

    /**
     * Correctly format the response from Transferwise.
     *
     * @codeCoverageIgnore
     *
     * @param Response $response
     *
     * @return mixed
     */
    public function getResponse(Response $response)
    {
        try {
            return json_decode($response->getBody(), true);
        } catch (Exception $ex) {
            return false;
        }
    }

    /**
     * For now we're only using the rates API, so we're just using the limited hard coded API
     * token in the sandbox.
     *
     * @return string
     **/
    protected function getAccessToken(): string
    {
        return config('services.transferwise.access_token');
    }

    /**
     * Helper function to get the recipients of the Transferwise account.
     *
     * @param string $source
     *
     * @return Collection<array<string,mixed>>
     */
    public function getRates(string $source = 'GBP'): Collection
    {
        $key = "transferwise_rates_{$source}";

        if (Cache::has($key)) {
            // @codeCoverageIgnoreStart
            return Cache::get($key);
        }
        // @codeCoverageIgnoreEnd

        $headers = array_merge($this->defaultHeaders, [
            'Authorization' => 'Bearer ' . $this->getAccessToken(),
        ]);

        $options = [
            'headers' => $headers,
        ];

        try {
            /** @var Response */
            $response = $this->getClient()->get("/v1/rates?source={$source}", $options);

            /** @var Collection<array<string,mixed>> */
            $rates = collect($this->getResponse($response));

            Cache::put($key, $rates, now()->addHour());

            return $rates;
            // @codeCoverageIgnoreStart
        } catch (RequestException $ex) {
            Log::error($ex->getMessage());

            /** @var Collection<array<string,mixed>> */
            return collect();
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * Convert amount from one source to another.
     *
     * @param string $source
     * @param string $target
     * @param float  $sourceAmount
     *
     * @return float
     */
    public function convert(string $source, string $target, float $sourceAmount): float
    {
        if ($source === $target) {
            return $sourceAmount;
        }

        if (!$rate = $this->getRates($source)->where('target', $target)->first()) {
            return 0.0;
        }

        return round($sourceAmount * $rate['rate'], 2);
    }

    /**
     * This was brought in from the old code base. Just keeping it here if we ever want to implement more
     * payment functionality.
     */

    // /**
    //  * Helper function to get the profile id of the Transferwise account.
    //  *
    //  * @return mixed
    //  */
    // public function getProfile()
    // {
    //     $headers = array_merge($this->defaultHeaders, [
    //         'Authorization' => 'Bearer ' . $this->getAccessToken(),
    //     ]);

    //     $options = [
    //         'headers' => $headers,
    //     ];

    //     try {
    //         $endpoint = '/v1/profiles';

    //         $response = $this->getClient()->get($endpoint, $options);

    //         return $this->getResponse($response);
    //     } catch (RequestException $ex) {
    //         Log::error($ex->getMessage());

    //         return false;
    //     }
    // }

    // /**
    //  * Helper function to get the recipients of the Transferwise account.
    //  *
    //  * @param string|null $currency
    //  *
    //  * @return mixed
    //  */
    // public function getRecipients(?string $currency = null)
    // {
    //     $query = ['profile' => config('services.transferwise.profile-id')];

    //     if ($currency) {
    //         $query['currency'] = $currency;
    //     }

    //     $headers = array_merge($this->defaultHeaders, [
    //         'Authorization' => 'Bearer ' . $this->getAccessToken(),
    //     ]);

    //     $options = [
    //         'headers' => $headers,
    //     ];

    //     try {
    //         $endpoint = '/v1/accounts?' . http_build_query($query);

    //         $response = $this->getClient()->get($endpoint, $options);

    //         return $this->getResponse($response);
    //     } catch (RequestException $ex) {
    //         Log::error($ex->getMessage());

    //         return false;
    //     }
    // }

    // /**
    //  * Generate a quote from Transferwise for a payment.
    //  *
    //  * @param Payment $payment
    //  *
    //  * @return mixed
    //  */
    // public function createQuote(Payment $payment)
    // {
    //     $headers = array_merge($this->defaultHeaders, [
    //         'Authorization' => 'Bearer ' . $this->getAccessToken(),
    //     ]);

    //     $options = [
    //         'headers' => $headers,
    //     ];

    //     try {
    //         $endpoint = '/v1/quotes';

    //         $body = [
    //             'json' => [
    //                 'profile' => config('services.transferwise.profile-id'),
    //                 'source' => $payment->source_currency,
    //                 'target' => $payment->target_currency,
    //                 'targetAmount' => $payment->target_amount,
    //                 'rateType' => config('services.transferwise.rate-type'),
    //                 'type' => config('services.transferwise.payout-type'),
    //             ],
    //         ];

    //         $response = $this->getClient()->post($endpoint, array_merge($options, $body));

    //         return $this->getResponse($response);
    //     } catch (RequestException $ex) {
    //         Log::error($ex->getMessage());

    //         return false;
    //     }
    // }

    // /**
    //  * Request the creation of a new transfer from Transferwise.
    //  *
    //  * @param Librarian\Models\Payment $payment
    //  *
    //  * @return mixed
    //  */
    // public function createTransfer(Payment $payment)
    // {
    //     $headers = array_merge($this->defaultHeaders, [
    //         'Authorization' => 'Bearer ' . $this->getAccessToken(),
    //     ]);

    //     $options = [
    //         'headers' => $headers,
    //     ];

    //     try {
    //         $endpoint = '/v1/transfers';

    //         $body = [
    //             'json' => [
    //                 'targetAccount' => $payment->team->account_id,
    //                 'quote' => $payment->quote_id,
    //                 'customerTransactionId' => $payment->customer_transaction_id,
    //                 'details' => [
    //                     'reference' => config('services.transferwise.turk_payment_reference'),
    //                 ],
    //             ],
    //         ];

    //         $response = $this->getClient()->post($endpoint, array_merge($options, $body));

    //         return $this->getResponse($response);
    //     } catch (RequestException $ex) {
    //         Log::error($ex->getMessage());

    //         return false;
    //     }
    // }

    // /**
    //  * Fund a transfer that has been submitted for a payment.
    //  *
    //  * @param Librarian\Models\Payment $payment
    //  *
    //  * @return mixed
    //  */
    // public function fundTransfer(Payment $payment)
    // {
    //     $headers = array_merge($this->defaultHeaders, [
    //         'Authorization' => 'Bearer ' . $this->getAccessToken(),
    //     ]);

    //     $options = [
    //         'headers' => $headers,
    //     ];

    //     try {
    //         $endpoint = "/v1/transfers/{$payment->transfer_id}/payments";

    //         $body = [
    //             'json' => [
    //                 'type' => config('services.transferwise.fund-type'),
    //             ],
    //         ];

    //         $response = $this->getClient()->post($endpoint, array_merge($options, $body));

    //         return $this->getResponse($response);
    //     } catch (RequestException $ex) {
    //         Log::error($ex->getMessage());

    //         return false;
    //     }
    // }

    // /**
    //  * Cancel a transfer that has been submitted.
    //  *
    //  * @param Librarian\Models\Payment $payment
    //  *
    //  * @return mixed
    //  */
    // public function cancelTransfer(Payment $payment)
    // {
    //     $headers = array_merge($this->defaultHeaders, [
    //         'Authorization' => 'Bearer ' . $this->getAccessToken(),
    //     ]);

    //     $options = [
    //         'headers' => $headers,
    //     ];

    //     try {
    //         $endpoint = "/v1/transfers/{$payment->transfer_id}/cancel";

    //         $response = $this->getClient()->put($endpoint, $options);

    //         return $this->getResponse($response);
    //     } catch (RequestException $ex) {
    //         Log::error($ex->getMessage());

    //         return false;
    //     }
    // }

    // /**
    //  * Poll the transferwise server for the status of a transfer.
    //  *
    //  * @param Librarian\Models\Payment $payment
    //  *
    //  * @return mixed
    //  */
    // public function getTransferStatus(Payment $payment)
    // {
    //     $headers = array_merge($this->defaultHeaders, [
    //         'Authorization' => 'Bearer ' . $this->getAccessToken(),
    //     ]);

    //     $options = [
    //         'headers' => $headers,
    //     ];

    //     try {
    //         $endpoint = "/v1/transfers/{$payment->transfer_id}";

    //         $response = $this->getClient()->get($endpoint, $options);

    //         return $this->getResponse($response);
    //     } catch (RequestException $ex) {
    //         Log::error($ex->getMessage());

    //         return false;
    //     }
    // }

    // /**
    //  * Get any issues associated with the transfer.
    //  *
    //  * @param Librarian\Models\Payment $payment
    //  *
    //  * @return mixed
    //  */
    // public function getTransferIssues(Payment $payment)
    // {
    //     $headers = array_merge($this->defaultHeaders, [
    //         'Authorization' => 'Bearer ' . $this->getAccessToken(),
    //     ]);

    //     $options = [
    //         'headers' => $headers,
    //     ];

    //     try {
    //         $endpoint = "/v1/transfers/{$payment->transfer_id}/issues";

    //         $response = $this->getClient()->get($endpoint, $options);

    //         return $this->getResponse($response);
    //     } catch (RequestException $ex) {
    //         Log::error($ex->getMessage());

    //         return false;
    //     }
    // }

    // /**
    //  * Changes a transfer status to processing.
    //  *
    //  * @param Librarian\Models\Payment $payment
    //  *
    //  * @return mixed
    //  */
    // public function simulateProcessing(Payment $payment)
    // {
    //     $headers = array_merge($this->defaultHeaders, [
    //         'Authorization' => 'Bearer ' . $this->getAccessToken(),
    //     ]);

    //     $options = [
    //         'headers' => $headers,
    //     ];

    //     try {
    //         $endpoint = "/v1/simulation/transfers/{$payment->transfer_id}/processing";

    //         $response = $this->getClient()->get($endpoint, $options);

    //         return $this->getResponse($response);
    //     } catch (RequestException $ex) {
    //         Log::error($ex->getMessage());

    //         return false;
    //     }
    // }

    // /**
    //  * Get the access token.
    //  *
    //  * @return string
    //  **/
    // protected function requestToken(): string
    // {
    //     $response = $this->getClient()->post('/oauth/token', [
    //         'headers' => [
    //             'Accept' => 'application/json',
    //         ],
    //         'form_params' => [
    //             'grant_type' => 'refresh_token',
    //             'refresh_token' => config('services.transferwise.refresh_token'),
    //         ],
    //         'auth' => [config('services.transferwise.client_id'), config('services.transferwise.client_secret')],
    //     ]);

    //     $r = json_decode((string) $response->getBody(), true);
    //     $this->accessToken = $r['access_token'];

    //     Cache::put(
    //         config('services.transferwise.token_cache_key'),
    //         $this->accessToken,
    //         ((int) $r['expires_in'] / 60) - 10
    //     );

    //     return $this->accessToken;
    // }
}
