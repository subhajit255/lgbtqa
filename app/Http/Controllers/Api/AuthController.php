<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Http\Resources\Api\Auth\BannerResource;
use App\Http\Resources\Api\Auth\BlogResource;
use App\Http\Resources\Api\Auth\CategoryResource;
use App\Http\Resources\Api\Auth\CmsResource;
use App\Http\Resources\Api\Auth\SettingResource;
use App\Http\Resources\Api\User\ProfileResource;
use App\Models\Banner;
use App\Models\Blog;
use App\Models\Category;
use App\Models\Cms;
use App\Models\Setting;
use App\Models\User;
use App\Models\LoginHistory;
use App\Models\Notification;
use App\Traits\CommonFunction;
use App\Traits\SmsTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AuthController extends BaseController
{
    use CommonFunction;
    use SmsTrait;
    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="User Login",
     *     tags={"Authentication"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"email","password"},
     *
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Login Successfully or 2FA OTP sent",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="response_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Login Successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="token", type="string", description="Only returned if 2FA is not enabled"),
     *                 @OA\Property(property="user", type="object", description="Only returned if 2FA is not enabled"),
     *                 @OA\Property(property="otp", type="string", description="Only returned if 2FA is enabled"),
     *                 @OA\Property(property="two_factor_required", type="boolean", description="Only returned if 2FA is enabled"),
     *                 @OA\Property(property="two_factor_auth", type="boolean", description="Indicates if 2FA is enabled (true/false)"),
     *                 @OA\Property(property="email", type="string", description="Only returned if 2FA is enabled")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=422, description="Validation Error")
     * )
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);
        if ($validator->fails()) {
            $status = false;
            $code = 422;
            $response = [];
            $message = $validator->errors()->first();

            return $this->responseJson($status, $code, $message, $response);
        }

        try {
            $credentials = $request->only('email', 'password');
            $user = User::where('email', $request->email)->first();

            if ($user) {
                if ($user->is_active == 0) {
                    return $this->responseJson(false, 422, 'Your account is inactive. Please contact admin.', []);
                }
                if ($user->is_approve == 0) {
                    return $this->responseJson(false, 422, 'Your account is not approved yet.', []);
                }
                if ($user->is_blocked == 1) {
                    return $this->responseJson(false, 422, 'Your account has been blocked.', []);
                }
                if ($user->is_verified_email == 0) {
                    return $this->responseJson(false, 422, 'Your account has not been verified yet.', []);
                }

                if (Hash::check($request->password, $user->password)) {
                    // Check if two factor authentication is enabled in user settings
                    $settings = $user->appSettingToggle;
                    $isTwoFactorEnabled = $settings ? (bool)$settings->two_factor_auth : false;

                    if ($isTwoFactorEnabled) {
                        $otp = generateOtp(4);
                        $user->update([
                            'verification_code' => $otp,
                        ]);

                        // Send the 2FA OTP email
                        try {
                            Mail::to($user->email)->send(new \App\Mail\TwoFactorOtpMail($otp));
                        } catch (\Exception $e) {
                        }

                        return $this->responseJson(true, 200, 'Two factor authentication OTP sent successfully.', [
                            'otp' => $otp,
                            'two_factor_required' => true,
                            'two_factor_auth' => true,
                            'email' => $user->email,
                        ]);
                    }

                    $token = $user->createToken('Login Successfully')->accessToken;

                    LoginHistory::create([
                        'user_id' => $user->id,
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                    ]);

                    Notification::create([
                        'user_id' => $user->id,
                        'title' => 'Login Alert',
                        'description' => 'You logged in successfully from IP ' . $request->ip() . '.',
                        'type' => 'login',
                        'for' => 2,
                        'is_read' => 0,
                        'is_active' => 1,
                    ]);

                    $status = true;
                    $code = 200;
                    $response = [
                        'token' => $token,
                        'user' => new ProfileResource($user),
                        'two_factor_auth' => false,
                    ];
                    $message = 'Login Successfully';

                    return $this->responseJson($status, $code, $message, $response);
                } else {
                    return $this->responseJson(false, 422, 'Invalid Password', []);
                }
            } else {
                return $this->responseJson(false, 422, 'User not found', []);
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
     *     path="/api/twofactor/verify",
     *     summary="Two Factor Authentication OTP Verification",
     *     tags={"Authentication"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"email","otp"},
     *
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="otp", type="string", example="1234")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Otp Verify Successfully & Login Successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="response_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Otp Verify Successfully & Login Successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="token", type="string"),
     *                 @OA\Property(property="user", type="object")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=422, description="Validation Error / Otp doesn't match")
     * )
     */
    public function twoFactorVerify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return $this->responseJson(false, 422, $validator->errors()->first(), []);
        }

        try {
            $user = User::where([
                'email' => $request->email,
                'verification_code' => $request->otp,
            ])->first();

            if ($user) {
                $user->update([
                    'verification_code' => null,
                    'last_login_ip' => $request->ip(),
                    'last_login_at' => now(),
                ]);

                $token = $user->createToken('Login Successfully')->accessToken;

                LoginHistory::create([
                    'user_id' => $user->id,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);

                Notification::create([
                    'user_id' => $user->id,
                    'title' => 'Login Alert',
                    'description' => 'You logged in successfully from IP ' . $request->ip() . '.',
                    'type' => 'login',
                    'for' => 2,
                    'is_read' => 0,
                    'is_active' => 1,
                ]);

                return $this->responseJson(true, 200, 'Otp Verify Successfully & Login Successfully', [
                    'token' => $token,
                    'user' => new ProfileResource($user)
                ]);
            } else {
                return $this->responseJson(false, 422, 'Otp doesn\'t match', []);
            }
        } catch (\Throwable $th) {
            return $this->responseJson(false, 500, config('constants.CATCH_ERROR_MSG'), errorLogAndReturn($th));
        }
    }

    /**
     * @OA\Post(
     *     path="/api/social/login",
     *     summary="Social Login",
     *     tags={"Authentication"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"name","email"},
     *
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Login Successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="response_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Login Successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="token", type="string"),
     *                 @OA\Property(property="user", type="object")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=422, description="Validation Error")
     * )
     */
    public function socialLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return $this->responseJson(false, 422, $validator->errors()->first(), []);
        }

        DB::beginTransaction();
        try {
            $user = User::where('email', $request->email)->first();

            $isNewUser = false;
            if ($user) {
                if ($user->is_active == 0) {
                    DB::rollBack();

                    return $this->responseJson(false, 422, 'Your account is inactive. Please contact admin.', []);
                }
                if ($user->is_approve == 0) {
                    DB::rollBack();

                    return $this->responseJson(false, 422, 'Your account is not approved yet.', []);
                }
                if ($user->is_blocked == 1) {
                    DB::rollBack();

                    return $this->responseJson(false, 422, 'Your account has been blocked.', []);
                }
            } else {
                $password = \Illuminate\Support\Str::random(10);
                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => Hash::make($password),
                    'original_password' => $password,
                    'is_verified_email' => 1,
                    'is_active' => 1,
                    'is_approve' => 1,
                ]);
                $isNewUser = true;
            }

            $token = $user->createToken('Login Successfully')->accessToken;

            LoginHistory::create([
                'user_id' => $user->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            if ($isNewUser) {
                Notification::create([
                    'user_id' => $user->id,
                    'title' => 'Welcome to LGBTQIA',
                    'description' => 'Your account has been created successfully.',
                    'type' => 'signup',
                    'for' => 2,
                    'is_read' => 0,
                    'is_active' => 1,
                ]);
            }

            Notification::create([
                'user_id' => $user->id,
                'title' => 'Login Alert',
                'description' => 'You logged in successfully from IP ' . $request->ip() . '.',
                'type' => 'login',
                'for' => 2,
                'is_read' => 0,
                'is_active' => 1,
            ]);

            DB::commit();

            $status = true;
            $code = 200;
            $response = ['token' => $token, 'user' => new ProfileResource($user)];
            $message = 'Login Successfully';

            return $this->responseJson($status, $code, $message, $response);
        } catch (\Throwable $th) {
            DB::rollBack();
            $status = false;
            $code = 500;
            $response = errorLogAndReturn($th);
            $message = config('constants.CATCH_ERROR_MSG');

            return $this->responseJson($status, $code, $message, $response);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/signup",
     *     summary="User Signup",
     *     tags={"Authentication"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"name","email","password","password_confirmation"},
     *
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="password123")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Signup Successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="response_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Signup Successfully, Please verify your email"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="otp", type="integer", example=1234),
     *                 @OA\Property(property="user", type="object")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=422, description="Validation Error")
     * )
     */
    public function signup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'password_confirmation' => 'required|string|min:6|same:password',
        ]);
        if ($validator->fails()) {
            $status = false;
            $code = 422;
            $response = [];
            $message = $validator->errors()->first();

            return $this->responseJson($status, $code, $message, $response);
        }
        DB::beginTransaction();
        try {
            $otp = generateOtp(4);
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'original_password' => $request->password,
                'verification_code' => $otp,
            ]);

            Notification::create([
                'user_id' => $user->id,
                'title' => 'Welcome to LGBTQIA',
                'description' => 'Your account has been created successfully. Please verify your email to get started.',
                'type' => 'signup',
                'for' => 2,
                'is_read' => 0,
                'is_active' => 1,
            ]);
            DB::commit();
            $status = true;
            $code = 200;
            $response = ['otp' => $otp, 'user' => new ProfileResource($user)];
            $message = 'Signup Successfully, Please verify your email';
        } catch (\Throwable $th) {
            DB::rollBack();
            $status = false;
            $code = 500;
            $response = errorLogAndReturn($th);
            $message = config('constants.CATCH_ERROR_MSG');
        }

        return $this->responseJson($status, $code, $message, $response);
    }

    /**
     * @OA\Post(
     *     path="/api/email/verification",
     *     summary="Email Verification",
     *     tags={"Authentication"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"email","verification_code"},
     *
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="verification_code", type="integer", example=1234),
     *             @OA\Property(property="fcm_token", type="string"),
     *             @OA\Property(property="device_type", type="integer", description="1: Android, 2: iOS")
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="Otp Verify Successfully"),
     *     @OA\Response(response=422, description="Validation Error / Otp doesn't match")
     * )
     */
    public function emailVerification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'verification_code' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            $status = false;
            $code = 422;
            $response = [];
            $message = $validator->errors()->first();

            return $this->responseJson($status, $code, $message, $response);
        }
        DB::beginTransaction();
        try {
            $user = User::where(['email' => $request->email, 'verification_code' => $request->verification_code])->first();
            if ($user) {
                $user->update([
                    'is_verified_email' => 1,
                    'fcm_token' => $request->fcm_token,
                    'device_type' => $request->device_type ?? 1,
                    'verification_code' => null,
                    'last_login_ip' => $request->ip(),
                    'last_login_at' => now(),
                ]);
                DB::Commit();
                $token = $user->createToken('Login Successfully')->accessToken;

                if ($token) {
                    $status = true;
                    $code = 200;
                    $response = ['token' => $token, 'user' => new ProfileResource($user)];
                    $message = 'Otp Verify Successfully & Login Successfully';
                } else {
                    $status = false;
                    $code = 500;
                    $response = [];
                    $message = 'Something went wrong';
                }
            } else {
                $status = false;
                $code = 422;
                $response = [];
                $message = 'Otp doesn\'t match';
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            $status = false;
            $code = 500;
            $response = errorLogAndReturn($th);
            $message = config('constants.CATCH_ERROR_MSG');
        }

        return $this->responseJson($status, $code, $message, $response);
    }

    /**
     * @OA\Post(
     *     path="/api/forgot/password",
     *     summary="Forgot Password",
     *     tags={"Authentication"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"email"},
     *
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com")
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="OTP Sent Successfully"),
     *     @OA\Response(response=422, description="Invalid Email")
     * )
     */
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);
        if ($validator->fails()) {
            $status = false;
            $code = 422;
            $response = [];
            $message = $validator->errors()->first();

            return $this->responseJson($status, $code, $message, $response);
        } else {
            DB::beginTransaction();
            try {
                $userDetails = User::where('email', $request->email)->first();
                if (! $userDetails || empty($userDetails->email)) {
                    $status = false;
                    $code = 422;
                    $response = [];
                    $message = 'Invalid Email Id !!';
                } else {
                    $otp = generateOTP(4);
                    User::where('id', $userDetails->id)->update([
                        'verification_code' => $otp,
                    ]);
                    DB::commit();
                    $status = true;
                    $code = 200;
                    $response = ['otp' => $otp];
                    $message = 'OTP Sent Successfully !!';
                }
            } catch (\Throwable $th) {
                DB::rollBack();
                $status = false;
                $code = 500;
                $response = errorLogAndReturn($th);
                $message = config('constants.CATCH_ERROR_MSG');
            }

            return $this->responseJson($status, $code, $message, $response);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/reset/password",
     *     summary="Reset Password",
     *     tags={"Authentication"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"email","new_password","confirm_password"},
     *
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="new_password", type="string", format="password", example="newpassword123"),
     *             @OA\Property(property="confirm_password", type="string", format="password", example="newpassword123")
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="Password Reset Successfully"),
     *     @OA\Response(response=422, description="Validation Error")
     * )
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'new_password' => 'required|min:6|string',
            'confirm_password' => 'required|same:new_password',
            'email' => 'required|email',
        ]);
        if ($validator->fails()) {
            $status = false;
            $code = 422;
            $response = [];
            $message = $validator->errors()->first();

            return $this->responseJson($status, $code, $message, $response);
        }

        DB::beginTransaction();
        try {
            if ($request->email) {
                $condition = ['email' => $request->email];
                $userFound = User::where($condition)->first();
            }
            if ($userFound) {
                $otpUpdate = User::find($userFound->id)->update(['password' => Hash::make($request->new_password), 'verification_code' => null, 'original_password' => $request->new_password]);
                if ($otpUpdate) {
                    DB::Commit();
                    $status = true;
                    $code = 200;
                    $response = [];
                    $message = 'Password Reset Successfully, Now you can login';
                } else {
                    $status = false;
                    $code = 422;
                    $response = [];
                    $message = 'Something went wrong';
                }
            } else {
                $status = false;
                $code = 500;
                $response = [];
                $message = 'User not found';
            }
        } catch (\Throwable $th) {
            DB::rollback();
            $status = false;
            $code = 500;
            $response = errorLogAndReturn($th);
            $message = config('constants.CATCH_ERROR_MSG');
        }

        return $this->responseJson($status, $code, $message, $response);
    }

    /**
     * @OA\Get(
     *     path="/api/banner/list",
     *     summary="Get Banner List",
     *     tags={"General"},
     *
     *     @OA\Response(response=200, description="Banner List Fetched"),
     *     @OA\Response(response=500, description="Internal Server Error")
     * )
     */
    public function bannerList(Request $request)
    {
        try {
            $perPage = $request->input('per_page') ?? 10;
            $page = $request->input('page_number') ?? $request->input('page_no') ?? $request->input('page') ?? 1;

            $bannerPaginator = Banner::where('is_active', 1)->paginate($perPage, ['*'], 'page_number', $page);
            $bannerPaginator->through(fn($item) => new BannerResource($item));

            return $this->responseJsonPaginated(
                true,
                200,
                $bannerPaginator->total() > 0 ? 'Banner List Fetched' : 'No Data Found',
                $bannerPaginator
            );
        } catch (\Throwable $th) {
            return $this->responseJson(
                false,
                500,
                config('constants.CATCH_ERROR_MSG'),
                errorLogAndReturn($th)
            );
        }
    }

    /**
     * @OA\Get(
     *     path="/api/blog/list",
     *     summary="Get Blog List",
     *     tags={"General"},
     *
     *     @OA\Response(response=200, description="Blog List Fetched"),
     *     @OA\Response(response=500, description="Internal Server Error")
     * )
     */
    public function blogList(Request $request)
    {
        try {
            $perPage = $request->input('per_page') ?? 10;
            $page = $request->input('page_number') ?? $request->input('page_no') ?? $request->input('page') ?? 1;

            $blogPaginator = Blog::where('is_active', 1)->paginate($perPage, ['*'], 'page_number', $page);
            $blogPaginator->through(fn($item) => new BlogResource($item));

            return $this->responseJsonPaginated(
                true,
                200,
                $blogPaginator->total() > 0 ? 'Blog List Fetched' : 'No Data Found',
                $blogPaginator
            );
        } catch (\Throwable $th) {
            return $this->responseJson(
                false,
                500,
                config('constants.CATCH_ERROR_MSG'),
                errorLogAndReturn($th)
            );
        }
    }

    /**
     * @OA\Post(
     *     path="/api/category/list",
     *     summary="Get Category List",
     *     tags={"General"},
     *
     *     @OA\RequestBody(
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="category_id", type="string", example="uuid-here")
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="Category List Fetched"),
     *     @OA\Response(response=500, description="Internal Server Error")
     * )
     */
    public function categoryList(Request $request)
    {
        try {
            $categoryId = $request->category_id ?? null;
            $query = Category::where('is_active', 1);
            if ($categoryId) {
                $id = uuidtoid($request->category_id, 'categories');
                $query->where('parent_id', $id);
            } else {
                $query->whereNull('parent_id');
            }

            $perPage = $request->input('per_page') ?? 10;
            $page = $request->input('page_number') ?? $request->input('page_no') ?? $request->input('page') ?? 1;

            $categoryPaginator = $query->paginate($perPage, ['*'], 'page_number', $page);
            $categoryPaginator->through(fn($item) => new CategoryResource($item));

            return $this->responseJsonPaginated(
                true,
                200,
                $categoryPaginator->total() > 0 ? 'Category List Fetched' : 'No Data Found',
                $categoryPaginator
            );
        } catch (\Throwable $th) {
            return $this->responseJson(
                false,
                500,
                config('constants.CATCH_ERROR_MSG'),
                errorLogAndReturn($th)
            );
        }
    }

    /**
     * @OA\Get(
     *     path="/api/setting",
     *     summary="Get Setting",
     *     tags={"General"},
     *
     *     @OA\Response(response=200, description="Setting Fetched"),
     *     @OA\Response(response=422, description="No Data Found")
     * )
     */
    public function setting()
    {
        try {
            $setting = Setting::find(1);
            if (! empty($setting)) {
                $status = true;
                $code = 200;
                $response = new SettingResource($setting);
                $message = 'Setting Fetched';
            } else {
                $status = true;
                $code = 200;
                $response = [];
                $message = 'No Data Found';
            }
        } catch (\Throwable $th) {
            $status = false;
            $code = 500;
            $response = errorLogAndReturn($th);
            $message = config('constants.CATCH_ERROR_MSG');
        }

        return $this->responseJson($status, $code, $message, $response);
    }

    /**
     * @OA\Get(
     *     path="/api/cms",
     *     summary="Get CMS Data",
     *     tags={"General"},
     *
     *     @OA\Response(response=200, description="CMS Fetched")
     * )
     */
    public function cms()
    {
        try {
            $cms = Cms::where('is_active', 1)->get();
            if (! empty($cms)) {
                $status = true;
                $code = 200;
                $response = CmsResource::collection($cms);
                $message = 'Cms Fetched';
            } else {
                $status = true;
                $code = 200;
                $response = [];
                $message = 'No Data Found';
            }
        } catch (\Throwable $th) {
            $status = false;
            $code = 500;
            $response = errorLogAndReturn($th);
            $message = config('constants.CATCH_ERROR_MSG');
        }

        return $this->responseJson($status, $code, $message, $response);
    }

    /**
     * @OA\Get(
     *     path="/api/cms/all",
     *     summary="Get All CMS Pages Grouped by Category",
     *     description="Fetch all active CMS pages categorized into 'faqs_and_supports' and 'legal_and_privacy' groups.",
     *     tags={"General"},
     *     @OA\Response(
     *         response=200,
     *         description="Grouped CMS Pages Fetched successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="response_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="All CMS Pages Fetched"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="faqs_and_supports",
     *                     type="array",
     *                     @OA\Items(type="object")
     *                 ),
     *                 @OA\Property(
     *                     property="legal_and_privacy",
     *                     type="array",
     *                     @OA\Items(type="object")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function cmsAll()
    {
        try {
            $cmsItems = Cms::where('is_active', 1)->get();

            $faqsAndSupportsAliases = [
                'mental-health-resources',
                'community-guidelines',
                'getting-started',
                'contact-support',
                'report-bug',
                'rate-app'
            ];

            $legalAndPrivacyAliases = [
                'privacy-policy',
                'terms-service',
                'cookie-policy',
                'community-standards',
                'open-source-licenses'
            ];

            $faqsAndSupports = $cmsItems->filter(fn($item) => in_array($item->alias, $faqsAndSupportsAliases))->values();
            $legalAndPrivacy = $cmsItems->filter(fn($item) => in_array($item->alias, $legalAndPrivacyAliases))->values();

            $status = true;
            $code = 200;
            $response = [
                'faqs_and_supports' => CmsResource::collection($faqsAndSupports),
                'legal_and_privacy' => CmsResource::collection($legalAndPrivacy),
            ];
            $message = 'All CMS Pages Fetched';
        } catch (\Throwable $th) {
            $status = false;
            $code = 500;
            $response = errorLogAndReturn($th);
            $message = config('constants.CATCH_ERROR_MSG');
        }

        return $this->responseJson($status, $code, $message, $response);
    }

    /**
     * @OA\Get(
     *     path="/api/cms/{name}",
     *     summary="Get Single CMS Page by Name",
     *     tags={"General"},
     *     @OA\Parameter(
     *         name="name",
     *         in="path",
     *         required=true,
     *         description="The name/title of the CMS page",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="CMS Page Fetched successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="response_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Cms Page Fetched"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="uuid", type="string", example="some-uuid"),
     *                 @OA\Property(property="title", type="string", example="Privacy Policy"),
     *                 @OA\Property(property="image_path", type="string", example="http://localhost/storage/cms/no-image.png"),
     *                 @OA\Property(property="short_desc", type="string", example="Read about how we handle your personal data..."),
     *                 @OA\Property(property="description", type="string", example="<h1>Privacy Policy</h1><p>...</p>"),
     *                 @OA\Property(property="added_time", type="string", example="June 11, 2026, 11:00 pm")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Page not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="response_code", type="integer", example=404),
     *             @OA\Property(property="message", type="string", example="Cms Page Not Found"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="string"))
     *         )
     *     )
     * )
     */
    public function cmsPage($name)
    {
        try {
            $cms = Cms::where(['title' => $name, 'is_active' => 1])->first();
            if (! empty($cms)) {
                $status = true;
                $code = 200;
                $response = new CmsResource($cms);
                $message = 'Cms Page Fetched';
            } else {
                $status = false;
                $code = 404;
                $response = [];
                $message = 'Cms Page Not Found';
            }
        } catch (\Throwable $th) {
            $status = false;
            $code = 500;
            $response = errorLogAndReturn($th);
            $message = config('constants.CATCH_ERROR_MSG');
        }

        return $this->responseJson($status, $code, $message, $response);
    }

    /**
     * @OA\Get(
     *     path="/api/countries",
     *     summary="Get Countries",
     *     tags={"Geography"},
     *
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search query to filter countries by name. Optional.",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of countries per page. Optional.",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Parameter(
     *         name="page_no",
     *         in="query",
     *         description="Page number. Optional.",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Countries Fetched",
     *
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="response_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Countries Fetched"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="total", type="integer", example=240),
     *                 @OA\Property(property="last_page", type="integer", example=16),
     *                 @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *             )
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation Error")
     * )
     */
    public function countries(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'search' => 'nullable|string',
            'per_page' => 'nullable|integer|min:1',
            'page_no' => 'nullable|integer|min:1',
        ]);
        if ($validator->fails()) {
            return $this->responseJson(false, 422, $validator->errors()->first(), []);
        }

        try {
            $countries = getCountries();

            $search = $request->search ?? null;
            if (! empty($search)) {
                $countries = array_filter($countries, fn($country) => isset($country['name']) && stripos($country['name'], $search) !== false);
                $countries = array_values($countries);
            }

            $perPage = $request->has('per_page') ? (int) $request->input('per_page') : 15;
            $pageNo = $request->has('page_no') ? (int) $request->input('page_no') : 1;

            $total = count($countries);
            $offset = ($pageNo - 1) * $perPage;
            $paginatedCountries = array_slice($countries, $offset, $perPage);

            $response = [
                'current_page' => $pageNo,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => (int) ceil($total / $perPage),
                'data' => array_values($paginatedCountries)
            ];

            if (! empty($paginatedCountries)) {
                $status = true;
                $code = 200;
                $message = 'Countries Fetched';
            } else {
                $status = true;
                $code = 200;
                $message = 'No Data Found';
            }
        } catch (\Throwable $th) {
            $status = false;
            $code = 500;
            $response = errorLogAndReturn($th);
            $message = config('constants.CATCH_ERROR_MSG');
        }

        return $this->responseJson($status, $code, $message, $response);
    }

    /**
     * @OA\Get(
     *     path="/api/languages",
     *     summary="Get Languages",
     *     tags={"Languages"},
     *
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search string to filter languages by name or code. Optional.",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of languages per page. Optional.",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Parameter(
     *         name="page_no",
     *         in="query",
     *         description="Page number. Optional.",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Languages Fetched",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="response_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Languages Fetched"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="total", type="integer", example=184),
     *                 @OA\Property(property="last_page", type="integer", example=13),
     *                 @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *             )
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation Error")
     * )
     */
    public function languages(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'search' => 'nullable|string',
            'per_page' => 'nullable|integer|min:1',
            'page_no' => 'nullable|integer|min:1',
        ]);
        if ($validator->fails()) {
            return $this->responseJson(false, 422, $validator->errors()->first(), []);
        }

        try {
            $languages = getLanguages();

            if ($request->has('search') && $request->filled('search')) {
                $search = strtolower($request->input('search'));
                $languages = array_filter($languages, function ($lang) use ($search) {
                    return str_contains(strtolower($lang['name']), $search) || str_contains(strtolower($lang['code']), $search);
                });

                // Sort to rank exact matches and prefix matches higher
                usort($languages, function ($a, $b) use ($search) {
                    $aCode = strtolower($a['code']);
                    $bCode = strtolower($b['code']);
                    $aName = strtolower($a['name']);
                    $bName = strtolower($b['name']);

                    if ($aCode === $search && $bCode !== $search) return -1;
                    if ($bCode === $search && $aCode !== $search) return 1;

                    if ($aName === $search && $bName !== $search) return -1;
                    if ($bName === $search && $aName !== $search) return 1;

                    $aPrefixCode = str_starts_with($aCode, $search);
                    $bPrefixCode = str_starts_with($bCode, $search);
                    if ($aPrefixCode && !$bPrefixCode) return -1;
                    if ($bPrefixCode && !$aPrefixCode) return 1;

                    $aPrefixName = str_starts_with($aName, $search);
                    $bPrefixName = str_starts_with($bName, $search);
                    if ($aPrefixName && !$bPrefixName) return -1;
                    if ($bPrefixName && !$aPrefixName) return 1;

                    return 0;
                });
            }

            $perPage = $request->has('per_page') ? (int) $request->input('per_page') : 15;
            $pageNo = $request->has('page_no') ? (int) $request->input('page_no') : 1;

            $total = count($languages);
            $offset = ($pageNo - 1) * $perPage;
            $paginatedLanguages = array_slice($languages, $offset, $perPage);

            $response = [
                'current_page' => $pageNo,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => (int) ceil($total / $perPage),
                'data' => array_values($paginatedLanguages)
            ];

            if (! empty($paginatedLanguages)) {
                $status = true;
                $code = 200;
                $message = 'Languages Fetched';
            } else {
                $status = true;
                $code = 200;
                $message = 'No Data Found';
            }
        } catch (\Throwable $th) {
            $status = false;
            $code = 500;
            $response = errorLogAndReturn($th);
            $message = config('constants.CATCH_ERROR_MSG');
        }

        return $this->responseJson($status, $code, $message, $response);
    }

    /**
     * @OA\Get(
     *     path="/api/nationalities",
     *     summary="Get Nationalities",
     *     tags={"Nationalities"},
     *
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search string to filter nationalities by name. Optional.",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of nationalities per page. Optional.",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Parameter(
     *         name="page_no",
     *         in="query",
     *         description="Page number. Optional.",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Nationalities Fetched",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="response_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Nationalities Fetched"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="total", type="integer", example=193),
     *                 @OA\Property(property="last_page", type="integer", example=13),
     *                 @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *             )
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation Error")
     * )
     */
    public function nationalities(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'search' => 'nullable|string',
            'per_page' => 'nullable|integer|min:1',
            'page_no' => 'nullable|integer|min:1',
        ]);
        if ($validator->fails()) {
            return $this->responseJson(false, 422, $validator->errors()->first(), []);
        }

        try {
            $nationalities = getNationalitiesList();

            if ($request->has('search') && $request->filled('search')) {
                $search = strtolower($request->input('search'));
                $nationalities = array_filter($nationalities, function ($nat) use ($search) {
                    return str_contains(strtolower($nat['name']), $search);
                });

                // Sort to rank exact matches and prefix matches higher
                usort($nationalities, function ($a, $b) use ($search) {
                    $aName = strtolower($a['name']);
                    $bName = strtolower($b['name']);

                    if ($aName === $search && $bName !== $search) return -1;
                    if ($bName === $search && $aName !== $search) return 1;

                    $aPrefixName = str_starts_with($aName, $search);
                    $bPrefixName = str_starts_with($bName, $search);
                    if ($aPrefixName && !$bPrefixName) return -1;
                    if ($bPrefixName && !$aPrefixName) return 1;

                    return 0;
                });
            }

            $perPage = $request->has('per_page') ? (int) $request->input('per_page') : 15;
            $pageNo = $request->has('page_no') ? (int) $request->input('page_no') : 1;

            $total = count($nationalities);
            $offset = ($pageNo - 1) * $perPage;
            $paginatedNationalities = array_slice($nationalities, $offset, $perPage);

            $response = [
                'current_page' => $pageNo,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => (int) ceil($total / $perPage),
                'data' => array_values($paginatedNationalities)
            ];

            if (! empty($paginatedNationalities)) {
                $status = true;
                $code = 200;
                $message = 'Nationalities Fetched';
            } else {
                $status = true;
                $code = 200;
                $message = 'No Data Found';
            }
        } catch (\Throwable $th) {
            $status = false;
            $code = 500;
            $response = errorLogAndReturn($th);
            $message = config('constants.CATCH_ERROR_MSG');
        }

        return $this->responseJson($status, $code, $message, $response);
    }


    /**
     * @OA\Post(
     *     path="/api/states",
     *     summary="Get States by Country",
     *     tags={"Geography"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"country_id"},
     *             @OA\Property(property="country_id", type="integer", example=101),
     *             @OA\Property(property="per_page", type="integer", example=15, description="Number of states per page. Optional."),
     *             @OA\Property(property="page_no", type="integer", example=1, description="Page number. Optional.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="States Fetched",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="response_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="States Fetched"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="total", type="integer", example=36),
     *                 @OA\Property(property="last_page", type="integer", example=3),
     *                 @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *             )
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation Error")
     * )
     */
    public function states(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'country_id' => 'required|numeric',
            'per_page' => 'nullable|integer|min:1',
            'page_no' => 'nullable|integer|min:1',
        ]);
        if ($validator->fails()) {
            return $this->responseJson(false, 422, $validator->errors()->first(), []);
        }
        try {
            $countryId = $request->country_id ?? null;
            $states = getStates($countryId);

            $perPage = $request->has('per_page') ? (int) $request->input('per_page') : 15;
            $pageNo = $request->has('page_no') ? (int) $request->input('page_no') : 1;

            $total = count($states);
            $offset = ($pageNo - 1) * $perPage;
            $paginatedStates = array_slice($states, $offset, $perPage);

            $response = [
                'current_page' => $pageNo,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => (int) ceil($total / $perPage),
                'data' => array_values($paginatedStates)
            ];

            if (! empty($paginatedStates)) {
                $status = true;
                $code = 200;
                $message = 'States Fetched';
            } else {
                $status = true;
                $code = 200;
                $message = 'No Data Found';
            }
        } catch (\Throwable $th) {
            $status = false;
            $code = 500;
            $response = errorLogAndReturn($th);
            $message = config('constants.CATCH_ERROR_MSG');
        }

        return $this->responseJson($status, $code, $message, $response);
    }

    /**
     * @OA\Post(
     *     path="/api/cities",
     *     summary="Get or Search Cities",
     *     tags={"Geography"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             @OA\Property(property="state_id", type="integer", example=1, description="Filter cities by state ID. Optional."),
     *             @OA\Property(property="country_id", type="integer", example=101, description="Filter cities by country ID. Optional."),
     *             @OA\Property(property="search", type="string", example="Mumbai", description="Search query to filter cities by name. Optional."),
     *             @OA\Property(property="per_page", type="integer", example=15, description="Number of cities per page. Optional."),
     *             @OA\Property(property="page_no", type="integer", example=1, description="Page number. Optional.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Cities Fetched",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="response_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Cities Fetched"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="total", type="integer", example=120),
     *                 @OA\Property(property="last_page", type="integer", example=8),
     *                 @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *             )
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation Error")
     * )
     */
    public function cities(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'state_id' => 'nullable|numeric',
            'country_id' => 'nullable|numeric',
            'search' => 'nullable|string',
            'per_page' => 'nullable|integer|min:1',
            'page_no' => 'nullable|integer|min:1',
        ]);
        if ($validator->fails()) {
            return $this->responseJson(false, 422, $validator->errors()->first(), []);
        }
        try {
            $stateId = $request->state_id ?? null;
            $countryId = $request->country_id ?? null;
            $search = $request->search ?? null;

            if ($stateId !== null) {
                $cities = getCities($stateId);
            } elseif ($countryId !== null) {
                $states = getStates($countryId);
                $stateIds = array_column($states, 'id');
                $allCities = getCities();
                $cities = array_filter($allCities, fn($city) => isset($city['state_id']) && in_array($city['state_id'], $stateIds));
                $cities = array_values($cities);
            } else {
                $cities = getCities();
            }

            if (! empty($search)) {
                $cities = array_filter($cities, fn($city) => isset($city['name']) && stripos($city['name'], $search) !== false);
                $cities = array_values($cities);
            }

            $perPage = $request->has('per_page') ? (int) $request->input('per_page') : 15;
            $pageNo = $request->has('page_no') ? (int) $request->input('page_no') : 1;

            $total = count($cities);
            $offset = ($pageNo - 1) * $perPage;
            $paginatedCities = array_slice($cities, $offset, $perPage);

            $response = [
                'current_page' => $pageNo,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => (int) ceil($total / $perPage),
                'data' => array_values($paginatedCities)
            ];

            if (! empty($paginatedCities)) {
                $status = true;
                $code = 200;
                $message = 'Cities Fetched';
            } else {
                $status = true;
                $code = 200;
                $message = 'No Data Found';
            }
        } catch (\Throwable $th) {
            $status = false;
            $code = 500;
            $response = errorLogAndReturn($th);
            $message = config('constants.CATCH_ERROR_MSG');
        }

        return $this->responseJson($status, $code, $message, $response);
    }

    /**
     * @OA\Get(
     *     path="/api/master/dropdown",
     *     summary="Master Dropdown",
     *     tags={"Master"},
     *
     *     @OA\Response(response=200, description="Master Dropdown Fetched"),
     *     @OA\Response(response=422, description="Validation Error")
     * )
     */
    public function masterDropdown(Request $request)
    {
        try {
            // Type 1: Hobbies / Interests
            $hobbiesGrouped = \App\Models\Hobby::with(['items' => function ($query) {
                $query->where('is_active', 1);
            }])->where(['is_active' => 1, 'type' => 1])->latest()->get()->map(function ($hobby) {
                return [
                    'title' => $hobby->title,
                    'item' => \App\Http\Resources\Api\HobbyItemResource::collection($hobby->items)->map(fn($item) => [
                        'id' => $item->id,
                        'uuid' => $item->uuid,
                        'name' => $item->name,
                    ])->values()->all(),
                ];
            })->values()->all();

            // Type 2: Lifestyle
            $lifestyleGrouped = \App\Models\Hobby::with(['items' => function ($query) {
                $query->where('is_active', 1);
            }])->where(['is_active' => 1, 'type' => 2])->latest()->get()->map(function ($hobby) {
                return [
                    'title' => $hobby->title,
                    'item' => \App\Http\Resources\Api\HobbyItemResource::collection($hobby->items)->map(fn($item) => [
                        'id' => $item->id,
                        'uuid' => $item->uuid,
                        'name' => $item->name,
                    ])->values()->all(),
                ];
            })->values()->all();

            // Type 3: Home & Future
            $homeAndFutureGrouped = \App\Models\Hobby::with(['items' => function ($query) {
                $query->where('is_active', 1);
            }])->where(['is_active' => 1, 'type' => 3])->latest()->get()->map(function ($hobby) {
                return [
                    'title' => $hobby->title,
                    'item' => \App\Http\Resources\Api\HobbyItemResource::collection($hobby->items)->map(fn($item) => [
                        'id' => $item->id,
                        'uuid' => $item->uuid,
                        'name' => $item->name,
                    ])->values()->all(),
                ];
            })->values()->all();

            // Type 4: Your Vibe
            $yourVibeGrouped = \App\Models\Hobby::with(['items' => function ($query) {
                $query->where('is_active', 1);
            }])->where(['is_active' => 1, 'type' => 4])->latest()->get()->map(function ($hobby) {
                return [
                    'title' => $hobby->title,
                    'item' => \App\Http\Resources\Api\HobbyItemResource::collection($hobby->items)->map(fn($item) => [
                        'id' => $item->id,
                        'uuid' => $item->uuid,
                        'name' => $item->name,
                    ])->values()->all(),
                ];
            })->values()->all();

            // Type 5: Values
            $valuesGrouped = \App\Models\Hobby::with(['items' => function ($query) {
                $query->where('is_active', 1);
            }])->where(['is_active' => 1, 'type' => 5])->latest()->get()->map(function ($hobby) {
                return [
                    'title' => $hobby->title,
                    'item' => \App\Http\Resources\Api\HobbyItemResource::collection($hobby->items)->map(fn($item) => [
                        'id' => $item->id,
                        'uuid' => $item->uuid,
                        'name' => $item->name,
                    ])->values()->all(),
                ];
            })->values()->all();

            $valuesFlat = \App\Models\HobbyItem::whereHas('hobby', function ($q) {
                $q->where('type', 5)->where('is_active', 1);
            })->where('is_active', 1)->get()->map(fn($item) => [
                'id' => $item->id,
                'uuid' => $item->uuid,
                'name' => $item->name,
            ])->values()->all();

            // Type 6: Interests
            $interestsGrouped = \App\Models\Hobby::with(['items' => function ($query) {
                $query->where('is_active', 1);
            }])->where(['is_active' => 1, 'type' => 6])->latest()->get()->map(function ($hobby) {
                return [
                    'title' => $hobby->title,
                    'item' => \App\Http\Resources\Api\HobbyItemResource::collection($hobby->items)->map(fn($item) => [
                        'id' => $item->id,
                        'uuid' => $item->uuid,
                        'name' => $item->name,
                    ])->values()->all(),
                ];
            })->values()->all();

            $data = [
                'genders' => collect(getGender())->map(fn($name, $id) => ['id' => $id, 'name' => $name])->values(),
                'orientations' => collect(getOrientation())->map(fn($name, $id) => ['id' => $id, 'name' => $name])->values(),
                'age_ranges' => collect(getAgeRanges())->map(fn($name, $id) => ['id' => $id, 'name' => $name])->values(),
                'distance_ranges' => collect(getDistanceRanges())->map(fn($name, $id) => ['id' => $id, 'name' => $name])->values(),
                'dating_preferences' => collect(getDatingPreferences())->map(fn($name, $id) => ['id' => $id, 'name' => $name])->values(),
                'relationship_statuses' => collect(getRelationshipStatus())->map(fn($name, $id) => ['id' => $id, 'name' => $name])->values(),

                // Add what I'm looking for
                'what_i_am_looking_for' => collect(getWhatImLookingFor())->map(fn($name, $id) => ['id' => $id, 'name' => $name])->values(),

                // Add body types, eye colors, hair colors
                'body_types' => collect(getBodyTypes())->map(fn($name, $id) => ['id' => $id, 'name' => $name])->values(),
                'eye_colors' => collect(getEyeColors())->map(fn($name, $id) => ['id' => $id, 'name' => $name])->values(),
                'hair_colors' => collect(getHairColors())->map(fn($name, $id) => ['id' => $id, 'name' => $name])->values(),
                'hair_lengths' => collect(getHairLengths())->map(fn($name, $id) => ['id' => $id, 'name' => $name])->values(),
                'tattoos' => collect(getTattoos())->map(fn($name, $id) => ['id' => $id, 'name' => $name])->values(),

                // Add sex importance, role positions, dating paces, presentation preferences
                'sex_importance' => collect(getSexImportance())->map(fn($name, $id) => ['id' => $id, 'name' => $name])->values(),
                'importance' => collect(getSexImportance())->map(fn($name, $id) => ['id' => $id, 'name' => $name])->values(),
                'role_positions' => collect(getRolePositions())->map(fn($name, $id) => ['id' => $id, 'name' => $name])->values(),
                'role' => collect(getRolePositions())->map(fn($name, $id) => ['id' => $id, 'name' => $name])->values(),
                'dating_paces' => collect(getDatingPaces())->map(fn($name, $id) => ['id' => $id, 'name' => $name])->values(),
                'datingPace' => collect(getDatingPaces())->map(fn($name, $id) => ['id' => $id, 'name' => $name])->values(),
                'presentation_preferences' => collect(getPresentationPreferences())->map(fn($name, $id) => ['id' => $id, 'name' => $name])->values(),
                'presentation' => collect(getPresentationPreferences())->map(fn($name, $id) => ['id' => $id, 'name' => $name])->values(),

                // Add lifestyle & home vibe fields
                'alcohol' => collect(getAlcohol())->map(fn($name, $id) => ['id' => $id, 'name' => $name])->values(),
                'smoking' => collect(getSmoking())->map(fn($name, $id) => ['id' => $id, 'name' => $name])->values(),
                'exercise' => collect(getExercise())->map(fn($name, $id) => ['id' => $id, 'name' => $name])->values(),
                'diet' => collect(getDiet())->map(fn($name, $id) => ['id' => $id, 'name' => $name])->values(),
                'sleep_rhythms' => collect(getSleepRhythm())->map(fn($name, $id) => ['id' => $id, 'name' => $name])->values(),
                'kids_haves' => collect(getKidsHave())->map(fn($name, $id) => ['id' => $id, 'name' => $name])->values(),
                'kids_futures' => collect(getKidsFuture())->map(fn($name, $id) => ['id' => $id, 'name' => $name])->values(),
                'pets_currents' => collect(getPetsCurrent())->map(fn($name, $id) => ['id' => $id, 'name' => $name])->values(),
                'pets_futures' => collect(getPetsFuture())->map(fn($name, $id) => ['id' => $id, 'name' => $name])->values(),
                'living_preferences' => collect(getLivingPreference())->map(fn($name, $id) => ['id' => $id, 'name' => $name])->values(),
                'travel_importances' => collect(getTravelImportance())->map(fn($name, $id) => ['id' => $id, 'name' => $name])->values(),
                'preferred_communications' => collect(getPreferredCommunication())->map(fn($name, $id) => ['id' => $id, 'name' => $name])->values(),
                'love_languages' => collect(getLoveLanguage())->map(fn($name, $id) => ['id' => $id, 'name' => $name])->values(),
                'social_energies' => collect(getSocialEnergy())->map(fn($name, $id) => ['id' => $id, 'name' => $name])->values(),
                'personality_types' => collect(getPersonalityType())->map(fn($name, $id) => ['id' => $id, 'name' => $name])->values(),
                'educations' => collect(getEducation())->map(fn($name, $id) => ['id' => $id, 'name' => $name])->values(),

                // New onboarding dropdowns
                'nationalities' => collect(getNationalities())->map(fn($name, $id) => ['id' => $id, 'name' => $name])->values(),
                'nationalities_strings' => array_values(getNationalities()),
                'living_in_countries' => collect(getNationalities())->map(fn($name, $id) => ['id' => $id, 'name' => $name])->values(),
                'living_in_countries_strings' => array_values(getNationalities()),
                'living_in_cities' => collect(getCitiesList())->map(fn($name, $id) => ['id' => $id, 'name' => $name])->values(),
                'living_in_cities_strings' => array_values(getCitiesList()),
                'coming_out_statuses' => collect(getComingOutStatuses())->map(fn($name, $id) => ['id' => $id, 'name' => $name])->values(),
                'coming_out_statuses_strings' => array_values(getComingOutStatuses()),
                'religions' => collect(getReligions())->map(fn($name, $id) => ['id' => $id, 'name' => $name])->values(),
                'religions_strings' => array_values(getReligions()),
                'political_views' => collect(getPoliticalViews())->map(fn($name, $id) => ['id' => $id, 'name' => $name])->values(),
                'political_views_strings' => array_values(getPoliticalViews()),
                'music_tests' => collect(getMusicTests())->map(fn($name, $id) => ['id' => $id, 'name' => $name])->values(),
                'music_tests_strings' => array_values(getMusicTests()),
                'Music_taste' => collect(getMusicTests())->map(fn($name, $id) => ['id' => $id, 'name' => $name])->values(),
                'music_taste' => collect(getMusicTests())->map(fn($name, $id) => ['id' => $id, 'name' => $name])->values(),
                'occupations' => collect(getOccupations())->map(fn($name, $id) => ['id' => $id, 'name' => $name])->values(),
                'occupation_list' => array_values(getOccupations()),
                'zodiacs' => collect(getZodiacs())->map(fn($name, $id) => ['id' => $id, 'name' => $name])->values(),
                'zodiac_list' => array_values(getZodiacs()),
                'drinking' => collect(getAlcohol())->map(fn($name, $id) => ['id' => $id, 'name' => $name])->values(),
                'drinking_list' => array_values(getAlcohol()),


                // Restructured grouped fields
                'hobbies' => $hobbiesGrouped,
                'lifestyle' => $lifestyleGrouped,
                'home_future' => $homeAndFutureGrouped,
                'your_vibe' => $yourVibeGrouped,
                'values' => $valuesFlat,
                'values_grouped' => $valuesGrouped,
                'interests' => $interestsGrouped,
            ];

            if (! empty($data)) {
                $status = true;
                $code = 200;
                $response = $data;
                $message = 'Data Fetched';
            } else {
                $status = true;
                $code = 200;
                $response = [];
                $message = 'No Data Found';
            }
        } catch (\Throwable $th) {
            $status = false;
            $code = 500;
            $response = errorLogAndReturn($th);
            $message = config('constants.CATCH_ERROR_MSG');
        }

        return $this->responseJson($status, $code, $message, $response);
    }

    /**
     * @OA\Post(
     *     path="/api/username/check",
     *     summary="Check Username",
     *     tags={"User"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"username"},
     *
     *             @OA\Property(property="username", type="string", example="john_doe")
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="Username Checked"),
     *     @OA\Response(response=422, description="Validation Error")
     * )
     */
    public function usernameCheck(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255',
        ]);
        if ($validator->fails()) {
            return $this->responseJson(false, 422, $validator->errors()->first(), []);
        }
        try {
            $username = $request->username ?? null;
            $is_exist = User::where('username', $username)->exists();
            if (! empty($is_exist)) {
                $status = true;
                $code = 200;
                $response = $is_exist ? false : true;
                $message = $is_exist ? 'Username already exist' : 'Username not exist';
            } else {
                $status = true;
                $code = 200;
                $response = true;
                $message = 'Username not exist';
            }
        } catch (\Throwable $th) {
            $status = false;
            $code = 500;
            $response = errorLogAndReturn($th);
            $message = config('constants.CATCH_ERROR_MSG');
        }

        return $this->responseJson($status, $code, $message, $response);
    }
}
