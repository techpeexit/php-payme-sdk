<?php

namespace PaymeQuantum\PaymentSdk;

use GuzzleHttp\Exception\RequestException;
use PaymeQuantum\PaymentSdk\Services\Repository;

class Payment
{
    private $email;
    private $password;
    private $apiKey;
    private $merchant;
    private $token;
    private $account;
    protected $externalDataService;

    public function __construct($email, $password, $apiKey)
    {
        $this->email = $email;
        $this->password = $password;
        $this->apiKey = $apiKey;
        $this->token = '';
        $this->externalDataService = new Repository();
        $this->init();
    }

    /**
     * This function checks if a merchant with a specific key exists
     * @param string $key apiKey of the merchant
     */
    public function init()
    {
        $resp = $this->externalDataService->postDataFromOnboarding(
            'users/developer/authenticate',
            [
                'email' => $this->email,
                'password' => $this->password,
                'subscription_key' => $this->apiKey,
            ]
        );

        if (!$resp || empty($resp['data'])) {
            throw new \Exception('Unable to fetch account related to the key provided');
        }
        $this->merchant = $resp['data']['user'];
        $this->account = $this->merchant['individualProfiles'][0];
        $this->token = $resp['data']['token'];
        
        // echo json_encode($this->account) . "\n";
        // echo "Token: " . $this->token . "\n";
        // dd($resp);

        // Set the token in the external data service
        $this->externalDataService->setToken($this->token);
    }

    /**
     * This function calculates the applicable fees of a payment
     * @param float $amount amount of the payment
     * @param string $country country where the payment will be done
     */
    public function getFees($amount, $country)
    {
        try {
            $resp = $this->externalDataService->getDataFromBilling('fees?filter[where][and][0][min_amount][lte]='.$amount.'&filter[where][and][1][max_amount][gte]='.$amount);

            if (!$resp || empty($resp)) {
                throw new \Exception('Fees have not yet been defined for this amount, please contact support');
            }

            return array_intersect_key($resp[0], array_flip([
                'operation_type',
                'corridor_tag',
                'operand',
                'min_amount',
                'max_amount',
                'value'
            ]));
        } catch (RequestException $e) {
            throw new \Exception('Error fetching fees: ' . $e->getMessage());
        }
    }

    /**
    * 
    * This function registers a payment / simple / partial / grouped
    * @param array $param (reference, amount, fees, tva, description)
    *
    **/
    public function postPayment($param)
    {
        try {
            // Add account_id to parameters
            $data = array_merge($param, ['account_id' => $this->account['id']]);
            return $this->externalDataService->postDataFromPayment('transactions', $data);
        } catch (RequestException $e) {
            throw new \Exception('Error posting payment: ' . $e->getMessage());
        }
    }

    /**
     * This function checks the status of a payment
     * @param string $reference unique reference of the transaction
     */
    public function getPaymentStatus($reference)
    {
        try {
            $resp = $this->externalDataService->getDataFromPayment(
                "transactions?filter={\"where\":{\"reference\":\"{$reference}\"}}"
            );

            if (!$resp || empty($resp)) {
                throw new \Exception('No payment found for this reference');
            }

            return array_intersect_key($resp[0], array_flip([
                'reference',
                'account_id',
                'amount',
                'fees',
                'tva',
                'description',
                'status',
                'created_at',
                'updated_at'
            ]));
        } catch (RequestException $e) {
            throw new \Exception('Error fetching payment status: ' . $e->getMessage());
        }
    }

    /**
     * This function initializes the payment of a customer
     * @param array $param (reference, currency, customer_name, customer_email, customer_country, amount, fees, transaction_id, phone)
     */
    public function postPaymentItem($param)
    {
        try {
            return $this->externalDataService->postDataFromPayment('payment-items', $param);
        } catch (RequestException $e) {
            throw new \Exception('Error posting payment item: ' . $e->getMessage());
        }
    }

    /**
     * This function checks the status of a payment item
     * @param string $reference unique reference of the payment item
     */
    public function getPaymentItemStatus($reference)
    {
        try {
            $resp = $this->externalDataService->getDataFromPayment(
                "payment-items?filter={\"where\":{\"reference\":\"{$reference}\"}}"
            );

            if (!$resp || empty($resp)) {
                throw new \Exception('No payment item found for this reference');
            }

            return array_intersect_key($resp[0], array_flip([
                'reference',
                'payment_id',
                'customer_id',
                'amount',
                'fees',
                'phone',
                'payment_method',
                'payment_proof',
                'status',
                'created_at',
                'updated_at'
            ]));
        } catch (RequestException $e) {
            throw new \Exception('Error fetching payment item status: ' . $e->getMessage());
        }
    }

    /**
     * This function returns the payment and its associated payment items
     * @param string $reference unique reference of the payment
     */
    public function getPaymentWithItems($reference)
    {
        try {
            // Fetch transaction with included payment items
            $resp =  $this->externalDataService->getDataFromPayment(
                "transactions?filter={\"where\":{\"reference\":\"{$reference}\"}, \"include\":[\"paymentItems\"]}"
            );

            if (!$resp || empty($resp)) {
                throw new \Exception('No payment found for this reference');
            }

            // Get basic payment details
            $payment = array_intersect_key($resp[0], array_flip([
                'reference',
                'account_id',
                'payment_type',
                'amount',
                'fees',
                'tva',
                'description',
                'status',
                'created_at',
                'updated_at'
            ]));

            // Get associated payment items details
            if (isset($resp[0]['paymentItems'])) {
                foreach ($resp[0]['paymentItems'] as &$item) {
                    // Pick relevant fields from each item
                    foreach ($item as &$value) {
                        unset($item['created_at'], $item['updated_at']);
                    }
                }
                // Attach items to payment details
                if (isset($payment['paymentItems'])) {
                    unset($payment['paymentItems']);
                }
                // Set items in response 
                return [
                    'payment' =>  $payment,
                    ['items' =>  $item]
                ];
            }
            return [];
        } catch (RequestException  $e) {
            throw new \Exception('Error fetching payment with items:  ' .  $e->getMessage());
        }
    }
}
