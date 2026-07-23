<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Models\User;
use App\Traits\UploadAble;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AdminController extends BaseController
{
    use UploadAble;

    public function dashboard()
    {
        $now = Carbon::now();

        // ── 30-Day User Registration Trend ──
        $last30DaysLabels = [];
        $last30DaysUsers  = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $last30DaysLabels[] = $date->format('M d');
            $last30DaysUsers[]  = User::where('user_type', 3)
                ->whereDate('created_at', $date)
                ->count();
        }

        // ── 30-Day Post Trend ──
        $last30DaysPosts = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $last30DaysPosts[] = DB::table('posts')->whereDate('created_at', $date)->count();
        }

        // ── 7-day labels for small sparklines ──
        $last7Days = [];
        $registrationData = [];
        $postData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $last7Days[] = $date->format('D');
            $registrationData[] = User::where('user_type', 3)->whereDate('created_at', $date)->count();
            $postData[] = DB::table('posts')->whereDate('created_at', $date)->count();
        }

        // ── Monthly Registration (last 12 months) ──
        $monthlyLabels = [];
        $monthlyUsers  = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthlyLabels[] = $month->format('M Y');
            $monthlyUsers[]  = User::where('user_type', 3)
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
        }

        // ── Engagement Metrics ──
        $totalComments = DB::table('post_comments')->count();
        $totalLoves    = DB::table('post_loves')->count();
        $totalEmojis   = DB::table('post_emojis')->count();
        $totalStars    = DB::table('post_stars')->count();
        $todayComments = DB::table('post_comments')->whereDate('created_at', Carbon::today())->count();
        $todayLoves    = DB::table('post_loves')->whereDate('created_at', Carbon::today())->count();

        // ── 7-day engagement trend ──
        $engagementComments = [];
        $engagementLoves    = [];
        $engagementEmojis   = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $engagementComments[] = DB::table('post_comments')->whereDate('created_at', $date)->count();
            $engagementLoves[]    = DB::table('post_loves')->whereDate('created_at', $date)->count();
            $engagementEmojis[]   = DB::table('post_emojis')->whereDate('created_at', $date)->count();
        }

        // ── Gender Distribution ──
        $genderData = DB::table('profiles')
            ->select('gender', DB::raw('COUNT(*) as cnt'))
            ->whereNotNull('gender')
            ->where('gender', '!=', '')
            ->groupBy('gender')
            ->orderByDesc('cnt')
            ->limit(10)
            ->get();

        // ── Orientation Distribution ──
        $orientationData = DB::table('profiles')
            ->select('orientation', DB::raw('COUNT(*) as cnt'))
            ->whereNotNull('orientation')
            ->where('orientation', '!=', '')
            ->groupBy('orientation')
            ->orderByDesc('cnt')
            ->limit(8)
            ->get();

        // ── KYC Verification Stats ──
        $kycPending  = DB::table('kyc_verifications')->where('status', 'pending')->count();
        $kycApproved = DB::table('kyc_verifications')->where('status', 'approved')->count();
        $kycRejected = DB::table('kyc_verifications')->where('status', 'rejected')->count();

        // ── Community & Event Stats ──
        $totalCommunities = DB::table('communities')->count();
        $totalEvents      = DB::table('events')->count();
        $upcomingEvents   = DB::table('events')->where('event_date', '>=', Carbon::today())->count();

        // ── Recent Posts (last 5 with user) ──
        $recentPosts = DB::table('posts')
            ->join('users', 'posts.user_id', '=', 'users.id')
            ->select('posts.*', 'users.name as user_name', 'users.profile_image')
            ->orderByDesc('posts.created_at')
            ->limit(5)
            ->get();

        // ── Top Active Users (by post count) ──
        $topActiveUsers = DB::table('posts')
            ->join('users', 'posts.user_id', '=', 'users.id')
            ->select('users.id', 'users.uuid', 'users.name', 'users.email', 'users.profile_image', 'users.is_active',
                DB::raw('COUNT(posts.id) as posts_count'))
            ->groupBy('users.id', 'users.uuid', 'users.name', 'users.email', 'users.profile_image', 'users.is_active')
            ->orderByDesc('posts_count')
            ->limit(5)
            ->get();

        // ── Bug Reports ──
        $totalBugs   = DB::table('bugs')->count();
        $pendingBugs = DB::table('bugs')->where('status', 'pending')->orWhereNull('status')->count();

        // ── Login Activity (last 7 days) ──
        $loginActivity = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $loginActivity[] = DB::table('login_histories')->whereDate('created_at', $date)->count();
        }

        // ── Friend Requests Stats ──
        $totalFriendRequests   = DB::table('friend_requests')->count();
        $pendingFriendRequests = DB::table('friend_requests')->where('status', 'pending')->count();
        $acceptedFriends       = DB::table('friend_requests')->where('status', 'accepted')->count();

        // ── Age Distribution ──
        $ageGroups = DB::table('profiles')
            ->select(DB::raw("
                CASE
                    WHEN age BETWEEN 18 AND 24 THEN '18-24'
                    WHEN age BETWEEN 25 AND 34 THEN '25-34'
                    WHEN age BETWEEN 35 AND 44 THEN '35-44'
                    WHEN age BETWEEN 45 AND 54 THEN '45-54'
                    WHEN age >= 55 THEN '55+'
                    ELSE 'Unknown'
                END as age_group,
                COUNT(*) as cnt
            "))
            ->whereNotNull('age')
            ->where('age', '>', 0)
            ->groupBy('age_group')
            ->orderByRaw("FIELD(age_group, '18-24', '25-34', '35-44', '45-54', '55+', 'Unknown')")
            ->get();

        // ── Week over Week Growth ──
        $thisWeekUsers = User::where('user_type', 3)
            ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->count();
        $lastWeekUsers = User::where('user_type', 3)
            ->whereBetween('created_at', [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()])
            ->count();
        $userGrowthPct = $lastWeekUsers > 0
            ? round((($thisWeekUsers - $lastWeekUsers) / $lastWeekUsers) * 100, 1)
            : ($thisWeekUsers > 0 ? 100 : 0);

        $thisWeekPosts = DB::table('posts')
            ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->count();
        $lastWeekPosts = DB::table('posts')
            ->whereBetween('created_at', [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()])
            ->count();
        $postGrowthPct = $lastWeekPosts > 0
            ? round((($thisWeekPosts - $lastWeekPosts) / $lastWeekPosts) * 100, 1)
            : ($thisWeekPosts > 0 ? 100 : 0);

        $details = [
            'total_users'       => User::where('user_type', 3)->count(),
            'today_users'       => User::where('user_type', 3)->whereDate('created_at', Carbon::today())->count(),
            'total_categories'  => \App\Models\Category::count(),
            'today_categories'  => \App\Models\Category::whereDate('created_at', Carbon::today())->count(),
            'total_blogs'       => \App\Models\Blog::count(),
            'today_blogs'       => \App\Models\Blog::whereDate('created_at', Carbon::today())->count(),
            'total_banners'     => \App\Models\Banner::count(),
            'today_banners'     => \App\Models\Banner::whereDate('created_at', Carbon::today())->count(),
            'total_hobbies'     => \App\Models\Hobby::count(),
            'today_hobbies'     => \App\Models\Hobby::whereDate('created_at', Carbon::today())->count(),
            'total_log_errors'  => \App\Models\LogError::count(),
            'today_log_errors'  => \App\Models\LogError::whereDate('created_at', Carbon::today())->count(),

            'total_posts'       => DB::table('posts')->count(),
            'today_posts'       => DB::table('posts')->whereDate('created_at', Carbon::today())->count(),
            'total_galleries'   => DB::table('galleries')->count(),
            'total_chats'       => DB::table('chats')->count(),
            'total_notifications' => DB::table('notifications')->count(),
            'total_cms'         => DB::table('cms')->count(),
            'total_roles'       => DB::table('roles')->count(),
            'total_statuses'    => DB::table('statuses')->count(),
            'active_statuses'   => \App\Models\Status::active()->count(),

            // 30-day trends
            'trend_30d_labels'  => $last30DaysLabels,
            'trend_30d_users'   => $last30DaysUsers,
            'trend_30d_posts'   => $last30DaysPosts,

            // 7-day labels
            'registration_labels' => $last7Days,
            'registration_data'   => $registrationData,
            'post_data'           => $postData,

            // Monthly
            'monthly_labels'    => $monthlyLabels,
            'monthly_users'     => $monthlyUsers,

            // Engagement
            'total_comments'      => $totalComments,
            'total_loves'         => $totalLoves,
            'total_emojis'        => $totalEmojis,
            'total_stars'         => $totalStars,
            'today_comments'      => $todayComments,
            'today_loves'         => $todayLoves,
            'engagement_comments' => $engagementComments,
            'engagement_loves'    => $engagementLoves,
            'engagement_emojis'   => $engagementEmojis,

            // Demographics
            'gender_labels'      => $genderData->pluck('gender')->toArray(),
            'gender_counts'      => $genderData->pluck('cnt')->toArray(),
            'orientation_labels' => $orientationData->pluck('orientation')->toArray(),
            'orientation_counts' => $orientationData->pluck('cnt')->toArray(),
            'age_labels'         => $ageGroups->pluck('age_group')->toArray(),
            'age_counts'         => $ageGroups->pluck('cnt')->toArray(),

            // KYC
            'kyc_pending'  => $kycPending,
            'kyc_approved' => $kycApproved,
            'kyc_rejected' => $kycRejected,

            // Community & Events
            'total_communities' => $totalCommunities,
            'total_events'      => $totalEvents,
            'upcoming_events'   => $upcomingEvents,

            // Users
            'recent_users'       => User::where('user_type', 3)->latest()->limit(5)->get(),
            'top_active_users'   => $topActiveUsers,
            'user_distribution'  => [
                'active'   => User::where('user_type', 3)->where('is_active', 1)->count(),
                'inactive' => User::where('user_type', 3)->where('is_active', 0)->count(),
            ],

            // Growth
            'user_growth_pct' => $userGrowthPct,
            'post_growth_pct' => $postGrowthPct,

            // Recent posts
            'recent_posts' => $recentPosts,

            // Bugs
            'total_bugs'   => $totalBugs,
            'pending_bugs' => $pendingBugs,

            // Login activity
            'login_activity' => $loginActivity,

            // Friend requests
            'total_friend_requests'   => $totalFriendRequests,
            'pending_friend_requests' => $pendingFriendRequests,
            'accepted_friends'        => $acceptedFriends,

            // Messages
            'total_messages' => DB::table('messages')->count(),
        ];

        return view('admin.dashboard', compact('details'));
    }

    public function profileUpdate(Request $request)
    {
        $request->validate([
            'admin_name' => 'required|string',
            'admin_email' => 'required|email',
            'admin_mobile_number' => 'required|digits:10|numeric',
        ]);
        if ($request->post()) {
            // dd($request->all());
            $postData = [
                'name' => $request->admin_name,
                'username' => $this->createUserName($request->admin_name),
                'mobile_number' => $request->admin_mobile_number,
                'email' => $request->admin_email,
            ];
            if (! empty($request->admin_profile_image)) {
                $image = $request->admin_profile_image;
                $type = $image->getClientOriginalExtension();
                $fileName = uniqid().'.'.$type;
                $isFileUploaded = $this->uploadOne($image, config('constants.SITE_PROFILE_IMAGE_UPLOAD_PATH'), $fileName, 'public');
                if ($isFileUploaded) {
                    $postData['profile_image'] = $fileName;
                }
            }
            $data = User::updateOrCreate(['id' => Auth::user()->id], $postData);
        }
        $message = 'Updated Successfully';
        $data = ['status' => true, 'message' => $message, 'data' => $postData];

        return response($data);
    }

    public function passwordUpdate(Request $request)
    {
        if ($request->post()) {
            $request->validate([
                'old_password' => 'required|min:8',
                'new_password' => 'required|min:8',
                'confirm_password' => 'required|min:8|same:new_password',
            ]);
            $old_pass = Auth::user()->password;
            if (empty($request->old_password)) {
                $hash_old_pass = '';
            } else {
                $hash_old_pass = $request->old_password;
            }
            $check = Hash::check($hash_old_pass, $old_pass);

            if (empty($request->old_password)) {
                $message = 'Provide Old Password';
                $data = ['status' => false, 'message' => $message, 'data' => ''];
            } elseif ($check !== true) {
                $message = 'Provided Old Password is Wrong';
                $data = ['status' => false, 'message' => $message, 'data' => ''];
            } elseif (empty($request->new_password)) {
                $message = 'Provide New Password';
                $data = ['status' => false, 'message' => $message, 'data' => ''];
            } elseif (empty($request->confirm_password)) {
                $message = 'Provide Confirm Password';
                $data = ['status' => false, 'message' => $message, 'data' => ''];
            } elseif ($request->confirm_password !== $request->new_password) {
                $message = 'New Password & Confirm Password Have to be Same';
                $data = ['status' => false, 'message' => $message, 'data' => ''];
            } else {
                $postData = [
                    'password' => Hash::make($request->confirm_password),
                ];
                $data = User::updateOrCreate(['id' => Auth::user()->id], $postData);
                $message = 'Admin Password Updated Successfully';
                $data = ['status' => true, 'message' => $message, 'data' => $postData];
            }
        }

        return response($data);
    }
}
