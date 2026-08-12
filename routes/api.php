<?php

use \App\Http\Controllers\Api\CommunityApiController;
use \App\Http\Controllers\Api\EventApiController;
use \App\Http\Controllers\Api\NotificationController;
use \App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\AppSettingToggleController;
use App\Http\Controllers\Api\AudienceVisibilityController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BadgeController;
use App\Http\Controllers\Api\BlockController;
use App\Http\Controllers\Api\BugController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\ChatMessageController;
use App\Http\Controllers\Api\FriendController;
use App\Http\Controllers\Api\GroupApiController;
use App\Http\Controllers\Api\HobbyController;
use App\Http\Controllers\Api\KycController;
use App\Http\Controllers\Api\MatchingController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\PostEngagementController;
use App\Http\Controllers\Api\StatusController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UserGeoMapController;
use Illuminate\Support\Facades\Route;





/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::controller(AuthController::class)->group(function () {
    Route::post('/signup', 'signup')->name('signup');
    Route::post('/email/verification', 'emailVerification')->name('email.verification');
    Route::post('/login', 'login')->name('login');
    Route::post('/twofactor/verify', 'twoFactorVerify')->name('twofactor.verify');
    Route::post('/forgot/password', 'forgotPassword')->name('forgot.password');
    Route::post('/reset/password', 'resetPassword')->name('reset.password');
    Route::post('/social/login', 'socialLogin')->name('social.login');
    Route::get('/master/dropdown', 'masterDropdown')->name('master.dropdown');
    Route::post('/username/check', 'usernameCheck')->name('username.check');

    Route::get('/banner/list', 'bannerList')->name('banner.list');
    Route::get('/blog/list', 'blogList')->name('blog.list');
    Route::post('/category/list', 'categoryList')->name('category.list');
    Route::get('/setting', 'setting')->name('setting');
    Route::get('/cms', 'cms')->name('cms');
    Route::get('/cms/all', 'cmsAll')->name('cms.all');
    Route::get('/cms/{name}', 'cmsPage')->name('cms.page');
    Route::get('/countries', 'countries')->name('countries');
    Route::post('/states', 'states')->name('states');
    Route::post('/cities', 'cities')->name('cities');
    Route::get('/languages', 'languages')->name('languages');
    Route::get('/nationalities', 'nationalities')->name('nationalities');
});

Route::get('/hobbies', [HobbyController::class, 'index'])->name('hobbies');
Route::get('/utilities/emojis', [PostEngagementController::class, 'getEmojis'])->name('utilities.emojis');
Route::get('/badge/styles', [BadgeController::class, 'getStyles'])->name('badge.styles');
Route::get('/badge/colors', [BadgeController::class, 'getColors'])->name('badge.colors');

