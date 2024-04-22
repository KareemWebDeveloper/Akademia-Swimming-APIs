<?php

namespace App\Http\Controllers;

use App\Models\Academy;
use App\Models\Branch;
use App\Models\Subscription;
use Illuminate\Http\Request;

class AcademiesController extends Controller
{
    public function getAcademies(Request $request) {
            $academies = Academy::get();
            return response()->json(['academies' => $academies],200);
    }
    public function getAcademyDetails(Request $request , $academyId){
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
        $academy = Academy::with('branches')->find($academyId);
        $activeSubscriptions = $academy->activeSubscriptions();
        return response()->json(['academy' => $academy , 'activeSubscriptions'=> $activeSubscriptions],200);
        }
        else{
            return response()->json(['message' => 'unauthorized'],401);
        }
    }
    public function getAcademiesInBranch(Request $request , $branchId){
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            $branch = Branch::find($branchId);
            $academies = $branch->academies;

            return response()->json(['academies' => $academies],200);
        }
        else{
            return response()->json(['message' => 'unauthorized'],401);
        }
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
    public function updateAcademy(Request $request , $academyId) {
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            $validatedData = $request->validate([
                'academy_name' => 'required|string|unique:academies,academy_name',
            ]);
            $academy = Academy::findOrFail($academyId);
            $academy = $academy->update($validatedData);
            return response()->json(['academy' => $academy],200);
        }
        else{
            return response()->json(['message' => 'unaothorized'],401);
        }
    }
    public function deleteAcademies(Request $request){
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            $academyIds = $request->academy_ids; // Array of IDs to be deleted
            foreach ($academyIds as $academyId) {
                $academy = Academy::find($academyId); // Retrieve the academy model instance
                if($academy->subscriptions()->where('state', 'active')->exists()){
                    return response()->json(['message' => 'Academy has active subscriptions'],500);
                }
                // Detach the relationships
                $academy->branches()->detach();
                $academy->subscriptions()->delete();

                // Delete the academy model
                $academy->delete();
            }
            return response()->json(['message' => 'deleted successfully'],200);
        }
        else{
            return response()->json(['message' => 'unauthorized'],401);
        }
    }
}
