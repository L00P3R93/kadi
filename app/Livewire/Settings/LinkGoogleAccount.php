<?php

namespace App\Livewire\Settings;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class LinkGoogleAccount extends Component
{
    public function unlink(): void
    {
        Auth::user()->update([
            'google_id' => null,
            'avatar'    => null,
        ]);
    }

    public function render()
    {
        return view('livewire.settings.link-google-account');
    }
}
