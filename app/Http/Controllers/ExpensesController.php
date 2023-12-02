<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Order;
use App\Models\Salary;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ExpensesController extends Controller
{
    public function createExpense(Request $request){
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            $fields = $request->validate([
                'expense_name' => 'required|string',
                'expense_cost' => 'required|numeric',
                'expense_type' => 'nullable|string',
                'branch_id' => 'required|numeric',
                'automatic_payment_date' => 'nullable|numeric',
            ]);
            $expense = Expense::create($fields);
            return response()->json(['expense' => $expense],200);
        }
        else {
            return response()->json(['message' => 'unauthorized'],401);
        }
    }
    public function getExpense(Request $request , $expenseId){
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            $expense = Expense::find($expenseId);
            return response()->json(['expense' => $expense],200);
        }
        else {
            return response()->json(['message' => 'unauthorized'],401);
        }
    }
    public function deleteExpense(Request $request , $expenseId){
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            $expense = Expense::find($expenseId);
            $expense->delete();
            return response()->json(['message' => 'deleted successfully'],200);
        }
        else {
            return response()->json(['message' => 'unauthorized'],401);
        }
    }
    public function updateExpense(Request $request , $expenseId){
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            $fields = $request->validate([
                'expense_name' => 'required|string',
                'expense_cost' => 'required|numeric',
                'expense_type' => 'nullable|string',
                'branch_id' => 'required|numeric',
                'automatic_payment_date' => 'nullable|numeric',
            ]);
            $expense = Expense::find($expenseId);
            $expense->update($fields);
            return response()->json(['expense' => $expense],200);
        }
        else {
            return response()->json(['message' => 'unauthorized'],401);
        }
    }
    public function getExpenses(Request $request , $branchId){
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            if($branchId == 0){
                $expenses = Expense::with('branch')->get();
                $salaries = Salary::with('coach' , 'employee' , 'branch')->get();
                return response()->json(['expenses' => $expenses , 'salaries' => $salaries],200);
            }
            $expenses = Expense::where('branch_id' , $branchId)->with('branch')->get();
            return response()->json(['expenses' => $expenses],200);
        }
        else {
            return response()->json(['message' => 'unauthorized'],401);
        }
    }
    public function getExpensesInterval(Request $request , $branchId){
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            $interval = $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date',
            ]);
            $start = Carbon::createFromFormat('m/d/Y', $interval['start_date']);
            $end = Carbon::createFromFormat('m/d/Y', $interval['end_date']);

            // Initialize an array to store the retrieved constant expenses
            $constantExpenses = [];
            // Loop through each day within the interval
            while ($start <= $end) {
                // Get the day of the month
                $dayOfMonth = $start->day;

                // Retrieve the expenses for the specific automatic payment day
                if($branchId == 0){
                    $matchingExpenses = Expense::where('automatic_payment_date', $dayOfMonth)->get();
                }
                else{
                    $matchingExpenses = Expense::where('branch_id' , $branchId)->where('automatic_payment_date', $dayOfMonth)->get();
                }

                // Add the matching expenses to the array
                $constantExpenses = array_merge($constantExpenses, $matchingExpenses->toArray());

                // Increment the day by one
                $start->addDay();

                // Check if the incremented day exceeds the end of the current month
                if ($start->day > $start->daysInMonth) {
                    $start->addMonthNoOverflow()->startOfMonth();
                }
            }
            $startDateTime = Carbon::createFromFormat('m/d/Y', $interval['start_date'])->startOfDay();
            $endDateTime = Carbon::createFromFormat('m/d/Y', $interval['end_date'])->endOfDay();
            if($branchId == 0){
                $variableExpenses = Expense::where('expense_type' , 'variable')->whereBetween('created_at',[$startDateTime, $endDateTime])->get();
                $salaries = Salary::whereBetween('paid_date',[$startDateTime, $endDateTime])->get();
                return response()->json(['variableExpenses' => $variableExpenses , 'constantExpenses' => $constantExpenses , 'salaries' => $salaries],200);
            }
            $variableExpenses = Expense::where('expense_type' , 'variable')->where('branch_id' , $branchId)->whereBetween('created_at',[$startDateTime , $endDateTime])->get();
            return response()->json(['variableExpenses' => $variableExpenses , 'constantExpenses' => $constantExpenses],200);
        }
        else {
            return response()->json(['message' => 'unauthorized'],401);
        }
    }
    public function getProductsOrders(Request $request){
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            $productsOrders = Order::with('branch' , 'product')->get();
            return response()->json(['orders' => $productsOrders],200);
        }
        else {
            return response()->json(['message' => 'unauthorized'],401);
        }
    }
}
