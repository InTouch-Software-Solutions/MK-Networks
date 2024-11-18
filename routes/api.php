<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CartController;
use App\Http\Controllers\API\VendorController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\SimDataController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\CheckOutController;
use App\Http\Controllers\API\SalesmanController;
use App\Http\Controllers\API\WishlistController;
use App\Http\Controllers\API\RoutePlanController;
use App\Http\Controllers\API\SimAssignController;
use App\Http\Controllers\API\FreebieAssignmentController;
use App\Http\Controllers\API\RouteController;



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

    //Sim Assignment
    // Route::get('assignhistory', [SimAssignController::class, 'assignhistory']);
    Route::post('/admin/assign-sim', [SimAssignController::class, 'simAssignByAdmin']);
    Route::get('/admin/sim-assignments', [SimAssignController::class, 'getSimAssignmentsForAdmin']);

    Route::post('/salesman/assign-sim', [SimAssignController::class, 'simAssignBySalesman']);
    Route::get('/salesman/sim-assignments', [SimAssignController::class, 'getSimAssignmentsForSalesman']);
    
    //view sim assigned 
    Route::get('/salesman/sims', [SimAssignController::class, 'viewSalesmanSims']);
    Route::get('/vendors/sims', [SimAssignController::class, 'viewVendorSims']);



    Route::post('/vendors', [VendorController::class, 'store']);
    Route::get('/vendors', [VendorController::class, 'index']);
    Route::post('/vendors-update', [VendorController::class, 'update']);
    Route::post('/salesman', [SalesmanController::class, 'store']);
    Route::get('/salesman', [SalesmanController::class, 'index']);
    Route::delete('/vendors/{id}', [VendorController::class, 'destroy']);  
    Route::delete('/salesman/{id}', [SalesmanController::class, 'destroy']);
    Route::post('/vendors/upload-images', [VendorController::class, 'uploadImages'])->name('vendor.uploadImages');


    //Route plan routes
    Route::post('/routes/upload', [RoutePlanController::class, 'uploadroute'])->name('routes.upload');
    Route::get('/routes', [RoutePlanController::class, 'showroute'])->name('routes.show');
    Route::post('/plannings', [RoutePlanController::class, 'store'])->name('plannings.store');
    Route::get('/plannings', [RoutePlanController::class, 'getAllPlannings'])->name('plannings.index');
    Route::get('/plannings/{id}', [RoutePlanController::class, 'getPlannings'])->name('plannings.show');
    Route::get('/salesman/plannings', [RoutePlanController::class, 'getSalesmanPlannings'])->name('plannings.getSalesmanPlannings');

    Route::get('/cities', [RouteController::class, 'getCities']);
    Route::get('/cities/{city}', [RouteController::class, 'getAreas']);
    Route::get('/areas/{area}', [RouteController::class, 'getShopsByArea']);
    Route::get('/shop/{id}', [RouteController::class, 'getShopById']);


    // Cart Routes
    Route::post('/carts', [CartController::class, 'store'])->name('carts.store');
    Route::get('/carts', [CartController::class, 'index'])->name('carts.index');
    Route::delete('/carts/{id}', [CartController::class, 'destroy'])->name('carts.destroy');

    // Wishlist routes
    Route::post('/wishlists', [WishlistController::class, 'store'])->name('wishlists.store');
    Route::get('/wishlists', [WishlistController::class, 'index'])->name('wishlists.index');
    Route::delete('/wishlists/{id}', [WishlistController::class, 'destroy'])->name('wishlists.destroy');


    // Checkout routes
    Route::post('/checkout', [CheckOutController::class, 'store'])->name('checkout.store');
    Route::get('/checkout', [CheckOutController::class, 'index'])->name('checkout.index');



    // Category Routes- for accessories categories 
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{id}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy'])->name('categories.destroy');


    // Product Routes- accessories
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::put('/products/{id}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{id}', [ProductController::class, 'destroy'])->name('products.destroy');
    Route::get('/products/{id}', [ProductController::class, 'show'])->name('products.show');



    //freebie assignment
    Route::get('/freebie-assignments', [FreebieAssignmentController::class, 'index'])->name('freebies.index');
    Route::post('/freebie-assignments', [FreebieAssignmentController::class, 'store'])->name('freebies.store');
    Route::post('/salesman/assign-product', [FreebieAssignmentController::class, 'assignToVendor']);
    Route::get('/salesman/inventory', [FreebieAssignmentController::class, 'salesmanInventory']);
    Route::get('/vendor/inventory', [FreebieAssignmentController::class, 'vendorInventory']);














});