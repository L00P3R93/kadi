<?php

use App\Providers\AppServiceProvider;
use App\Providers\BrevoMailServiceProvider;
use App\Providers\Filament\ConsolePanelProvider;
use App\Providers\Filament\MarketingPanelProvider;
use App\Providers\FortifyServiceProvider;

return [
    AppServiceProvider::class,
    BrevoMailServiceProvider::class,
    ConsolePanelProvider::class,
    MarketingPanelProvider::class,
    FortifyServiceProvider::class,
];
