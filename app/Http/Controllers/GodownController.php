<?php

namespace App\Http\Controllers;

use App\Models\Godown;
use Illuminate\Http\Request;

class GodownController extends Controller
{
    public function index()
    {
        return response()->json(Godown::orderBy('name')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'location' => 'nullable|string',
            'is_active' => 'boolean',
        ]);
        return response()->json(Godown::create($validated), 201);
    }

    public function show(Godown $godown)
    {
        return response()->json($godown);
    }

    public function update(Request $request, Godown $godown)
    {
        $validated = $request->validate([
            'name'     => 'sometimes|required|string|max:255',
            'location' => 'nullable|string',
            'is_active' => 'boolean',
        ]);
        $godown->update($validated);
        return response()->json($godown);
    }

    public function destroy(Godown $godown)
    {
        $godown->delete();
        return response()->json(null, 204);
    }
}
