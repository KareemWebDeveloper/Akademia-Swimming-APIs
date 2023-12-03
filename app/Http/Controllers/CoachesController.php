<?php

namespace App\Http\Controllers;

use App\Models\Coach;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CoachesController extends Controller
{
    public function getCoaches(Request $request){
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            $coaches = Coach::with('branches')->get();
            return response()->json(['coaches' => $coaches],200);
        }
        else{
            return response()->json(['message' => 'unauthorized'],401);
        }
    }
    public function getCoachesInBranch(Request $request , $branchId){
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            $coaches = Coach::whereHas('branches', function ($query) use ($branchId) {
                $query->where('branch_id', $branchId);
            })->get();
            return response()->json(['coaches' => $coaches],200);
        }
        else{
            return response()->json(['message' => 'unauthorized'],401);
        }
    }
    public function getCoach(Request $request , $coachId){
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            $coach = Coach::with('branches')->find($coachId);
            $activeSubscriptions = $coach->subscriptions()->where('state', 'active')->get();
            return response()->json(['coach' => $coach , 'activeSubscriptions' => $activeSubscriptions],200);
        }
        else{
            return response()->json(['message' => 'unauthorized'],401);
        }
    }
    public function attachBranchesForCoach(Request $request , $coachId){
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            $validatedData = $request->validate([
                'branchIds' => 'required',
            ]);
            $coach = Coach::find($coachId);
            $coach->branches()->sync($validatedData['branchIds']);
            return response()->json(['coach' => $coach],200);
        }
        else{
            return response()->json(['message' => 'unauthorized'],401);
        }
    }
    public function createCoach(Request $request){
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            $validatedData = $request->validate([
                'name' => 'required|string',
                'email' => 'required|email|unique:coaches,email',
                'address' => 'required|string',
                'password' => 'required|string',
                'phone' => 'required|string|unique:coaches,phone',
                'salary_per_hour' => 'required|numeric',
                'hours_worked' => 'nullable|numeric',
            ]);
            $validatedData['password'] = bcrypt($validatedData['password']);
            $coach = Coach::create($validatedData);
            // Attach the coach to branches
            if($request->input('branchIds')){
                $branchIds = $request->input('branchIds');
                $coach->branches()->attach($branchIds);
            }
        return response()->json(['coach' => $coach],200);
        }
        else{
            return response()->json(['message' => 'unauthorized'],401);
        }
    }
    public function updateCoach(Request $request , $id){
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            $validatedData = $request->validate([
                'name' => 'required|string',
                'email'  => [
                    Rule::unique('coaches', 'email')->ignore($id)
                ],
                'address' => 'required|string',
                'password' => 'required|string',
                'phone' => [
                    Rule::unique('coaches' , 'phone')->ignore($id)
                ],
                'salary_per_hour' => 'required|numeric',
                'hours_worked' => 'nullable|numeric',
            ]);
            $validatedData['password'] = bcrypt($validatedData['password']);
            $coach = Coach::find($id);
            $coach->update($validatedData);
            // Attach the coach to branches
            if($request->input('branchIds')){
                $branchIds = $request->input('branchIds');
                $coach->branches()->sync($branchIds);
            }
        return response()->json(['coach' => $coach],200);

        }
        else{
            return response()->json(['message' => 'unauthorized'],401);
        }
    }
    public function coachesBulkDelete(Request $request){
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            $validatedData = $request->validate([
                'coaches_ids' => 'required',
            ]);
            $coachIds = $validatedData['coaches_ids']; // Array of IDs to be deleted
            $failedDeletions = [];

            foreach ($coachIds as $coachId) {
                $coach = Coach::with('subscriptions')->find($coachId); // Retrieve the coach model instance
                if ($coach->subscriptions()->where('state', 'active')->exists()) {
                    $subscriptions = $coach->subscriptions()->with('customer','branch')->where('state', 'active')->get();
                    // Add coach's ID and subscriptions to failedDeletions array
                    $failedDeletions[] = [
                        'coach_id' => $coachId,
                        'coach_name' => $coach->name,
                        'subscriptions' => $subscriptions
                    ];
                    continue; // Skip to the next coach
                }
                // Detach the relationships
                $coach->branches()->detach();
                $coach->salaries()->delete();
                // Delete the coach model
                $coach->delete();
            }

            if (!empty($failedDeletions)) {
                // Return an error response with failed deletions and subscriptions
                return response()->json(['failed_deletions' => $failedDeletions], 500);
            }
            else{
                return response()->json(['message' => 'deleted successfully'], 200);
            }
        }
        else{
            return response()->json(['message' => 'unauthorized'],401);
        }
}

}
