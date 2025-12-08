<?php

use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\MealController;
use App\Http\Controllers\MealPreparationController;

use App\Http\Controllers\UserController;
use App\Http\Controllers\WeeklyMenuController;
use App\Http\Controllers\Api\SubscriptionController;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PlanController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RewardController;
use App\Http\Controllers\UserNutritionSummaryController;
use App\Http\Controllers\MembershipPlanController;
use App\Http\Controllers\MembershipController;
use App\Http\Controllers\MembershipTransactionController;

//  Public routes



Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);
Route::post('forget-password', [AuthController::class, 'forgetPassword']);
// Plan
Route::get('plans', [PlanController::class, 'index']);
// Public Category Routes
Route::get('categories', [CategoryController::class, 'index']);
Route::get('categories/active', [CategoryController::class, 'active']);
Route::get('categories/slug/{slug}', [CategoryController::class, 'getBySlug']);
Route::get('categories/{id}', [CategoryController::class, 'show']);

// Public Meal Routes
Route::get('meals', [MealController::class, 'index']);
Route::get('meals/categories', [MealController::class, 'getCategories']);
Route::get('meals/featured', [MealController::class, 'featured']);
Route::get('meals/diet', [MealController::class, 'getByDiet']);
Route::get('meals/slug/{slug}', [MealController::class, 'getBySlug']);
Route::get('meals/{id}', [MealController::class, 'show']);

// Public Weekly Menu Routes
Route::get('weekly-menus', [WeeklyMenuController::class, 'index']);
Route::get('weekly-menus/current', [WeeklyMenuController::class, 'getCurrentWeek']);
Route::get('weekly-menus/upcoming', [WeeklyMenuController::class, 'getUpcoming']);
Route::get('weekly-menus/{id}', [WeeklyMenuController::class, 'show']);
Route::get('weekly-menus/{id}/meals', [WeeklyMenuController::class, 'getMeals']);

// Public Order Routes
Route::post('orders', [OrderController::class, 'store']);

// Public Membership Plan Routes
Route::get('membership-plans', [MembershipPlanController::class, 'index']);
Route::get('membership-plans/popular', [MembershipPlanController::class, 'popular']);
Route::get('membership-plans/{id}', [MembershipPlanController::class, 'show']);

