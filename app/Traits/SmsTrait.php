<?php

namespace App\Traits;

/**
 * Trait FlashMessages
 * @package App\Traits
 */

trait SmsTrait
{
    public function sendOtpSms($data)
    {
        $phone = str_replace("+91", "", $data['phone']);
        $otp = $data['otp'];
        $message = "Hi, Your one time password is: " . $otp . ". Please don't share this with anyone - Legal meet.";
        $apiKey = urlencode('NDI0Nzc3NjQ0MjY5NjI2ZDQ0MzI0NjM0Njk2ZDU1NjM=');
        $numbers = $phone;
        $sender = urlencode('Legal Meet');
        $message = rawurlencode($message);
        $data = array('apikey' => $apiKey, 'numbers' => $numbers, "sender" => $sender, "message" => $message);
        $ch = curl_init('https://api.textlocal.in/send/');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $resp = curl_exec($ch);
        curl_close($ch);
        $resp = json_decode($resp);
        return $resp;
    }
}
