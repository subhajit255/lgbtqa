<?php

namespace App\Http\Controllers;

class BaseController extends Controller
{
    protected $data = null;

    protected function setPageTitle($title, $subTitle = '')
    {
        view()->share(['pageTitle' => $title, 'subTitle' => $subTitle]);
    }

    /**
     * @param int $errorCode
     * @param null $message
     * @return \Illuminate\Http\Response
     */
    protected function showErrorPage($errorCode = 404, $message = null)
    {
        $data['message'] = $message;
        return response()->view('errors.' . $errorCode, $data, $errorCode);
    }

    /**
     * @param bool $status
     * @param int $responseCode
     * @param string|array $message
     * @param null|array $data
     * @return \Illuminate\Http\JsonResponse
     */
    protected function responseJson($status = true, $responseCode = 200, $message = "", $data = [])
    {
        return response()->json([
            'status'        =>  $status,
            'response_code' =>  $responseCode,
            'message'       =>  $message,
            'data'          =>  $data
        ], $responseCode);
    }

    protected function createUserName($string)
    {
        $pattern = " ";
        $firstPart = strstr(strtolower($string), $pattern, true);
        $secondPart = substr(strstr(strtolower($string), $pattern, false), 0, 3);
        $nrRand = rand(0, 100);

        $username = trim($firstPart) . trim($secondPart) . trim($nrRand);
        return $username;
    }
}
