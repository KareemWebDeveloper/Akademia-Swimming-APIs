<?php

namespace App\Http\Controllers;

use App\Models\Installment;
use App\Models\Subscription;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
    public function getRevenues(Request $request , $branchId){
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
             if($branchId == 0){
                 // retrieve all subscriptions where subscription_type is not installments
                 $cashSubscriptions = Subscription::where('subscription_type', '!=', 'installments')->with('branch')->get(
                 ['price', 'branch_id', 'subscription_type' , 'created_at' , 'category_name' , 'sale' , 'subscription_date' , 'expiration_date' , 'academy_name']);

                 // retrieve all the installments where paid is set to true with their subscriptions branch
                 $installments = Installment::where('paid',true)->with(['subscription' => function ($query) {
                     $query->select('id', 'branch_id', 'category_name' , 'academy_name' , 'sale' , 'subscription_date' , 'expiration_date');
                 }, 'subscription.branch'])->get();
                 return response()->json(['subscriptions' => $cashSubscriptions , 'installments' => $installments],200);
             }
             else{
                 // retrieve a branch subscriptions where subscription_type is not installments
                 $cashSubscriptions = Subscription::where('subscription_type', '!=', 'installments')->where('branch_id', $branchId)->with('branch')->get(
                 ['price', 'branch_id', 'category_name' , 'created_at' , 'subscription_type' , 'sale' , 'subscription_date' , 'expiration_date' , 'academy_name']);

                 // retrieve a branch installments where paid is set to true with their subscriptions branch
                 $installments = Installment::where('paid', true)
                 ->whereHas('subscription', function ($query) use ($branchId) {
                     $query->where('branch_id', $branchId);
                 })->with(['subscription' => function ($query) {
                    $query->select('id', 'branch_id', 'category_name', 'academy_name', 'sale', 'subscription_date', 'expiration_date');
                 }, 'subscription.branch'])
                 ->get();
                 return response()->json(['subscriptions' => $cashSubscriptions , 'installments' => $installments],200);
             }

        }
        else {
            return response()->json(['message' => 'unauthorized'],401);
        }
    }
    public function getRevenuesInterval(Request $request , $branchId){
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            $interval = $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date',
            ]);
             if($branchId == 0){
                 // retrieve all subscriptions where subscription_type is cash in a time interval
                 $cashSubscriptions = Subscription::where('subscription_type', '!=', 'installments')
                     ->whereBetween('subscription_date', [$interval['start_date'], $interval['end_date']])
                     ->get(['price', 'branch_id', 'subscription_type' , 'sale', 'subscription_date']);

                 // retrieve all the installments where paid is set to true with their subscriptions branch
                 $installments = Installment::where('paid',true)->whereBetween('updated_at',[$interval['start_date'], $interval['end_date']])->with(['subscription' => function ($query) {
                    $query->select('id', 'branch_id');
                }])->get();
                 return response()->json(['subscriptions' => $cashSubscriptions , 'installments' => $installments],200);
             }
             else{
                 // retrieve a branch subscriptions where subscription_type is cash in a time interval
                 $cashSubscriptions = Subscription::where('subscription_type', '!=', 'installments')->where('branch_id', $branchId)->whereBetween('subscription_date', [$interval['start_date'], $interval['end_date']])->get(
                 ['price', 'branch_id', 'sale' , 'subscription_type' , 'subscription_date']);

                 // retrieve a branch installments where paid is set to true with their subscriptions branch in a specific time interval
                 $installments = Installment::where('paid', true)
                 ->whereHas('subscription', function ($query) use ($branchId) {
                     $query->where('branch_id', $branchId);
                 })->whereBetween('updated_at',[$interval['start_date'], $interval['end_date']])->get();
                 return response()->json(['subscriptions' => $cashSubscriptions , 'installments' => $installments],200);
             }

        }
        else {
            return response()->json(['message' => 'unauthorized'],401);
        }
    }
}
