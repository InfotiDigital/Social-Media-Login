<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirectToSpecificSocialMediaPage()
    {
        return Socialite::driver('google')->redirect(); // Redirects to Google if Fb then driver will be facebook if github then driver will be github so on
    }

    public function callBackFunToSpecificSocialMediaAuthPage()
    {
        try {
            // 1. Find the user social media details
            $user_social_media_details = Socialite::driver('google')->user();

            // // 2. Find the user is exists in our database or not
            // $find_user_in_database = User::where('google_id', $user_social_media_details->getId())->first();

            // // 3. If user social media details or user is not exists in DB then create the user
            // if (!$find_user_in_database) {
            //     $new_user = User::create([
            //         'name' => $user_social_media_details->getName(),
            //         'email' => $user_social_media_details->getEmail(),
            //         'google_id' => $user_social_media_details->getId(),
            //     ]);

            //     // 4. Login the user
            //     Auth::login($new_user);
            //     // 5. return to the dashboard with success message
            //     return redirect('dashboard')->with('success', 'You have successfully logged in with Google.');
            // } else {
            //     // 6. If user social media details already exists  in DB then login the user
            //     Auth::login($find_user_in_database);
            //     // 7. return to the dashboard with success message
            //     return redirect('dashboard')->with('success', 'You have successfully logged in with Google.');
            // }
            $find_user_in_database = User::where('email', $user_social_media_details->getEmail())->first();

            if ($find_user_in_database) {
                // If google_id is not set, update it
                if (!$find_user_in_database->google_id) {
                    $find_user_in_database->update([
                        'google_id' => $user_social_media_details->getId(),
                    ]);
                }
                // If provider is not set, update it
                if (!$find_user_in_database->provider) {
                    $find_user_in_database->update([
                        'provider' => 'google',
                    ]);
                }

                Auth::login($find_user_in_database);
                return redirect('dashboard')->with('success', 'You have successfully logged in with Google.');
            } else {
                // Create a new user
                $new_user = User::create([
                    'name' => $user_social_media_details->getName(),
                    'email' => $user_social_media_details->getEmail(),
                    'google_id' => $user_social_media_details->getId(),
                    'provider' => 'google',
                ]);

                Auth::login($new_user);
                return redirect('dashboard')->with('success', 'You have successfully logged in with Google.');
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
