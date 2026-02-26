<?php

namespace App\Providers;

use App\Domain\RepositoryInterfaces\OnboardingRepositoryInterface;
use App\Domain\RepositoryInterfaces\PaymentRepositoryInterface;
use App\Domain\RepositoryInterfaces\SubscriptionRepositoryInterface;
use App\Domain\RepositoryInterfaces\TenantRepositoryInterface;
use App\Infrastructure\Repositories\EloquentOnboardingRepository;
use App\Infrastructure\Repositories\EloquentPaymentRepository;
use App\Infrastructure\Repositories\EloquentSubscriptionRepository;
use App\Infrastructure\Repositories\EloquentTenantRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            TenantRepositoryInterface::class,
            EloquentTenantRepository::class
        );

        $this->app->bind(
            SubscriptionRepositoryInterface::class,
            EloquentSubscriptionRepository::class
        );

        $this->app->bind(
            OnboardingRepositoryInterface::class,
            EloquentOnboardingRepository::class
        );

        $this->app->bind(
            PaymentRepositoryInterface::class,
            EloquentPaymentRepository::class
        );
    }
}
