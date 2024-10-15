<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CartController;
use App\Http\Controllers\API\CheckOutController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\RoutePlanController;
use App\Http\Controllers\API\SimAssignController;
use App\Http\Controllers\API\SimDataController;
use App\Http\Controllers\API\VendorController;
use App\Http\Controllers\API\WishlistController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\FreebieAssignmentController;


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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::get('user', [AuthController::class, 'userDetails']);
    Route::get('logout', [AuthController::class, 'logout']);

    Route::post('/upload_provider_excel', [SimDataController::class, 'uploadProviderExcel']);
    Route::get('/showexcel', [SimDataController::class, 'showexcel']);
    Route::post('/uploadroute', [RoutePlanController::class, 'uploadroute']);
    Route::get('/showroute', [RoutePlanController::class, 'showroute']);
    Route::get('assignhistory', [SimAssignController::class, 'assignhistory']);

    Route::post('/admin/assign-sim', [SimAssignController::class, 'simAssignByAdmin']);
    Route::post('/salesman/assign-sim', [SimAssignController::class, 'simAssignBySalesman']);
    Route::get('/admin/sim-assignments', [SimAssignController::class, 'getSimAssignmentsForAdmin']);
    Route::get('/salesperson/sim-assignments', [SimAssignController::class, 'getSimAssignmentsForSalesperson']);


    Route::post('/store', [RoutePlanController::class, 'store']);
    Route::post('/createvendor', [VendorController::class, 'savevendor']);
    Route::get('/getvendor', [VendorController::class, 'viewvendor']);


    Route::get('/get_sales_executive', [VendorController::class, 'get_sales_executive']);
    Route::post('/create_sales_executive', [VendorController::class, 'create_sales_executive']);

    Route::get('shops', [RoutePlanController::class, 'getShopsByArea']);
    Route::get('plannings', [RoutePlanController::class, 'getPlannings']);
    Route::post('/assign-route', [RoutePlanController::class, 'assignroute']);

    

    Route::post('addcart', [CartController::class, 'addProductToCart']);
    Route::get('getcart', [CartController::class, 'viewCart']);
    Route::delete('cart/{id}', [CartController::class, 'deleteCart']);

    Route::post('addwishlist', [WishlistController::class, 'addProductToWishlist']);
    Route::get('getwishlist', [WishlistController::class, 'viewWishlist']);
    Route::delete('wishlist/{id}', [WishlistController::class, 'deleteWishlist']);

    Route::post('/checkout', [CheckOutController::class, 'saveorder']);
    Route::get('/getcheckout', [CheckOutController::class, 'viewCheckout']);

    Route::post('/assign-accessories', [AccessoriesController::class, 'accessories']);
    Route::post('/salesman/assign-accessories', [AccessoriesController::class, 'assigntosalesman']);
    Route::get('/getaccessories', [AccessoriesController::class, 'viewaccessories']);
    Route::get('/salesperson/accessories-assignments', [AccessoriesController::class, 'viewaccessories1']);
    Route::post('/createCategory', [CategoryController::class, 'createCategory']);
    Route::post('/updatecategory/{id}', [CategoryController::class, 'updateCategory']);
    Route::get('/deleteCategory/{id}', [CategoryController::class, 'deleteCategory']);
    
    Route::get('/getproduct', [ProductController::class, 'getproduct']);
    Route::get('/getCategory', [CategoryController::class, 'getCategory']);
    
    Route::post('/products', [ProductController::class, 'saveproduct']);
    Route::post('/update/{id}', [ProductController::class, 'Updateproduct']);
    Route::get('/deleteproduct/{id}', [ProductController::class, 'deleteProduct']);

    // Freebie routes

    Route::post('/assign-freebie', [FreebieAssignmentController::class, 'assignFreebie']);
    Route::get('/get-freebies/{id}', [FreebieAssignmentController::class, 'getAssignedFreebies'])->name('get-freebies');
    

    

});