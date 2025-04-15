<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Events\BiddingCreated;
use App\Events\ProposalSubmitted;
use App\Events\BiddingClosing;
use App\Listeners\NotifyNewBidding;
use App\Listeners\NotifyProposalSubmission;
use App\Listeners\NotifyBiddingClosing;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        BiddingCreated::class => [
            NotifyNewBidding::class,
        ],
        ProposalSubmitted::class => [
            NotifyProposalSubmission::class,
        ],
        BiddingClosing::class => [
            NotifyBiddingClosing::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
