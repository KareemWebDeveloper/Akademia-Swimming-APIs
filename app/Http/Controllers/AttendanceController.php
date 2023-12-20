<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Coach;
use App\Models\Customer;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function getActiveCoachesByBranch(Request $request , $branchId){
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            $currentDate = Carbon::now()->format('Y-m-d');
            $coaches = Coach::whereHas('subscriptions', function ($query) use ($branchId) {
                $query->where('state', 'active')->where('branch_id', $branchId);
            })
            ->with(['attendances' => function ($query) use ($currentDate) {
                $query->whereDate('created_at', $currentDate)->with('branch', 'category');
            }])->get();
            return response()->json(['coaches' => $coaches],200);
        }
        else{
            return response()->json(['message' => 'unauthorized'],401);
        }
    }
    public function getActiveCustomersByBranch(Request $request , $branchId){
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            $customers = Customer::whereHas('subscriptions', function ($query) use ($branchId) {
                $query->where('state', 'active')->where('branch_id', $branchId);
            })->with(['subscriptions' => function ($query) use ($branchId) {
                $query->where('state' , 'active')->where('branch_id', $branchId)->with(['coach' => function ($query) {
                    $query->select('id', 'name');
                }]);
            }])->get(['customer_name' , 'customer_phone' , 'last_attendance_date' , 'id']);
            return response()->json(['customers' => $customers],200);
        }
        else{
            return response()->json(['message' => 'unauthorized'],401);
        }
    }
    public function getAttendances(Request $request){
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            $validatedData = $request->validate([
                'target_date' => 'date',
            ]);
            $CoachesAttendances = Attendance::whereNotNull('coach_id')->whereDate('created_at', $validatedData['target_date'])->
            with('coach' , 'category')->get();
            $CustomersAttendances = Attendance::whereNotNull('customer_id')->whereDate('created_at', $validatedData['target_date'])->
            with('customer' , 'branch' , 'subscription.coach')->get();
            return response()->json(['coaches' => $CoachesAttendances , 'customers' => $CustomersAttendances],200);
        }
        else{
            return response()->json(['message' => 'unauthorized'],401);
        }
    }
    public function deleteAttendance (Request $request , $id){
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            $attendance = Attendance::find($id);
            if($attendance->subscription_id){
                $subscription = Subscription::find($attendance->subscription_id);
                $number_of_sessions = $subscription->number_of_sessions;
                $newSessionsNumber = $number_of_sessions + 1;
                $subscription->update([
                    'number_of_sessions' => $newSessionsNumber
                ]);
            }
            if($attendance->coach_id){
                $coach = Coach::find($attendance->coach_id);
                $coachWorkedHours = $coach->hours_worked;
                $session_duration = $attendance->session_duration;
                $coach->update([
                    'hours_worked' => $coachWorkedHours - $session_duration
                ]);
            }
            $attendance->delete();
            return response()->json(['message' => 'deleted successfully'],200);
        }
        else{
            return response()->json(['message' => 'unauthorized'],401);
        }
    }
    public function bulkAttendance(Request $request){
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            $validatedData = $request->validate([
                'coach_ids' => 'nullable|array',
                'category_id' => 'nullable|numeric',
                'branch_id' => 'required|numeric',
                'training_start_time' => 'required',
                'session_duration' => 'required|numeric',
                'customers' => 'nullable|array',
                'is_attended' => 'nullable|boolean',
            ]);
            $currentDateTime = Carbon::now()->toIso8601String();
            if(isset($validatedData['customers'])){
                foreach ($request->input('customers') as $customer) {
                    Attendance::create([
                        'branch_id' => $validatedData['branch_id'],
                        'training_start_time' => $validatedData['training_start_time'],
                        'session_duration' => $validatedData['session_duration'],
                        'customer_id' => $customer['customer_id'],
                        'subscription_id' => $customer['subscription_id'],
                    ]);
                    $currentCustomer = Customer::find($customer['customer_id']);
                    $currentCustomer->last_attendance_date = $currentDateTime;
                    $subscription = Subscription::find($customer['subscription_id']);
                    $newNumberOfSession = $subscription->number_of_sessions - 1;
                    $subscription->update([
                        'number_of_sessions' => $newNumberOfSession
                    ]);
                    $subscription->save();
                    $currentCustomer->save();
                }
            }
            if(isset($validatedData['coach_ids'])){
                foreach ($request->input('coach_ids') as $id) {
                    Attendance::create([
                        'branch_id' => $validatedData['branch_id'],
                        'category_id' => $validatedData['category_id'],
                        'training_start_time' => $validatedData['training_start_time'],
                        'session_duration' => $validatedData['session_duration'],
                        'coach_id' => $id,
                        'is_attended' => isset($validatedData['is_attended']) ? $validatedData['is_attended'] : true,
                    ]);
                    $coach = Coach::find($id);
                    $coach->hours_worked += $validatedData['session_duration'];
                    $coach->save();
                }
            }
            return response()->json(['message' => 'attendance done successfully' , 'time' => $currentDateTime],200);
        }
        else{
            return response()->json(['message' => 'unauthorized'],401);
        }
    }
}
