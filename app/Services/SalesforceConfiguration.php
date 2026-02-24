<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SalesforceConfiguration
{
    private $accessToken;

    private $instanceUrl;

    private $refreshToken;

    public function __construct()
    {
        $this->accessToken = session('sf_access_token');
        $this->instanceUrl = session('sf_instance_url');
        $this->refreshToken = session('sf_refresh_token');
    }

    public function runcommand($query)
    {
        if (! $this->instanceUrl) {
            return response()->json(['error' => 'Salesforce session missing. Please login again.'], 401);
        }
        $apiVersion = config('services.salesforce.api_version', 'v59.0');
        $url = rtrim($this->instanceUrl, '/').'/services/data/'.$apiVersion.'/query';
        $response = Http::withToken($this->accessToken)->get($url, [
            'q' => $query,
        ]);

        if ($response->status() === 401 && $this->refreshToken) {
            $refreshed = $this->refreshAccessToken();
            if ($refreshed) {
                $response = Http::withToken($this->accessToken)->get($url, [
                    'q' => $query,
                ]);
            }
        }

        return response()->json($response->json(), $response->status());
    }

    private function refreshAccessToken(): bool
    {
        $loginUrl = rtrim(config('services.salesforce.login_url'), '/');

        $tokenRes = Http::asForm()->post($loginUrl.'/services/oauth2/token', [
            'grant_type' => 'refresh_token',
            'client_id' => config('services.salesforce.client_id'),
            'client_secret' => config('services.salesforce.client_secret'),
            'refresh_token' => $this->refreshToken,
        ]);

        if (! $tokenRes->ok()) {
            Log::error('Salesforce token refresh failed: '.$tokenRes->status().' '.$tokenRes->body());

            return false;
        }

        $tokens = $tokenRes->json();
        $this->accessToken = $tokens['access_token'] ?? null;
        if (! $this->accessToken) {
            return false;
        }

        session()->put('sf_access_token', $this->accessToken);

        if (! empty($tokens['instance_url'])) {
            $this->instanceUrl = $tokens['instance_url'];
            session()->put('sf_instance_url', $this->instanceUrl);
        }

        return true;
    }
}
