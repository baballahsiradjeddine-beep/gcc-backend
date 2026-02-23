<?php

namespace App\Services;

use Exception;
use Google\Client as GoogleClient;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FCMService
{
    /**
     * Send an FCM notification via the HTTP v1 API.
     *
     * @param string $token The device FCM token.
     * @param string $title The notification title.
     * @param string $body The notification body.
     * @param array $data Additional data payload.
     * @return bool
     */
    public static function send(string $token, string $title, string $body, array $data = []): bool
    {
        try {
            $credentialsFilePath = base_path('firebase-credentials.json');

            if (!file_exists($credentialsFilePath)) {
                Log::error('FCM credentials file not found at: ' . $credentialsFilePath);
                return false;
            }

            // Get the project ID from the JSON credentials file
            $credentials = json_decode(file_get_contents($credentialsFilePath), true);
            $projectId = $credentials['project_id'] ?? null;

            if (!$projectId) {
                Log::error('No project_id found in firebase-credentials.json');
                return false;
            }

            // Initialize the Google Client to get an OAuth 2.0 token
            $client = new GoogleClient();
            $client->setAuthConfig($credentialsFilePath);
            $client->addScope('https://www.googleapis.com/auth/firebase.messaging');

            // Force refresh to get access token string
            $client->fetchAccessTokenWithAssertion();
            $accessToken = $client->getAccessToken();

            if (!isset($accessToken['access_token'])) {
                Log::error('Could not get FCM access token.');
                return false;
            }

            // Build payload
            $message = [
                'message' => [
                    'token' => $token,
                    'notification' => [
                        'title' => $title,
                        'body'  => $body,
                    ],
                    'data' => array_merge([
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    ], $data),
                ],
            ];

            // Send actual notification
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken['access_token'],
                'Content-Type'  => 'application/json',
            ])->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", $message);

            if ($response->successful()) {
                return true;
            }

            Log::error("FCM Send Error: " . $response->body());
            return false;

        } catch (Exception $e) {
            Log::error("FCM Exception: " . $e->getMessage());
            return false;
        }
    }
}
