<?php

namespace App\Traits;

use App\Models\User;
use Google\Client as Google_Client;
use App\Models\PushNotificationQueue;

/**
 * Trait FlashMessages
 * @package App\Traits
 */

trait PushNotificationTrait
{
    function addPushNotificationToAllUser($title, $body, $type = null)
    {
        $dataToStore = [];
        $allCustomers = User::where(['user_type' => 3, 'is_active' => 1])->where('fcm_token', '!=', NULL)->get();
        if (!empty($allCustomers)) {
            foreach ($allCustomers as $customer) {
                $dataToStore[] = ['fcm_token' => $customer->fcm_token, 'title' => $title, 'body' => $body, 'type' => $type];
            }
            PushNotificationQueue::insert($dataToStore);
        }
        return true;
    }
    function getAccessToken()
    {
        $credentialsFilePath = config_path('google-services.json');
        $client = new Google_Client();
        $client->setAuthConfig($credentialsFilePath);
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        $token = $client->fetchAccessTokenWithAssertion();
        $accessToken = $token['access_token'];
        return $accessToken;
    }

    function sendPushNotification($userId = null, $title, $body, $type = null)
    {
        if (!empty($userId)) {
            $user = User::find($userId);
            $deviceToken = $user->fcm_token ?? null;
        } else {
            $deviceToken = auth()->user()->fcm_token ?? null;
        }
        $response = false;
        if ($deviceToken) {
            $accessToken = $this->getAccessToken();
            $notification = [
                "title" => $title,
                "body" => $body,
                "image" => asset('assets/media/logos/logo-sm.png'),
            ];
            $data = [
                "message" => [
                    "token" => $deviceToken,
                    "notification" => $notification,
                ]
            ];
            $dataString = json_encode($data);
            $headers = [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $accessToken,
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/v1/projects/kydir-ffd93/messages:send');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
            $response = curl_exec($ch);
            if ($response === FALSE) {
                die('FCM Send Error: ' . curl_error($ch));
            }

            curl_close($ch);
        }

        return $response;
    }

    function sendPushNotificationOnToken($deviceToken, $title, $body, $type = null)
    {
        $response = false;
        if ($deviceToken) {
            $accessToken = $this->getAccessToken();
            $notification = [
                "title" => $title,
                "body" => $body,
                "image" => asset('assets/media/logos/logo-sm.png'),
            ];
            $data = [
                "message" => [
                    "token" => $deviceToken,
                    "notification" => $notification,
                ]
            ];
            $dataString = json_encode($data);
            $headers = [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $accessToken,
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/v1/projects/kydir-ffd93/messages:send');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
            $response = curl_exec($ch);
            if ($response === FALSE) {
                die('FCM Send Error: ' . curl_error($ch));
            }

            curl_close($ch);
        }
        return $response;
    }

    function sendPushNotificationQueue($queue)
    {
        $response = false;

        if ($queue) {
            $accessToken = $this->getAccessToken();
            $notification = [
                "title" => $queue->title,
                "body" => $queue->body,
                "image" => asset('assets/media/logos/logo-sm.png'),
            ];
            $data = [
                "message" => [
                    "token" => $queue->fcm_token,
                    "notification" => $notification,
                ]
            ];
            $dataString = json_encode($data);
            $headers = [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $accessToken,
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/v1/projects/kydir-ffd93/messages:send');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
            $response = curl_exec($ch);
            if ($response === FALSE) {
                die('FCM Send Error: ' . curl_error($ch));
            } else {
                $queue->delete();
            }
            curl_close($ch);
        }
        return $response;
    }
}
