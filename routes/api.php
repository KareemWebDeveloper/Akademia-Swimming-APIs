<?php

use App\Http\Controllers\AcademiesController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BranchesController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\CoachesController;
use App\Http\Controllers\CustomersController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeesController;
use App\Http\Controllers\LevelsController;
use App\Http\Controllers\OrdersController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\SalariesController;
use App\Http\Controllers\SubscriptionsController;
use App\Http\Controllers\ExpensesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Auth APIs
Route::post("/adminLogin" ,  [AuthController::class, 'adminLogin']);
Route::post("/coachLogin" ,  [AuthController::class, 'coachLogin']);
Route::post("/customerLogin" ,  [AuthController::class, 'customerLogin']);
Route::post("/employeeLogin" ,  [AuthController::class, 'employeeLogin']);
Route::post("/adminRegister" ,  [AuthController::class, 'adminRegister']);

Route::group(['middleware' => ['auth:sanctum']],function(){
    Route::post("/adminAuthorize" ,  [AuthController::class, 'adminAuthorize']);
    Route::post("/userAuthorize" ,  [AuthController::class, 'userAuthorize']);
    Route::post("/coachAuthorize" ,  [AuthController::class, 'coachAuthorize']);
    Route::post("/customerAuthorize" ,  [AuthController::class, 'customerAuthorize']);
    Route::post("/employeeAuthorize" ,  [AuthController::class, 'employeeAuthorize']);

    // Branches APIs
    Route::post("/branchBulkDelete" ,  [BranchesController::class, 'deleteBranch']);
    Route::post("/createBranch" ,  [BranchesController::class, 'createBranchWithCategories']);
    Route::put("/updateBranch/{id}" ,  [BranchesController::class, 'updateBranchWithCategories']);
    Route::get("/branches" ,  [BranchesController::class, 'getBranches']);
    Route::get("/branch/{id}" ,  [BranchesController::class, 'getBranchDetails']);
    Route::get("/branch/workingDays/{id}" ,  [BranchesController::class, 'getBranchWorkingDays']);

    // Coaches APIs
    Route::get("/coaches" ,  [CoachesController::class, 'getCoaches']);
    Route::get("/coachesOfBranch/{id}" ,  [CoachesController::class, 'getCoachesInBranch']); // retrieve the coaches that are associated to a specific branch
    Route::get("/coach/{id}" ,  [CoachesController::class, 'getCoach']);
    Route::post("/createCoach" ,  [CoachesController::class, 'createCoach']);
    Route::put("/coach/attachBranches/{id}" ,  [CoachesController::class, 'attachBranchesForCoach']); // attach a coach to specific branches
    Route::put("/updateCoach/{id}" ,  [CoachesController::class, 'updateCoach']);
    Route::post("/coachBulkDelete" ,  [CoachesController::class, 'coachesBulkDelete']);

    // Categories APIs
    Route::get("/categories" ,  [CategoriesController::class, 'getCategories']);
    Route::get("/categoriesOfBranch/{id}" ,  [CategoriesController::class, 'getCategoriesInBranch']); // retrieve the categories that are associated to a specific branch
    Route::post("/categoryBulkDelete" ,  [CategoriesController::class, 'deleteCategory']);
    Route::post("/createCategory" ,  [CategoriesController::class, 'createCategory']);
    Route::put("/updateCategory/{id}" ,  [CategoriesController::class, 'updateCategory']);

    // Employees APIs
    Route::get("/employees" ,  [EmployeesController::class, 'getEmployees']);
    Route::get("/employee/{id}" ,  [EmployeesController::class, 'getEmployee']);
    Route::put("/employee/attachBranches/{id}" ,  [EmployeesController::class, 'attachBranchesForEmployee']); // attach an employee to specific branches
    Route::post("/employeeBulkDelete" ,  [EmployeesController::class, 'EmployeesBulkDelete']);
    Route::post("/createEmployee" ,  [EmployeesController::class, 'createEmployee']);
    Route::put("/updateEmployee/{id}" ,  [EmployeesController::class, 'updateEmployee']);
    Route::get("/employeeAuthorize" ,  [EmployeesController::class, 'employeeAuthorize']);

    // Customers APIs
    Route::get("/customers" ,  [CustomersController::class, 'getCustomers']);
    Route::post("/validateCustomer" ,  [CustomersController::class, 'validateCustomer']);
    Route::get("/customerActiveSubscriptions/{id}" ,  [CustomersController::class, 'getCustomerActiveSubscriptions']);
    Route::get("/customer/private/{id}" ,  [CustomersController::class, 'getCustomerWithPrivateSubscription']);
    Route::get("/customer/{id}" ,  [CustomersController::class, 'getCustomer']);
    Route::post("/customerBulkDelete" ,  [CustomersController::class, 'CustomersBulkDelete']);
    Route::post("/createCustomer" ,  [CustomersController::class, 'createCustomer']);
    Route::put("/customerLevelUpdate/{id}" ,  [CustomersController::class, 'updateCustomerLevel']);
    Route::put("/payInstallment/{id}" ,  [CustomersController::class, 'payCustomerInstallment']); // pay a specific installment for a customer
    Route::put("/updateInstallment/{id}" ,  [CustomersController::class, 'updateCustomerInstallment']); // edit a specific installment for a customer
    Route::delete("/deleteInstallment/{id}" ,  [CustomersController::class, 'deleteCustomerInstallment']); // delete a specific installment for a customer
    Route::put("/updateCustomer/{id}" ,  [CustomersController::class, 'updateCustomer']);
    Route::get("/customerPenultimateSubscription/{customerId}" ,  [CustomersController::class, 'getCustomerPenultimateSubscription']);

    // Roles APIs
    Route::get("/roles" ,  [RolesController::class, 'getRoles']);
    Route::post("/createRole" ,  [RolesController::class, 'createRole']);

    // Subscriptions APIs
    Route::post("/createSubscription" ,  [SubscriptionsController::class, 'createNewSubscription']);
    Route::get("/customerSubcription/{id}" ,  [SubscriptionsController::class, 'getCustomerSubcription']);
    Route::put("/updateSubscription/{id}" ,  [SubscriptionsController::class, 'updateSubscription']);
    Route::post("/subscriptionCoaches/update" ,  [SubscriptionsController::class, 'updateSubscriptionsCoach']);
    Route::put("/freezeSubscription/{id}" ,  [SubscriptionsController::class, 'freezeSubscription']); // freezing a customer subscription
    Route::put("/freezeCancellation/{id}" ,  [SubscriptionsController::class, 'freezeCancellation']); // cancelling a customer subscription freeze
    Route::get("/privateSubscriptions" ,  [SubscriptionsController::class, 'getPrivateSubscriptions']); // cancelling a customer subscription freeze
    Route::post("/checkSubscriptionStatuses" ,  [SubscriptionsController::class, 'checkSubscriptionStatuses']); // used to check the subscriptions end date and ensures that if a subscription is expired update the status into "inactive"
    Route::post("/checkFreezeStatuses" ,  [SubscriptionsController::class, 'checkFreezeStatuses']); // used to check the subscriptions freeze end date and ensures that if the freeze is expired update the status into "active"

    // Products APIs
    Route::get("/products" ,  [ProductsController::class, 'getProducts']);
    Route::get("/product/{id}" ,  [ProductsController::class, 'getProduct']);
    Route::post("/createProduct" ,  [ProductsController::class, 'createProduct']);
    Route::post("/productsBulkDelete" ,  [ProductsController::class, 'ProductsBulkDelete']);
    Route::put("/updateProduct/{id}" ,  [ProductsController::class, 'updateProduct']);

    // Academies APIs
    Route::get("/academies" ,  [AcademiesController::class, 'getAcademies']);
    Route::get("/academiesOfBranch/{branchId}" ,  [AcademiesController::class, 'getAcademiesInBranch']);
    Route::post("/createAcademy" ,  [AcademiesController::class, 'createAcademy']);


    // Orders APIs
    Route::post("/createBuyingOrder" ,  [OrdersController::class, 'createBuyingOrder']); // updating the amount available of a specific product and add it to the orders table as a 'buy' order type
    Route::post("/createSellingOrder" ,  [OrdersController::class, 'createSellingOrder']); // create a selling order then update then update the product amount

    // Levels APIs
    Route::get("/levels" ,  [LevelsController::class, 'getLevels']);
    Route::get("/levelsTree" ,  [LevelsController::class, 'getLevelsTree']);
    Route::get("/level/{id}" ,  [LevelsController::class, 'getLevel']);
    Route::post("/createLevel" ,  [LevelsController::class, 'createLevel']);
    Route::post("/updateLevel/{id}" ,  [LevelsController::class, 'updateLevel']);
    Route::post("levelsBulkDelete" ,  [LevelsController::class, 'levelsBulkDelete']);

    // Attendance APIs
    Route::get("/coaches/active/{branch_id}" ,  [AttendanceController::class, 'getActiveCoachesByBranch']);
    Route::get("/customers/active/{branch_id}" ,  [AttendanceController::class, 'getActiveCustomersByBranch']);
    Route::post("/bulkAttendance" ,  [AttendanceController::class, 'bulkAttendance']);

    // Reports APIs
    Route::get("/revenues/{branchId}" ,  [ReportsController::class, 'getRevenues']);
    Route::post("/revenuesInInterval/{branchId}" ,  [ReportsController::class, 'getRevenuesInterval']);
    Route::get("/expenses/{branchId}" ,  [ExpensesController::class, 'getExpenses']);
    Route::get("/orders" ,  [ExpensesController::class, 'getProductsOrders']);

    // Expenses APIs
    Route::get("/expense/{branchId}" ,  [ExpensesController::class, 'getExpense']);
    Route::post("/expensesInInterval/{expenseId}" ,  [ExpensesController::class, 'getExpensesInterval']);
    Route::post("/createExpense" ,  [ExpensesController::class, 'createExpense']);
    Route::delete("/deleteExpense/{expenseId}" ,  [ExpensesController::class, 'deleteExpense']);
    Route::put("/updateExpense/{expenseId}" ,  [ExpensesController::class, 'updateExpense']);

    // Salaries APIs
    Route::get("/expectedSalaries" ,  [SalariesController::class, 'getCoachesAndEmployees']);
    Route::post("/payEmployeeSalary/{employeeId}" ,  [SalariesController::class, 'payEmployeeSalary']);
    Route::post("/payCoachSalary/{coachId}" ,  [SalariesController::class, 'payCoachSalary']);
    Route::get("/coach/attendances/{coachId}" ,  [SalariesController::class, 'getCoachAttendances']);
    // Generate Dashboard Charts
    Route::get("/annualProfitsChart" ,  [DashboardController::class, 'annualProfitsChart']);
    Route::get("/insights" ,  [DashboardController::class, 'getInsights']);

});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
