<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Coach;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\Installment;
use App\Models\Product;
use App\Models\Salary;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function getInsights(Request $request) {
        $customersCount = Customer::count();
        $productsCount = Product::count();
        $maleCustomersCount = Customer::where('gender' , 'male')->count();
        $femaleCustomersCount = Customer::where('gender' , 'female')->count();
        $activeCustomers = Customer::whereHas('subscriptions', function ($query) {
            $query->where('state', 'active');
        })->count();
        $activeSubscriptions = Subscription::where('state' , 'active')->count();
        $inactiveSubscriptions = Subscription::where('state' , 'inactive')->count();
        $frozenSubscriptions = Subscription::where('state' , 'frozen')->count();
        $unpaidInstallments = Installment::where('paid' , 'false')->count();
        $coachesCount = Coach::count();
        $employeesCount = Employee::count();
        $branchesCount = Branch::count();
        return response()->json(['customersCount'=> $customersCount, 'activeCustomers'=> $activeCustomers, 'activeSubscriptions' => $activeSubscriptions ,
        'frozenSubscriptions' => $frozenSubscriptions , 'unpaidInstallments' => $unpaidInstallments , 'inactiveSubscriptions' => $inactiveSubscriptions ,
         'coachesCount' => $coachesCount , 'femaleCustomersCount' => $femaleCustomersCount , 'maleCustomersCount' => $maleCustomersCount , 'productsCount' => $productsCount , 'employeesCount' => $employeesCount , 'branchesCount' => $branchesCount ],200);
    }
    public function annualProfitsChart(Request $request) {
        // Initialize an array to store the monthly revenues and expenses
        $monthlyData = [];
        // Define an array of month names
        $monthNames = [
            1 => 'January',
            2 => 'February',
            3 => 'March',
            4 => 'April',
            5 => 'May',
            6 => 'June',
            7 => 'July',
            8 => 'August',
            9 => 'September',
            10 => 'October',
            11 => 'November',
            12 => 'December',
        ];
        // Get the current year
        $currentYear = Carbon::now()->year;

        // Loop through each month in the year
        for ($month = 1; $month <= 12; $month++) {
            // Calculate the start and end dates of the month
            $startDate = Carbon::createFromDate($currentYear, $month, 1)->startOfMonth();
            $endDate = Carbon::createFromDate($currentYear, $month, 1)->endOfMonth();

            // Retrieve the revenues for the month
            $cashSubscriptions = Subscription::where('subscription_type', 'cash')->whereBetween('subscription_date', [$startDate, $endDate])->get(['price','sale',]);
            $installments = Installment::where('paid', true)->whereBetween('updated_at', [$startDate, $endDate])->get();

            // Calculate the sum of cash subscription prices
            $cashRevenue = $cashSubscriptions->sum(function ($subscription) {
                return $subscription->price - ($subscription->sale ?? 0);
            });
            // Calculate the sum of installment amounts
            $installmentsRevenue = $installments->sum('amount');

            // Calculate the total revenue (cash + installments)
            $totalRevenue = $cashRevenue + $installmentsRevenue;

            // Retrieve the expenses for the month
            $constantExpenses = Expense::where('expense_type' , 'constant')->where('created_at' , '<' , $endDate)->get();
            // $constantExpenses = Expense::where('expense_type' , 'constant')->get();
            $variableExpenses = Expense::where('expense_type' , 'variable')->whereBetween('created_at',[$startDate, $endDate])->get();
            $salaries = Salary::whereBetween('paid_date',[$startDate, $endDate])->get();

            // Calculate the sum of constant expenses amounts
            $constantExpensesTotal = $constantExpenses->sum('expense_cost');
            // Calculate the sum of variable expenses amounts
            $variableExpensesTotal = $variableExpenses->sum('expense_cost');
            // Calculate the sum of variable expenses amounts
            $salariesTotal = $salaries->sum('amount');

            // Calculate the total expenses
            $totalExpense = $constantExpensesTotal + $variableExpensesTotal + $salariesTotal;

            $monthName = $monthNames[$month];
            // Store the monthly revenues and expenses in the array
            $monthlyData[$monthName] = [
                'revenue' => $totalRevenue,
                'expense' => $totalExpense,
            ];
        }

        // Return the monthly revenues and expenses data
        return response()->json(['annualProfits' => $monthlyData],200);
            }
}
