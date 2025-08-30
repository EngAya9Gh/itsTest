<?php
namespace App\Providers;

interface ProviderInterface
{
    public function sendOrder($order, $input);
}