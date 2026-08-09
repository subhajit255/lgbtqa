<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BadgeColorController;
use App\Http\Controllers\Admin\BadgeStyleController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\BlogController;
use App\Http\Controllers\Admin\BroadcastController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\PostCategoryController;
use App\Http\Controllers\Admin\CmsController;
use App\Http\Controllers\Admin\GalleryController;
use App\Http\Controllers\Admin\HobbyController;
use App\Http\Controllers\Admin\LogErrorController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\RolePermissionController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::as('admin.')->group(function () {
    Route::controller(AuthController::class)->group(function () {
        Route::match(['get', 'post'], 'login', 'login')->name('login');
        Route::match(['get', 'post'], 'logout', 'logout')->name('logout');
        Route::match(['get', 'post'], 'forgot/password', 'forgotPassword')->name('forgot.password');
        Route::match(['get', 'post'], 'reset/password/{token?}', 'resetPassword')->name('reset.password');
        Route::post('notification/read', 'readNotification')->name('read.notification');
    });

    Route::middleware(['auth', \App\Http\Middleware\UpdateUserLastActivity::class])->group(function () {
        Route::controller(AdminController::class)->group(function () {
            Route::get('dashboard', 'dashboard')->name('dashboard');
            Route::post('profile/update', 'profileUpdate')->name('profile.update');
            Route::post('password/update', 'passwordUpdate')->name('password.update');
        });
        Route::controller(RolePermissionController::class)->as('role.')->prefix('role')->group(function () {
            Route::get('list', 'index')->name('list')->can('view-role-permissions');
            Route::post('add', 'roleAdd')->name('add');
            Route::any('permission/{uuid}', 'rolePermission')->name('permission');
            Route::any('user-permission/{uuid}', 'userRolePermission')->name('user.permission');

            Route::any('user/list', 'userList')->name('user.list');
            Route::any('user/add/{uuid?}', 'userAdd')->name('user.add');
        });
        Route::controller(UserController::class)->as('user.')->prefix('user')->group(function () {
            Route::get('list', 'index')->name('list')->can('view-user');
            Route::any('add/{uuid?}', 'add')->name('add');
            Route::get('view/{uuid}', 'view')->name('view');
            Route::get('delete/{uuid}', 'delete')->name('delete');
            Route::get('get/cities/{stateID?}', 'getCities')->name('get.cities');
            Route::get('get/states/{countryID?}', 'getStates')->name('get.states');
        });
        Route::controller(BannerController::class)->as('banner.')->prefix('banner')->group(function () {
            Route::get('list', 'index')->name('list');
            Route::any('add/{uuid?}', 'add')->name('add');
        });
        Route::controller(CmsController::class)->as('cms.')->prefix('cms')->group(function () {
            Route::get('list', 'index')->name('list');
            Route::any('add/{uuid?}', 'add')->name('add');
        });
        Route::controller(CategoryController::class)->as('category.')->prefix('category')->group(function () {
            Route::get('list', 'index')->name('list');
            Route::any('add/{uuid?}', 'add')->name('add');
        });
        Route::controller(PostCategoryController::class)->as('post-category.')->prefix('post-category')->group(function () {
            Route::get('list', 'index')->name('list');
            Route::any('add/{uuid?}', 'add')->name('add');
        });
        Route::controller(BlogController::class)->as('blog.')->prefix('blog')->group(function () {
            Route::get('list', 'index')->name('list');
            Route::any('add/{uuid?}', 'add')->name('add');
        });
        Route::controller(SettingController::class)->as('setting.')->prefix('setting')->group(function () {
            Route::any('update', 'index')->name('update');
        });
        Route::controller(NotificationController::class)->as('notification.')->prefix('notification')->group(function () {
            Route::get('list', 'index')->name('list');
            Route::get('delete/{id}', 'delete')->name('delete');
            Route::post('bulk-delete', 'bulkDelete')->name('bulk-delete');
        });
        Route::controller(BroadcastController::class)->as('broadcast.')->prefix('broadcast')->group(function () {
            Route::get('list', 'index')->name('list');
            Route::any('add', 'add')->name('add');
            Route::any('edit/{uuid}', 'add')->name('edit');
            Route::any('send/{uuid?}', 'send')->name('send');
        });
        Route::controller(LogErrorController::class)->as('log-error.')->prefix('log-error')->group(function () {
            Route::get('list', 'index')->name('list');
            Route::get('view/{id}', 'view')->name('view');
            Route::get('delete/{id}', 'delete')->name('delete');
            Route::post('bulk-delete', 'bulkDelete')->name('bulk-delete');
        });
        Route::controller(HobbyController::class)->as('hobby.')->prefix('hobby')->group(function () {
            Route::get('list', 'index')->name('list');
            Route::any('add/{uuid?}', 'add')->name('add');
        });
        Route::controller(GalleryController::class)->as('gallery.')->prefix('gallery')->group(function () {
            Route::get('list', 'index')->name('list');
            Route::post('delete', 'delete')->name('delete');
        });
        Route::controller(PostController::class)->as('post.')->prefix('post')->group(function () {
            Route::get('list', 'index')->name('list');
            Route::get('view/{uuid}', 'view')->name('view');
            Route::any('add/{uuid?}', 'add')->name('add');
            Route::post('store', 'store')->name('store');
            Route::get('delete/{uuid}', 'delete')->name('delete');
            Route::post('upload-media', 'uploadMedia')->name('upload.media');
            Route::post('delete-media', 'deleteMedia')->name('delete.media');
        });
        Route::controller(\App\Http\Controllers\Admin\StatusController::class)->as('status.')->prefix('status')->group(function () {
            Route::get('list', 'index')->name('list');
            Route::get('students', 'students')->name('students');
            Route::post('store', 'store')->name('store');
            Route::get('delete/{status}', 'destroy')->name('delete');
            Route::get('get-details/{status}', 'getDetails')->name('get-details');
            Route::get('search-users', 'searchUsers')->name('search-users');
        });

        Route::controller(\App\Http\Controllers\Admin\PostEngagementController::class)->as('post.engagements.')->prefix('post')->group(function () {
            Route::get('{uuid}/engagements', 'list')->name('list');
            Route::delete('engagements/comment/{id}', 'deleteComment')->name('delete-comment');
            Route::delete('engagements/love/{id}', 'deleteLove')->name('delete-love');
            Route::delete('engagements/star/{id}', 'deleteStar')->name('delete-star');
            Route::delete('engagements/emoji/{id}', 'deleteEmoji')->name('delete-emoji');
            Route::post('engagements/add', 'addEngagement')->name('add');
            Route::get('engagements/search-users', 'searchUsers')->name('search-users');
        });
        Route::controller(\App\Http\Controllers\Admin\ChatController::class)->as('chat.')->prefix('chat')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/start', 'startChat')->name('start');
            Route::get('/{chat}/messages', 'getMessages')->name('messages');
            Route::get('/{chat}/participants', 'getParticipants')->name('participants');
            Route::post('/{chat}/send', 'sendMessage')->name('send');
            Route::post('/message/{message}/edit', 'editMessage')->name('message.edit');
            Route::delete('/message/{message}/delete', 'deleteMessage')->name('message.delete');
            Route::post('/message/{message}/pin', 'togglePinMessage')->name('message.pin');
            Route::post('/message/{message}/forward', 'forwardMessage')->name('message.forward');
        });

        Route::controller(\App\Http\Controllers\Admin\GroupController::class)->as('groups.')->prefix('groups')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/store', 'store')->name('store');
            Route::get('/edit/{group}', 'edit')->name('edit');
            Route::post('/update/{group}', 'update')->name('update');
            Route::get('/delete/{group}', 'destroy')->name('delete');
            Route::get('/lock/{group}', 'toggleLock')->name('lock');
            Route::get('/{group}/members', 'getMembers')->name('members');
            Route::get('/{group}/search-users', 'searchUsers')->name('search-users');
            Route::post('/{group}/add-member', 'addMember')->name('add-member');
            Route::delete('/{group}/remove-member/{user}', 'removeMember')->name('remove-member');
        });

        Route::controller(BadgeStyleController::class)->as('badge-style.')->prefix('badge-style')->group(function () {
            Route::get('list', 'index')->name('list');
            Route::any('add/{uuid?}', 'add')->name('add');
            Route::get('delete/{id}', 'delete')->name('delete');
        });
        Route::controller(BadgeColorController::class)->as('badge-color.')->prefix('badge-color')->group(function () {
            Route::get('list', 'index')->name('list');
            Route::any('add/{uuid?}', 'add')->name('add');
            Route::get('delete/{id}', 'delete')->name('delete');
        });

        Route::controller(\App\Http\Controllers\Admin\EventController::class)->as('event.')->prefix('event')->group(function () {
            Route::get('list', 'index')->name('list');
            Route::any('add/{uuid?}', 'add')->name('add');
        });

        Route::controller(\App\Http\Controllers\Admin\CommunityCategoryController::class)->as('community-category.')->prefix('community-category')->group(function () {
            Route::get('list', 'index')->name('list');
            Route::any('add/{id?}', 'add')->name('add');
            Route::get('delete/{id}', 'delete')->name('delete');
        });

        Route::controller(\App\Http\Controllers\Admin\CommunityController::class)->as('community.')->prefix('community')->group(function () {
            Route::get('list', 'index')->name('list');
            Route::any('add/{uuid?}', 'add')->name('add');
            Route::get('view/{uuid}', 'view')->name('view');
            Route::get('approve/{id}', 'approveMember')->name('approve');
            Route::get('reject/{id}', 'rejectMember')->name('reject');
        });

        Route::controller(\App\Http\Controllers\Admin\KycVerificationController::class)->as('kyc.')->prefix('kyc')->group(function () {
            Route::get('list', 'index')->name('list');
            Route::get('view/{id}', 'view')->name('view');
            Route::post('approve/{id}', 'approve')->name('approve');
            Route::post('reject/{id}', 'reject')->name('reject');
            Route::get('add', 'add')->name('add');
            Route::post('store', 'store')->name('store');
        });

        Route::controller(\App\Http\Controllers\Admin\BugController::class)->as('bug.')->prefix('bug')->group(function () {
            Route::get('list', 'index')->name('list');
            Route::get('view/{id}', 'view')->name('view');
            Route::post('update-status/{id}', 'updateStatus')->name('update-status');
        });

        // Administrative routes for shared hosting debugging
        Route::get('/run-migration', function () {
            try {
                \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);

                return 'Migration successful: <pre>'.\Illuminate\Support\Facades\Artisan::output().'</pre>';
            } catch (\Exception $e) {
                return 'Migration failed: '.$e->getMessage();
            }
        })->name('run.migration');

        Route::get('/clear-cache', function () {
            try {
                \Illuminate\Support\Facades\Artisan::call('config:clear');
                \Illuminate\Support\Facades\Artisan::call('cache:clear');
                \Illuminate\Support\Facades\Artisan::call('view:clear');
                \Illuminate\Support\Facades\Artisan::call('route:clear');

                return 'Cache cleared successfully: <pre>'.\Illuminate\Support\Facades\Artisan::output().'</pre>';
            } catch (\Exception $e) {
                return 'Cache clearing failed: '.$e->getMessage();
            }
        })->name('clear.cache');

        Route::post('/pusher/auth', function (\Illuminate\Http\Request $request) {
            $user = auth()->user();

            if (! $user) {
                return response()->json(['error' => 'Unauthenticated in session'], 401);
            }

            try {
                // Ensure broadcast driver is pusher
                if (config('broadcasting.default') !== 'pusher') {
                    return response()->json([
                        'error' => 'Server Configuration Error',
                        'detail' => 'Broadcasting driver is set to '.config('broadcasting.default').' instead of pusher.',
                    ], 500);
                }

                // Ensure channel name is present
                if (! $request->channel_name) {
                    return response()->json(['error' => 'Channel name is missing'], 400);
                }

                // Check authorization
                $auth = Broadcast::auth($request);

                if (! $auth) {
                    // Log the failure details to laravel.log for the user to check
                    \Illuminate\Support\Facades\Log::warning('Pusher Authorization Failed', [
                        'user_id' => $user->id,
                        'channel' => $request->channel_name,
                        'socket_id' => $request->socket_id,
                    ]);

                    return response()->json([
                        'error' => 'Forbidden - Authorization Failed',
                        'detail' => 'User '.$user->id.' is not authorized for channel '.$request->channel_name.'. Please verify you are a participant of this chat.',
                    ], 403);
                }

                $content = is_array($auth) ? json_encode($auth) : $auth->getContent();

                return response($content)
                    ->header('Content-Type', 'application/json')
                    ->header('Cache-Control', 'no-cache, must-revalidate');

            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Pusher Auth Error: '.$e->getMessage());

                return response()->json(['error' => $e->getMessage()], 500);
            }
        })->name('pusher.auth');
    });
});
