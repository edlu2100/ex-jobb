<?php

use App\Http\Middleware\TestMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);
		$middleware->trustProxies(at: '*');
		$middleware->trustProxies(
			headers: Request::HEADER_X_FORWARDED_AWS_ELB
		);
		$middleware->trustHosts(at: ['quiet-stream-17873-96d9fe60ffcf.herokuapp.com'], subdomains: false);
    })

    ->withExceptions(function (Exceptions $exceptions) {
        
    })->create();