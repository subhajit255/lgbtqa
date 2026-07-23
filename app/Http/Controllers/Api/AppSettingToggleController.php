<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\AppSettingToggle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AppSettingToggleController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/app-setting-toggle",
     *     summary="Fetch App Setting Toggles",
     *     description="Retrieve the authenticated user's application setting toggles. Default values will be returned if no settings exist yet.",
     *     tags={"App Settings"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Settings fetched successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="response_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="App settings fetched successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="stealth_mode", type="boolean", example=false),
     *                 @OA\Property(property="ghost_mode", type="boolean", example=false),
     *                 @OA\Property(property="two_factor_auth", type="boolean", example=false),
     *                 @OA\Property(property="biometric_login", type="boolean", example=false),
     *                 @OA\Property(property="login_alerts", type="boolean", example=false),
     *                 @OA\Property(property="show_in_discovery", type="boolean", example=true),
     *                 @OA\Property(property="location_based", type="boolean", example=false),
     *                 @OA\Property(property="match_by_interests", type="boolean", example=true),
     *                 @OA\Property(property="pride_events_nearby", type="boolean", example=true),
     *                 @OA\Property(property="message_friends_only", type="boolean", example=true),
     *                 @OA\Property(property="message_community", type="boolean", example=true),
     *                 @OA\Property(property="message_open", type="boolean", example=true),
     *                 @OA\Property(property="notify_new_message", type="boolean", example=true),
     *                 @OA\Property(property="notify_event_reminder", type="boolean", example=true),
     *                 @OA\Property(property="notify_friend_requests", type="boolean", example=true),
     *                 @OA\Property(property="notify_post_interactions", type="boolean", example=true),
     *                 @OA\Property(property="notify_mentions_tags", type="boolean", example=true),
     *                 @OA\Property(property="notify_profile_visits", type="boolean", example=true),
     *                 @OA\Property(property="notify_marketing_updates", type="boolean", example=false),
     *                 @OA\Property(property="push_notification", type="boolean", example=true),
     *                 @OA\Property(property="email_notification", type="boolean", example=true),
     *                 @OA\Property(property="audience", type="string", example="open"),
     *                 @OA\Property(property="connection_node", type="string", example="open"),
     *                 @OA\Property(property="distance_range", type="integer", example=10),
     *                 @OA\Property(property="send_email_when", type="string", example="after_1_hours_offline"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2026-06-11T22:00:00.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2026-06-11T22:00:00.000000Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function index()
    {
        try {
            $userId = Auth::id();
            
            // Fetch or create settings with defaults
            $settings = AppSettingToggle::firstOrCreate(
                ['user_id' => $userId],
                [
                    // Default values matching the migration defaults
                    'stealth_mode' => false,
                    'ghost_mode' => false,
                    'two_factor_auth' => false,
                    'biometric_login' => false,
                    'login_alerts' => false,
                    'show_in_discovery' => true,
                    'location_based' => false,
                    'match_by_interests' => true,
                    'pride_events_nearby' => true,
                    'audience' => 'open',
                    'connection_node' => 'open',
                    'message_friends_only' => true,
                    'message_community' => true,
                    'message_open' => true,
                    'notify_new_message' => true,
                    'notify_event_reminder' => true,
                    'notify_friend_requests' => true,
                    'notify_post_interactions' => true,
                    'notify_mentions_tags' => true,
                    'notify_profile_visits' => true,
                    'notify_marketing_updates' => false,
                    'push_notification' => true,
                    'email_notification' => true,
                    'send_email_when' => 'after_1_hours_offline',
                ]
            );

            return $this->responseJson(true, 200, 'App settings fetched successfully.', $settings);
        } catch (\Throwable $th) {
            return $this->responseJson(false, 500, config('constants.CATCH_ERROR_MSG'), errorLogAndReturn($th));
        }
    }

    /**
     * @OA\Post(
     *     path="/api/app-setting-toggle",
     *     summary="Save/Update App Setting Toggles",
     *     description="Save or update the authenticated user's application setting toggles. All fields are optional and boolean.",
     *     tags={"App Settings"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="stealth_mode", type="boolean", example=false),
     *             @OA\Property(property="ghost_mode", type="boolean", example=false),
     *             @OA\Property(property="two_factor_auth", type="boolean", example=false),
     *             @OA\Property(property="biometric_login", type="boolean", example=false),
     *             @OA\Property(property="login_alerts", type="boolean", example=false),
     *             @OA\Property(property="show_in_discovery", type="boolean", example=true),
     *             @OA\Property(property="location_based", type="boolean", example=false),
     *             @OA\Property(property="match_by_interests", type="boolean", example=true),
     *             @OA\Property(property="pride_events_nearby", type="boolean", example=true),
     *             @OA\Property(property="audience", type="string", example="open"),
     *             @OA\Property(property="Audiance", type="string", example="open"),
     *             @OA\Property(property="connection_node", type="string", example="open"),
     *             @OA\Property(property="distance_range", type="integer", example=10),
     *             @OA\Property(property="send_email_when", type="string", example="after_1_hours_offline"),
     *             @OA\Property(property="message_friends_only", type="boolean", example=true),
     *             @OA\Property(property="message_community", type="boolean", example=true),
     *             @OA\Property(property="message_open", type="boolean", example=true),
     *             @OA\Property(property="notify_new_message", type="boolean", example=true),
     *             @OA\Property(property="notify_event_reminder", type="boolean", example=true),
     *             @OA\Property(property="notify_friend_requests", type="boolean", example=true),
     *             @OA\Property(property="notify_post_interactions", type="boolean", example=true),
     *             @OA\Property(property="notify_mentions_tags", type="boolean", example=true),
     *             @OA\Property(property="notify_profile_visits", type="boolean", example=true),
     *             @OA\Property(property="notify_marketing_updates", type="boolean", example=false),
     *             @OA\Property(property="push_notification", type="boolean", example=true),
     *             @OA\Property(property="email_notification", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Settings saved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="response_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="App settings saved successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="stealth_mode", type="boolean", example=false),
     *                 @OA\Property(property="ghost_mode", type="boolean", example=false),
     *                 @OA\Property(property="two_factor_auth", type="boolean", example=false),
     *                 @OA\Property(property="biometric_login", type="boolean", example=false),
     *                 @OA\Property(property="login_alerts", type="boolean", example=false),
     *                 @OA\Property(property="show_in_discovery", type="boolean", example=true),
     *                 @OA\Property(property="location_based", type="boolean", example=false),
     *                 @OA\Property(property="match_by_interests", type="boolean", example=true),
     *                 @OA\Property(property="pride_events_nearby", type="boolean", example=true),
     *                 @OA\Property(property="audience", type="string", example="open"),
     *                 @OA\Property(property="connection_node", type="string", example="open"),
     *                 @OA\Property(property="distance_range", type="integer", example=10),
     *                 @OA\Property(property="send_email_when", type="string", example="after_1_hours_offline"),
     *                 @OA\Property(property="message_friends_only", type="boolean", example=true),
     *                 @OA\Property(property="message_community", type="boolean", example=true),
     *                 @OA\Property(property="message_open", type="boolean", example=true),
     *                 @OA\Property(property="notify_new_message", type="boolean", example=true),
     *                 @OA\Property(property="notify_event_reminder", type="boolean", example=true),
     *                 @OA\Property(property="notify_friend_requests", type="boolean", example=true),
     *                 @OA\Property(property="notify_post_interactions", type="boolean", example=true),
     *                 @OA\Property(property="notify_mentions_tags", type="boolean", example=true),
     *                 @OA\Property(property="notify_profile_visits", type="boolean", example=true),
     *                 @OA\Property(property="notify_marketing_updates", type="boolean", example=false),
     *                 @OA\Property(property="push_notification", type="boolean", example=true),
     *                 @OA\Property(property="email_notification", type="boolean", example=true),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2026-06-11T22:00:00.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2026-06-11T22:00:00.000000Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="response_code", type="integer", example=422),
     *             @OA\Property(property="message", type="string", example="The stealth mode field must be true or false."),
     *             @OA\Property(property="data", type="array", @OA\Items(type="string"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function update(Request $request)
    {
        $userId = Auth::id();
        
        $rules = [
            'stealth_mode' => 'nullable|boolean',
            'ghost_mode' => 'nullable|boolean',
            'two_factor_auth' => 'nullable|boolean',
            'biometric_login' => 'nullable|boolean',
            'login_alerts' => 'nullable|boolean',
            'show_in_discovery' => 'nullable|boolean',
            'location_based' => 'nullable|boolean',
            'match_by_interests' => 'nullable|boolean',
            'pride_events_nearby' => 'nullable|boolean',
            'audience' => 'nullable|string|in:friends_only,community,open',
            'Audiance' => 'nullable|string|in:friends_only,community,open',
            'connection_node' => 'nullable|string|in:friends_only,community,open',
            'distance_range' => 'nullable|integer',
            'send_email_when' => 'nullable|string|in:immediately,after_1_hours_offline,after_6_hours_offline,daily_digest,never',
            'message_friends_only' => 'nullable|boolean',
            'message_community' => 'nullable|boolean',
            'message_open' => 'nullable|boolean',
            'notify_new_message' => 'nullable|boolean',
            'notify_event_reminder' => 'nullable|boolean',
            'notify_friend_requests' => 'nullable|boolean',
            'notify_post_interactions' => 'nullable|boolean',
            'notify_mentions_tags' => 'nullable|boolean',
            'notify_profile_visits' => 'nullable|boolean',
            'notify_marketing_updates' => 'nullable|boolean',
            'push_notification' => 'nullable|boolean',
            'email_notification' => 'nullable|boolean',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->responseJson(false, 422, $validator->errors()->first(), []);
        }

        try {
            // Retrieve existing settings or initialize with defaults if not present
            $settings = AppSettingToggle::firstOrCreate(
                ['user_id' => $userId],
                [
                    'stealth_mode' => false,
                    'ghost_mode' => false,
                    'two_factor_auth' => false,
                    'biometric_login' => false,
                    'login_alerts' => false,
                    'show_in_discovery' => true,
                    'location_based' => false,
                    'match_by_interests' => true,
                    'pride_events_nearby' => true,
                    'audience' => 'open',
                    'connection_node' => 'open',
                    'message_friends_only' => true,
                    'message_community' => true,
                    'message_open' => true,
                    'notify_new_message' => true,
                    'notify_event_reminder' => true,
                    'notify_friend_requests' => true,
                    'notify_post_interactions' => true,
                    'notify_mentions_tags' => true,
                    'notify_profile_visits' => true,
                    'notify_marketing_updates' => false,
                    'push_notification' => true,
                    'email_notification' => true,
                    'send_email_when' => 'after_1_hours_offline',
                ]
            );

            // Update only fields that are sent in the request
            $updateData = [];
            foreach (array_keys($rules) as $field) {
                if ($request->has($field)) {
                    if ($field === 'distance_range') {
                        $profile = Auth::user()->profile()->firstOrCreate([]);
                        $profile->update(['distance_range' => $request->input('distance_range')]);
                    } elseif ($field === 'audience' || $field === 'Audiance') {
                        $updateData['audience'] = $request->input($field);
                    } elseif ($field === 'connection_node') {
                        $updateData['connection_node'] = $request->input($field);
                    } elseif ($field === 'send_email_when') {
                        $updateData['send_email_when'] = $request->input($field);
                    } else {
                        $updateData[$field] = $request->boolean($field);
                    }
                }
            }

            if (!empty($updateData)) {
                $settings->update($updateData);
            }

            return $this->responseJson(true, 200, 'App settings saved successfully.', $settings->fresh());
        } catch (\Throwable $th) {
            return $this->responseJson(false, 500, config('constants.CATCH_ERROR_MSG'), errorLogAndReturn($th));
        }
    }
}
