<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\PartnerPackage;
use App\Models\PartnerRequest;

class PartnerApiController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/partner/packages",
     *     operationId="getPartnerPackages",
     *     tags={"Partners"},
     *     summary="Get all partner packages",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
     */
    public function packages()
    {
        $venuePackages = PartnerPackage::where('type', 'VENUE_ORGANISER')->where('is_active', 1)->get();
        $sponsorPackages = PartnerPackage::where('type', 'SPONSOR')->where('is_active', 1)->get();

        return response()->json([
            'status' => true,
            'data' => [
                'venue_organiser' => $venuePackages,
                'sponsors' => $sponsorPackages
            ]
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/partner/request",
     *     operationId="submitPartnerRequest",
     *     tags={"Partners"},
     *     summary="Submit a partner request",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"organization_name", "email"},
     *             @OA\Property(property="organization_name", type="string"),
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="package_id", type="integer"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Request submitted successfully"
     *     )
     * )
     */
    public function request(Request $request)
    {
        $request->validate([
            'organization_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'package_id' => 'nullable|exists:partner_packages,id',
            'message' => 'nullable|string'
        ]);

        $partnerRequest = PartnerRequest::create([
            'user_id' => auth()->id(),
            'organization_name' => $request->organization_name,
            'email' => $request->email,
            'package_id' => $request->package_id,
            'message' => $request->message,
            'status' => 'PENDING'
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Your request has been submitted successfully. Our team will contact you soon.'
        ]);
    }
}
