<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductSections;
use App\Models\Subscription;
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
    public function createProductSection(Request $request){
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
           $fields =  $request->validate([
                'section_name' => 'required|string|unique:product_sections,section_name',
            ]);
            $section = ProductSections::create($fields);

            return response()->json(['section' => $section],200);
        }
        else{
            return response()->json(['message' => 'unauthorized'],401);
        }
    }
    public function getProductSections(Request $request){
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            $sections = ProductSections::withCount('products')->get();
            return response()->json(['sections' => $sections],200);
        }
        else{
            return response()->json(['message' => 'unauthorized'],401);
        }
    }
    public function updateProductSection(Request $request , $sectionId){
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
           $fields =  $request->validate([
                'section_name' => [
                    Rule::unique('product_sections')->ignore($sectionId)
                ],
            ]);
            $section = ProductSections::find($sectionId);
            $section->update($fields);

            return response()->json(['section' => $section],200);
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
    public function productSectionBulkDelete(Request $request){
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            $ids = $request->section_ids; // Array of IDs to be deleted
            foreach ($ids as $sectionId) {
                $section = ProductSections::find($sectionId);
                if ($section->products()->where('product_section_id', $sectionId)->exists()){
                    Product::where('product_section_id', $sectionId)->update(['product_section_id' => null]);
                }
            }
            ProductSections::whereIn('id', $ids)->delete();
            return response()->json(['message' => 'deleted successfully'],200);
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
            // Update the category_id to null for subscriptions of the specific category
            foreach ($ids as $categoryId) {
                $category = Category::find($categoryId);
                if ($category->subscriptions()->where('category_id', $categoryId)->exists()){
                    Subscription::where('category_id', $categoryId)->update(['category_id' => null]);
                }
            }
            Category::whereIn('id', $ids)->delete();
            return response()->json(['message' => 'deleted successfully'],200);
        }
        else{
            return response()->json(['message' => 'unauthorized'],401);
        }
    }
}
