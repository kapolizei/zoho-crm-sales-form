<?php

namespace App\Http\Controllers;

use App\Models\ZohoToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ZohoAuthController extends Controller
{
    public function __construct() {}

    public function redirect()
    {
        $url = 'https://accounts.zoho.eu/oauth/v2/auth?'.http_build_query([
            'scope' => 'ZohoInventory.fullaccess.all',
            'client_id' => config('services.zoho.client_id'),
            'response_type' => 'code',
            'redirect_uri' => 'http://localhost:8000/zoho/callback',
            'access_type' => 'offline',
        ]);

        return redirect($url);

    }

    public function callback(Request $request)
    {
        $response = Http::asForm()->post('https://accounts.zoho.eu/oauth/v2/token', [
            'grant_type' => 'authorization_code',
            'client_id' => config('services.zoho.client_id'),
            'client_secret' => config('services.zoho.client_secret'),
            'redirect_uri' => 'http://localhost:8000/zoho/callback',
            'code' => $request->query('code'),
        ]);

        $data = $response->json();

        if (! isset($data['access_token'])) {
            return redirect(config('services.vite.url'))->with(['error' => 'Zoho authorization failed']);
        }

        $updateData = [
            'access_token' => $data['access_token'],
            'expires_at' => now()->addSeconds($data['expires_in']),
        ];

        if (isset($data['refresh_token'])) {
            $updateData['refresh_token'] = $data['refresh_token'];
        }

        ZohoToken::updateOrCreate([], $updateData);

        return redirect(config('services.vite.url'));
    }
}
