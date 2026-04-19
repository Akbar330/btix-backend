<?php

namespace App\Http\Controllers;

use App\Models\MembershipPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MembershipPlanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $plans = MembershipPlan::orderBy('order')->get();
        return response()->json($plans);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:membership_plans',
            'display_name' => 'required|string',
            'price' => 'required|integer|min:0',
            'discount_percentage' => 'required|numeric|min:0|max:100',
            'description' => 'nullable|string',
            'features' => 'required|array',
            'is_active' => 'boolean',
            'order' => 'integer|min:0',
        ]);

        $plan = MembershipPlan::create($validated);
        return response()->json($plan, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(MembershipPlan $membershipPlan): JsonResponse
    {
        return response()->json($membershipPlan);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MembershipPlan $membershipPlan): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|unique:membership_plans,name,' . $membershipPlan->id,
            'display_name' => 'sometimes|string',
            'price' => 'sometimes|integer|min:0',
            'discount_percentage' => 'sometimes|numeric|min:0|max:100',
            'description' => 'nullable|string',
            'features' => 'sometimes|array',
            'is_active' => 'sometimes|boolean',
            'order' => 'sometimes|integer|min:0',
        ]);

        $membershipPlan->update($validated);
        return response()->json($membershipPlan);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MembershipPlan $membershipPlan): JsonResponse
    {
        $membershipPlan->delete();
        return response()->json(['message' => 'Membership plan deleted successfully']);
    }
}
