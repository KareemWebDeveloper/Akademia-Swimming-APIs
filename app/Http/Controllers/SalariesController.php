<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Coach;
use App\Models\Employee;
use App\Models\Salary;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SalariesController extends Controller
{
    public function getCoachesAndEmployees(Request $request){
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            $coaches = Coach::get();
            $employees = Employee::get();
            return response()->json(['coaches' => $coaches , 'employees' => $employees],200);
        }
        else {
            return response()->json(['message' => 'unauthorized'],401);
        }
    }
    public function getCoachSalaries(Request $request , $coachId){
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            $coach = Coach::find($coachId);
            $salaries = $coach->salaries;
            return response()->json(['salaries' => $salaries],200);
        }
        else {
            return response()->json(['message' => 'unauthorized'],401);
        }
    }
    public function getCoachAttendances(Request $request , $coachId){
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            $coach = Coach::find($coachId);
            if($coach->last_paid_date){
                $latestSalary = $coach->salaries()->latest()->first();
                $attendances = Attendance::with('branch')->where('coach_id', $coachId)->where('created_at', '>', date('Y-m-d H:i:s', strtotime($latestSalary->created_at)))->get();
                return response()->json(['attendances' => $attendances],200);
            }
            else{
                $attendances = Attendance::with('branch')->where('coach_id', $coachId)->get();
                return response()->json(['attendances' => $attendances],200);
            }
        }
        else {
            return response()->json(['message' => 'unauthorized'],401);
        }
    }
    public function payEmployeeSalary(Request $request , $employeeId){
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            $currentDate = Carbon::now()->format('Y-m-d');
            $employee = Employee::find($employeeId);
            $employeeSalary = 0;
            if($employee->salary_discount && $employee->salary_discount > 0){
                $employeeSalary = $employee->salary - $employee->salary_discount;
            }
            else{
                $employeeSalary = $employee->salary;
            }
            $salary = Salary::create([
                'employee_id' => $employee->id,
                'amount' => $employeeSalary,
                'paid_date' => $currentDate
            ]);
            $employee->update(['last_paid_date' => $currentDate]);
            return response()->json(['salary' => $salary],200);
        }
        else {
            return response()->json(['message' => 'unauthorized'],401);
        }
    }
    public function payCoachSalary(Request $request , $coachId){
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            $fields = $request->validate([
                'amount' => 'required|numeric',
                'bonus' => 'nullable|numeric',
                'discount' => 'nullable|numeric',
                'notes' => 'nullable|string',
            ]);
            $currentDate = Carbon::now()->format('Y-m-d');
            $coach = Coach::find($coachId);
            $coachLastPaidDate = null;
            if($coach->last_paid_date){
                $coachLastPaidDate = $coach->last_paid_date;
            }
            else{
                $coachLastPaidDate = $coach->created_at;
            }
            $salary = Salary::create([
                'coach_id' => $coachId,
                'amount' =>  $fields['amount'],
                'paid_date' => $currentDate,
                'hours_worked'=> $coach->hours_worked,
                'bonus'=> isset($fields['bonus']) ? $fields['bonus'] : 0,
                'discount' => isset($fields['discount']) ? $fields['discount'] : 0,
                'notes' => isset($fields['notes']) ? $fields['notes'] : null,
                'date_from' => $coachLastPaidDate,
            ]);
            $coach->update([
                'last_paid_date' => $currentDate,
                'hours_worked' => 0,
            ]);
            return response()->json(['salary' => $salary],200);
        }
        else {
            return response()->json(['message' => 'unauthorized'],401);
        }
    }
}
