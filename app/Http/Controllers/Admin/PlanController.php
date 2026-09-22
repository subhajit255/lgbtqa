<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Models\Plan;
use Illuminate\Http\Request;

class PlanController extends BaseController
{
    use \App\Traits\StripeHelper;

    public function index(Request $request)
    {
        $details = Plan::withCount('subscriptions')->latest()->paginate(10);
        return view('admin.plan.index', compact('details'));
    }

    public function add(Request $request)
    {
        if ($request->post()) {
            $id = $request->id ?? null;

            $rules = [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'price' => 'required|numeric|min:0',
                'currency' => 'required|string|max:3',
                'billing_cycle' => 'required|in:MONTHLY,YEARLY,ONE_TIME',
                'is_active' => 'required|boolean',
            ];

            $request->validate($rules);

            $plan = Plan::find($id);

            // Sync with Stripe using the trait
            $stripeData = $this->syncStripePlan($plan, $request->all());

            $postData = [
                'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price,
                'currency' => $request->currency,
                'billing_cycle' => $request->billing_cycle,
                'stripe_product_id' => $stripeData['stripe_product_id'],
                'stripe_price_id' => $stripeData['stripe_price_id'],
                'is_active' => $request->is_active,
            ];

            $plan = Plan::updateOrCreate(['id' => $id], $postData);

            $message = $id ? 'Plan Updated Successfully' : 'Plan Added Successfully';
            return response(['status' => true, 'message' => $message, 'url' => route('admin.plan.list')]);
        }

        $detail = null;
        if (!empty($request->uuid)) {
            $uuid = uuidtoid($request->uuid, 'plans');
            $detail = Plan::find($uuid);
        }

        return view('admin.plan.add', compact('detail'));
    }

    public function updateStatus(Request $request, $id)
    {
        $plan = Plan::findOrFail($id);

        $request->validate([
            'is_active' => 'required|boolean',
        ]);

        $plan->update([
            'is_active' => $request->is_active,
        ]);

        return response(['status' => true, 'message' => 'Plan status updated successfully']);
    }

    public function delete($uuid)
    {
        $id = uuidtoid($uuid, 'plans');
        $plan = Plan::find($id);

        if (!$plan) {
            return response(['status' => false, 'message' => 'Plan not found']);
        }

        if ($plan->subscriptions()->count() > 0) {
            return response(['status' => false, 'message' => 'Cannot delete plan because users are currently subscribed to it.']);
        }

        $plan->delete();

        return response(['status' => true, 'message' => 'Plan deleted successfully.']);
    }
}
