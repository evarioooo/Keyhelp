<?php

namespace App\Extensions\Servers\Keyhelp;

use App\Classes\Extensions\Server;
use App\Helpers\ExtensionHelper;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class Keyhelp extends Server
{
    /**
     * Get the extension metadata
     * 
     * @return array
     */
    public function getMetadata()
    {
        return [
            'display_name' => 'Keyhelp',
            'version' => '1.0.0',
            'author' => 'Alexander Bergmann',
            'website' => 'https://evarioo.eu',
        ];
    }

    private function config($key): ?string
    {
        $config = ExtensionHelper::getConfig('Keyhelp', $key);
        if ($config) {
            if ($key == 'host') {
                return rtrim($config, '/');
            }
            return $config;
        }

        return null;
    }

    /**
     * Get all the configuration for the extension
     * 
     * @return array
     */
    public function getConfig()
    {
        return [
            [
                'name' => 'host',
                'friendlyName' => 'Keyhelp panel url',
                'type' => 'text',
                'required' => true,
            ],
            [
                'name' => 'apiKey',
                'friendlyName' => 'API Key',
                'type' => 'text',
                'required' => true,
            ],
        ];
    }

    /**
     * Get product config
     * 
     * @param array $options
     * @return array
     */
    public function getProductConfig($options)
    {
        $plans = $this->getRequest($this->config('host') . '/api/v2/hosting-plans');
        $plansList = [
            [
                'name' => 'None',
                'value' => '',
            ],
        ];

        foreach ($plans->json() as $plan) {
            $plansList[] = [
                'name' => $plan['name'],
                'value' => $plan['id'],
            ];
        }

        return [
            [
                'name' => 'plan',
                'friendlyName' => 'Hosting plan',
                'type' => 'dropdown',
                'options' => $plansList,
            ],
        ];
    }

    private function getRequest($url): \GuzzleHttp\Promise\PromiseInterface|\Illuminate\Http\Client\Response
    {
        $response = Http::withHeaders([
            'X-API-Key' => $this->config('apiKey'),
            'Content-Type' => 'application/json'
        ])->get($url);

        return $response;
    }

    private function postRequest($url, $data): \GuzzleHttp\Promise\PromiseInterface|\Illuminate\Http\Client\Response
    {
        return Http::withHeaders([
            'X-API-Key' => $this->config('apiKey'),
            'Content-Type' => 'application/json'
        ])->post($url, $data);
    }

    private function patchRequest($url, $data): \GuzzleHttp\Promise\PromiseInterface|\Illuminate\Http\Client\Response
    {
        return Http::withHeaders([
            'X-API-Key' => $this->config('apiKey'),
            'Content-Type' => 'application/json'
        ])->patch($url, $data);
    }

    public function deleteRequest($url): \GuzzleHttp\Promise\PromiseInterface|\Illuminate\Http\Client\Response
    {
        return Http::withHeaders([
            'X-API-Key' => $this->config('apiKey'),
            'Content-Type' => 'application/json'
        ])->delete($url);
    }

    private function random_string($length = 10): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; ++$i) {
            $randomString .= $characters[mt_rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }

    /**
     * Create a server
     * 
     * @param User $user
     * @param array $params
     * @param Order $order
     * @param OrderProduct $orderProduct
     * @param array $configurableOptions
     * @return bool
     */
    public function createServer($user, $params, $order, $orderProduct, $configurableOptions)
    {
        $sanitized = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($user->username));
        $json = [
            'username' =>  $sanitized,
            'email' => $user->email,
            'id_hosting_plan' => $params['plan'],
        ];

        $url = $this->config('host') . '/api/v2/clients';
        $response = $this->postRequest($url, $json);

        if (!$response->successful()) {
            ExtensionHelper::error('Keyhelp', 'Failed to create hosting for order ' . $orderProduct->id . ' with error ' . $response->body());
            return false;
        }

        return true;
    }

    /**
     * Suspend a server
     * 
     * @param User $user
     * @param array $params
     * @param Order $order
     * @param OrderProduct $orderProduct
     * @param array $configurableOptions
     * @return bool
     */
    public function suspendServer($user, $params, $order, $orderProduct, $configurableOptions)
    {
        return false;
    }

    /**
     * Unsuspend a server
     * 
     * @param User $user
     * @param array $params
     * @param Order $order
     * @param OrderProduct $orderProduct
     * @param array $configurableOptions
     * @return bool
     */
    public function unsuspendServer($user, $params, $order, $orderProduct, $configurableOptions)
    {
        return false;
    }

    /**
     * Terminate a server
     * 
     * @param User $user
     * @param array $params
     * @param Order $order
     * @param OrderProduct $orderProduct
     * @param array $configurableOptions
     * @return bool
     */
    public function terminateServer($user, $params, $order, $orderProduct, $configurableOptions)
    {
        return false;
    }

    public function getLink($user, $params, $order, $orderProduct): bool|string
    {
        return $this->config('host');
    }
}
