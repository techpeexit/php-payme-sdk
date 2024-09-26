<?php

namespace PaymeQuantum\PaymentSdk;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class Repository {
    private $url_payment;
    private $url_onboard;
    private $url_billing;
    protected $token;
    private $client_billing;
    private $client_onboarding;
    private $client_payment;

    public function __construct() {
        // Load environment variables from .env file
        $this->loadEnv();

        // Initialize URLs
        $this->url_payment = getenv('BASE_URL_PAYMENT');
        $this->url_onboard = getenv('BASE_URL_ONBOARDING');
        $this->url_billing = getenv('BASE_URL_BILLING');

        // Initialize Guzzle clients
        $this->client_billing = new Client(['base_uri' => $this->url_billing, 'headers' => ['Content-Type' => 'application/json']]);
        $this->client_onboarding = new Client(['base_uri' => $this->url_onboard, 'headers' => ['Content-Type' => 'application/json']]);
        $this->client_payment = new Client(['base_uri' => $this->url_payment, 'headers' => ['Content-Type' => 'application/json']]);

        $this->token = '';
    }

    private function loadEnv() {
        if (file_exists('.env')) {
            $lines = file('.env');
            foreach ($lines as $line) {
                putenv(trim($line));
            }
        }
    }

    public function getToken() {
        return $this->token;
    }

    public function setToken($token) {
        $this->token = $token;
    }

    public function getDataFromBilling($path) {
        try {
            echo "token: " . $this->token . "\n";
            $response = $this->client_billing->request('GET', $path, [
                'headers' => ['Authorization' => "Bearer {$this->token}"]
            ]);
            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            echo "Failed to fetch data from Billing: " . $e->getMessage() . "\n";
            return null;
        }
    }

    public function postDataFromOnboarding($path, $body) {
        try {
            echo "body: " . json_encode($body) . "\n";
            $response = $this->client_onboarding->request('POST', $path, [
                'headers' => ['Authorization' => "Bearer {$this->token}"],
                'json' => $body
            ]);
            echo "Successfully fetched and cached data from Onboarding {$path}\n";
            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            echo "Failed to fetch data from Onboarding: " . $e->getMessage() . "\n";
            return null;
        }
    }

    public function getDataFromOnboarding($path) {
        try {
            $response = $this->client_onboarding->request('GET', $path, [
                'headers' => ['Authorization' => "Bearer {$this->token}"]
            ]);
            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            echo "Failed to fetch data from Onboarding: " . $e->getMessage() . "\n";
            return null;
        }
    }

    public function getDataFromPayment($path) {
        try {
            $response = $this->client_payment->request('GET', $path, [
                'headers' => ['Authorization' => "Bearer {$this->token}"]
            ]);
            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            echo "Failed to fetch data from Payment: " . $e->getMessage() . "\n";
            return null;
        }
    }

    public function postDataFromPayment($path, $body) {
        try {
            echo "body: " . json_encode($body) . "\n";
            $response = $this->client_payment->request('POST', $path, [
                'headers' => ['Authorization' => "Bearer {$this->token}"],
                'json' => $body
            ]);
            echo "Successfully posted data from Payment {$path}\n";
            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            echo "Failed to fetch data from Payment: " . $e->getMessage() . "\n";
            return null;
        }
    }
}

// // Example usage
// $dataService = new ExternalDataService();
// $dataService->setToken('YOUR_TOKEN_HERE'); // Set your token here
// $response = $dataService->getDataFromBilling('/your-billing-path');
// print_r($response);