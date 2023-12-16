<?php

namespace App\Http\Controllers;

use App\Models\Academy;
use App\Models\Category;
use App\Models\Employee;
use App\Models\Installment;
use App\Models\Subscription;
use App\Models\TrainingSchedule;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SubscriptionsController extends Controller
{
    public function createNewSubscription(Request $request){
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            $created_by = $user->name;

            $validatedData = $request->validate([
                'customer_id' => 'required|numeric',
                'coach_id' => 'required|numeric',
                'category_id' => 'required|numeric',
                'branch_id' => 'required|numeric',
                'subscription_date' => 'required|date',
                'expiration_date' => 'required|date',
                'avail_freeze_days' => 'required|numeric',
                'academy_id' => 'numeric',
                // 'academy_name' => 'required|string',
                'number_of_sessions' => 'required|numeric',
                'sessions_per_week' => 'required|numeric',
                'subscription_type' => 'required|string',
                'sale' => 'nullable|numeric',
                'is_private' => 'nullable|boolean',
                'price' => 'required|numeric',
                'invitations' => 'numeric',
            ]);
            $academy = Academy::find($validatedData['academy_id']);
            $validatedData['academy_name'] = $academy->academy_name;
            $validatedData['created_by'] = $created_by;
            $category = Category::find($validatedData['category_id']);
            $validatedData['category_name'] = $category->category_name;
            $subscription = Subscription::create($validatedData);

            $trainingSchedule = $request->validate([
                'training_schedules' => 'required|array',
                'training_schedules.*.day' => 'required|string',
                'training_schedules.*.time' => 'required',
            ]);
            foreach ($trainingSchedule['training_schedules'] as $schedule) {
                $trainingSchedule = new TrainingSchedule($schedule);
                $subscription->trainingSchedules()->save($trainingSchedule);
            }
            if($subscription->subscription_type == 'installments'){
                $installments = $request->validate([
                    'installments' => 'required|array',
                    'installments.*.installment_number' => 'required|numeric',
                    'installments.*.amount' => 'required|numeric',
                    'installments.*.due_date' => 'required|date',
                    'installments.*.paid' => 'nullable|boolean',
                ]);
                foreach ($installments['installments'] as $installment) {
                    $installment['subscription_id'] = $subscription->id;
                    $installment['customer_id'] = $validatedData['customer_id'];
                    Installment::create($installment);
                }
            }
            return response()->json(['subscription' => $subscription , 'customer' => $subscription->customer , 'created_by' => $created_by],200);
        }
        else{
            return response()->json(['message' => 'unauthorized'],401);
        }
    }

    public function getCustomerSubcription(Request $request , $id){
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            $subscription = Subscription::with('customer.level', 'branch', 'coach' , 'category' , 'trainingSchedules' , 'installments')->find($id);
            $sublevel = $subscription->customer->sublevel;
            return response()->json(['subscription' => $subscription , 'sublevel' => $sublevel],200);
        }
        else{
            return response()->json(['message' => 'unauthorized'],401);
        }
    }

    public function checkSubscriptionStatuses(){
        $currentDate = Carbon::now()->format('Y-m-d');

        // Get the active customers with subscription end dates smaller than or equal to today
        $subscriptions = Subscription::where('state', 'active')
        ->where('expiration_date', '<', $currentDate)->get();

        // Update the status of the selected customers to 'inactive'
        foreach ($subscriptions as $subscription) {
            $subscription->update([
                    'state' =>  'inactive'
            ]);
        }
        return response()->json(['message' => 'subscriptions statuses updated successfully'],200);
    }

    public function checkFreezeStatuses(){
        $currentDate = Carbon::now()->format('Y-m-d');
        // Get the frozen customers with freeze end dates smaller than or equal to today
        $subscriptions = Subscription::where('state', 'frozen')
            ->where('freeze_end_date', '<=', $currentDate)->get();

        // Update the status of the selected customers to 'inactive'
        foreach ($subscriptions as $subscription) {
            $subscription->update([
                'state' =>  'active'
            ]);
        }
        return response()->json(['message' => 'subscriptions statuses updated successfully']);
    }

    public function getPrivateSubscriptions(Request $request){
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            $privateSubscriptions = Subscription::with('customer', 'branch', 'coach')->where('is_private', true)->get();
            return response()->json(['privateSubscriptions' => $privateSubscriptions],200);
        }
        else{
            return response()->json(['message' => 'unauthorized'],401);
        }
    }
    public function updateSubscriptionsCoach(Request $request) {
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            $subscriptions = $request->validate([
                'subscriptions' => 'required|array',
                'subscriptions.*.id' => 'required|numeric',
                'subscriptions.*.coach_id' => 'required|numeric',
            ]);
            foreach ($subscriptions['subscriptions'] as $subscription) {
                // $subscription['id'] = $subscription->id;
                $subscriptionModel = Subscription::find($subscription['id']);
                $subscriptionModel->coach_id = $subscription['coach_id'];
                $subscriptionModel->save();
            }
            return response()->json(['message' => 'updated successfully'],200);
        }
        else{
            return response()->json(['message' => 'unauthorized'],401);
        }
    }
    public function freezeSubscription(Request $request , $id) {
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            $freezeData = $request->validate([
                'freeze_start_date' => 'required|date',
                'freeze_end_date' => 'required|date',
                'expiration_date' => 'required|date',
                'avail_freeze_days' => 'required|numeric',
                'state' => 'required',
                'isfrozen' => 'required|boolean',
            ]);
            $Subscription = Subscription::find($id);
            $Subscription->update($freezeData);
            $Subscription->save();
            return response()->json(['message' => 'updated successfully'],200);
        }
        else{
            return response()->json(['message' => 'unauthorized'],401);
        }
    }
    public function freezeCancellation(Request $request , $id) {
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            $freezeData = $request->validate([
                'expiration_date' => 'required|date',
                'avail_freeze_days' => 'required|numeric',
            ]);
            $freezeData['state'] = 'active';
            $freezeData['isfrozen'] = false;
            $freezeData['freeze_start_date'] = null;
            $freezeData['freeze_end_date'] = null;
            $Subscription = Subscription::find($id);
            $Subscription->update($freezeData);
            $Subscription->save();
            return response()->json(['message' => 'cancelled successfully'],200);
        }
        else{
            return response()->json(['message' => 'unauthorized'],401);
        }
    }
    public function updateSubscription(Request $request , $id) {
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            $validatedData = $request->validate([
                'customer_id' => 'required|numeric',
                'coach_id' => 'required|numeric',
                'category_id' => 'required|numeric',
                'branch_id' => 'required|numeric',
                'subscription_date' => 'required|date',
                'expiration_date' => 'required|date',
                'avail_freeze_days' => 'required|numeric',
                'academy_id' => 'numeric',
                // 'academy_name' => 'required|string',
                'number_of_sessions' => 'required|numeric',
                'sessions_per_week' => 'required|numeric',
                'subscription_type' => 'required|string',
                'sale' => 'nullable|numeric',
                'is_private' => 'nullable|boolean',
                'price' => 'required|numeric',
                'training_schedules' => 'required|array',
            ]);
            $academy = Academy::find($validatedData['academy_id']);
            $validatedData['academy_name'] = $academy->academy_name;
            $Subscription = Subscription::find($id);
            $Subscription->update($validatedData);
            $Subscription->trainingSchedules()->each(function($trainingSchedule) {
                $trainingSchedule->delete();
            });
            foreach ($validatedData['training_schedules'] as $ScheduleData) {
                TrainingSchedule::create([
                    'day' => $ScheduleData['day'],
                    'time' => $ScheduleData['time'],
                    'subscription_id' => $id,
                ]);
            }

            return response()->json(['subscription' => $Subscription],200);
        }
        else{
            return response()->json(['message' => 'unauthorized'],401);
        }
    }
}
