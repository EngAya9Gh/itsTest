<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Foundation\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request as HttpRequest;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * @var array|string
     */
    protected $proxies = '*'; // يمكنك تحديد البروكسيات الموثوقة، '*' تعني جميع البروكسيات

    /**
     * The headers that should be used to detect proxies.
     *
     * @var array
     */
    protected $headers = HttpRequest::HEADER_X_FORWARDED_ALL;
}
