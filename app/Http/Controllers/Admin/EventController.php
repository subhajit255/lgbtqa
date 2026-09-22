<?php

namespace App\Http\Controllers\Admin;

use \App\Models\Location;
use App\Http\Controllers\BaseController;
use App\Models\Event;
use App\Traits\CommonFunction;
use App\Traits\UploadAble;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventController extends BaseController
{
    use CommonFunction;
    use UploadAble;

    public function index(Request $request)
    {
        $details = Event::query()->latest()->get();
        return view('admin.event.index', compact('details'));
    }

    public function add(Request $request)
    {
        if ($request->post()) {
            $id = $request->id ?? NULL;

            $rules = [
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'about' => 'nullable|string',
                'event_date' => 'required|date',
                'start_time' => 'required|string',
                'end_time' => 'nullable|string',
                'is_all_day' => 'boolean',
                'time_zone' => 'nullable|string|max:100',
                'location_id' => 'nullable|exists:locations,id',
                'location_string' => 'nullable|string|max:255',
                'host_name' => 'nullable|string|max:255',
                'host_type' => 'nullable|string|max:100',
                'host_pronouns' => 'nullable|string|max:100',
                'tags' => 'nullable|string|max:255',
                'audience' => 'nullable|string|max:255',
                'age_restriction' => 'required|in:16-17,18+,ALL',
                'official_ticket_url' => 'nullable|url|max:255',
                'source_attribution' => 'nullable|string|max:255',
                'admin_status' => 'required|in:DRAFT,SUBMITTED,IN_REVIEW,CHANGES_REQUESTED,APPROVED,PUBLISHED,PAUSED,EXPIRED,REVOKED,ARCHIVED',
            ];

            if (empty($id)) {
                $rules['file'] = 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:102400';
                $message = "Event Created Successfully";
            } else {
                $rules['file'] = 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:102400';
                $message = "Event Updated Successfully";
            }
            $rules['host_file'] = 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:102400';

            $request->validate($rules);

            DB::beginTransaction();
            try {
                $postData = [
                    "title" => $request->title,
                    "description" => $request->description,
                    "about" => $request->about,
                    "event_date" => $request->event_date,
                    "start_time" => $request->start_time,
                    "end_time" => $request->end_time,
                    "is_all_day" => $request->is_all_day ?? 0,
                    "time_zone" => $request->time_zone,
                    "location_id" => $request->location_id,
                    "location_string" => $request->location_string,
                    "host_name" => $request->host_name,
                    "host_type" => $request->host_type ?? 'PARTNER',
                    "host_pronouns" => $request->host_pronouns,
                    "tags" => $request->tags,
                    "audience" => $request->audience,
                    "age_restriction" => $request->age_restriction ?? 'ALL',
                    "official_ticket_url" => $request->official_ticket_url,
                    "source_attribution" => $request->source_attribution,
                    "admin_status" => $request->admin_status ?? 'DRAFT',
                    "is_active" => $request->is_active ?? 1,
                ];

                // Handle Event Main Image Upload
                if ($request->hasFile('file')) {
                    $image = $request->file('file');
                    $fileName = uniqid() . '.' . $image->getClientOriginalExtension();
                    $isFileUploaded = $this->uploadOne($image, config('constants.SITE_EVENT_IMAGE_UPLOAD_PATH'), $fileName, 'public');
                    if ($isFileUploaded) {
                        $postData['image'] = $fileName;
                    }
                }

                // Handle Host Profile Image Upload
                if ($request->hasFile('host_file')) {
                    $hostImage = $request->file('host_file');
                    $hostFileName = uniqid() . '_host.' . $hostImage->getClientOriginalExtension();
                    $isFileUploaded = $this->uploadOne($hostImage, config('constants.SITE_EVENT_IMAGE_UPLOAD_PATH'), $hostFileName, 'public');
                    if ($isFileUploaded) {
                        $postData['host_image'] = $hostFileName;
                    }
                }

                $details = Event::updateOrCreate(['id' => $id], $postData);
                DB::Commit();
            } catch (\Throwable $th) {
                DB::rollback();
                $status = false;
                $code = 500;
                $response = errorLogAndReturn($th);
                $message = config('constants.CATCH_ERROR_MSG');
                return $this->responseJson($status, $code, $message, $response);
            }

            $data = ['status' => true, 'message' => $message, 'data' => $details ?? null, 'url' => route('admin.event.list')];
            return response($data);
        }

        $details = null;
        if (!empty($request->uuid)) {
            $uuid = uuidtoid($request->uuid, 'events');
            $details = Event::query()->find($uuid);
        }

        $locations = Location::query()->where('is_active', 1)->whereIn('status', ['APPROVED', 'PUBLISHED'])->orderBy('name')->get();

        return view('admin.event.add', compact('details', 'locations'));
    }

    public function updateStatus(Request $request, $id)
    {
        $event = Event::findOrFail($id);

        $request->validate([
            'admin_status' => 'required|in:DRAFT,SUBMITTED,IN_REVIEW,CHANGES_REQUESTED,APPROVED,PUBLISHED,PAUSED,EXPIRED,REVOKED,ARCHIVED',
        ]);

        $event->admin_status = $request->admin_status;
        $event->save();

        return response(['status' => true, 'message' => 'Event status updated successfully']);
    }
}
