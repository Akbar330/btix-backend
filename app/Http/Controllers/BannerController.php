<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Banner;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    public function index()
    {
        return response()->json(Banner::where('is_active', true)->latest()->get());
    }

    public function adminIndex(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized action'], 403);
        }
        return response()->json(Banner::latest()->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'url' => 'nullable|url'
        ]);

        $banner = new Banner();
        $banner->url = $request->url;
        $banner->is_active = $request->input('is_active', true);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('banners', 'public');
            $banner->image = $path;
        }

        $banner->save();
        return response()->json(['message' => 'Banner created', 'banner' => $banner]);
    }

    public function update(Request $request, Banner $banner)
    {
        $request->validate([
            'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
            'url' => 'sometimes|nullable|url',
            'is_active' => 'sometimes|boolean'
        ]);

        if ($request->has('url')) $banner->url = $request->url;
        if ($request->has('is_active')) $banner->is_active = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            if ($banner->image && Storage::disk('public')->exists($banner->image)) {
                Storage::disk('public')->delete($banner->image);
            }
            $path = $request->file('image')->store('banners', 'public');
            $banner->image = $path;
        }

        $banner->save();
        return response()->json([
            'message' => 'Banner updated successfully',
            'banner' => $banner->fresh()
        ]);
    }

    public function destroy(Banner $banner)
    {
        if ($banner->image && Storage::disk('public')->exists($banner->image)) {
            Storage::disk('public')->delete($banner->image);
        }
        $banner->delete();
        return response()->json(['message' => 'Banner deleted']);
    }
}
