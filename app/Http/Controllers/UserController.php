<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $tenantId = auth('api')->user()->tenant_id;
        return response()->json(
            User::where('tenant_id', $tenantId)
                ->select('id', 'name', 'email', 'phone', 'role', 'created_at')
                ->orderByDesc('created_at')
                ->get()
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'phone'    => 'nullable|string',
            'role'     => 'required|in:admin,staff,viewer',
        ]);

        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'phone'     => $request->phone,
            'role'      => $request->role,
            'tenant_id' => auth('api')->user()->tenant_id,
        ]);

        return response()->json($user->only('id', 'name', 'email', 'phone', 'role', 'created_at'), 201);
    }

    public function update(Request $request, $id)
    {
        $tenantId = auth('api')->user()->tenant_id;
        $user = User::where('tenant_id', $tenantId)->findOrFail($id);

        $request->validate([
            'name'     => 'sometimes|required|string|max:255',
            'phone'    => 'nullable|string',
            'role'     => 'sometimes|required|in:admin,staff,viewer',
            'password' => 'nullable|string|min:6',
        ]);

        $data = $request->only(['name', 'phone', 'role']);
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }
        $user->update($data);

        return response()->json($user->only('id', 'name', 'email', 'phone', 'role', 'created_at'));
    }

    public function destroy($id)
    {
        $tenantId = auth('api')->user()->tenant_id;
        $user = User::where('tenant_id', $tenantId)->findOrFail($id);

        if ($user->id === auth('api')->id()) {
            return response()->json(['error' => 'Cannot delete yourself'], 422);
        }
        $user->delete();
        return response()->json(null, 204);
    }
}
