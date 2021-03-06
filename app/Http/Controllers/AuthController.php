<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Google_Client;
use Google_Service_Oauth2;
use Google_Service_Drive;

class AuthController extends Controller
{
    const ACCESS_TOKEN_BUNDLE = 'ACCESS_TOKEN_BUNDLE';

    public function index(Request $request)
    {
        if (!session()->exists(self::ACCESS_TOKEN_BUNDLE)) {
            return redirect()->action('AuthController@authorizeUser');
        }

        $accessTokenBundle = session()->get(self::ACCESS_TOKEN_BUNDLE);
        $client = $this->createGoogleClient();
        $client->setAccessToken($accessTokenBundle);

        if ($client->isAccessTokenExpired()) {
            $refreshToken = $accessTokenBundle['refresh_token'];
            $accessTokenBundle = $client->refreshToken($refreshToken);
            session()->put(self::ACCESS_TOKEN_BUNDLE, $accessTokenBundle);
        }
        return $accessTokenBundle;
    }

    public function authorizeUser()
    {
        $client = $this->createGoogleClient();
        return redirect($client->createAuthUrl());
    }

    public function oauthCallback(Request $request)
    {
        $code = $request->input('code');

        if (empty($code)) {
            throw new \Exception('Wrong code parameter', 400);
        }

        $client = $this->createGoogleClient();
        $accessTokenBundle = $client->fetchAccessTokenWithAuthCode($code);
        session()->put(self::ACCESS_TOKEN_BUNDLE, $accessTokenBundle);
        return redirect()->action('AuthController@index');
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
