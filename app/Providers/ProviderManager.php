<?php

namespace App\Providers;

use App\Providers\ZnetProvider;
use App\Providers\MasarProvider;

class ProviderManager
{
    public static function make(string $providerName): ProviderInterface
    {
        return match ($providerName) {
            'znet' => new ZnetProvider(),
            'masar' => new MasarProvider(),
            default => throw new \Exception("مزود غير مدعوم: $providerName"),
        };
    }
}
