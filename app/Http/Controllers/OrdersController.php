<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;

class OrdersController extends Controller
{
    public function createBuyingOrder(Request $request){
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            $validatedData = $request->validate([
                'branch_id' => 'nullable|numeric',
                'product_id' => 'required|numeric|exists:products,id',
                'count' => 'required|numeric',
                'total_price' => 'required|numeric',
            ]);
            $validatedData['order_type'] = 'buy';
            $order = Order::create($validatedData);
            $product = Product::find($validatedData['product_id']);
            $newProductCount = $product->product_count + $validatedData['count'];
            $product->update([
                'product_count' => $newProductCount,
            ]);
            return response()->json(['order' => $order , 'product' => $product],200);
        }
        else{
            return response()->json(['message' => 'unauthorized'],401);
        }
    }
    public function deleteOrder(Request $request , $orderId) {
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            $order = Order::find($orderId);
            $product = $order->product;
            if($order->order_type == 'buy'){
                $productCount = $product->product_count;
                $orderQuantity = $order->count;
                $product->update([
                    'product_count' => $productCount == 0 || $orderQuantity > $productCount ? 0 : $productCount - $orderQuantity
                ]);
            }
            else{
                $productCount = $product->product_count;
                $orderQuantity = $order->count;
                $product->update([
                    'product_count' => $productCount + $orderQuantity
                ]);
            }
            $order->delete();
            return response()->json(['message' => 'deleted successfully'],200);
        }
        else{
            return response()->json(['message' => 'unauthorized'],401);
        }
    }
    public function createSellingOrder(Request $request){
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            $validatedData = $request->validate([
                'branch_id' => 'required|numeric',
                'customer_id' => 'nullable|numeric',
                'total_price' => 'required|numeric',
                'order_items' => 'required|array|min:1',
                'order_items.*.id' => 'required|numeric',
                'order_items.*.amount' => 'required|numeric',
            ]);
            foreach ($request->input('order_items') as $item) {
                Order::create([
                    'branch_id' => $validatedData['branch_id'],
                    'customer_id' => isset($validatedData['customer_id']) ? $validatedData['customer_id'] : null,
                    'product_id' => $item['id'],
                    'total_price' => $validatedData['total_price'],
                    'order_type' => 'sell',
                    'count' => $item['amount'],
                ]);
                $product = Product::find($item['id']);
                $newProductCount = $product->product_count - $item['amount'];
                $product->update([
                    'product_count' => $newProductCount
                ]);
            }
            return response()->json(['message' => 'order created successfully'],200);
        }
        else{
            return response()->json(['message' => 'unauthorized'],401);
        }
    }
}
