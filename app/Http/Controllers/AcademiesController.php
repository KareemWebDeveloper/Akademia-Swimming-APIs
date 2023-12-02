<?php

namespace App\Http\Controllers;

use App\Models\Academy;
use Illuminate\Http\Request;

class AcademiesController extends Controller
{
    public function getAcademies(Request $request) {
            $academies = Academy::get();
            return response()->json(['academies' => $academies],200);
    }
    public function createAcademy(Request $request) {
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            $validatedData = $request->validate([
                'academy_name' => 'required|string|unique:academies,academy_name',
            ]);
            $academy = Academy::create($validatedData);
            return response()->json(['academy' => $academy],200);
        }
        else{
            return response()->json(['message' => 'unaothorized'],401);
        }
    }
}
