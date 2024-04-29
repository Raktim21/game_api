<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

    })
    ->withExceptions(function (Exceptions $exceptions) {
        // $exceptions->register( App\Exceptions\Handler::class);
        // $exceptions->respond(function (Response $response) {
        //     if ($response->getStatusCode() === 401) {
        //         return response()->json(['error' => 'Unauthenticated.'], 401);
        //     }
        //     return $response;
        // });
    })->create();
