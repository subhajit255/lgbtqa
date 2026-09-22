<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\CampaignBlock;
use App\Models\Event;
use App\Models\Location;
use Carbon\Carbon;

class CampaignBlockController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status', 'SCHEDULED');
        $details = CampaignBlock::with('target')
            ->where('status', $status)
            ->latest()
            ->paginate(10);
            
        return view('admin.campaign-block.index', compact('details', 'status'));
    }

    public function searchTarget(Request $request)
    {
        $term = $request->input('q');
        $type = $request->input('type');
        
        if ($type == 'App\Models\Event') {
            $results = Event::where('title', 'LIKE', '%' . $term . '%')->select('id', 'title as text')->limit(10)->get();
        } else {
            $results = Location::where('name', 'LIKE', '%' . $term . '%')->select('id', 'name as text')->limit(10)->get();
        }

        return response()->json($results);
    }

    public function add(Request $request)
    {
        if ($request->isMethod('post')) {
            $request->validate([
                'target_type' => 'required|in:App\Models\Event,App\Models\Location',
                'target_id' => 'required|integer',
                'type' => 'required|in:FEATURED,WEEKEND_PICK',
                'start_date' => 'required|date',
            ]);

            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = $startDate->copy()->addDays(4)->endOfDay(); // 5-day block

            // Check for overbooking (overlap for the same target)
            $overlapping = CampaignBlock::where('target_type', $request->target_type)
                ->where('target_id', $request->target_id)
                ->where('status', '!=', 'CANCELLED')
                ->where(function($q) use ($startDate, $endDate) {
                    $q->whereBetween('start_date', [$startDate, $endDate])
                      ->orWhereBetween('end_date', [$startDate, $endDate])
                      ->orWhere(function($q2) use ($startDate, $endDate) {
                          $q2->where('start_date', '<=', $startDate)
                             ->where('end_date', '>=', $endDate);
                      });
                })->exists();

            if ($overlapping) {
                return response(['status' => false, 'message' => 'Overbooking Error: This item already has an active or scheduled campaign during these dates.']);
            }

            CampaignBlock::create([
                'target_type' => $request->target_type,
                'target_id' => $request->target_id,
                'type' => $request->type,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => 'SCHEDULED',
            ]);

            return response(['status' => true, 'message' => 'Campaign block scheduled successfully!', 'url' => route('admin.campaign-block.list')]);
        }

        return view('admin.campaign-block.add');
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:campaign_blocks,id',
            'status' => 'required|in:ACTIVE,COMPLETED,CANCELLED',
        ]);

        $block = CampaignBlock::find($request->id);
        $block->status = $request->status;
        $block->save();

        return response(['status' => true, 'message' => 'Campaign status updated successfully!', 'url' => route('admin.campaign-block.list')]);
    }
}
