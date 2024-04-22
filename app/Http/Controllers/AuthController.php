<?php

namespace App\Http\Controllers;

use App\Models\Coach;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function adminRegister(Request $request){
        $fields = $request->validate([
            'username' => 'required|string',
            'email' => 'required|string|unique:users,email',
            'password' => 'required|string',
            'type' => 'required|string',
        ]);
        $user = User::create([
            'username' => $fields['username'],
            'email' => $fields['email'],
            'type' => $fields['type'],
            'password' => bcrypt($fields['password']),
        ]);
        $token = $user->createToken('UserToken')->plainTextToken;
        return response()->json(['Token' => $token , 'user' => $user]);
    }

    public function Logout(Request $request){
        auth()->user()->tokens()->delete();
        return response()->json(['message' => 'Logged out'],200);
    }

    public function adminLogin(Request $request){
        $fields = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);
        //check email and password
        $user = User::where('username',$fields['username'])->first();
        if(!$user || !Hash::check($fields['password'], $user->password)){
            return response()->json(['message'=> 'email or password not correct'],401);
        }
        $token = $user->createToken('UserToken')->plainTextToken;
        return response()->json(['Token' => $token , 'user' => $user]);
    }

    public function coachLogin(Request $request){
        $fields = $request->validate([
            'id' => 'required|numeric',
            'password' => 'required|string',
        ]);
        //check email and password
        $coach = Coach::find($fields['id']);
        if(!$coach || !Hash::check($fields['password'], $coach->password)){
            return response()->json(['message'=> 'id or password not correct'],401);
        }
        $token = $coach->createToken('CoachToken')->plainTextToken;
        return response()->json(['Token' => $token , 'coach' => $coach]);
    }
    public function employeeLogin(Request $request){
        $fields = $request->validate([
            'id' => 'required|numeric',
            'password' => 'required|string',
        ]);
        //check email and password
        $employee = Employee::find($fields['id']);
        if(!$employee || !Hash::check($fields['password'], $employee->password)){
            return response()->json(['message'=> 'id or password not correct'],401);
        }
        $token = $employee->createToken('EmployeeToken')->plainTextToken;
        return response()->json(['Token' => $token , 'employee' => $employee]);
    }

    public function customerLogin(Request $request){
        $fields = $request->validate([
            'id' => 'required|numeric',
        ]);
        //check email and password
        $customer = Customer::find($fields['id']);
        if(!$customer){
            return response()->json(['message'=> 'id is not correct'],401);
        }
        $token = $customer->createToken('CustomerToken')->plainTextToken;
        return response()->json(['Token' => $token , 'customer' => $customer]);
    }
    public function customerAuthorize(Request $request){
        $user = $request->user();
        if ($user instanceof Customer) {
            if($user){
                $activeSubscriptions = $user->activeSubscriptions()->with('coach', 'branch', 'trainingSchedules')
                ->get();
                $customerLevel = $user->level;
                $customerSublevel = $user->sublevel()->with('checkpoints')->get();
                return response()->json(['customer' => $user , 'subscriptions' => $activeSubscriptions , 'level' => $customerLevel,
                'sublevel' => $customerSublevel , 'installments' => $user->unpaidInstallments()->get() ]);
            }
        }
        else{
            return response()->json(['message'=> 'unauthorized'],401);
        }
    }
    public function userAuthorize(Request $request){
        $user = $request->user();
        if($user){
            return response()->json(['authorized' => true]);
        }
        else{
            return response()->json(['message'=> 'unauthorized'],401);
        }
    }
    public function coachAuthorize(Request $request){
        $user = $request->user();
        if ($user instanceof Coach) {
            if($user){
                $activeSubscriptions = $user->activeSubscriptions()->with('branch', 'trainingSchedules')
                ->get(['category_name' , 'branch_id' , 'id']);
                $attendances = $user->attendances()->with('branch','category')->get();
                return response()->json(['coach' => $user , 'subscriptions' => $activeSubscriptions ,
                'salaries' => $user->salaries , 'attendances' => $attendances]);
            }
        }
        else{
            return response()->json(['message'=> 'unauthorized'],401);
        }
    }
    public function employeeAuthorize(Request $request){
        $user = $request->user();
        if ($user instanceof Employee) {
            if($user){
                return response()->json(['employee' => $user , 'permissions' => $user->roles()->get(['role_name'])]);
            }
        }
        else{
            return response()->json(['message'=> 'unauthorized'],401);
        }
    }

    public function adminAuthorize(Request $request){
        $user = $request->user();
        if($user->type == 'admin'){
            return response()->json(['user'=> $user],200);
        }
        else{
            return response()->json(['message'=> 'unauthorized'],401);
        }
    }

    public function userUpdate(Request $request){
        $user = $request->user();
        if ($user instanceof Employee) {
            $fields = $request->validate([
                'name' => 'required|string',
                'email' => [
                    Rule::unique('employees')->ignore($user->id)
                ],
                'phone' => 'required|string',
            ]);
            if($user){
                $user->update($fields);
            }
        }
        else{
            $fields = $request->validate([
                'name' => 'required|string',
                'email' => [
                    Rule::unique('employees')->ignore($user->id)
                ],
                'username' => 'required|string',
            ]);
            if($user){
                $user->update($fields);
            }
        }
        return response()->json(['user'=> $user],200);
    }



    public function getUser(Request $request){
        $user = $request->user();
        return response()->json(['user'=> $user],200);
    }

    public function getUsers(Request $request){
        $user = $request->user();
        if($user->type == 'admin'){
            $users = User::get();
            return response()->json(['users'=> $users],200);
        }
        else{
            return response()->json(['message'=> 'unauthorized'],401);
        }
    }

    public function updateUserRole(Request $request){
        $user = $request->user();
        if($user->type == 'admin'){
            $request->validate([
                'id' => 'required',
                'type' => 'required|string',
            ]);
            $targetUser = User::find($request->id);
            $targetUser->update([
                'type' => $request->type
            ]);
            return response()->json(['message'=> 'user role updated successfully'],200);
        }
        else{
            return response()->json(['message'=> 'unauthorized'],401);
        }
    }
    public function userProfile(Request $request){
        $user = $request->user();
        $userDetails = $user->userDetails;
        return response()->json(['userDetails' => $userDetails , 'user' => $user],200);
    }

    public function changePw(Request $request){
        $user = $request->user();
        $current_pw = $request->current_password;
        if(Hash::check($current_pw, $user->password)){
            $fields = $request->validate([
                'new_password' => 'required',
            ]);
            $user->update([
                'password' => bcrypt($fields['new_password']),
            ]);
            return response()->json(['message'=> 'password changed successfully'],200);
        }
        else{
            return response()->json(['message'=> 'Current Password Incorrect'],401);
        }
    }

}
