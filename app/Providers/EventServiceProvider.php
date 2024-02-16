<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use App\Events\BuyerOrderConfirmationEvent;
use App\Listeners\OrderEmailConfirmation;
use App\Events\RequestConnectionEmailEvent;
use App\Listeners\RequestConnectionEmailListener;
use App\Events\PaymentRequestEvent;
use App\Listeners\PaymentRequestListener;
use App\Events\OrderPaymentDoneByAdmin;
use App\Listeners\OrderPaymentDoneByAdminListener;
use App\Events\OrderStatusChangeEmailEvent;
use App\Listeners\OrderStatusChangeListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

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
        BuyerOrderConfirmationEvent::class => [OrderEmailConfirmation::class],
        RequestConnectionEmailEvent::class => [RequestConnectionEmailListener::class],
        PaymentRequestEvent::class => [PaymentRequestListener::class],
        OrderPaymentDoneByAdmin::class => [OrderPaymentDoneByAdminListener::class],
        OrderStatusChangeEmailEvent::class => [OrderStatusChangeListener::class],
        'App\Events\ForgotPassword' => [
            'App\Listeners\sendPasswordOtpNotification',
        ],
        'App\Events\Welcome' => [
            'App\Listeners\sendWelcomeNotification',
        ],
        'App\Events\VerifyEmail' => [
            'App\Listeners\sendVerifyEmailNotification',
        ],
        'App\Events\StoreReviewed' => [
            'App\Listeners\sendStoreReviewedNotification',
        ],
        'App\Events\StoreRequest' => [
            'App\Listeners\sendStoreRequestNotification',
        ],
        'App\Events\EmailChangeOtp' => [
            'App\Listeners\sendEmailChangeOtp',
        ],
        'App\Events\SendMailIncompleteProfile' => [

            'App\Listeners\SendMailIncompleteProfileFired',

        ],
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
