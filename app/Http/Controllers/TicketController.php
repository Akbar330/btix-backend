<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $query = Ticket::query();
        $viewer = auth('sanctum')->user();

        if (!$viewer || $viewer->role !== 'admin') {
            $query->whereIn('status', ['published', 'sold_out', 'ended']);
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('title', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%')
                    ->orWhere('city', 'like', '%' . $search . '%')
                    ->orWhere('venue', 'like', '%' . $search . '%')
                    ->orWhere('organizer', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->string('category'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->boolean('upcoming_only', true)) {
            $query->where('event_date', '>=', now()->subDay());
        }

        $sort = $request->string('sort', 'event_date_asc')->toString();
        match ($sort) {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'title_asc' => $query->orderBy('title'),
            'quota_desc' => $query->orderByDesc('quota'),
            default => $query->orderBy('event_date'),
        };

        return response()->json([
            'tickets' => $query->get(),
            'filters' => [
                'categories' => Ticket::query()->whereNotNull('category')->distinct()->orderBy('category')->pluck('category')->values(),
                'statuses' => Ticket::STATUSES,
            ],
        ]);
    }

    public function store(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'price'       => 'required|numeric|min:0',
            'quota'       => 'required|integer|min:1',
            'event_date'  => 'required|date',
            'category'    => 'nullable|string|max:100',
            'status'      => 'nullable|in:draft,published,sold_out,ended',
            'venue'       => 'nullable|string|max:255',
            'city'        => 'nullable|string|max:255',
            'organizer'   => 'nullable|string|max:255',
            'highlights'  => 'nullable|string',
            'terms'       => 'nullable|string',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('tickets', 'public');
            $validated['image'] = $path;
        }

        $ticket = Ticket::create($validated);
        return response()->json($ticket, 201);
    }

    public function show(Ticket $ticket)
    {
        $viewer = auth('sanctum')->user();

        if ((!$viewer || $viewer->role !== 'admin') && $ticket->status === 'draft') {
            abort(404);
        }

        return response()->json($ticket);
    }

    public function update(Request $request, Ticket $ticket)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'title'       => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'price'       => 'sometimes|numeric|min:0',
            'quota'       => 'sometimes|integer|min:0',
            'event_date'  => 'sometimes|date',
            'category'    => 'sometimes|nullable|string|max:100',
            'status'      => 'sometimes|in:draft,published,sold_out,ended',
            'venue'       => 'sometimes|nullable|string|max:255',
            'city'        => 'sometimes|nullable|string|max:255',
            'organizer'   => 'sometimes|nullable|string|max:255',
            'highlights'  => 'sometimes|nullable|string',
            'terms'       => 'sometimes|nullable|string',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($ticket->image) {
                Storage::disk('public')->delete($ticket->image);
            }
            $path = $request->file('image')->store('tickets', 'public');
            $validated['image'] = $path;
        }

        if (($validated['quota'] ?? $ticket->quota) <= 0 && !isset($validated['status'])) {
            $validated['status'] = 'sold_out';
        }

        $ticket->update($validated);
        return response()->json($ticket);
    }

    public function destroy(Request $request, Ticket $ticket)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if ($ticket->image) {
            Storage::disk('public')->delete($ticket->image);
        }

        $ticket->delete();
        return response()->json(['message' => 'Ticket deleted']);
    }
}
