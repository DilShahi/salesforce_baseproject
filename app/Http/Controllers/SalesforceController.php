<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SalesforceController extends Controller
{
    public function redirect(Request $request)
    {
        $state = Str::random(40);
        $request->session()->put('sf_oauth_state', $state);
        $loginUrl = rtrim(config('services.salesforce.login_url'), '/');
        $clientId = config('services.salesforce.client_id');
        $redirectUri = config('services.salesforce.redirect_uri');
        $scope = config('services.salesforce.scopes');

        $url = $loginUrl.'/services/oauth2/authorize?'.http_build_query([
            'response_type' => 'code',
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'scope' => $scope,
            'state' => $state,
        ]);

        return redirect()->away($url);
    }

    public function callback(Request $request)
    {
        $expected = $request->session()->pull('sf_oauth_state');
        if (! $expected || ! hash_equals($expected, (string) $request->query('state'))) {
            abort(400, 'Invalid state');
        }

        $code = $request->query('code');
        if (! $code) {
            abort(400, 'Missing code');
        }

        $loginUrl = rtrim(config('services.salesforce.login_url'), '/');

        $tokenRes = Http::asForm()->post($loginUrl.'/services/oauth2/token', [
            'grant_type' => 'authorization_code',
            'client_id' => config('services.salesforce.client_id'),
            'client_secret' => config('services.salesforce.client_secret'),
            'redirect_uri' => config('services.salesforce.redirect_uri'),
            'code' => $code,
        ]);

        if (! $tokenRes->ok()) {
            abort(500, 'Token exchange failed: '.$tokenRes->status().' '.$tokenRes->body());
        }

        $tokens = $tokenRes->json();

        $request->session()->put('sf_access_token', $tokens['access_token'] ?? null);
        $request->session()->put('sf_refresh_token', $tokens['refresh_token'] ?? null);
        $request->session()->put('sf_instance_url', $tokens['instance_url'] ?? null);

        return redirect('/');
    }

    public function logout(Request $request)
    {
        $request->session()->forget([
            'sf_access_token',
            'sf_refresh_token',
            'sf_instance_url',
            'sf_oauth_state',
        ]);

        return redirect()->route('home');
    }
}
