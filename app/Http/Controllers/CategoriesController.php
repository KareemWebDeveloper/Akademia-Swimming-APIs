<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoriesController extends Controller
{
    public function getCategories(Request $request){
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            $categories = Category::get();
            return response()->json(['categories' => $categories],200);
        }
        else{
            return response()->json(['message' => 'unauthorized'],401);
        }
    }
    public function getCategoriesInBranch(Request $request , $branchId){
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            $categories = Category::whereHas('branches', function ($query) use ($branchId) {
                $query->where('branch_id', $branchId);
            })->get();
            return response()->json(['categories' => $categories],200);
        }
        else{
            return response()->json(['message' => 'unauthorized'],401);
        }
    }
    public function createCategory(Request $request){
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
           $fields =  $request->validate([
                'category_name' => 'required|string|unique:categories,category_name',
            ]);
            $category = Category::create($fields);

            return response()->json(['category' => $category],200);

        }
        else{
            return response()->json(['message' => 'unauthorized'],401);
        }
    }
    public function updateCategory(Request $request , $categoryId){
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
           $fields =  $request->validate([
                'category_name' => [
                    Rule::unique('categories')->ignore($categoryId)
                ],
            ]);
            $category = Category::find($categoryId);
            $category->update($fields);

            return response()->json(['category' => $category],200);
        }
        else{
            return response()->json(['message' => 'unauthorized'],401);
        }
    }
    public function deleteCategory(Request $request){
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            $ids = $request->category_ids; // Array of IDs to be deleted
            $branches = Branch::all(); // Retrieve all branches
            foreach ($branches as $branch) {
                $branch->categories()->detach($ids);
            }
            Category::whereIn('id', $ids)->delete();
            return response()->json(['message' => 'deleted successfully'],200);
        }
        else{
            return response()->json(['message' => 'unauthorized'],401);
        }
    }
}
