<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventParticipant;
use App\Models\Notification;
use App\Traits\UploadAble;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventApiController extends Controller
{
    use UploadAble;
    /**
     * @OA\Get(
     *     path="/api/events",
     *     summary="Get list of active events",
     *     tags={"Events"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search events by title, description or location",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="audience",
     *         in="query",
     *         description="Filter events by audience type (e.g. Friends, All, Community)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="tag",
     *         in="query",
     *         description="Filter events by tag (e.g. Party, Nightlife)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="is_interested",
     *         in="query",
     *         description="Filter events by user interest status (1 or true / 0 or false)",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(
     *         name="is_joined",
     *         in="query",
     *         description="Filter events by user joined status (1 or true / 0 or false)",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Events retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Events retrieved successfully"),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="uuid", type="string"),
     *                     @OA\Property(property="title", type="string"),
     *                     @OA\Property(property="description", type="string"),
     *                     @OA\Property(property="image_path", type="string"),
     *                     @OA\Property(property="event_date", type="string", format="date"),
     *                     @OA\Property(property="start_time", type="string"),
     *                     @OA\Property(property="end_time", type="string"),
     *                     @OA\Property(property="location", type="string"),
     *                     @OA\Property(property="host_name", type="string"),
     *                     @OA\Property(property="host_image_path", type="string"),
     *                     @OA\Property(property="host_type", type="string"),
     *                     @OA\Property(property="host_pronouns", type="string"),
     *                     @OA\Property(property="tags", type="string"),
     *                     @OA\Property(property="audience", type="string"),
     *                     @OA\Property(property="joined_count", type="integer"),
     *                     @OA\Property(property="interested_count", type="integer"),
     *                     @OA\Property(property="user_joined", type="boolean"),
     *                     @OA\Property(property="user_interested", type="boolean")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $userId = auth()->id();

        $query = Event::where('is_active', 1);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhere('host_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('audience')) {
            $query->where('audience', 'like', "%{$request->audience}%");
        }

        if ($request->filled('tag')) {
            $query->where('tags', 'like', "%{$request->tag}%");
        }

        if ($request->has('is_interested') && $request->is_interested !== null && $request->is_interested !== '') {
            $isInterested = filter_var($request->is_interested, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($isInterested !== null) {
                if ($isInterested) {
                    $query->whereHas('participants', function ($q) use ($userId) {
                        $q->where('user_id', $userId)->where('status', 'interested');
                    });
                } else {
                    $query->whereDoesntHave('participants', function ($q) use ($userId) {
                        $q->where('user_id', $userId)->where('status', 'interested');
                    });
                }
            }
        }

        if ($request->has('is_joined') && $request->is_joined !== null && $request->is_joined !== '') {
            $isJoined = filter_var($request->is_joined, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($isJoined !== null) {
                if ($isJoined) {
                    $query->whereHas('participants', function ($q) use ($userId) {
                        $q->where('user_id', $userId)->where('status', 'joined');
                    });
                } else {
                    $query->whereDoesntHave('participants', function ($q) use ($userId) {
                        $q->where('user_id', $userId)->where('status', 'joined');
                    });
                }
            }
        }

        $perPage = $request->input('per_page') ?? 10;
        $page = $request->input('page_number') ?? $request->input('page_no') ?? $request->input('page') ?? 1;

        $eventsPaginator = $query->latest('event_date')->paginate($perPage, ['*'], 'page_number', $page);

        $eventsPaginator->through(function ($event) use ($userId) {
            $event->joined_count = $event->joinedUsers()->count();
            $event->interested_count = $event->interestedUsers()->count();
            
            $event->user_joined = $event->joinedUsers()->where('user_id', $userId)->exists();
            $event->user_interested = $event->interestedUsers()->where('user_id', $userId)->exists();

            return $event;
        });

        return $this->responseJsonPaginated(
            true,
            200,
            'Events retrieved successfully',
            $eventsPaginator
        );
    }

    /**
     * @OA\Get(
     *     path="/api/events/{uuid}",
     *     summary="Get details of a specific event",
     *     tags={"Events"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         description="UUID of the event",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Event details retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Event details retrieved successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Event not found"
     *     )
     * )
     */
    public function show($uuid)
    {
        $userId = auth()->id();
        $event = Event::where('uuid', $uuid)->first();

        if (!$event) {
            return response()->json([
                'status' => false,
                'message' => 'Event not found'
            ], 404);
        }

        $event->joined_count = $event->joinedUsers()->count();
        $event->interested_count = $event->interestedUsers()->count();
        
        $event->user_joined = $event->joinedUsers()->where('user_id', $userId)->exists();
        $event->user_interested = $event->interestedUsers()->where('user_id', $userId)->exists();

        // Include simple host details and attendees lists
        $event->joined_users = $event->joinedUsers()->select('users.id', 'users.name', 'users.profile_image')->get()->map(function($u) {
            return [
                'id' => $u->id,
                'name' => $u->name,
                'image_path' => $u->image_path
            ];
        });

        $event->interested_users = $event->interestedUsers()->select('users.id', 'users.name', 'users.profile_image')->get()->map(function($u) {
            return [
                'id' => $u->id,
                'name' => $u->name,
                'image_path' => $u->image_path
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'Event details retrieved successfully',
            'data' => $event
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/events/{uuid}/join",
     *     summary="Toggle Join Event status for current user",
     *     tags={"Events"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         description="UUID of the event",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Toggle join status success",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Successfully joined the event"),
     *             @OA\Property(property="joined", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(response=404, description="Event not found")
     * )
     */
    public function toggleJoin($uuid)
    {
        $userId = auth()->id();
        $event = Event::where('uuid', $uuid)->first();

        if (!$event) {
            return response()->json([
                'status' => false,
                'message' => 'Event not found'
            ], 404);
        }

        // Check if already joined
        $existing = EventParticipant::where('event_id', $event->id)
            ->where('user_id', $userId)
            ->where('status', 'joined')
            ->first();

        if ($existing) {
            $existing->delete();
            return response()->json([
                'status' => true,
                'message' => 'Successfully left the event',
                'joined' => false
            ]);
        }

        // If user is interested, remove interest state and change to joined
        EventParticipant::where('event_id', $event->id)
            ->where('user_id', $userId)
            ->where('status', 'interested')
            ->delete();

        EventParticipant::create([
            'event_id' => $event->id,
            'user_id' => $userId,
            'status' => 'joined'
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Successfully joined the event',
            'joined' => true
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/events/{uuid}/interest",
     *     summary="Toggle Interested status for current user",
     *     tags={"Events"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         description="UUID of the event",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Toggle interest status success",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Marked interest successfully"),
     *             @OA\Property(property="interested", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(response=404, description="Event not found")
     * )
     */
    public function toggleInterest($uuid)
    {
        $userId = auth()->id();
        $event = Event::where('uuid', $uuid)->first();

        if (!$event) {
            return response()->json([
                'status' => false,
                'message' => 'Event not found'
            ], 404);
        }

        // Check if already interested
        $existing = EventParticipant::where('event_id', $event->id)
            ->where('user_id', $userId)
            ->where('status', 'interested')
            ->first();

        if ($existing) {
            $existing->delete();
            return response()->json([
                'status' => true,
                'message' => 'Removed interest from the event',
                'interested' => false
            ]);
        }

        // If user is joined, remove joined state and change to interested
        EventParticipant::where('event_id', $event->id)
            ->where('user_id', $userId)
            ->where('status', 'joined')
            ->delete();

        EventParticipant::create([
            'event_id' => $event->id,
            'user_id' => $userId,
            'status' => 'interested'
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Marked interest successfully',
            'interested' => true
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/events/create",
     *     summary="Create a new event",
     *     tags={"Events"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"title", "event_date", "start_time", "end_time", "location", "host_name", "file"},
     *                 @OA\Property(property="title", type="string"),
     *                 @OA\Property(property="description", type="string"),
     *                 @OA\Property(property="about", type="string"),
     *                 @OA\Property(property="event_date", type="string", format="date", example="2026-05-03"),
     *                 @OA\Property(property="start_time", type="string", example="8:00 PM"),
     *                 @OA\Property(property="end_time", type="string", example="1:00 AM"),
     *                 @OA\Property(property="location", type="string"),
     *                 @OA\Property(property="host_name", type="string"),
     *                 @OA\Property(property="host_type", type="string", default="PARTNER"),
     *                 @OA\Property(property="host_pronouns", type="string"),
     *                 @OA\Property(property="tags", type="string", description="Comma separated tags"),
     *                 @OA\Property(property="audience", type="string", default="Friends"),
     *                 @OA\Property(property="file", type="string", format="binary", description="Event Banner Image"),
     *                 @OA\Property(property="host_file", type="string", format="binary", description="Host Profile Image")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Event created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Event created successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'about' => 'nullable|string',
            'event_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|string',
            'end_time' => 'required|string',
            'location' => 'required|string|max:255',
            'host_name' => 'required|string|max:255',
            'host_type' => 'nullable|string|max:100',
            'host_pronouns' => 'nullable|string|max:100',
            'tags' => 'nullable|string|max:255',
            'audience' => 'nullable|string|max:255',
            'file' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:102400',
            'host_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:102400',
        ]);

        DB::beginTransaction();
        try {
            $postData = [
                "title" => $request->title,
                "description" => $request->description,
                "about" => $request->about,
                "event_date" => $request->event_date,
                "start_time" => $request->start_time,
                "end_time" => $request->end_time,
                "location" => $request->location,
                "host_name" => $request->host_name,
                "host_type" => $request->host_type ?? 'PARTNER',
                "host_pronouns" => $request->host_pronouns,
                "tags" => $request->tags,
                "audience" => $request->audience ?? 'Friends',
                "is_active" => 1,
            ];

            if ($request->hasFile('file')) {
                $image = $request->file('file');
                $fileName = uniqid() . '.' . $image->getClientOriginalExtension();
                $isFileUploaded = $this->uploadOne($image, config('constants.SITE_EVENT_IMAGE_UPLOAD_PATH'), $fileName, 'public');
                if ($isFileUploaded) {
                    $postData['image'] = $fileName;
                }
            }

            if ($request->hasFile('host_file')) {
                $hostImage = $request->file('host_file');
                $hostFileName = uniqid() . '_host.' . $hostImage->getClientOriginalExtension();
                $isFileUploaded = $this->uploadOne($hostImage, config('constants.SITE_EVENT_IMAGE_UPLOAD_PATH'), $hostFileName, 'public');
                if ($isFileUploaded) {
                    $postData['host_image'] = $hostFileName;
                }
            }

            $event = Event::create($postData);

            Notification::create([
                'user_id' => auth()->id(),
                'title' => 'Event Created',
                'description' => 'You created the event "' . $request->title . '" successfully.',
                'type' => 'event_create',
                'for' => 2,
                'is_read' => 0,
                'is_active' => 1,
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Event created successfully',
                'data' => $event
            ], 201);

        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => 'Failed to create event: ' . $th->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/events/update/{uuid}",
     *     summary="Update an existing event",
     *     tags={"Events"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         description="UUID of the event to update",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"title", "event_date", "start_time", "end_time", "location", "host_name"},
     *                 @OA\Property(property="title", type="string"),
     *                 @OA\Property(property="description", type="string"),
     *                 @OA\Property(property="about", type="string"),
     *                 @OA\Property(property="event_date", type="string", format="date", example="2026-05-03"),
     *                 @OA\Property(property="start_time", type="string", example="8:00 PM"),
     *                 @OA\Property(property="end_time", type="string", example="1:00 AM"),
     *                 @OA\Property(property="location", type="string"),
     *                 @OA\Property(property="host_name", type="string"),
     *                 @OA\Property(property="host_type", type="string", default="PARTNER"),
     *                 @OA\Property(property="host_pronouns", type="string"),
     *                 @OA\Property(property="tags", type="string", description="Comma separated tags"),
     *                 @OA\Property(property="audience", type="string", default="Friends"),
     *                 @OA\Property(property="file", type="string", format="binary", description="Event Banner Image"),
     *                 @OA\Property(property="host_file", type="string", format="binary", description="Host Profile Image")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Event updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Event updated successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Event not found")
     * )
     */
    public function update(Request $request, $uuid)
    {
        $event = Event::where('uuid', $uuid)->first();

        if (!$event) {
            return response()->json([
                'status' => false,
                'message' => 'Event not found'
            ], 404);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'about' => 'nullable|string',
            'event_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|string',
            'end_time' => 'required|string',
            'location' => 'required|string|max:255',
            'host_name' => 'required|string|max:255',
            'host_type' => 'nullable|string|max:100',
            'host_pronouns' => 'nullable|string|max:100',
            'tags' => 'nullable|string|max:255',
            'audience' => 'nullable|string|max:255',
            'file' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:102400',
            'host_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:102400',
        ]);

        DB::beginTransaction();
        try {
            $postData = [
                "title" => $request->title,
                "description" => $request->description,
                "about" => $request->about,
                "event_date" => $request->event_date,
                "start_time" => $request->start_time,
                "end_time" => $request->end_time,
                "location" => $request->location,
                "host_name" => $request->host_name,
                "host_type" => $request->host_type ?? 'PARTNER',
                "host_pronouns" => $request->host_pronouns,
                "tags" => $request->tags,
                "audience" => $request->audience ?? 'Friends',
            ];

            if ($request->hasFile('file')) {
                $image = $request->file('file');
                $fileName = uniqid() . '.' . $image->getClientOriginalExtension();
                $isFileUploaded = $this->uploadOne($image, config('constants.SITE_EVENT_IMAGE_UPLOAD_PATH'), $fileName, 'public');
                if ($isFileUploaded) {
                    $postData['image'] = $fileName;
                }
            }

            if ($request->hasFile('host_file')) {
                $hostImage = $request->file('host_file');
                $hostFileName = uniqid() . '_host.' . $hostImage->getClientOriginalExtension();
                $isFileUploaded = $this->uploadOne($hostImage, config('constants.SITE_EVENT_IMAGE_UPLOAD_PATH'), $hostFileName, 'public');
                if ($isFileUploaded) {
                    $postData['host_image'] = $hostFileName;
                }
            }

            $event->update($postData);
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Event updated successfully',
                'data' => $event
            ]);

        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => 'Failed to update event: ' . $th->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/events/delete/{uuid}",
     *     summary="Delete an event",
     *     tags={"Events"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         description="UUID of the event to delete",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Event deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Event deleted successfully")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Event not found")
     * )
     */
    public function destroy($uuid)
    {
        $event = Event::where('uuid', $uuid)->first();

        if (!$event) {
            return response()->json([
                'status' => false,
                'message' => 'Event not found'
            ], 404);
        }

        $event->delete();

        return response()->json([
            'status' => true,
            'message' => 'Event deleted successfully'
        ]);
    }
}
