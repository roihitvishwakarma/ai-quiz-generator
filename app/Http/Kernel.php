<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    // ... other properties and methods ...

    /**
     * The application's route middleware aliases.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array<string, string>
     */
    protected $middlewareAliases = [
        'auth.check' => \App\Http\Middleware\AuthCheck::class,
    ];
}