Route::middleware(['auth:api', \App\Http\Middleware\UpdateUserLastActivity::class])->group(function () {
    Route::controller(UserController::class)->group(function () {
        Route::post('/logout', 'logout')->name('logout');
        Route::post('/get/profile', 'profileDetails')->name('get.profile');
        Route::post('/setup/profile', 'setupProfile')->name('setup.profile');
        Route::post('/change/password', 'changePassword')->name('change.password');
        Route::get('/login-history', 'loginHistory')->name('login.history');
        Route::post('/trusted-email/add', 'addTrustedEmail')->name('trusted.email.add');
        Route::post('/trusted-email/verify', 'verifyTrustedEmail')->name('trusted.email.verify');
        Route::post('/user/pause-notifications', 'togglePauseNotifications')->name('user.pause-notifications');
        Route::delete('/delete-account', 'deleteAccount')->name('delete.account');
    });

    Route::controller(KycController::class)->group(function () {
        Route::post('/kyc/verify', 'submitKyc')->name('kyc.verify');
    });

    Route::controller(BugController::class)->group(function () {
        Route::post('/bug/report', 'reportBug')->name('bug.report');
    });

    Route::controller(ChatController::class)->prefix('chats')->group(function () {
        Route::get('/', 'index');
        Route::get('/users', 'chatUserList');
        Route::post('/', 'store');
        Route::post('/{chat}/participants', 'addParticipant');
        Route::post('/{chat}/read', 'markAsRead');
    });

    Route::controller(ChatMessageController::class)->prefix('chats/{chat}/messages')->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
    });

    Route::controller(ChatMessageController::class)->prefix('messages/{message}')->group(function () {
        Route::put('/', 'update');
        Route::delete('/', 'destroy');
        Route::post('/pin', 'togglePin');
        Route::post('/forward', 'forward');
        Route::get('/read-by', 'getReadBy');
        Route::post('/react', 'react');
    });

    Route::controller(GroupApiController::class)->prefix('groups')->group(function () {
        Route::get('/discover', 'discover');
        Route::post('/{chat}/join', 'join');
        Route::post('/{chat}/leave', 'leave');
        Route::post('/{chat}/lock', 'toggleLock');
    });

    Route::controller(PostEngagementController::class)->prefix('posts')->group(function () {
        Route::post('/{post_id}/love', 'toggleLove');
        Route::post('/{post_id}/comment', 'addComment');
        Route::post('/{post_id}/star', 'addStar');
        Route::post('/{post_id}/emoji', 'addEmoji');
        Route::get('/{post_id}/engagements', 'getEngagements');
    });

    Route::controller(PostController::class)->prefix('posts')->group(function () {
        Route::get('/post-categories', 'postCategories');
        Route::get('/feed', 'feed');
        Route::get('/user/{userId}', 'userFeed');
        Route::post('/create', 'createPost');
        Route::post('/update/{id}', 'updatePost');
        Route::delete('/delete/{id}', 'deletePost');
    });

    // Matching Routes
    Route::controller(MatchingController::class)->prefix('matching')->group(function () {
        Route::get('/feed', 'feed');
        Route::post('/swipe', 'swipe');
        Route::post('/icebreaker', 'sendIcebreaker');
        Route::get('/matches', 'matches');
        Route::get('/user-details/{id}', 'userDetails');
    });

    // Status Routes
    Route::controller(StatusController::class)->prefix('statuses')->group(function () {
        Route::get('/', 'index');
        Route::get('/my-statuses', 'myStatuses');
        Route::post('/', 'store');
        Route::post('/{statusId}/read', 'markAsRead');
        Route::delete('/{status}', 'destroy');
        Route::post('/{status}/react', 'react');
        Route::get('/{status}/reactions', 'reactions');
        Route::post('/{status}/comments', 'addComment');
        Route::get('/{status}/comments', 'getComments');
        Route::get('/user/{user}', 'userStatuses');
        Route::get('/same-communities-statuses', 'statusOfSameCommunitiesMembers');
    });

    // Friend Routes
    Route::controller(FriendController::class)->prefix('friends')->group(function () {
        Route::get('/my-friends', 'myFriends');
        Route::post('/request/{user_id}', 'sendRequest');
        Route::get('/requests', 'viewRequests');
        Route::post('/accept/{request_id}', 'acceptRequest');
        Route::post('/reject/{request_id}', 'rejectRequest');
    });

    // Block Routes
    Route::controller(BlockController::class)->prefix('users')->group(function () {
        Route::get('/block-list', 'blockedList');
        Route::get('/blocked', 'blockedList');
        Route::post('/block/{user_id}', 'blockUser');
        Route::post('/unblock/{user_id}', 'unblockUser');
    });

    // App Setting Toggle Routes
    Route::get('/app-setting-toggle', [AppSettingToggleController::class, 'index']);
    Route::post('/app-setting-toggle', [AppSettingToggleController::class, 'update']);

    // Audience Visibility Routes
    Route::get('/audience-visibility', [AudienceVisibilityController::class, 'index']);
    Route::post('/audience-visibility', [AudienceVisibilityController::class, 'update']);

    // Event Routes
    Route::controller(EventApiController::class)->prefix('events')->group(function () {
        Route::get('/', 'index');
        Route::post('/create', 'store');
        Route::get('/{uuid}', 'show');
        Route::post('/update/{uuid}', 'update');
        Route::delete('/delete/{uuid}', 'destroy');
        Route::post('/{uuid}/join', 'toggleJoin');
        Route::post('/{uuid}/interest', 'toggleInterest');
    });

    // Community Routes
    Route::controller(CommunityApiController::class)->prefix('communities')->group(function () {
        Route::get('/', 'index');
        Route::get('/feed', 'communityFeed');
        Route::get('/trending', 'trendingCommunities');
        Route::get('/suggested-communities', 'suggestedCommunities');
        Route::post('/create', 'store');
        Route::get('/members-list', 'membersList');
        Route::get('/categories', 'categories');
        Route::get('/{uuid}', 'show');
        Route::post('/update/{uuid}', 'update');
        Route::delete('/delete/{uuid}', 'destroy');
        Route::post('/{uuid}/join', 'join');
        Route::post('/{uuid}/leave', 'leave');
        Route::post('/{uuid}/add-members', 'addMembers');
        Route::get('/{uuid}/requests', 'listRequests');
        Route::post('/requests/{id}/approve', 'approveRequest');
        Route::post('/requests/{id}/reject', 'rejectRequest');
    });

    // Notification Routes
    Route::controller(NotificationController::class)->prefix('notifications')->group(function () {
        Route::get('/', 'index');
        Route::post('/{id}/read', 'markAsRead');
        Route::post('/read-all', 'markAllAsRead');
    });

    // Global Search Route
    Route::get('/search', [SearchController::class, 'search'])->name('global.search');

    // Map Routes
    Route::controller(UserGeoMapController::class)->prefix('map')->group(function () {
        Route::post('/location', 'addOrUpdateCurrentLocation');
        Route::get('/users', 'getUsersOnMap');
        Route::get('/suggested-searches', 'getSuggestedSearches');
        Route::get('/recent', 'getRecentSearches');
        Route::post('/recent', 'addRecentSearch');
        Route::delete('/recent/clear', 'clearRecentSearches');
        Route::delete('/recent/{id}', 'deleteRecentSearch');
        Route::post('/favourite/{user_id}', 'toggleFavourite');
    });
});
