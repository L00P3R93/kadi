<?php

namespace App\Livewire\Navigation;

use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class BottomNav extends Component
{
    #[Computed]
    public function items(): array
    {
        return [
            [
                'key' => 'menu',
                'type' => 'action', // dispatches a browser event instead of navigating
                'label' => 'Menu',
            ],
            [
                'key' => 'home',
                'type' => 'link',
                'label' => 'Home',
                'route' => 'dashboard',
                'url' => null,
            ],
            /*[
                'key' => 'store',
                'type' => 'link',
                'label' => 'Store',
                'route' => 'storefront.home',
                'url' => null,
            ],
            [
                'key' => 'earn',
                'type' => 'link',
                'label' => 'Free Coins',
                'route' => 'earn-coins',
                'url' => null,
            ],*/
            [
                'key' => 'wallet',
                'type' => 'link',
                'label' => 'My Vault',
                'route' => 'wallet',
                'url' => null,
            ],
            /*
            [
                'key' => 'campaigns',
                'type' => 'link',
                'label' => 'Campaigns',
                'route' => null,
                'url' => '/marketing',
            ],
            */
            [
                'key' => 'profile',
                'type' => 'link',
                'label' => 'Profile',
                'route' => 'profile',
                'url' => null,
            ],
        ];
    }

    public function isActive(array $item): bool
    {
        if (isset($item['route'])) {
            return request()->routeIs($item['route']);
        }

        if (isset($item['url'])) {
            return request()->is($item['url']);
        }

        return false;
    }

    public function render(): Factory|\Illuminate\Contracts\View\View|View
    {
        return view('livewire.navigation.bottom-nav');
    }
}
