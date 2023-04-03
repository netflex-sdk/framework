<?php

namespace Netflex\API;

use Illuminate\Support\Facades\Config;

use Netflex\API\Client;
use Netflex\API\Contracts\APIClient;

use InvalidArgumentException;
use Netflex\API\Contracts\ClientResolver;

class APIClientResolver implements ClientResolver
{
    /**
     * @param string $connection
     * @return APIClient
     */
    public function resolve(string $connection): APIClient
    {
        $config = Config::get('api.connections.' . $connection);

        // Backwards compatibility for old config
        if (!$config && $connection === 'default') {
            $config = [
                'baseUri' => Config::get('api.baseUri'),
                'publicKey' => Config::get('api.publicKey'),
                'privateKey' => Config::get('api.privateKey')
            ];
        }

        if (!$config) {
            throw new InvalidArgumentException('Invalid connection name: ' . $connection);
        }

        $options = [
            'base_uri' => $config['baseUri'] ?? null,
            'auth' => [
                $config['publicKey'] ?? null,
                $config['privateKey'] ?? null,
            ]
        ];

        return (new Client($options))->setConnectionName($connection);
    }
}
