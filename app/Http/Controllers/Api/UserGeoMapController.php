<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;

use App\Models\UserLocation;
use Illuminate\Support\Facades\Validator;

class UserGeoMapController extends BaseController
{
    public function addOrUpdateCurrentLocation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return $this->responseJson(false, 400, $validator->errors()->first());
        }

        $user = auth()->user();

        if (!$user) {
            return $this->responseJson(false, 401, 'Unauthorized');
        }

        $location = UserLocation::updateOrCreate(
            ['user_id' => $user->id],
            [
                'lat' => $request->lat,
                'lng' => $request->lng,
                'last_pinged_at' => now(),
            ]
        );

        return $this->responseJson(true, 200, 'Location updated successfully', $location);
    }
}
