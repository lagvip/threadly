<?php

namespace App\Providers;

use App\Events\Inventory\StockMovementRecorded;
use App\Events\Sales\OrderPlaced;
use App\Events\Sales\OrderStatusChanged;
use App\Events\Sales\RefundApproved;
use App\Events\Sales\RefundRejected;
use App\Listeners\Inventory\CreateStockMovementRecord;
use App\Listeners\Sales\CreateOrderStatusLog;
use App\Listeners\Sales\LogRefundApproval;
use App\Listeners\Sales\LogRefundRejection;
use App\Listeners\Sales\QueueOrderPlacedMail;
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
        OrderStatusChanged::class => [
            CreateOrderStatusLog::class,
        ],
        StockMovementRecorded::class => [
            CreateStockMovementRecord::class,
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
