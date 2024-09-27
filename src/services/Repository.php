<?php

namespace PaymeQuantum\PaymentSdk\Services;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ClientException;

class Repository {
    protected static $url_payment;
    protected static $url_onboard;
    protected static $url_billing;
    protected static $token;
   
    public function __construct() {
        // Load environment variables from .env file
        // $this->loadEnv();

        // Initialize URLs
        self::$url_payment = 'http://payme-routing-payment.peexit.com/';
        self::$url_onboard = 'https://payme-onboarding.peexit.com/api/v1/';
        self::$url_billing = 'https://payme-billing.peexit.com/';

        self::$token = '';
    }

    public function getToken() {
        return self::$token;
    }

    public function setToken($token) {
        self::$token = $token;
    }

    public static function getDataFromBilling($path) {
        try {
            $client = new Client();
            $full_path = self::$url_billing. $path;
            $headers = [
                'Content-Type' => 'application/json; charset=utf-8',
                'Authorization' => 'Bearer '. self::$token
            ];
            $response = $client->request('GET', $full_path, [
                'headers' => $headers
            ]);
            return json_decode($response->getBody(), true);
        } catch (ClientException $e) {
            throw $e;
        }
    }

    public static function postDataFromOnboarding($path, $body) {
        try {
            $client = new Client();
            $full_path = self::$url_onboard. $path;
            $headers = [
                'Content-Type' => 'application/json; charset=utf-8',
                'Authorization' => 'Bearer '. self::$token
            ];
            // dd($body);
            $response = $client->request('POST', $full_path, [
                'headers' => $headers,
                'json' => $body
            ]);
            return json_decode($response->getBody(), true);
        } catch (ClientException $e) {
            throw $e;
        }
    }

    public static function getDataFromOnboarding($path) {
        try {
            $client = new Client();
            $full_path = self::$url_onboard. $path;
            $headers = [
                'Content-Type' => 'application/json; charset=utf-8',
                'Authorization' => 'Bearer '. self::$token
            ];
            $response = $client->request('GET', $full_path, [
                'headers' => $headers
            ]);
            return json_decode($response->getBody(), true);
        } catch (ClientException $e) {
            throw $e;
        }
    }

    public static function getDataFromPayment($path) {
        try {
            $client = new Client();
            $full_path = self::$url_payment. $path;
            $headers = [
                'Content-Type' => 'application/json; charset=utf-8',
                'Authorization' => 'Bearer '. self::$token
            ];
            $response = $client->request('GET', $full_path, [
                'headers' => $headers
            ]);
            return json_decode($response->getBody(), true);
        } catch (ClientException $e) {
            throw $e;
        }
    }

    public static function postDataFromPayment($path, $body) {
        try {
            $client = new Client();
            $full_path = self::$url_payment. $path;
            $headers = [
                'Content-Type' => 'application/json; charset=utf-8',
                'Authorization' => 'Bearer '. self::$token
            ];
            $response = $client->request('POST', $full_path, [
                'headers' => $headers,
                'json' => $body
            ]);
            return json_decode($response->getBody(), true);
        } catch (ClientException $e) {
            throw $e;
        }
    }
}

// // Example usage
// $dataService = new ExternalDataService();
// $dataService->setToken('YOUR_TOKEN_HERE'); // Set your token here
// $response = $dataService->getDataFromBilling('/your-billing-path');
// print_r($response);