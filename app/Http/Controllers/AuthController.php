<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Google_Client;
use Google_Service_Oauth2;
use Google_Service_Drive;

class AuthController extends Controller
{
    public function index(Request $request)
    {

    }

    public function authorizeUser(Request $request)
    {
        $client = $this->createGoogleClient();
        return redirect($client->createAuthUrl());
    }

    private function createGoogleClient() {
        $client = new Google_Client();
        $client->setClientId(env('GOOGLE_CLIENT_ID'));
        $client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
        $client->setRedirectUri(env('GOOGLE_REDIRECT_URI'));
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');
        $client->setScopes([
            Google_Service_Oauth2::USERINFO_PROFILE,
            Google_Service_Oauth2::USERINFO_EMAIL,
            Google_Service_Drive::DRIVE
        ]);
        return $client;
    }
}
