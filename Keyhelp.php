<?php

namespace App\Extensions\Servers\Keyhelp;

use App\Classes\Extensions\Server;
use App\Helpers\ExtensionHelper;
use Illuminate\Support\Facades\Http;

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

        dd($plans->json());

        foreach ($plans->json() as $plan) {
            $plansList[] = [
                'name' => $plan['name'],
                'id' => $plan['id'],
            ];
        }

        return [
            [
                'name' => 'node',
                'friendlyName' => 'Hosting plans',
                'type' => 'dropdown',
                'options' => $plansList,
            ],
        ];
    }

    private function getRequest($url): \GuzzleHttp\Promise\PromiseInterface|\Illuminate\Http\Client\Response
    {
        $response = Http::withHeaders([
            'X-API-KEY:' . $this->config('apiKey'),
            'Content-Type: application/json',
        ])->get($url);

        return $response;
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
        return false;
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
}
