<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppSettingController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'maintenance_mode' => AppSetting::get('maintenance_mode', false),
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'maintenance_mode' => 'required|boolean',
        ]);

        AppSetting::set('maintenance_mode', $validated['maintenance_mode'], 'boolean');

        return response()->json([
            'message' => 'Settings updated successfully.',
            'maintenance_mode' => AppSetting::get('maintenance_mode'),
        ]);
    }
}
