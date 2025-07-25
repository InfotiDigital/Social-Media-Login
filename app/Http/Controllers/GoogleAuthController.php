<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite as Socialite;

class GoogleAuthController extends Controller
{
    public function redirectToSpecificSocialMediaPage($provider)
    {
        return Socialite::driver($provider)->stateless()->redirect(); // Redirects to Google if Fb then driver will be facebook if github then driver will be github so on
    }

    public function callBackFunToSpecificSocialMediaAuthPage($provider)
    {
        try {
            if ($provider) {
                // 1. Find the user social media details
                $user_social_media_details = Socialite::driver($provider)->stateless()->user();

                $find_user_in_database = User::where('email', $user_social_media_details->getEmail())->first();

                if ($find_user_in_database) {
                    // If social_id is not set, update it
                    if (!$find_user_in_database->social_id) {
                        $find_user_in_database->update([
                            'social_id' => $user_social_media_details->getId(),
                        ]);
                    }
                    // If provider is not set, update it
                    if (!$find_user_in_database->provider) {
                        if ($provider === 'google') {
                            // If the provider is Google, set it to 'google'
                            $find_user_in_database->update([
                                'provider' => 'google',
                            ]);
                        } elseif ($provider === 'facebook') {
                            // If the provider is Facebook, set it to 'facebook'
                            $find_user_in_database->update([
                                'provider' => 'facebook',
                            ]);
                        }
                    }

                    Auth::login($find_user_in_database);
                    return redirect('dashboard')->with('success', 'You have successfully logged in with Google.');
                } else {
                    // Create a new user
                    $new_user = User::create([
                        'name' => $user_social_media_details->getName(),
                        'email' => $user_social_media_details->getEmail(),
                        'social_id' => $user_social_media_details->getId(),
                        'provider' => $provider,
                    ]);

                    Auth::login($new_user);
                    return redirect('dashboard')->with('success', 'You have successfully logged in with Google.');
                }
            }
        } catch (\Throwable $th) {
            // Handle the error
            dd([
                'message' => $th->getMessage(),
                'line' => $th->getLine(),
                'file' => $th->getFile(),
                'trace' => $th->getTraceAsString()
            ]);
        }
    }
}
