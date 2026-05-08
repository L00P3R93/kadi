<?php

namespace App\Http\Controllers;

use App\Facades\KadiApi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ProfilePictureController extends Controller
{
    public function upload(Request $request): RedirectResponse
    {
        $request->validate([
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $user = $request->user();

        if (! $user->linked_id) {
            return back()->withErrors(['photo' => 'Account is not linked to Kadi.']);
        }

        try {
            $file     = $request->file('photo');
            $ext      = $file->getClientOriginalExtension() ?: 'jpg';
            $filename = 'profile_'.$user->linked_id.'_'.time().'.'.$ext;

            $response = KadiApi::uploadProfilePic(
                $user->linked_id,
                $file->getRealPath(),
                $filename
            );

            if (isset($response['data'])) {
                Cache::put('kadi.customer.'.$user->id, $response['data'], now()->addHour());
            }

            return back()->with('profile_success', 'Profile picture updated successfully.');
        } catch (\Throwable $e) {
            Log::error('Profile pic upload failed for user #'.$user->id.': '.$e->getMessage());

            return back()->withErrors(['photo' => 'Upload failed: '.$e->getMessage()]);
        }
    }
}
