<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

class RolesController extends Controller
{
    public function getRoles(Request $request) {
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            $roles = Role::get();
            return response()->json(['roles' => $roles],200);
        }
        else {
            return response()->json(['message' => 'unauthorized'],401);
        }
    }
    public function createRole(Request $request) {
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            $validatedData = $request->validate([
                'role_name' => 'required|string',
            ]);
            $role = Role::create($validatedData);
            return response()->json(['role' => $role],200);
        }
        else{
            return response()->json(['message' => 'unauthorized'],401);
        }
    }
}
