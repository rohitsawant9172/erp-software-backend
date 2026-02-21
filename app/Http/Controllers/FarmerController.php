<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Farmer;

class FarmerController extends Controller
{
    public function index()
    {
        return Farmer::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'land_size' => 'nullable|numeric',
            'crops_grown' => 'nullable|string',
            'credit_limit' => 'nullable|numeric',
        ]);

        $farmer = Farmer::create($validated);
        return response()->json($farmer, 201);
    }

    public function show(Farmer $farmer)
    {
        return $farmer;
    }

    public function update(Request $request, Farmer $farmer)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'land_size' => 'nullable|numeric',
            'crops_grown' => 'nullable|string',
            'credit_limit' => 'nullable|numeric',
        ]);

        $farmer->update($validated);
        return $farmer;
    }

    public function destroy(Farmer $farmer)
    {
        $farmer->delete();
        return response()->json(null, 204);
    }
}
