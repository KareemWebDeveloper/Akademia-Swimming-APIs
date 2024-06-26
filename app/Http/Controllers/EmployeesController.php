<?php

namespace App\Http\Controllers;

use App\Models\Coach;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class EmployeesController extends Controller
{
    public function getEmployees(Request $request){
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            $employees = Employee::get();
            return response()->json(['employees' => $employees],200);
        }
        else{
            return response()->json(['message' => 'unauthorized'],401);
        }
    }
    public function getAllWorkers(Request $request){
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            $employees = Employee::get();
            $coaches = Coach::get();
            return response()->json(['employees' => $employees , 'coaches' => $coaches],200);
        }
        else{
            return response()->json(['message' => 'unauthorized'],401);
        }
    }
    public function getEmployee(Request $request , $employeeId){
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            $employee = Employee::with('branches' , 'roles')->find($employeeId);
            return response()->json(['employee' => $employee],200);
        }
        else{
            return response()->json(['message' => 'unauthorized'],401);
        }
    }
    public function attachBranchesForEmployee(Request $request , $employeeId){
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            $validatedData = $request->validate([
                'branchIds' => 'required',
            ]);
            $employee = Employee::find($employeeId);
            $employee->branches()->sync($validatedData['branchIds']);
            return response()->json(['employee' => $employee],200);
        }
        else{
            return response()->json(['message' => 'unauthorized'],401);
        }
    }
    public function createEmployee(Request $request){
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            $created_by = $user->name;
            $validatedData = $request->validate([
                'name' => 'required|string',
                'email' => 'required|email|unique:employees,email',
                'address' => 'required|string',
                'password' => 'required|string',
                'phone' => 'required|string|unique:employees,phone',
                'type' => 'nullable',
                'salary' => 'required|numeric',
            ]);
            $validatedData['created_by'] = $created_by;
            $validatedData['password'] = bcrypt($validatedData['password']);
            $employee = Employee::create($validatedData);
            // Attach the employee to branches
            if($request->input('branchIds')){
                $branchIds = $request->input('branchIds');
                $employee->branches()->attach($branchIds);
            }
            if($request->input('roleIds')){
                $roleIds = $request->input('roleIds');
                $employee->roles()->attach($roleIds);
            }
        return response()->json(['employee' => $employee],200);
        }
        else{
            return response()->json(['message' => 'unauthorized'],401);
        }
    }
    public function updateEmployee(Request $request , $employeeId){
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            $validatedData = $request->validate([
                'name' => 'required|string',
                'email'  => [
                    Rule::unique('employees', 'email')->ignore($employeeId)
                ],
                'address' => 'required|string',
                'phone' => [
                    Rule::unique('employees' , 'phone')->ignore($employeeId)
                ],
                'type' => 'nullable',
                'salary' => 'required|numeric',
                'advance_payment' => 'nullable|numeric',
                'salary_discount' => 'nullable|numeric',
            ]);
            if($request->input('password')){
                $request->validate([
                    'password' => 'required|string',
                ]);
                $validatedData['password'] = bcrypt($request->input('password'));
            }
            $employee = Employee::find($employeeId);
            $employee->update($validatedData);
            // Attach the employee to branches
            if($request->input('branchIds')){
                $branchIds = $request->input('branchIds');
                $employee->branches()->sync($branchIds);
            }
            if($request->input('roleIds')){
                $roleIds = $request->input('roleIds');
                $employee->roles()->sync($roleIds);
            }
        return response()->json(['employee' => $employee],200);

        }
        else{
            return response()->json(['message' => 'unauthorized'],401);
        }
    }
    public function updateEmployeeFinances(Request $request , $employeeId){
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            $validatedData = $request->validate([
                'advance_payment' => 'nullable',
                'salary_discount' => 'nullable',
            ]);
            $employee = Employee::find($employeeId);
            $employee->update($validatedData);
            return response()->json(['employee' => $employee],200);
        }
        else{
            return response()->json(['message' => 'unauthorized'],401);
        }
    }
    public function EmployeesBulkDelete(Request $request){
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            $validatedData = $request->validate([
                'employees_ids' => 'required|array',
            ]);
            $employeeIds = $validatedData['employees_ids']; // Array of IDs to be deleted
            foreach ($employeeIds as $id) {
                $employee = Employee::find($id); // Retrieve the Employee model instance
                // Detach the relationships
                $employee->branches()->detach();
                $employee->roles()->detach();
                $employee->salaries()->delete();
                // Delete the Employee model
                $employee->delete();
            }
            return response()->json(['message' => 'deleted successfully'],200);
        }
        else{
            return response()->json(['message' => 'unauthorized'],401);
        }
    }

    public function employeeAuthorize(Request $request){
        $employee = $request->user();
        if($employee){
            return response()->json(['employee'=> $employee],200);
        }
        else{
            return response()->json(['message' => 'unauthorized'],401);
        }
    }
}
