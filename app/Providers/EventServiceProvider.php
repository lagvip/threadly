<?php

namespace App\Providers;

use App\Events\OrderPlaced;
use App\Events\RefundApproved;
use App\Events\RefundRejected;
use App\Listeners\LogRefundApproval;
use App\Listeners\LogRefundRejection;
use App\Listeners\QueueOrderPlacedMail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        OrderPlaced::class => [
            QueueOrderPlacedMail::class,
        ],
        RefundApproved::class => [
            LogRefundApproval::class,
        ],
        RefundRejected::class => [
            LogRefundRejection::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
