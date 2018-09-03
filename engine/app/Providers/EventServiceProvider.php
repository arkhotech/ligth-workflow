<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        "App\Events\ActivityEvent" => [
            "App\Listeners\ActivityEventHandler"
        ],
        "App\Events\ProcessEvent" => [
            "App\Listeners\ProcessEventHandler"
        ]
    ];

    protected $subscribe = [
        "App\Listeners\FormEventListener",
        
    ];
    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
