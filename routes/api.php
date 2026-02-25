 <?php

    use App\Http\Controllers\Api\OrderController;
    use App\Http\Controllers\AuthController;
    use App\Http\Controllers\CategoryController;
    use App\Http\Controllers\MealController;
    use App\Http\Controllers\MealPreparationController;

    use App\Http\Controllers\UserController;
    use App\Http\Controllers\WeeklyMenuController;

    use Illuminate\Support\Facades\Route;
    use App\Http\Controllers\Api\PlanController;
    use App\Http\Controllers\DashboardController;
    use App\Http\Controllers\RewardController;
    use App\Http\Controllers\UserNutritionSummaryController;
    use App\Http\Controllers\MembershipPlanController;
    use App\Http\Controllers\MembershipController;
    use App\Http\Controllers\MembershipTransactionController;
    use App\Http\Controllers\DeliverySlotController;
    use App\Http\Controllers\SettingController;
    use App\Http\Controllers\MediaController;

    //  Public routes




    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('forgot-password', [AuthController::class, 'forgetPassword']);
    Route::post('forgot-password/reset', [AuthController::class, 'verifyCodeResetPassword']);

    // Media Upload
    Route::post('media/upload', [MediaController::class, 'uploadImage']);

    // Plan
    Route::get('plans', [PlanController::class, 'index']);
    // Public Category Routes
    Route::get('categories', [CategoryController::class, 'index']);


    // Public Team Partnership and Collaboration Routes
    Route::post('team-partnerships', [\App\Http\Controllers\TeamPartnershipController::class, 'store']);
    Route::post('collaborations', [\App\Http\Controllers\CollaborationController::class, 'store']);

    // Public Meal Routes
    Route::get('meals', [MealController::class, 'index']);


 

    // Public Order Routes
    Route::post('orders', [OrderController::class, 'store']);

    // Public Membership Plan Routes
    Route::get('membership-plans', [MembershipPlanController::class, 'index']);

    // Public Delivery Slot Routes
    Route::get('delivery-slots', [DeliverySlotController::class, 'index']);

    Route::get('settings', [SettingController::class, 'index']);
    // Protected routes
    Route::group(['middleware' => ['auth:sanctum']], function (): void {


        // Settings Management Routes (Admin only)
        Route::put('settings/{id}', [SettingController::class, 'update']);

        // Admin Team Partnership and Collaboration Routes
        Route::get('team-partnerships', [\App\Http\Controllers\TeamPartnershipController::class, 'index']);
        Route::get('team-partnerships/{id}', [\App\Http\Controllers\TeamPartnershipController::class, 'show']);
        Route::get('collaborations', [\App\Http\Controllers\CollaborationController::class, 'index']);
        Route::get('collaborations/{id}', [\App\Http\Controllers\CollaborationController::class, 'show']);

        // User rewards (authenticated user)
        Route::get('rewards', [RewardController::class, 'myRewards']);

        Route::get('meals_dashboard', [MealController::class, 'index2']);
        // Dashboard stats (authenticated user)
        Route::get('dashboard', [DashboardController::class, 'stats']);

        // User nutrition summary (authenticated user)
        Route::get('nutrition-summary', [UserNutritionSummaryController::class, 'index']);

        // Membership Routes (User)
        Route::get('memberships/{id}', [MembershipController::class, 'show']);
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
 
        // Protected Meal Routes (Admin/Manager only)
        Route::post('meals', [MealController::class, 'store']);
        Route::put('meals/{id}', [MealController::class, 'update']);
        Route::delete('meals/{id}', [MealController::class, 'destroy']);
 
        // Image Upload Routes
        Route::post('meals/upload-image', [MealController::class, 'uploadImage']);
        Route::post('meals/upload-gallery', [MealController::class, 'uploadGalleryImages']);
        Route::delete('meals/delete-image', [MealController::class, 'deleteImage']);

  
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

        // Delivery Slot Management Routes (Admin/Manager only)
        Route::post('delivery-slots', [DeliverySlotController::class, 'store']);
        Route::put('delivery-slots/{deliverySlot}', [DeliverySlotController::class, 'update']);
        Route::delete('delivery-slots/{deliverySlot}', [DeliverySlotController::class, 'destroy']);
        Route::post('delivery-slots/{deliverySlot}/toggle-active', [DeliverySlotController::class, 'toggleActive']);
    });
