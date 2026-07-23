<?php

namespace App\Http\Controllers\Admin;

use App\Models\Event;
use App\Traits\UploadAble;
use App\Traits\CommonFunction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BaseController;

class EventController extends BaseController
{
    use CommonFunction;
    use UploadAble;

    public function index(Request $request)
    {
        $details = Event::latest()->get();
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
                'event_date' => 'required|date|after_or_equal:today',
                'start_time' => 'required|string',
                'end_time' => 'required|string',
                'location' => 'required|string|max:255',
                'host_name' => 'required|string|max:255',
                'host_type' => 'nullable|string|max:100',
                'host_pronouns' => 'nullable|string|max:100',
                'tags' => 'nullable|string|max:255',
                'audience' => 'nullable|string|max:255',
            ];

            if (empty($id)) {
                $rules['file'] = 'required|image|mimes:jpeg,png,jpg,gif,svg|max:102400';
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
                    "location" => $request->location,
                    "host_name" => $request->host_name,
                    "host_type" => $request->host_type ?? 'PARTNER',
                    "host_pronouns" => $request->host_pronouns,
                    "tags" => $request->tags,
                    "audience" => $request->audience,
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
            $details = Event::find($uuid);
        }

        return view('admin.event.add', compact('details'));
    }
}