Route::group(['middleware' => ['auth:sanctum']], function (): void {

    // User rewards (authenticated user)
    Route::get('rewards', [RewardController::class, 'myRewards']);

    // Subscription Routes
    Route::get('subscriptions', [SubscriptionController::class, 'index']);
    Route::get('subscriptions/stats', [SubscriptionController::class, 'stats']);
    Route::get('subscriptions/{subscription}', [SubscriptionController::class, 'show']);
    Route::put('subscriptions/{subscription}', [SubscriptionController::class, 'update']);
    Route::post('subscriptions/{subscription}/pause', [SubscriptionController::class, 'pause']);
    Route::post('subscriptions/{subscription}/resume', [SubscriptionController::class, 'resume']);
    Route::post('subscriptions/{subscription}/cancel', [SubscriptionController::class, 'cancel']);
    Route::post('subscriptions/{subscription}/reactivate', [SubscriptionController::class, 'reactivate']);
    Route::post('subscriptions/{subscription}/toggle-auto-renew', [SubscriptionController::class, 'toggleAutoRenew']);


    // Dashboard stats (authenticated user)
    Route::get('dashboard', [DashboardController::class, 'stats']);

    // User nutrition summary (authenticated user)
    Route::get('nutrition-summary', [UserNutritionSummaryController::class, 'index']);

    // Membership Routes (User)
    Route::get('memberships/current/{userId}', [MembershipController::class, 'getCurrentMembership']);
    Route::get('memberships/{id}', [MembershipController::class, 'show']);
    Route::post('memberships', [MembershipController::class, 'store']);
    Route::post('memberships/{id}/cancel', [MembershipController::class, 'cancel']);
    Route::post('memberships/{id}/freeze', [MembershipController::class, 'freeze']);
    Route::post('memberships/{id}/unfreeze', [MembershipController::class, 'unfreeze']);

    // Membership Transaction Routes (User)
    Route::get('membership-transactions/user/{userId}', [MembershipTransactionController::class, 'getByUser']);
    Route::get('membership-transactions/membership/{membershipId}', [MembershipTransactionController::class, 'getByMembership']);
    Route::get('membership-transactions/{id}', [MembershipTransactionController::class, 'show']);



    Route::get('total-points-earned', [AuthController::class, 'TotalPointsEarned']);
    Route::post('logout', [AuthController::class, 'logout']);

    // Meal Preparation Routes (Admin/Manager/Chef)
    Route::get('meal-preparations', [MealPreparationController::class, 'index']);
    Route::put('meal-preparations/{id}/status', [MealPreparationController::class, 'updateStatus']);

    // Protected Category Routes (Admin/Manager only)
    Route::post('categories', [CategoryController::class, 'store']);
    Route::put('categories/{id}', [CategoryController::class, 'update']);
    Route::delete('categories/{id}', [CategoryController::class, 'destroy']);
    Route::post('categories/upload-image', [CategoryController::class, 'uploadImage']);
    Route::delete('categories/delete-image', [CategoryController::class, 'deleteImage']);

    // Protected Meal Routes (Admin/Manager only)
    Route::post('meals', [MealController::class, 'store']);
    Route::put('meals/{id}', [MealController::class, 'update']);
    Route::delete('meals/{id}', [MealController::class, 'destroy']);
    Route::post('meals/{id}/restore', [MealController::class, 'restore']);
    Route::delete('meals/{id}/force', [MealController::class, 'forceDelete']);

    // Image Upload Routes
    Route::post('meals/upload-image', [MealController::class, 'uploadImage']);
    Route::post('meals/upload-gallery', [MealController::class, 'uploadGalleryImages']);
    Route::delete('meals/delete-image', [MealController::class, 'deleteImage']);

    // Protected Weekly Menu Routes (Admin/Manager only)
    Route::post('weekly-menus', [WeeklyMenuController::class, 'store']);
    Route::put('weekly-menus/{id}', [WeeklyMenuController::class, 'update']);
    Route::delete('weekly-menus/{id}', [WeeklyMenuController::class, 'destroy']);
    Route::post('weekly-menus/{id}/meals', [WeeklyMenuController::class, 'attachMeals']);
    Route::delete('weekly-menus/{id}/meals/{mealId}', [WeeklyMenuController::class, 'detachMeal']);
    Route::post('weekly-menus/{id}/publish', [WeeklyMenuController::class, 'publish']);

    // User Management Routes (Admin only)
    Route::get('users', [UserController::class, 'index']);
    Route::post('users', [UserController::class, 'store']);
    Route::get('users/{user}', [UserController::class, 'show']);
    Route::put('users/{user}', [UserController::class, 'update']);
    Route::delete('users/{user}', [UserController::class, 'destroy']);

    // Order Management Routes (Admin only)
    Route::get('orders', [OrderController::class, 'index']);
    Route::get('orders/{order}', [OrderController::class, 'show']);
    Route::put('orders/{order}', [OrderController::class, 'update']);
    Route::delete('orders/{order}', [OrderController::class, 'destroy']);

    Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus']);

    // Subscription Weekly Selection Routes

    // PLAN
    Route::post('plans', [PlanController::class, 'store']);
    Route::get('plans/{plan}', [PlanController::class, 'show']);
    Route::put('plans/{plan}', [PlanController::class, 'update']);
    Route::delete('plans/{plan}', [PlanController::class, 'destroy']);

    // Membership Plan Management Routes (Admin/Manager only)
    Route::post('membership-plans', [MembershipPlanController::class, 'store']);
    Route::put('membership-plans/{id}', [MembershipPlanController::class, 'update']);
    Route::delete('membership-plans/{id}', [MembershipPlanController::class, 'destroy']);
    Route::post('membership-plans/{id}/toggle-active', [MembershipPlanController::class, 'toggleActive']);

    // Membership Management Routes (Admin/Manager only)
    Route::get('memberships', [MembershipController::class, 'index']);
    Route::get('memberships/statistics', [MembershipController::class, 'statistics']);
    Route::post('memberships/{id}/activate', [MembershipController::class, 'activate']);

    // Membership Transaction Management Routes (Admin/Manager only)
    Route::get('membership-transactions', [MembershipTransactionController::class, 'index']);
    Route::get('membership-transactions/statistics', [MembershipTransactionController::class, 'statistics']);
    Route::post('membership-transactions', [MembershipTransactionController::class, 'store']);
    Route::post('membership-transactions/{id}/complete', [MembershipTransactionController::class, 'markCompleted']);
    Route::post('membership-transactions/{id}/fail', [MembershipTransactionController::class, 'markFailed']);
    Route::post('membership-transactions/{id}/refund', [MembershipTransactionController::class, 'refund']);
});
