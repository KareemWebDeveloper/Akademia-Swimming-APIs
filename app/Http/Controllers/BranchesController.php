<?php

namespace App\Http\Controllers;

use App\Models\Academy;
use App\Models\Branch;
use App\Models\Category;
use App\Models\WorkingDay;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BranchesController extends Controller
{
    public function getBranches(Request $request){
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
        $branches = Branch::with('categories')->get();
        return response()->json(['branches' => $branches],200);
        }
        else{
            return response()->json(['message' => 'unauthorized'],401);
        }
    }
    public function getBranchDetails(Request $request , $branchId){
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
        $branch = Branch::with('categories' , 'workingDays' , 'academies')->find($branchId);
        return response()->json(['branch' => $branch],200);
        }
        else{
            return response()->json(['message' => 'unauthorized'],401);
        }
    }
    public function getBranchWorkingDays(Request $request , $branchId){
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
        $branch = Branch::find($branchId);
        $workingDays = $branch->workingDays;
        return response()->json(['workingDays' => $workingDays],200);
        }
        else{
            return response()->json(['message' => 'unauthorized'],401);
        }
    }

    public function createBranchWithCategories(Request $request){
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            $validatedData = $request->validate([
                'branch_name' => 'required|string|unique:branches,branch_name',
                'working_days' => 'required|array',
                'working_days.*.day' => 'required|string',
                'working_days.*.start_time' => 'nullable',
                'working_days.*.end_time' => 'nullable',
                'academies' => 'required|array',
                'categories' => 'required|array',
                'categories.*.categoryId' => 'required|exists:categories,id',
                'categories.*.duration' => 'required|numeric',
                'categories.*.price_per_session' => 'required|numeric',
                'categories.*.session_prices' => 'json|nullable',
            ]);

            $branch = Branch::create([
                'branch_name' => $validatedData['branch_name'],
            ]);

            foreach ($validatedData['categories'] as $categoryData) {
                $category = Category::find($categoryData['categoryId']);

                $branch->categories()->attach($category, [
                    'duration' => $categoryData['duration'],
                    'price_per_session' => $categoryData['price_per_session'],
                    'session_prices' => $categoryData['session_prices'],
                ]);
            }
            foreach ($validatedData['academies'] as $academy) {
                $academy = Academy::find($academy);
                $branch->academies()->attach($academy);
            }

            foreach ($validatedData['working_days'] as $schedule) {
                $workingDay = WorkingDay::create([
                    'branch_id' => $branch->id,
                    'day' => $schedule['day'],
                    'start_time' => $schedule['start_time'],
                    'end_time' => $schedule['end_time']
                ]);
            }
            return response()->json(['message' => 'Branch created and attached to categories successfully']);
        }
        else{
            return response()->json(['message' => 'unauthorized'],401);
        }
    }

public function updateBranchWithCategories(Request $request, $branchId){
    $user = $request->user();
    if($user->type == 'admin' || $user->type == 'Employee'){
        $validatedData = $request->validate([
            'branch_name' => [
                Rule::unique('branches')->ignore($branchId)
            ],
            'working_days' => 'required|array',
            'academies' => 'required|array',
            'categories' => 'required|array',
            'categories.*.categoryId' => 'required|exists:categories,id',
            'categories.*.duration' => 'required|numeric',
            'categories.*.price_per_session' => 'required|numeric',
            'categories.*.session_prices' => 'json|nullable',
        ]);

        $branch = Branch::findOrFail($branchId);

        $branch->update([
            'branch_name' => $validatedData['branch_name'],
        ]);

        $categoryData = [];
        foreach ($validatedData['categories'] as $category) {
            $categoryData[$category['categoryId']] = [
                'duration' => $category['duration'],
                'price_per_session' => $category['price_per_session'],
                'session_prices' => $category['session_prices'],
            ];
        }
        $branch->categories()->sync($categoryData);

        $branch->academies()->sync($validatedData['academies']);

        $branch->workingDays()->delete();
        foreach ($validatedData['working_days'] as $schedule) {
            $workingDay = WorkingDay::create([
                'branch_id' => $branch->id,
                'day' => $schedule['day'],
                'start_time' => $schedule['start_time'],
                'end_time' => $schedule['end_time']
            ]);
        }
        return response()->json(['message' => 'Branch updated and categories synchronized successfully']);
    }
    else{
        return response()->json(['message' => 'unauthorized'],401);
    }
}

public function deleteBranch(Request $request){
    $user = $request->user();
    if($user->type == 'admin' || $user->type == 'Employee'){
        $branchIds = $request->branch_ids; // Array of IDs to be deleted
        foreach ($branchIds as $branchId) {
            $branch = Branch::find($branchId); // Retrieve the Branch model instance
            if($branch->subscriptions()->where('state', 'active')->exists()){
                return response()->json(['message' => 'Branch has active subscriptions'],500);
            }
            // Detach the relationships
            $branch->coaches()->detach();
            $branch->employees()->detach();
            $branch->categories()->detach();
            $branch->academies()->detach();
            $branch->workingDays()->delete();

            // Delete the Branch model
            $branch->delete();
        }
        return response()->json(['message' => 'deleted successfully'],200);
    }
    else{
        return response()->json(['message' => 'unauthorized'],401);
    }
}
}
