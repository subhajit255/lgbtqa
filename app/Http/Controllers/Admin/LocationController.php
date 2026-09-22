<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LocationController extends BaseController
{
    public function index(Request $request)
    {
        $details = Location::latest()->paginate(10);
        return view('admin.location.index', compact('details'));
    }

    public function add(Request $request)
    {
        if ($request->post()) {
            $id = $request->id ?? null;

            $rules = [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'address' => 'nullable|string|max:255',
                'lat' => 'nullable|numeric',
                'lng' => 'nullable|numeric',
                'hours' => 'nullable|string|max:255',
                'official_link' => 'nullable|url|max:255',
                'status' => 'required|in:DRAFT,SUBMITTED,IN_REVIEW,CHANGES_REQUESTED,APPROVED,PUBLISHED,PAUSED,EXPIRED,REVOKED,ARCHIVED',
            ];

            $request->validate($rules);

            $postData = [
                'user_id' => auth()->user()->id,
                'name' => $request->name,
                'description' => $request->description,
                'address' => $request->address,
                'lat' => $request->lat,
                'lng' => $request->lng,
                'hours' => $request->hours,
                'official_link' => $request->official_link,
                'status' => $request->status,
                'is_active' => $request->is_active ?? 1,
            ];

            $location = Location::updateOrCreate(['id' => $id], $postData);

            $message = $id ? 'Location Updated Successfully' : 'Location Added Successfully';
            return response(['status' => true, 'message' => $message, 'url' => route('admin.location.list')]);
        }

        $detail = null;
        if (!empty($request->uuid)) {
            $uuid = uuidtoid($request->uuid, 'locations');
            $detail = Location::find($uuid);
        }

        return view('admin.location.add', compact('detail'));
    }

    public function updateStatus(Request $request, $id)
    {
        $location = Location::findOrFail($id);

        $request->validate([
            'status' => 'required|in:DRAFT,SUBMITTED,IN_REVIEW,CHANGES_REQUESTED,APPROVED,PUBLISHED,PAUSED,EXPIRED,REVOKED,ARCHIVED',
        ]);

        $location->update([
            'status' => $request->status,
        ]);

        return response(['status' => true, 'message' => 'Location status updated successfully']);
    }
}
