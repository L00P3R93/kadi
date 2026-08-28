<?php

namespace App\Providers;

use App\Events\PasswordChanged;
use App\Listeners\HandleEmailVerified;
use App\Listeners\HandleLogin;
use App\Listeners\RecordSecurityAudit;
use App\Listeners\SendSecurityNotification;
use App\Models\Ad;
use App\Models\AdCampaign;
use App\Observers\AdCampaignObserver;
use App\Observers\AdObserver;
use App\Services\BugsApiService;
use App\Services\KadiApiService;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Verified;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Laravel\Fortify\Events\RecoveryCodeReplaced;
use Laravel\Fortify\Events\RecoveryCodesGenerated;
use Laravel\Fortify\Events\TwoFactorAuthenticationConfirmed;
use Laravel\Fortify\Events\TwoFactorAuthenticationDisabled;
use Laravel\Fortify\Events\TwoFactorAuthenticationEnabled;
use Laravel\Passkeys\Events\PasskeyDeleted;
use Laravel\Passkeys\Events\PasskeyRegistered;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(KadiApiService::class, function ($app) {
            return new KadiApiService;
        });
        $this->app->singleton(BugsApiService::class, function ($app) {
            return new BugsApiService;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureObservers();

        Event::listen(Verified::class, HandleEmailVerified::class);
        Event::listen(Login::class, HandleLogin::class);

        $this->configureSecurityEventListeners();
    }

    /**
     * Audit + notify on account security credential changes.
     */
    protected function configureSecurityEventListeners(): void
    {
        $events = [
            TwoFactorAuthenticationEnabled::class,
            TwoFactorAuthenticationConfirmed::class,
            TwoFactorAuthenticationDisabled::class,
            RecoveryCodesGenerated::class,
            RecoveryCodeReplaced::class,
            PasskeyRegistered::class,
            PasskeyDeleted::class,
            PasswordChanged::class,
        ];

        foreach ($events as $event) {
            Event::listen($event, RecordSecurityAudit::class);
            Event::listen($event, SendSecurityNotification::class);
        }
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Model::automaticallyEagerLoadRelationships();
        Model::unguard();

        if (app()->environment('production')) {
            URL::forceHttps();
        }

        // Fail fast when the shared KadiApi encryption key is missing or
        // malformed outside local/testing. Silent fallbacks here would put
        // money-endpoint customer-ID encryption at risk (audit finding C-2).
        //        if (! app()->environment('local', 'testing')) {
        //            openssl_shared_key();
        //        }

        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }

    protected function configureObservers(): void
    {
        AdCampaign::observe(AdCampaignObserver::class);
        Ad::observe(AdObserver::class);
    }
}
