<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\BadgeStyle;
use App\Models\BadgeColor;
use Illuminate\Http\Request;

class BadgeController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/badge/styles",
     *     summary="Get Badge Styles",
     *     tags={"General"},
     *     @OA\Response(
     *         response=200,
     *         description="Badge Styles Fetched",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="response_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Badge Styles Fetched"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function getStyles()
    {
        try {
            $styles = BadgeStyle::all()->toArray();
            return $this->responseJson(true, 200, 'Badge Styles Fetched', $styles);
        } catch (\Throwable $th) {
            return $this->responseJson(false, 500, config('constants.CATCH_ERROR_MSG'), errorLogAndReturn($th));
        }
    }

    /**
     * @OA\Get(
     *     path="/api/badge/colors",
     *     summary="Get Badge Colors",
     *     tags={"General"},
     *     @OA\Response(
     *         response=200,
     *         description="Badge Colors Fetched",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="response_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Badge Colors Fetched"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function getColors()
    {
        try {
            $colors = BadgeColor::all()->toArray();
            return $this->responseJson(true, 200, 'Badge Colors Fetched', $colors);
        } catch (\Throwable $th) {
            return $this->responseJson(false, 500, config('constants.CATCH_ERROR_MSG'), errorLogAndReturn($th));
        }
    }
}
