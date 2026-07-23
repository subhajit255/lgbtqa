<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Http\Resources\Api\User\ProfileResource;
use App\Http\Resources\Api\User\LoginHistoryResource;
use App\Models\Gallery;
use App\Models\Profile;
use App\Models\User;
use App\Traits\CommonFunction;
use App\Traits\NotificationTrait;
use App\Traits\UploadAble;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class UserController extends BaseController
{
    use CommonFunction;
    use NotificationTrait;
    use UploadAble;

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     summary="User Logout",
     *     tags={"User"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Response(response=200, description="Logged out successfully"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function logout(Request $request)
    {
        $user = auth()->user();
        if ($user) {
            $tokenDeleted = $request->user()->currentAccessToken()->delete();
            if ($tokenDeleted) {
                $status = true;
                $code = 200;
                $response = [];
                $message = 'You have been successfully logged out!';
            } else {
                $status = false;
                $code = 500;
                $response = [];
                $message = 'Something went wrong';
            }
        } else {
            $status = false;
            $code = 401;
            $response = [];
            $message = 'User not authenticated';
        }

        return $this->responseJson($status, $code, $message, $response);
    }

    /**
     * @OA\Post(
     *     path="/api/get/profile",
     *     summary="Get Profile Details",
     *     tags={"User"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Data Found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="response_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Profile Details Fetched"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="uuid", type="string"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="email", type="string"),
     *                 @OA\Property(property="username", type="string"),
     *                 @OA\Property(property="profile_image", type="string"),
     *                 @OA\Property(property="nationality", type="string", description="Decoded nationality array, string or single ID"),
     *                 @OA\Property(property="sleep_rhythm", type="integer"),
     *                 @OA\Property(property="sleepRhythm", type="integer"),
     *                 @OA\Property(property="kids_have", type="integer"),
     *                 @OA\Property(property="kidsHave", type="integer"),
     *                 @OA\Property(property="kids_future", type="integer"),
     *                 @OA\Property(property="kidsFuture", type="integer"),
     *                 @OA\Property(property="pets_current", type="array", @OA\Items(type="integer")),
     *                 @OA\Property(property="petsCurrent", type="array", @OA\Items(type="integer")),
     *                 @OA\Property(property="pets_future", type="integer"),
     *                 @OA\Property(property="petsFuture", type="integer"),
     *                 @OA\Property(property="living_preference", type="integer"),
     *                 @OA\Property(property="livingPreference", type="integer"),
     *                 @OA\Property(property="travel_importance", type="integer"),
     *                 @OA\Property(property="travelImportance", type="integer"),
     *                 @OA\Property(property="preferred_communication", type="array", @OA\Items(type="integer")),
     *                 @OA\Property(property="preferredCommunication", type="array", @OA\Items(type="integer")),
     *                 @OA\Property(property="love_language", type="array", @OA\Items(type="integer")),
     *                 @OA\Property(property="loveLanguage", type="array", @OA\Items(type="integer")),
     *                 @OA\Property(property="social_energy", type="integer"),
     *                 @OA\Property(property="socialEnergy", type="integer"),
     *                 @OA\Property(property="personality_type", type="integer"),
     *                 @OA\Property(property="personalityType", type="integer"),
     *                 @OA\Property(property="coming_out_status", type="string"),
     *                 @OA\Property(property="comingOutStatus", type="string"),
     *                 @OA\Property(property="show_coming_out_status", type="boolean"),
     *                 @OA\Property(property="showComingOutStatus", type="boolean"),
     *                 @OA\Property(property="religion", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="show_religion", type="boolean"),
     *                 @OA\Property(property="showReligion", type="boolean"),
     *                 @OA\Property(property="political_views", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="politicalViews", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="show_political_views", type="boolean"),
     *                 @OA\Property(property="showPoliticalViews", type="boolean"),
     *                 @OA\Property(property="music_tests", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="musicTests", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="musicTaste", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="languages_spoken", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="languagesSpoken", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="languages_learning", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="languagesLearning", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="languages_written", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="languagesWritten", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="all_show_on_profile", type="boolean"),
     *                 @OA\Property(property="allShowOnProfile", type="boolean")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function profileDetails(Request $request)
    {
        try {
            $userDetails = User::with(['profile', 'hobbies.hobby', 'galleries'])->find(Auth::id());
            if ($userDetails) {
                $status = true;
                $code = 200;
                $response = new ProfileResource($userDetails);
                $message = 'Data Found';

                return $this->responseJson($status, $code, $message, $response);
            } else {
                $status = true;
                $code = 200;
                $response = [];
                $message = 'No Data Found';

                return $this->responseJson($status, $code, $message, $response);
            }
        } catch (\Throwable $th) {
            $status = false;
            $code = 500;
            $response = errorLogAndReturn($th);
            $message = config('constants.CATCH_ERROR_MSG');

            return $this->responseJson($status, $code, $message, $response);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/setup/profile",
     *     summary="Setup User Profile",
     *     tags={"User"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *
     *             @OA\Schema(
     *
     *                 @OA\Property(property="profile_image", type="string", format="binary"),
     *                 @OA\Property(property="about", type="string"),
     *                 @OA\Property(property="dob", type="string", format="date"),
     *                 @OA\Property(property="gender", type="integer"),
     *                 @OA\Property(property="orientation", type="integer"),
     *                 @OA\Property(property="display_name", type="string"),
     *                 @OA\Property(property="hobbies", type="array", @OA\Items(type="integer"), description="Array of hobby item IDs"),
     *                 @OA\Property(property="interests", type="array", @OA\Items(type="integer"), description="Array of interest item IDs"),
     *                 @OA\Property(property="values", type="array", @OA\Items(type="integer"), description="Array of value item IDs"),
     *                 @OA\Property(property="gallery_images[]", type="array", @OA\Items(type="string", format="binary")),
     *                 @OA\Property(property="friends", type="boolean"),
     *                 @OA\Property(property="dates", type="boolean"),
     *                 @OA\Property(property="events_and_communities", type="boolean"),
     *                 @OA\Property(property="guest_mode", type="boolean"),
     *                 @OA\Property(property="relationship_status", type="integer"),
     *                 @OA\Property(property="username", type="string"),
     *                 @OA\Property(property="first_name", type="string"),
     *                 @OA\Property(property="last_name", type="string"),
     *                 @OA\Property(property="agreed_to_terms", type="boolean"),
     *                 @OA\Property(property="what_i_am_looking_for", type="integer"),
     *                 @OA\Property(property="living_in_country", type="string"),
     *                 @OA\Property(property="living_in_city", type="string"),
     *                 @OA\Property(property="show_location_on_profile", type="boolean"),
     *                 @OA\Property(property="currently_traveling", type="boolean"),
     *                 @OA\Property(property="languages_spoken[]", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="languagesSpoken[]", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="languages_learning[]", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="languagesLearning[]", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="languages_written[]", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="languagesWritten[]", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="height", type="integer"),
     *                 @OA\Property(property="body_type", type="integer"),
     *                 @OA\Property(property="bodyType", type="integer"),
     *                 @OA\Property(property="eye_color", type="integer"),
     *                 @OA\Property(property="eyeColor", type="integer"),
     *                 @OA\Property(property="hair_color", type="integer"),
     *                 @OA\Property(property="hairColor", type="integer"),
     *                 @OA\Property(property="hair_length", type="integer"),
     *                 @OA\Property(property="tattoos", type="integer"),
     *                 @OA\Property(property="confirm_18_plus", type="boolean"),
     *                 @OA\Property(property="sex_importance", type="integer"),
     *                 @OA\Property(property="role_position", type="integer"),
     *                 @OA\Property(property="dating_pace", type="integer"),
     *                 @OA\Property(property="datingPace", type="integer"),
     *                 @OA\Property(property="presentation_preference", type="integer"),
     *                 @OA\Property(property="private_album", type="boolean"),
     *                 @OA\Property(property="age_range_min", type="integer"),
     *                 @OA\Property(property="age_range_max", type="integer"),
     *                 @OA\Property(property="dating_preferences[]", type="array", @OA\Items(type="integer")),
     *                 @OA\Property(property="datingPreferences[]", type="array", @OA\Items(type="integer")),
     *                 @OA\Property(property="alcohol", type="integer"),
     *                 @OA\Property(property="smoking", type="integer"),
     *                 @OA\Property(property="exercise", type="integer"),
     *                 @OA\Property(property="diet", type="integer"),
     *                 @OA\Property(property="sleep_rhythm", type="integer"),
     *                 @OA\Property(property="sleepRhythm", type="integer"),
     *                 @OA\Property(property="kids_have", type="integer"),
     *                 @OA\Property(property="kidsHave", type="integer"),
     *                 @OA\Property(property="kids_future", type="integer"),
     *                 @OA\Property(property="kidsFuture", type="integer"),
     *                 @OA\Property(property="pets_current[]", type="array", @OA\Items(type="integer")),
     *                 @OA\Property(property="petsCurrent[]", type="array", @OA\Items(type="integer")),
     *                 @OA\Property(property="pets_future", type="integer"),
     *                 @OA\Property(property="petsFuture", type="integer"),
     *                 @OA\Property(property="living_preference", type="integer"),
     *                 @OA\Property(property="livingPreference", type="integer"),
     *                 @OA\Property(property="travel_importance", type="integer"),
     *                 @OA\Property(property="travelImportance", type="integer"),
     *                 @OA\Property(property="preferred_communication[]", type="array", @OA\Items(type="integer")),
     *                 @OA\Property(property="preferredCommunication[]", type="array", @OA\Items(type="integer")),
     *                 @OA\Property(property="love_language[]", type="array", @OA\Items(type="integer")),
     *                 @OA\Property(property="loveLanguage[]", type="array", @OA\Items(type="integer")),
     *                 @OA\Property(property="social_energy", type="integer"),
     *                 @OA\Property(property="socialEnergy", type="integer"),
     *                 @OA\Property(property="personality_type", type="integer"),
     *                 @OA\Property(property="personalityType", type="integer"),
     *                 @OA\Property(property="education", type="integer"),
     *                 @OA\Property(property="nationality", type="string", description="Nationality can be a string, single ID, or JSON array of strings/IDs"),
     *                 @OA\Property(property="coming_out_status", type="string"),
     *                 @OA\Property(property="comingOutStatus", type="string"),
     *                 @OA\Property(property="show_coming_out_status", type="boolean"),
     *                 @OA\Property(property="showComingOutStatus", type="boolean"),
     *                 @OA\Property(property="religion[]", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="show_religion", type="boolean"),
     *                 @OA\Property(property="showReligion", type="boolean"),
     *                 @OA\Property(property="political_views[]", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="politicalViews[]", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="show_political_views", type="boolean"),
     *                 @OA\Property(property="showPoliticalViews", type="boolean"),
     *                 @OA\Property(property="music_tests[]", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="musicTests[]", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="musicTaste[]", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="all_show_on_profile", type="boolean"),
     *                 @OA\Property(property="allShowOnProfile", type="boolean")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="Profile setup successfully"),
     *     @OA\Response(response=422, description="Validation Error"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function setupProfile(Request $request)
    {
        $id = Auth::user()->id;
        $rules = [
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:102400',
            'hobbies' => 'nullable|array',
            'hobbies.*' => 'exists:hobby_items,id',
            'interests' => 'nullable',
            'interst' => 'nullable',
            'values' => 'nullable|array',
            'values.*' => 'exists:hobby_items,id',
            'gallery_images' => 'nullable|array',
            'gallery_images.*' => 'image|mimes:jpeg,png,jpg,gif,svg,webp|max:102400',
            'about' => 'nullable|string',
            'dob' => 'nullable|date',
            'gender' => 'nullable|integer',
            'orientation' => 'nullable|integer',
            'relationship_status' => 'nullable|integer',
            'display_name' => 'nullable|string|max:255',
            'username' => 'nullable|string|max:255|unique:users,username,'.$id.',id',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'agreed_to_terms' => 'nullable|boolean',
            'what_i_am_looking_for' => 'nullable|integer',
            'living_in_country' => 'nullable|string|max:255',
            'living_in_city' => 'nullable|string|max:255',
            'show_location_on_profile' => 'nullable|boolean',
            'currently_traveling' => 'nullable|boolean',
            'languages_spoken' => 'nullable|array',
            'languages_spoken.*' => 'nullable',
            'languagesSpoken' => 'nullable|array',
            'languagesSpoken.*' => 'nullable',
            'languages' => 'nullable|array',
            'languages.*' => 'nullable',
            'language' => 'nullable|array',
            'language.*' => 'nullable',
            'languages_learning' => 'nullable|array',
            'languages_learning.*' => 'nullable',
            'languagesLearning' => 'nullable|array',
            'languagesLearning.*' => 'nullable',
            'nationality' => 'nullable',
            'coming_out_status' => 'nullable|string|max:255',
            'comingOutStatus' => 'nullable|string|max:255',
            'show_coming_out_status' => 'nullable|boolean',
            'showComingOutStatus' => 'nullable|boolean',
            'religion' => 'nullable|array',
            'religion.*' => 'nullable',
            'show_religion' => 'nullable|boolean',
            'showReligion' => 'nullable|boolean',
            'political_views' => 'nullable|array',
            'political_views.*' => 'nullable',
            'politicalViews' => 'nullable|array',
            'politicalViews.*' => 'nullable',
            'show_political_views' => 'nullable|boolean',
            'showPoliticalViews' => 'nullable|boolean',
            'music_tests' => 'nullable|array',
            'music_tests.*' => 'nullable',
            'Music_tests' => 'nullable|array',
            'Music_tests.*' => 'nullable',
            'Music_taste' => 'nullable|array',
            'Music_taste.*' => 'nullable',
            'music_taste' => 'nullable|array',
            'music_taste.*' => 'nullable',
            'musicTests' => 'nullable|array',
            'musicTests.*' => 'nullable',
            'musicTaste' => 'nullable|array',
            'musicTaste.*' => 'nullable',
            'height' => 'nullable|integer',
            'body_type' => 'nullable|integer',
            'eye_color' => 'nullable|integer',
            'hair_color' => 'nullable|integer',
            'hair_length' => 'nullable|integer',
            'tattoos' => 'nullable|integer',
            'confirm_18_plus' => 'nullable|boolean',
            'occupation' => 'nullable|string|max:255',
            'weight' => 'nullable|string|max:255',
            'zodiac' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'languages_written' => 'nullable|array',
            'languages_written.*' => 'nullable',
            'languagesWritten' => 'nullable|array',
            'languagesWritten.*' => 'nullable',
            'drinking' => 'nullable|integer',
            'sex_importance' => 'nullable|integer',
            'role_position' => 'nullable|integer',
            'dating_pace' => 'nullable|integer',
            'presentation_preference' => 'nullable|integer',
            'importance' => 'nullable|integer',
            'role' => 'nullable|integer',
            'datingPace' => 'nullable|integer',
            'presentation' => 'nullable|integer',
            'AllshowOnProfile' => 'nullable|boolean',
            'allShowOnProfile' => 'nullable|boolean',
            'bodyType' => 'nullable|integer',
            'hairColor' => 'nullable|integer',
            'eye' => 'nullable|integer',
            'eyeColor' => 'nullable|integer',
            'relationshipStatus' => 'nullable|integer',
            'looking_for' => 'nullable|integer',
            'lookingFor' => 'nullable|integer',
            'private_album' => 'nullable|boolean',
            'age_range_min' => 'nullable|integer',
            'age_range_max' => 'nullable|integer',
            'dating_preferences' => 'nullable',
            'dating_preferances' => 'nullable',
            'datingPreferences' => 'nullable',
            'alcohol' => 'nullable|integer',
            'smoking' => 'nullable|integer',
            'exercise' => 'nullable|integer',
            'diet' => 'nullable|integer',
            'sleep_rhythm' => 'nullable|integer',
            'sleepRhythm' => 'nullable|integer',
            'kids_have' => 'nullable|integer',
            'kidsHave' => 'nullable|integer',
            'kids_future' => 'nullable|integer',
            'kidsFuture' => 'nullable|integer',
            'pets_current' => 'nullable|array',
            'pets_current.*' => 'integer',
            'petsCurrent' => 'nullable|array',
            'petsCurrent.*' => 'integer',
            'pets_future' => 'nullable|integer',
            'petsFuture' => 'nullable|integer',
            'living_preference' => 'nullable|integer',
            'livingPreference' => 'nullable|integer',
            'travel_importance' => 'nullable|integer',
            'travelImportance' => 'nullable|integer',
            'preferred_communication' => 'nullable|array',
            'preferred_communication.*' => 'integer',
            'preferredCommunication' => 'nullable|array',
            'preferredCommunication.*' => 'integer',
            'love_language' => 'nullable|array',
            'love_language.*' => 'integer',
            'loveLanguage' => 'nullable|array',
            'loveLanguage.*' => 'integer',
            'social_energy' => 'nullable|integer',
            'socialEnergy' => 'nullable|integer',
            'personality_type' => 'nullable|integer',
            'personalityType' => 'nullable|integer',
            'education' => 'nullable|integer',
        ];

        if ($request->has('dob') && $request->input('dob') !== null) {
            try {
                $dob = $request->input('dob');
                if (strpos($dob, '/') !== false) {
                    $parts = explode('/', $dob);
                    if (strlen($parts[0]) !== 4) {
                        $dob = \Carbon\Carbon::createFromFormat('d/m/Y', $dob)->format('Y-m-d');
                    } else {
                        $dob = \Carbon\Carbon::parse($dob)->format('Y-m-d');
                    }
                } else {
                    if (strpos($dob, '-') !== false) {
                        $parts = explode('-', $dob);
                        if (strlen($parts[0]) !== 4) {
                            $dob = \Carbon\Carbon::createFromFormat('d-m-Y', $dob)->format('Y-m-d');
                        } else {
                            $dob = \Carbon\Carbon::parse($dob)->format('Y-m-d');
                        }
                    } else {
                        $dob = \Carbon\Carbon::parse($dob)->format('Y-m-d');
                    }
                }
                $request->merge(['dob' => $dob]);
            } catch (\Throwable $e) {
                // Ignore parsing errors and let the validator catch them
            }
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->responseJson(false, 422, $validator->errors()->first(), []);
        }

        DB::beginTransaction();
        try {
            $user = User::find($id);

            // Update User Table
            if ($request->hasFile('profile_image')) {
                $image = $request->file('profile_image');
                $fileName = uniqid().'.'.$image->getClientOriginalExtension();
                $this->uploadOne($image, config('constants.SITE_PROFILE_IMAGE_UPLOAD_PATH'), $fileName, 'public');
                $user->profile_image = $fileName;
            }
            if ($request->username) {
                $user->username = $request->username;
            }
            
            // Generate full name if first_name or last_name is provided
            if ($request->has('first_name') || $request->has('last_name')) {
                $firstName = $request->input('first_name') ?? $user->profile?->first_name ?? '';
                $lastName = $request->input('last_name') ?? $user->profile?->last_name ?? '';
                $user->name = trim($firstName . ' ' . $lastName);
            }
            
            $user->save();

            // Update/Create Profile Table
            $profileFields = [
                'display_name', 'about', 'dob', 'gender', 'orientation', 'relationship_status',
                'age', 'age_range', 'distance_range', 'friends', 'dates',
                'events_and_communities', 'hookups', 'guest_mode', 'verified_profiles',
                'invite_only_access', 'no_tracking', 'everyone', 'selected_groups', 'no_one_at_all',
                'first_name', 'last_name', 'agreed_to_terms', 'what_i_am_looking_for',
                'living_in_country', 'living_in_city', 'show_location_on_profile',
                'currently_traveling', 'height', 'body_type', 'eye_color', 'hair_color',
                'hair_length', 'tattoos',
                'confirm_18_plus', 'sex_importance', 'role_position', 'dating_pace',
                'presentation_preference', 'private_album', 'age_range_min', 'age_range_max',
                'alcohol', 'smoking', 'exercise', 'diet', 'sleep_rhythm', 'kids_have', 'kids_future',
                'pets_future', 'living_preference', 'travel_importance', 'social_energy',
                'personality_type', 'education',
                'nationality', 'coming_out_status', 'show_coming_out_status', 'show_religion', 'show_political_views',
                'occupation', 'weight', 'zodiac', 'location', 'all_show_on_profile'
            ];

            $profileData = [];
            foreach ($profileFields as $field) {
                if ($request->has($field)) {
                    $value = $request->input($field);
                    if ($value === 'true') {
                        $value = 1;
                    } elseif ($value === 'false') {
                        $value = 0;
                    }
                    $profileData[$field] = $value;
                }
            }

            // Normalise date of birth (dob) to Y-m-d format
            if (!empty($profileData['dob'])) {
                try {
                    $dob = $profileData['dob'];
                    if (strpos($dob, '/') !== false) {
                        $parts = explode('/', $dob);
                        if (strlen($parts[0]) !== 4) {
                            $profileData['dob'] = \Carbon\Carbon::createFromFormat('d/m/Y', $dob)->format('Y-m-d');
                        } else {
                            $profileData['dob'] = \Carbon\Carbon::parse($dob)->format('Y-m-d');
                        }
                    } else {
                        if (strpos($dob, '-') !== false) {
                            $parts = explode('-', $dob);
                            if (strlen($parts[0]) !== 4) {
                                $profileData['dob'] = \Carbon\Carbon::createFromFormat('d-m-Y', $dob)->format('Y-m-d');
                            } else {
                                $profileData['dob'] = \Carbon\Carbon::parse($dob)->format('Y-m-d');
                            }
                        } else {
                            $profileData['dob'] = \Carbon\Carbon::parse($dob)->format('Y-m-d');
                        }
                    }
                } catch (\Throwable $e) {
                    // Fallback to raw value if parsing fails
                }
            }

            // Handle dating_preferences array / id / comma-separated list
            // Handle dating_preferences array / id / comma-separated list
            $datingPrefsInput = $request->input('dating_preferences') ?? $request->input('dating_preferances') ?? $request->input('datingPreferences');
            if ($datingPrefsInput !== null) {
                if (is_array($datingPrefsInput)) {
                    $profileData['dating_preferences'] = json_encode(array_map('intval', $datingPrefsInput));
                } elseif (is_string($datingPrefsInput) && strpos($datingPrefsInput, ',') !== false) {
                    $profileData['dating_preferences'] = json_encode(array_map('intval', array_filter(explode(',', $datingPrefsInput))));
                } else {
                    $profileData['dating_preferences'] = json_encode([intval($datingPrefsInput)]);
                }
            }

            // Handle languages_spoken / languages / language / languagesSpoken array
            if ($request->has('languages_spoken') || $request->has('languages') || $request->has('language') || $request->has('languagesSpoken')) {
                $val = $request->input('languages_spoken') ?? $request->input('languages') ?? $request->input('language') ?? $request->input('languagesSpoken');
                $profileData['languages_spoken'] = is_array($val) ? json_encode($val) : $val;
            }

            // Handle languages_learning / languagesLearning array
            if ($request->has('languages_learning') || $request->has('languagesLearning')) {
                $val = $request->input('languages_learning') ?? $request->input('languagesLearning');
                $profileData['languages_learning'] = is_array($val) ? json_encode($val) : $val;
            }

            // Handle pets_current / petsCurrent array
            if ($request->has('pets_current') || $request->has('petsCurrent')) {
                $val = $request->input('pets_current') ?? $request->input('petsCurrent');
                $profileData['pets_current'] = is_array($val) ? json_encode($val) : $val;
            }

            // Handle preferred_communication / preferredCommunication array
            if ($request->has('preferred_communication') || $request->has('preferredCommunication')) {
                $val = $request->input('preferred_communication') ?? $request->input('preferredCommunication');
                $profileData['preferred_communication'] = is_array($val) ? json_encode($val) : $val;
            }

            // Handle love_language / loveLanguage array
            if ($request->has('love_language') || $request->has('loveLanguage')) {
                $val = $request->input('love_language') ?? $request->input('loveLanguage');
                $profileData['love_language'] = is_array($val) ? json_encode($val) : $val;
            }

            // Handle religion array
            if ($request->has('religion')) {
                $val = $request->input('religion');
                $profileData['religion'] = is_array($val) ? json_encode($val) : $val;
            }

            // Handle political_views / politicalViews array
            if ($request->has('political_views') || $request->has('politicalViews')) {
                $val = $request->input('political_views') ?? $request->input('politicalViews');
                $profileData['political_views'] = is_array($val) ? json_encode($val) : $val;
            }

            // Handle music_tests / musicTests / musicTaste / etc. array
            if ($request->has('music_tests') || $request->has('Music_tests') || $request->has('Music_taste') || $request->has('music_taste') || $request->has('musicTests') || $request->has('musicTaste')) {
                $val = $request->input('music_tests') ?? $request->input('Music_tests') ?? $request->input('Music_taste') ?? $request->input('music_taste') ?? $request->input('musicTests') ?? $request->input('musicTaste');
                $profileData['music_tests'] = is_array($val) ? json_encode($val) : $val;
            }

            // Handle languages_written / languagesWritten array
            if ($request->has('languages_written') || $request->has('languagesWritten')) {
                $val = $request->input('languages_written') ?? $request->input('languagesWritten');
                $profileData['languages_written'] = is_array($val) ? json_encode($val) : $val;
            }

            // Handle nationality array / ID / string
            if ($request->has('nationality')) {
                $val = $request->input('nationality');
                $profileData['nationality'] = is_array($val) ? json_encode($val) : $val;
            }

            // Handle drinking input
            if ($request->has('drinking')) {
                $profileData['alcohol'] = $request->input('drinking');
            }

            // Handle camelCase mappings for scalar fields
            $camelCaseMappings = [
                'sleepRhythm' => 'sleep_rhythm',
                'kidsHave' => 'kids_have',
                'kidsFuture' => 'kids_future',
                'petsFuture' => 'pets_future',
                'livingPreference' => 'living_preference',
                'travelImportance' => 'travel_importance',
                'socialEnergy' => 'social_energy',
                'personalityType' => 'personality_type',
                'comingOutStatus' => 'coming_out_status',
                'showComingOutStatus' => 'show_coming_out_status',
                'showReligion' => 'show_religion',
                'showPoliticalViews' => 'show_political_views',
                'allShowOnProfile' => 'all_show_on_profile',
                'AllshowOnProfile' => 'all_show_on_profile',
                'bodyType' => 'body_type',
                'hairColor' => 'hair_color',
                'eyeColor' => 'eye_color',
                'relationshipStatus' => 'relationship_status',
                'lookingFor' => 'what_i_am_looking_for',
                'looking_for' => 'what_i_am_looking_for',
                'datingPace' => 'dating_pace',
            ];

            foreach ($camelCaseMappings as $camelKey => $snakeKey) {
                if ($request->has($camelKey)) {
                    $value = $request->input($camelKey);
                    if ($value === 'true') {
                        $value = 1;
                    } elseif ($value === 'false') {
                        $value = 0;
                    }
                    $profileData[$snakeKey] = $value;
                }
            }

            // Handle aliases for sex importance, role, dating pace, presentation, and show on profile toggle
            if ($request->has('importance')) {
                $profileData['sex_importance'] = $request->input('importance');
            }
            if ($request->has('role')) {
                $profileData['role_position'] = $request->input('role');
            }
            if ($request->has('datingPace')) {
                $profileData['dating_pace'] = $request->input('datingPace');
            }
            if ($request->has('presentation')) {
                $profileData['presentation_preference'] = $request->input('presentation');
            }
            if ($request->has('AllshowOnProfile')) {
                $val = $request->input('AllshowOnProfile');
                if ($val === 'true') {
                    $val = 1;
                } elseif ($val === 'false') {
                    $val = 0;
                }
                $profileData['all_show_on_profile'] = $val;
            }

            // Handle aliases/camelCase for body_type, hair_color, eye_color, relationship_status, what_i_am_looking_for
            if ($request->has('bodyType')) {
                $profileData['body_type'] = $request->input('bodyType');
            }
            if ($request->has('hairColor')) {
                $profileData['hair_color'] = $request->input('hairColor');
            }
            if ($request->has('eye')) {
                $profileData['eye_color'] = $request->input('eye');
            }
            if ($request->has('eyeColor')) {
                $profileData['eye_color'] = $request->input('eyeColor');
            }
            if ($request->has('relationshipStatus')) {
                $profileData['relationship_status'] = $request->input('relationshipStatus');
            }
            if ($request->has('looking_for')) {
                $profileData['what_i_am_looking_for'] = $request->input('looking_for');
            }
            if ($request->has('lookingFor')) {
                $profileData['what_i_am_looking_for'] = $request->input('lookingFor');
            }

            $user->profile()->updateOrCreate(['user_id' => $id], $profileData);

            // Sync Hobbies, Interests, and Values
            if ($request->has('hobbies') || $request->has('interests') || $request->has('interst') || $request->has('values')) {
                // Get all current pivot item IDs
                $currentHobbyItemIds = $user->hobbies()->pluck('hobby_items.id')->toArray();
                
                // Get the items grouped by their hobby category type
                $currentItems = \App\Models\HobbyItem::with('hobby')
                    ->whereIn('id', $currentHobbyItemIds)
                    ->get();
                
                $hobbiesIds = $currentItems->filter(fn($item) => $item->hobby?->type == 1)->pluck('id')->toArray();
                $valuesIds = $currentItems->filter(fn($item) => $item->hobby?->type == 5)->pluck('id')->toArray();
                $interestsIds = $currentItems->filter(fn($item) => $item->hobby?->type == 6)->pluck('id')->toArray();
                
                // Override with request inputs if present
                if ($request->has('hobbies')) {
                    $hobbiesInput = $request->hobbies;
                    $hobbiesIds = is_array($hobbiesInput) ? $hobbiesInput : array_filter(explode(',', $hobbiesInput));
                }
                if ($request->has('values')) {
                    $valuesInput = $request->values;
                    $valuesIds = is_array($valuesInput) ? $valuesInput : array_filter(explode(',', $valuesInput));
                }
                if ($request->has('interests') || $request->has('interst')) {
                    $interestsInput = $request->input('interests') ?? $request->input('interst');
                    if (is_array($interestsInput)) {
                        // Check if it's a nested array structure (where values are arrays)
                        $isNested = false;
                        foreach ($interestsInput as $val) {
                            if (is_array($val)) {
                                $isNested = true;
                                break;
                            }
                        }
                        if ($isNested) {
                            $interestsIds = [];
                            foreach ($interestsInput as $cat => $items) {
                                if (is_array($items)) {
                                    foreach ($items as $itm) {
                                        if ($itm !== null && $itm !== '') {
                                            $interestsIds[] = $itm;
                                        }
                                    }
                                }
                            }
                        } else {
                            $interestsIds = $interestsInput;
                        }
                    } else {
                        $interestsIds = array_filter(explode(',', $interestsInput));
                    }
                }
                
                // Merge all
                $allSyncIds = array_unique(array_merge($hobbiesIds, $valuesIds, $interestsIds));
                
                // Sync to pivot table
                $user->hobbies()->sync($allSyncIds);
            }

            // Handle Gallery Images
            if ($request->hasFile('gallery_images')) {
                $files = $request->file('gallery_images');
                if (! is_array($files)) {
                    $files = [$files];
                }
                foreach ($files as $key => $file) {
                    $fileName = uniqid().'.'.$file->getClientOriginalExtension();
                    $this->uploadOne($file, config('constants.SITE_GALLERY_IMAGE_UPLOAD_PATH'), $fileName, 'public');

                    $user->galleries()->create([
                        'file' => $fileName,
                        'type' => 1, // Image
                        'level' => $key + 1,
                        'is_active' => 1,
                    ]);
                }
            }

            DB::commit();

            $userDetails = User::with(['profile', 'hobbies.hobby', 'galleries'])->find($id);

            return $this->responseJson(true, 200, 'Profile setup successfully', new ProfileResource($userDetails));

        } catch (\Throwable $th) {
            DB::rollBack();

            return $this->responseJson(false, 500, config('constants.CATCH_ERROR_MSG'), errorLogAndReturn($th));
        }
    }

    /**
     * @OA\Post(
     *     path="/api/change/password",
     *     summary="Change User Password",
     *     tags={"User"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"old_password","new_password","confirm_password"},
     *
     *             @OA\Property(property="old_password", type="string", format="password", example="oldpassword123"),
     *             @OA\Property(property="new_password", type="string", format="password", example="newpassword123"),
     *             @OA\Property(property="confirm_password", type="string", format="password", example="newpassword123")
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="Password changed successfully"),
     *     @OA\Response(response=422, description="Validation Error"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function changePassword(Request $request)
    {
        $rules = [
            'old_password' => 'required|string',
            'new_password' => 'required|string|min:6',
            'confirm_password' => 'required|string|same:new_password',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->responseJson(false, 422, $validator->errors()->first(), []);
        }

        try {
            $user = Auth::user();

            if (!Hash::check($request->old_password, $user->password)) {
                return $this->responseJson(false, 422, 'The old password does not match.', []);
            }

            $user->password = Hash::make($request->new_password);
            $user->original_password = $request->new_password;
            $user->save();

            return $this->responseJson(true, 200, 'Password changed successfully.', []);

        } catch (\Throwable $th) {
            return $this->responseJson(false, 500, config('constants.CATCH_ERROR_MSG'), errorLogAndReturn($th));
        }
    }

    /**
     * @OA\Get(
     *     path="/api/login-history",
     *     summary="Get User Login History",
     *     tags={"User"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         description="Number of records per page",
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         description="Page number",
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Response(response=200, description="Login history retrieved successfully"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function loginHistory(Request $request)
    {
        try {
            $user = Auth::user();
            $perPage = $request->input('per_page', 10);
            
            $history = \App\Models\LoginHistory::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return LoginHistoryResource::collection($history)->additional([
                'status' => true,
                'response_code' => 200,
                'message' => 'Login history retrieved successfully.',
            ]);

        } catch (\Throwable $th) {
            return $this->responseJson(false, 500, config('constants.CATCH_ERROR_MSG'), errorLogAndReturn($th));
        }
    }

    /**
     * @OA\Post(
     *     path="/api/trusted-email/add",
     *     summary="Add Trusted Email Address",
     *     tags={"User"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="email", example="trusted@example.com")
     *         )
     *     ),
     *     @OA\Response(response=200, description="OTP generated successfully for trusted email"),
     *     @OA\Response(response=422, description="Validation Error"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function addTrustedEmail(Request $request)
    {
        $rules = [
            'email' => 'required|email|max:255',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->responseJson(false, 422, $validator->errors()->first(), []);
        }

        try {
            $user = Auth::user();
            
            // Generate a 4-digit OTP
            $otp = generateOtp(4);

            $user->trusted_email = $request->email;
            $user->trusted_email_otp = $otp;
            $user->is_trusted_email_verified = 0;
            $user->save();

            return $this->responseJson(true, 200, 'OTP generated successfully for trusted email.', [
                'otp' => $otp,
                'email' => $request->email,
            ]);

        } catch (\Throwable $th) {
            return $this->responseJson(false, 500, config('constants.CATCH_ERROR_MSG'), errorLogAndReturn($th));
        }
    }

    /**
     * @OA\Post(
     *     path="/api/trusted-email/verify",
     *     summary="Verify Trusted Email Address",
     *     tags={"User"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","otp"},
     *             @OA\Property(property="email", type="string", format="email", example="trusted@example.com"),
     *             @OA\Property(property="otp", type="string", example="1234")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Trusted email verified successfully"),
     *     @OA\Response(response=422, description="Validation Error / Incorrect OTP"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function verifyTrustedEmail(Request $request)
    {
        $rules = [
            'email' => 'required|email',
            'otp' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->responseJson(false, 422, $validator->errors()->first(), []);
        }

        try {
            $user = Auth::user();

            if ($user->trusted_email === $request->email && $user->trusted_email_otp === $request->otp) {
                $user->is_trusted_email_verified = 1;
                $user->trusted_email_otp = null;
                $user->save();

                return $this->responseJson(true, 200, 'Trusted email verified successfully.', [
                    'trusted_email' => $user->trusted_email,
                    'is_trusted_email_verified' => true,
                ]);
            } else {
                return $this->responseJson(false, 422, 'The OTP or email does not match.', []);
            }

        } catch (\Throwable $th) {
            return $this->responseJson(false, 500, config('constants.CATCH_ERROR_MSG'), errorLogAndReturn($th));
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/delete-account",
     *     summary="Delete User Account",
     *     description="Permanently deletes the authenticated user's account and all associated data, including profiles, posts, media, messages, statuses, etc.",
     *     tags={"User"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Account deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="response_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Your account and all associated data have been permanently deleted.")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function deleteAccount(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return $this->responseJson(false, 401, 'User not authenticated', []);
            }

            // 1. Gather all file paths from database before deletion
            $profileImage = $user->profile_image;
            $galleryFiles = $user->galleries()->pluck('file')->toArray();

            $postMediaFiles = DB::table('post_media')
                ->join('posts', 'post_media.post_id', '=', 'posts.id')
                ->where('posts.user_id', $user->id)
                ->pluck('post_media.file')
                ->toArray();

            $statusFiles = $user->statuses()
                ->whereIn('type', ['image', 'video'])
                ->pluck('content')
                ->toArray();

            $kycFiles = [];
            if ($user->kycVerification) {
                if ($user->kycVerification->govt_id_image) {
                    $kycFiles[] = $user->kycVerification->govt_id_image;
                }
                if ($user->kycVerification->identity_image) {
                    $kycFiles[] = $user->kycVerification->identity_image;
                }
            }

            $bugImages = \App\Models\Bug::where('user_id', $user->id)
                ->whereNotNull('image')
                ->pluck('image')
                ->toArray();

            $messageAttachments = DB::table('messages')
                ->where('sender_id', $user->id)
                ->whereNotNull('attachment')
                ->pluck('attachment')
                ->toArray();

            // 2. Perform deletion inside database transaction
            DB::beginTransaction();

            // Revoke Passport tokens
            DB::table('oauth_access_tokens')->where('user_id', $user->id)->delete();

            // Force delete user. DB constraints automatically cascade delete all related database rows.
            $user->forceDelete();

            DB::commit();

            // 3. Clean up physical files from disk
            // Profile image
            if ($profileImage) {
                $path = storage_path('app/public/profile/' . $profileImage);
                if (file_exists($path)) {
                    @unlink($path);
                }
            }

            // Gallery images
            foreach ($galleryFiles as $file) {
                $path = storage_path('app/public/gallery/' . $file);
                if (file_exists($path)) {
                    @unlink($path);
                }
            }

            // Post media
            foreach ($postMediaFiles as $file) {
                $path = public_path($file);
                if (file_exists($path)) {
                    @unlink($path);
                }
            }

            // Status media
            foreach ($statusFiles as $file) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($file);
            }

            // KYC images
            foreach ($kycFiles as $file) {
                $path = storage_path('app/public/kyc/' . $file);
                if (file_exists($path)) {
                    @unlink($path);
                }
            }

            // Bug screenshots
            foreach ($bugImages as $image) {
                $path = storage_path('app/public/bugs/' . $image);
                if (file_exists($path)) {
                    @unlink($path);
                }
            }

            // Chat attachments
            foreach ($messageAttachments as $attachment) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($attachment);
            }

            return $this->responseJson(true, 200, 'Your account and all associated data have been permanently deleted.', []);

        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->responseJson(false, 500, config('constants.CATCH_ERROR_MSG'), errorLogAndReturn($th));
        }
    }

    /**
     * @OA\Post(
     *     path="/api/user/pause-notifications",
     *     summary="Toggle Pause all Notifications",
     *     description="Enable or disable pause all notifications (Quiet Mode)",
     *     tags={"User"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"pause_notifications"},
     *             @OA\Property(property="pause_notifications", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Notification pause status updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="response_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Notification pause status updated successfully."),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation Error"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function togglePauseNotifications(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pause_notifications' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return $this->responseJson(false, 422, $validator->errors()->first(), []);
        }

        try {
            $user = Auth::user();
            $user->pause_notifications = $request->pause_notifications;
            $user->save();

            return $this->responseJson(true, 200, 'Notification pause status updated successfully.', [
                'pause_notifications' => (bool)$user->pause_notifications
            ]);
        } catch (\Throwable $th) {
            return $this->responseJson(false, 500, config('constants.CATCH_ERROR_MSG'), errorLogAndReturn($th));
        }
    }
}



