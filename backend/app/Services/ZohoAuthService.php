<?php

namespace App\Services;

use App\Models\ZohoToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ZohoAuthService
{
    private string $clientId;

    private string $clientSecret;

    private string $inventoryUrl;

    private string $authUrl;

    public function __construct()
    {
        $this->clientId = config('services.zoho.client_id');
        $this->clientSecret = config('services.zoho.client_secret');
        $this->inventoryUrl = config('services.zoho.inventory_url');
        $this->authUrl = config('services.zoho.auth_url');
    }

    private function getAccessToken(): string
    {
        $token = ZohoToken::first();
        if (! $token) {
            throw new \Exception('No token found. Authorize the application.');
        }

        if ($token->isExpired()) {
            $token = $this->refreshToken($token);
        }

        return $token->access_token;
    }

    private function refreshToken(?ZohoToken $token): ZohoToken
    {
        $response = Http::asForm()->post($this->authUrl, [
            'grant_type' => 'refresh_token',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'refresh_token' => $token->refresh_token,
        ]);

        if ($response->failed()) {
            throw new \Exception('Zoho token refresh failed: '.$response->body());
        }
        $data = $response->json();

        if (empty($data['access_token'])) {
            throw new \Exception('No access token in refresh response: '.json_encode($data));
        }

        $updateData = [
            'access_token' => $data['access_token'],
            'expires_at' => now()->addSeconds($data['expires_in']),
        ];

        $token->update($updateData);

        return $token->fresh();
    }

    public function request(string $method, string $endpoint, array $data = []): array
    {
        $url = "{$this->inventoryUrl}/{$endpoint}";
        $response = Http::withToken($this->getAccessToken())->asJson()->$method($url, $data);

        Log::info('Zoho API Request', [
            'method' => $method,
            'endpoint' => $endpoint,
            'payload' => $data,
            'status' => $response->status(),
            'response' => $response->json(),
        ]);

        if ($response->status() === 401) {
            $token = ZohoToken::first();
            $this->refreshToken($token);
            $response = Http::withToken($this->getAccessToken())->asJson()->$method($url, $data);
        }

        if ($response->failed()) {
            throw new \Exception('Zoho API error: '.$response->body());
        }

        return $response->json();
    }

    public function delete(string $endpoint): array
    {
        $url = "{$this->inventoryUrl}/{$endpoint}";
        $response = Http::withToken($this->getAccessToken())->asJson()->delete($url);
        if ($response->status() === 401) {
            $token = ZohoToken::first();
            $this->refreshToken($token);
            $response = Http::withToken($this->getAccessToken())->asJson()->delete($url);
        }

        if ($response->failed()) {
            throw new \Exception('Zoho API error: '.$response->body());
        }

        return $response->json();
    }

    public function get(string $endpoint, $params = []): array
    {
        $url = "{$this->inventoryUrl}/{$endpoint}";

        $response = Http::withToken($this->getAccessToken())->asJson()->get($url, $params);
        if ($response->status() === 401) {
            $token = ZohoToken::first();
            $this->refreshToken($token);
            $response = Http::withToken($this->getAccessToken())->asJson()->get($url, $params);
        }

        if ($response->failed()) {
            throw new \Exception('Zoho API error: '.$response->body());
        }

        return $response->json();
    }
}
