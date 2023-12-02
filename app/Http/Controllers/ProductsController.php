<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductsController extends Controller
{
    public function getProducts(Request $request) {
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            $products = Product::get();
            return response()->json(['products' => $products],200);
        }
        else {
            return response()->json(['message' => 'unauthorized'],401);
        }
    }
    public function getProduct(Request $request , $id) {
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            $product = Product::find($id);
            return response()->json(['product' => $product],200);
        }
        else {
            return response()->json(['message' => 'unauthorized'],401);
        }
    }
    public function createProduct(Request $request) {
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            $validatedData = $request->validate([
                'product_name' => 'required|string|unique:products,product_name',
                'product_price' => 'required|numeric',
                'product_sale' => 'nullable|numeric',
                'product_cost' => 'nullable|numeric',
                'product_count' => 'nullable|numeric',
                'product_image' => 'nullable|string',
                'total_price' => 'nullable|numeric',
            ]);
            $product = Product::create($validatedData);
            $order = Order::create([
                'product_id' => $product->id,
                'order_type' => 'buy',
                'count' => $validatedData['product_count'],
                'total_price' => $validatedData['total_price'],
            ]);
            return response()->json(['product' => $product , 'order' => $order],200);
        }
        else{
            return response()->json(['message' => 'unauthorized'],401);
        }
    }
    public function updateProduct(Request $request , $id) {
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            $validatedData = $request->validate([
                'product_name'  => [
                    Rule::unique('products', 'product_name')->ignore($id)
                ],
                'product_price' => 'required|numeric',
                'product_sale' => 'nullable|numeric',
                'product_cost' => 'nullable|numeric',
                'product_count' => 'nullable|numeric',
                'product_image' => 'nullable|string',
            ]);
            $product = Product::find($id);
            $product->update($validatedData);
            return response()->json(['product' => $product],200);
        }
        else{
            return response()->json(['message' => 'unauthorized'],401);
        }
    }
    public function ProductsBulkDelete(Request $request){
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            $validatedData = $request->validate([
                'product_ids' => 'required|array',
            ]);
            $productIds = $validatedData['product_ids']; // Array of IDs to be deleted
            foreach ($productIds as $id) {
                $product = Product::find($id); // Retrieve the product model instance
                // Detach the relationships


                // Delete the product model
                $product->delete();
            }
            return response()->json(['message' => 'deleted successfully'],200);
        }
        else{
            return response()->json(['message' => 'unauthorized'],401);
        }
    }
}
