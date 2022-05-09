<?php

namespace Netflex\Foundation\Wallet\Contracts;

use Netflex\Foundation\Wallet\PKPass;

interface PKPassRepresentable
{
    public function toPKPass(): PKPass;
}
