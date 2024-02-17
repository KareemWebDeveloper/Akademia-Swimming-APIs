<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Installment;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomersController extends Controller
{
    public function getCustomers(Request $request){
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            $customers = Customer::with(['subscriptions' => function ($query) {
                $query->where('is_private', false)->latest('created_at');
            },'subscriptions.branch' , 'subscriptions.coach'])
            ->whereHas('subscriptions', function ($query) {
                $query->where('is_private', false);
            })
            ->get();
            return response()->json(['customers' => $customers],200);
        }
        else{
            return response()->json(['message' => 'unauthorized'],401);
        }
    }
    public function getCustomer(Request $request , $customerId){
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            $customer = Customer::find($customerId);
            $lastSubscription = $customer->subscriptions()->latest()->first();
            $lastSubscriptionSchedule =  $lastSubscription->trainingSchedules;
            return response()->json(['customer' => $customer , 'lastSubscriptionSchedule' => $lastSubscriptionSchedule , 'lastSubscription' => $lastSubscription],200);
        }
        else{
            return response()->json(['message' => 'unauthorized'],401);
        }
    }
    public function getCustomerWithPrivateSubscription(Request $request , $customerId){
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            $customer = Customer::find($customerId);
            $lastSubscription = $customer->subscriptions()->where('is_private', true)->latest()->first();
            $lastSubscriptionSchedule =  $lastSubscription->trainingSchedules;
            return response()->json(['customer' => $customer , 'lastSubscriptionSchedule' => $lastSubscriptionSchedule , 'lastSubscription' => $lastSubscription],200);
        }
        else{
            return response()->json(['message' => 'unauthorized'],401);
        }
    }
    public function getReservedOnlyCustomers(Request $request){
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            $customersWithoutSubscriptions = Customer::doesntHave('subscriptions')->get();
            return response()->json(['customers' => $customersWithoutSubscriptions],200);
        }
        else{
            return response()->json(['message' => 'unauthorized'],401);
        }
    }

    public function getCustomerActiveSubscriptions(Request $request , $customerId){
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            $customer = Customer::find($customerId);
            $activeSubscriptions = $customer->subscriptions()->where('state', 'active')->with('branch' , 'coach')->get();
            // $lastSubscriptionSchedule =  $lastSubscription->trainingSchedules;
            return response()->json(['customer' => $customer , 'activeSubscriptions' => $activeSubscriptions] ,200);
        }
        else{
            return response()->json(['message' => 'unauthorized'],401);
        }
    }
    public function getCustomerPenultimateSubscription(Request $request , $customerId){
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            $customer = Customer::find($customerId);
            $penultimateSubscription = $customer->subscriptions()->with('branch','coach')->latest('id')->skip(1)->first();
            if ($penultimateSubscription) {
                return response()->json(['penultimateSubscription' => $penultimateSubscription] ,200);
            } else {
                // The customer may have only one subscription or no subscriptions.
                return response()->json(['penultimateSubscription' => []] ,200);
            }
        }
        else{
            return response()->json(['message' => 'unauthorized'],401);
        }
    }
    public function validateCustomer(Request $request){
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            $validatedData = $request->validate([
                'customer_name' => 'required|string',
                'customer_email' => 'required|email|unique:customers,customer_email',
                'customer_address' => 'required|string',
                'birthdate' => 'date|nullable',
                'customer_phone' => 'required|string|unique:customers,customer_phone',
                'gender' => 'nullable|string',
                'job' => 'nullable|string',
            ]);
            return response()->json(['validation' => true],200);
        }
        else{
            return response()->json(['message' => 'unauthorized'],401);
        }
    }

    public function createCustomer(Request $request){
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            $validatedData = $request->validate([
                'customer_name' => 'required|string',
                'customer_email' => 'required|email|unique:customers,customer_email',
                'customer_address' => 'required|string',
                'birthdate' => 'date|nullable',
                'customer_phone' => 'required|string|unique:customers,customer_phone',
                'gender' => 'nullable|string',
                'job' => 'nullable|string',
            ]);
            $customer = Customer::create($validatedData);
        return response()->json(['customer' => $customer],200);
        }
        else{
            return response()->json(['message' => 'unauthorized'],401);
        }
    }
    public function updateCustomerInstallment(Request $request , $id){
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            $installment = Installment::find($id);
            $validatedData = $request->validate([
                'amount' => 'required|numeric',
                'due_date' => 'required|date',
            ]);
            $installment->update($validatedData);
            return response()->json(['installment' => $installment],200);
        }
        else{
            return response()->json(['message' => 'unauthorized'],401);
        }
    }
    public function customerSubscriptionAttendances(Request $request , $subscriptionId){
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            $subscription = Subscription::find($subscriptionId);
            $attendances = $subscription->attendances;
            return response()->json(['attendances' => $attendances],200);
        }
        else{
            return response()->json(['message' => 'unauthorized'],401);
        }
    }
    public function updateCustomerLevel(Request $request , $id){
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            $validatedData = $request->validate([
                'level_id' => 'nullable|numeric',
                'sublevel_id' => 'nullable|numeric',
            ]);
            $customer = Customer::find($id);
            $customer->update($validatedData);
            return response()->json(['customer' => $customer],200);
        }
        else{
            return response()->json(['message' => 'unauthorized'],401);
        }
    }
    public function payCustomerInstallment(Request $request , $id){
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            $installment = Installment::find($id);
            $installment->paid = true;
            $installment->save();
            return response()->json(['installment' => $installment],200);
        }
        else{
            return response()->json(['message' => 'unauthorized'],401);
        }
    }
    public function deleteCustomerInstallment(Request $request , $id){
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            $installment = Installment::find($id);
            $installment->delete();
            return response()->json(['message' => 'deletedSuccessfully'],200);
        }
        else{
            return response()->json(['message' => 'unauthorized'],401);
        }
    }
    public function updateCustomer(Request $request , $customerId){
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            $validatedData = $request->validate([
                'customer_name' => 'required|string',
                'customer_email' => [
                    Rule::unique('customers', 'customer_email')->ignore($customerId)
                ],
                'customer_address' => 'required|string',
                'birthdate' => 'date|nullable',
                'customer_phone' => [
                    Rule::unique('customers', 'customer_phone')->ignore($customerId)
                ],
                'gender' => 'nullable|string',
                'job' => 'nullable|string',
            ]);
            $customer = Customer::find($customerId);
            $customer->update($validatedData);
        return response()->json(['customer' => $customer],200);
        }
        else{
            return response()->json(['message' => 'unauthorized'],401);
        }
    }
    public function CustomersBulkDelete(Request $request){
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            $validatedData = $request->validate([
                'customer_ids' => 'required|array',
            ]);
            $customerIds = $validatedData['customer_ids']; // Array of IDs to be deleted
            $failedDeletions = [];

            foreach ($customerIds as $Id) {
                $customer = Customer::find($Id); // Retrieve the customer model instance
                // Detach the relationships
                // $customer->subscriptions()->detach();
                if ($customer->installments()->where('paid', false)->exists()){
                    $installments = $customer->installments->where('paid', false);
                    // Add customer's ID and installments to failedDeletions array
                    $failedDeletions[] = [
                        'customer_id' => $Id,
                        'customer_name' => $customer->customer_name,
                        'installments' => $installments
                    ];
                    continue; // Skip to the next customer
                }
                // Delete the customer model
                $customer->installments()->delete();
                $customer->attendances()->delete();
                $customer->orders()->delete();
                $customerSubscriptions = $customer->subscriptions;
                foreach($customerSubscriptions as $subscription){
                    $subscription->customer()->dissociate();
                    $subscription->branch()->dissociate();
                    $subscription->coach()->dissociate();
                    $subscription->category()->dissociate();
                    $subscription->trainingSchedules()->delete();
                    $subscription->delete();
                }
                $customer->delete();
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
