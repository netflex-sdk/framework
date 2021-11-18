<?php

namespace Netflex\API\Contracts;

interface ClientResolver
{
    /**
     * @param string $connection
     * @return APIClient
     */
    public function resolve(string $connection): APIClient;
}