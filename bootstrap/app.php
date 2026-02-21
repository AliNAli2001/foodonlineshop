<?php

use App\Exceptions\EmptyCartException;
use App\Exceptions\InsufficientStockException;
use App\Exceptions\InvalidOrderStatusTransitionException;
use App\Exceptions\OrderItemBatchNotFoundException;
use App\Exceptions\OrderStateException;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\SetLocale;
use Illuminate\Http\Request;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            SetLocale::class,
            HandleInertiaRequests::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (InsufficientStockException $exception, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $exception->getMessage(),
                    'error' => 'insufficient_stock',
                ], 422);
            }

            return back()->withErrors(['error' => $exception->getMessage()]);
        });

        $exceptions->render(function (EmptyCartException $exception, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $exception->getMessage(),
                    'error' => 'empty_cart',
                ], 422);
            }

            return back()->withErrors(['error' => $exception->getMessage()]);
        });

        $exceptions->render(function (OrderStateException|InvalidOrderStatusTransitionException $exception, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $exception->getMessage(),
                    'error' => 'invalid_order_state',
                ], 409);
            }

            return back()->withErrors(['error' => $exception->getMessage()]);
        });

        $exceptions->render(function (OrderItemBatchNotFoundException $exception, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $exception->getMessage(),
                    'error' => 'order_item_batch_missing',
                ], 422);
            }

            return back()->withErrors(['error' => $exception->getMessage()]);
        });
    })->create();
